<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Landlord;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class LandlordController extends Controller
{
    /**
     * Get landlord settings
     */
    public function getSettings()
    {
        try {
            $user = Auth::user();
            $landlord = Landlord::where('user_id', $user->id)->first();
            
            if (!$landlord) {
                return response()->json([
                    'success' => false,
                    'message' => 'Landlord profile not found'
                ], 404);
            }

            return response()->json([
                'success' => true,
                'message' => 'Landlord settings retrieved successfully',
                'data' => [
                    'business_name' => $landlord->business_name,
                    'phone' => $landlord->phone,
                    'address' => $landlord->address,
                    'city' => $landlord->city,
                    'state' => $landlord->state,
                    'country' => $landlord->country,
                    'commission_rate' => $landlord->commission_rate,
                    'overdue_release_days' => $landlord->overdue_release_days,
                    'status' => $landlord->status,
                    'verified' => $landlord->verified,
                ]
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve landlord settings',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update landlord settings
     */
    public function updateSettings(Request $request)
    {
        try {
            $user = Auth::user();
            $landlord = Landlord::where('user_id', $user->id)->first();
            
            if (!$landlord) {
                return response()->json([
                    'success' => false,
                    'message' => 'Landlord profile not found'
                ], 404);
            }

            $validator = validator($request->all(), [
                'business_name' => 'sometimes|string|max:255',
                'phone' => 'sometimes|string|max:20',
                'address' => 'sometimes|string|max:500',
                'city' => 'sometimes|string|max:100',
                'state' => 'sometimes|string|max:100',
                'country' => 'sometimes|string|max:100',
                'commission_rate' => 'sometimes|numeric|min:0|max:100',
                'overdue_release_days' => 'sometimes|integer|min:7|max:365',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            $validatedData = $validator->validated();
            
            // Update only provided fields
            $landlord->update($validatedData);

            return response()->json([
                'success' => true,
                'message' => 'Landlord settings updated successfully',
                'data' => [
                    'business_name' => $landlord->business_name,
                    'phone' => $landlord->phone,
                    'address' => $landlord->address,
                    'city' => $landlord->city,
                    'state' => $landlord->state,
                    'country' => $landlord->country,
                    'commission_rate' => $landlord->commission_rate,
                    'overdue_release_days' => $landlord->overdue_release_days,
                    'status' => $landlord->status,
                    'verified' => $landlord->verified,
                ]
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update landlord settings',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
