<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use PragmaRX\Google2FA\Google2FA;
use Endroid\QrCode\QrCode;
use Endroid\QrCode\Writer\PngWriter;
use App\Models\User;
use Exception;

class TwoFactorController extends Controller
{
    protected $google2fa;

    public function __construct()
    {
        $this->google2fa = new Google2FA();
    }

    /**
     * Generate 2FA secret and QR code for setup
     */
    public function generateSecret(Request $request): JsonResponse
    {
        $user = $request->user();

        // Generate secret
        $secret = $this->google2fa->generateSecretKey();
        
        // Store secret temporarily (not confirmed yet)
        $user->two_factor_secret = encrypt($secret);
        $user->save();

        // Generate QR code
        $qrCodeUrl = $this->google2fa->getQRCodeUrl(
            config('app.name'),
            $user->email,
            $secret
        );

        // Try to generate QR code image, fallback if GD extension is not available
        $qrCodeImage = null;
        try {
            if (extension_loaded('gd')) {
                $qrCode = new QrCode($qrCodeUrl);
                $writer = new PngWriter();
                $result = $writer->write($qrCode);
                $qrCodeImage = 'data:image/png;base64,' . base64_encode($result->getString());
            } else {
                // Fallback: Use external QR code service
                $qrCodeImage = $this->generateFallbackQRCode($qrCodeUrl);
            }
        } catch (Exception $e) {
            // If QR code generation fails, provide fallback
            $qrCodeImage = $this->generateFallbackQRCode($qrCodeUrl);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'secret' => $secret,
                'qr_code' => $qrCodeImage,
                'qr_code_url' => $qrCodeUrl, // Include raw URL for manual setup
                'manual_entry_key' => $secret,
                'backup_codes' => null // Will be generated after confirmation
            ]
        ]);
    }

    /**
     * Confirm 2FA setup with verification code
     */
    public function confirmSetup(Request $request): JsonResponse
    {
        $request->validate([
            'code' => 'required|string|size:6'
        ]);

        $user = $request->user();
        
        if (!$user->two_factor_secret) {
            return response()->json([
                'success' => false,
                'message' => 'No 2FA secret found. Please generate a secret first.'
            ], 400);
        }

        $secret = decrypt($user->two_factor_secret);
        $valid = $this->google2fa->verifyKey($secret, $request->code);

        if (!$valid) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid verification code'
            ], 422);
        }

        // Confirm 2FA setup
        $user->two_factor_confirmed_at = now();
        $user->two_factor_enabled = true;
        $user->save();

        // Generate recovery codes
        $recoveryCodes = $user->generateRecoveryCodes();

        return response()->json([
            'success' => true,
            'message' => '2FA has been successfully enabled',
            'data' => [
                'recovery_codes' => $recoveryCodes,
                'enabled' => true
            ]
        ]);
    }

    /**
     * Verify 2FA code during login
     */
    public function verify(Request $request): JsonResponse
    {
        $request->validate([
            'code' => 'required|string',
            'recovery' => 'sometimes|boolean'
        ]);

        $user = $request->user();
        
        if (!$user->hasTwoFactorAuthentication()) {
            return response()->json([
                'success' => false,
                'message' => '2FA is not set up for this user'
            ], 400);
        }

        $isRecoveryCode = $request->boolean('recovery', false);

        if ($isRecoveryCode) {
            // Verify recovery code
            if ($user->useRecoveryCode($request->code)) {
                return response()->json([
                    'success' => true,
                    'message' => '2FA verified with recovery code'
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid recovery code'
                ], 422);
            }
        } else {
            // Verify TOTP code
            $secret = decrypt($user->two_factor_secret);
            $valid = $this->google2fa->verifyKey($secret, $request->code);

            if ($valid) {
                return response()->json([
                    'success' => true,
                    'message' => '2FA verified successfully'
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid verification code'
                ], 422);
            }
        }
    }

    /**
     * Disable 2FA for current user
     */
    public function disable(Request $request): JsonResponse
    {
        // Debug: Log what we're receiving
        Log::info('2FA Disable Request Data:', [
            'all_data' => $request->all(),
            'has_password' => $request->has('password'),
            'password_value' => $request->input('password'),
            'content_type' => $request->header('Content-Type'),
            'method' => $request->method()
        ]);

        $request->validate([
            'password' => 'required|string'
        ]);

        $user = $request->user();

        // Verify password
        if (!password_verify($request->password, $user->password)) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid password'
            ], 422);
        }

        $user->disableTwoFactorAuthentication();

        return response()->json([
            'success' => true,
            'message' => '2FA has been disabled'
        ]);
    }

    /**
     * Get 2FA status for current user
     */
    public function status(Request $request): JsonResponse
    {
        $user = $request->user();
        $recoveryCodes = $user->two_factor_recovery_codes ?? [];

        return response()->json([
            'success' => true,
            'data' => [
                'two_factor_enabled' => $user->hasTwoFactorAuthentication(),
                'two_factor_confirmed' => $user->hasTwoFactorAuthentication(),
                'two_factor_required' => $user->requiresTwoFactorAuthentication(),
                'two_factor_method' => $user->two_factor_method ?: 'app',
                'recovery_codes_generated' => !empty($recoveryCodes),
                'recovery_codes_remaining' => count($recoveryCodes)
            ]
        ]);
    }

    /**
     * Generate new recovery codes
     */
    public function generateRecoveryCodes(Request $request): JsonResponse
    {
        $request->validate([
            'password' => 'required|string'
        ]);

        $user = $request->user();

        // Verify password
        if (!password_verify($request->password, $user->password)) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid password'
            ], 422);
        }

        if (!$user->hasTwoFactorAuthentication()) {
            return response()->json([
                'success' => false,
                'message' => '2FA is not enabled'
            ], 400);
        }

        $recoveryCodes = $user->generateRecoveryCodes();

        return response()->json([
            'success' => true,
            'message' => 'New recovery codes generated',
            'data' => [
                'recovery_codes' => $recoveryCodes
            ]
        ]);
    }

    /**
     * Get current recovery codes for the user
     */
    public function getRecoveryCodes(Request $request): JsonResponse
    {
        $user = $request->user();

        if (!$user->hasTwoFactorAuthentication()) {
            return response()->json([
                'success' => false,
                'message' => '2FA is not enabled'
            ], 400);
        }

        $recoveryCodes = [];
        if ($user->two_factor_recovery_codes) {
            $recoveryCodes = $user->two_factor_recovery_codes; // Already decrypted by Laravel cast
        }

        return response()->json([
            'success' => true,
            'data' => [
                'recovery_codes' => $recoveryCodes,
                'codes_remaining' => count($recoveryCodes)
            ]
        ]);
    }

    /**
     * Regenerate recovery codes (alias for generateRecoveryCodes)
     */
    public function regenerateRecoveryCodes(Request $request): JsonResponse
    {
        // This is just an alias for generateRecoveryCodes to match the route name
        return $this->generateRecoveryCodes($request);
    }

    // Admin methods

    /**
     * Enable/disable 2FA requirement for a user (Admin only)
     */
    public function toggleRequirement(Request $request, User $user): JsonResponse
    {
        // Check if current user is admin
        if (!$request->user()->isAdmin()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized. Admin access required.'
            ], 403);
        }

        $request->validate([
            'required' => 'required|boolean'
        ]);

        $user->two_factor_required = $request->boolean('required');
        $user->save();

        return response()->json([
            'success' => true,
            'message' => '2FA requirement updated for user',
            'data' => [
                'user_id' => $user->id,
                'email' => $user->email,
                'two_factor_required' => $user->two_factor_required
            ]
        ]);
    }

    /**
     * Get 2FA status for all users (Admin only)
     */
    public function adminStatus(Request $request): JsonResponse
    {
        // Check if current user is admin
        if (!$request->user()->isAdmin()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized. Admin access required.'
            ], 403);
        }

        $users = User::select([
            'id', 'firstname', 'lastname', 'email', 'role',
            'two_factor_enabled', 'two_factor_required', 'two_factor_confirmed_at'
        ])->get()->map(function ($user) {
            return [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'role' => $user->role,
                'two_factor_enabled' => $user->two_factor_enabled,
                'two_factor_required' => $user->two_factor_required,
                'two_factor_setup' => $user->hasTwoFactorAuthentication(),
                'setup_date' => $user->two_factor_confirmed_at?->format('Y-m-d H:i:s')
            ];
        });

        return response()->json([
            'success' => true,
            'data' => $users
        ]);
    }

    /**
     * Force disable 2FA for a user (Admin only)
     */
    public function adminDisable(Request $request, User $user): JsonResponse
    {
        // Check if current user is admin
        if (!$request->user()->isAdmin()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized. Admin access required.'
            ], 403);
        }

        $user->disableTwoFactorAuthentication();

        return response()->json([
            'success' => true,
            'message' => '2FA has been disabled for user by admin',
            'data' => [
                'user_id' => $user->id,
                'email' => $user->email
            ]
        ]);
    }

    /**
     * Generate a fallback QR code when GD extension is not available
     */
    private function generateFallbackQRCode(string $qrCodeUrl): string
    {
        // Option 1: Use an external QR code service (for development/testing)
        $encodedUrl = urlencode($qrCodeUrl);
        $externalQRUrl = "https://api.qrserver.com/v1/create-qr-code/?size=200x200&data=" . $encodedUrl;
        
        // Return the external URL instead of base64 image
        // Frontend can display this directly or fetch and convert
        return $externalQRUrl;
        
        // Option 2: Return a placeholder message
        // return 'data:text/plain;base64,' . base64_encode('QR Code generation requires GD extension. Please scan this URL manually: ' . $qrCodeUrl);
    }
}
