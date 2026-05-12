<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\AdminSetting;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;

class AdminSettingsController extends Controller
{
    /**
     * Default settings structure
     */
    private function getDefaultSettings(): array
    {
        return [
            'general' => [
                'platform_name' => ['value' => 'Efiewura Property Management', 'type' => 'string'],
                'platform_description' => ['value' => 'Modern property rental management platform for Ghana', 'type' => 'string'],
                'support_email' => ['value' => 'support@efiewura.com', 'type' => 'string'],
                'support_phone' => ['value' => '+233 123 456 789', 'type' => 'string'],
                'timezone' => ['value' => 'Africa/Accra', 'type' => 'string'],
            ],
            'users' => [
                'allow_registration' => ['value' => true, 'type' => 'boolean'],
                'require_email_verification' => ['value' => true, 'type' => 'boolean'],
                'require_manual_approval' => ['value' => false, 'type' => 'boolean'],
                'password_min_length' => ['value' => 8, 'type' => 'integer'],
                'session_timeout' => ['value' => 120, 'type' => 'integer'],
                'require_special_chars' => ['value' => true, 'type' => 'boolean'],
            ],
            'properties' => [
                'auto_approve' => ['value' => false, 'type' => 'boolean'],
                'max_images' => ['value' => 20, 'type' => 'integer'],
                'max_file_size' => ['value' => 10, 'type' => 'integer'],
                'commission_rate' => ['value' => 10.0, 'type' => 'float'],
            ],
            'payments' => [
                'paypal' => ['value' => ['enabled' => true], 'type' => 'json'],
                'stripe' => ['value' => ['enabled' => true], 'type' => 'json'],
                'paystack' => ['value' => ['enabled' => true], 'type' => 'json'],
                'mobile_money' => ['value' => ['enabled' => true], 'type' => 'json'],
                'default_currency' => ['value' => 'GHS', 'type' => 'string'],
                'payment_timeout' => ['value' => 30, 'type' => 'integer'],
            ],
            'notifications' => [
                'email' => ['value' => [
                    'new_user' => true,
                    'new_property' => true,
                    'booking_confirm' => true
                ], 'type' => 'json'],
                'system' => ['value' => [
                    'low_disk_space' => true,
                    'failed_payments' => true
                ], 'type' => 'json'],
            ],
            'security' => [
                'require_2fa' => ['value' => false, 'type' => 'boolean'],
                'max_login_attempts' => ['value' => 5, 'type' => 'integer'],
                'lockout_duration' => ['value' => 30, 'type' => 'integer'],
                'data_retention_days' => ['value' => 365, 'type' => 'integer'],
            ],
        ];
    }

    /**
     * Get all settings
     */
    public function index(): JsonResponse
    {
        try {
            $settings = AdminSetting::getAllGrouped();
            
            // If no settings exist, initialize with defaults
            if (empty($settings)) {
                $this->initializeDefaultSettings();
                $settings = AdminSetting::getAllGrouped();
            }

            return response()->json([
                'success' => true,
                'data' => $settings
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve settings',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update settings (can be section-specific or all)
     */
    public function update(Request $request): JsonResponse
    {
        try {
            DB::beginTransaction();

            $data = $request->all();
            
            foreach ($data as $category => $settings) {
                if (!is_array($settings)) {
                    continue;
                }

                foreach ($settings as $key => $value) {
                    // Determine data type
                    $dataType = $this->getDataType($value);
                    
                    AdminSetting::setSetting($category, $key, $value, $dataType);
                }
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Settings updated successfully',
                'data' => AdminSetting::getAllGrouped()
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to update settings',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Reset to default settings
     */
    public function reset(): JsonResponse
    {
        try {
            DB::beginTransaction();

            // Clear all existing settings
            AdminSetting::truncate();

            // Initialize with defaults
            $this->initializeDefaultSettings();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Settings reset to defaults successfully',
                'data' => AdminSetting::getAllGrouped()
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to reset settings',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get specific category settings
     */
    public function getCategory(string $category): JsonResponse
    {
        try {
            $settings = AdminSetting::getByCategory($category);
            
            // If category doesn't exist, initialize with defaults for that category
            if (empty($settings)) {
                $defaults = $this->getDefaultSettings();
                if (isset($defaults[$category])) {
                    $this->initializeCategoryDefaults($category, $defaults[$category]);
                    $settings = AdminSetting::getByCategory($category);
                }
            }

            return response()->json([
                'success' => true,
                'data' => [$category => $settings]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => "Failed to retrieve $category settings",
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update specific category settings
     */
    public function updateCategory(Request $request, string $category): JsonResponse
    {
        try {
            DB::beginTransaction();

            $settings = $request->all();
            
            foreach ($settings as $key => $value) {
                $dataType = $this->getDataType($value);
                AdminSetting::setSetting($category, $key, $value, $dataType);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => ucfirst($category) . ' settings updated successfully',
                'data' => [$category => AdminSetting::getByCategory($category)]
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            
            return response()->json([
                'success' => false,
                'message' => "Failed to update $category settings",
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Initialize default settings
     */
    private function initializeDefaultSettings(): void
    {
        $defaults = $this->getDefaultSettings();
        
        foreach ($defaults as $category => $settings) {
            $this->initializeCategoryDefaults($category, $settings);
        }
    }

    /**
     * Initialize defaults for a specific category
     */
    private function initializeCategoryDefaults(string $category, array $settings): void
    {
        foreach ($settings as $key => $config) {
            AdminSetting::setSetting($category, $key, $config['value'], $config['type']);
        }
    }

    /**
     * Determine data type from value
     */
    private function getDataType($value): string
    {
        if (is_bool($value)) {
            return 'boolean';
        }
        
        if (is_int($value)) {
            return 'integer';
        }
        
        if (is_float($value)) {
            return 'float';
        }
        
        if (is_array($value) || is_object($value)) {
            return 'json';
        }
        
        return 'string';
    }
}
