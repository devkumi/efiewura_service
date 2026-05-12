<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class RegisterRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $rules = [
            'firstname' => 'required|string|max:255',
            'lastname' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
            'role' => 'required|in:admin,landlord,tenant,user',
        ];

        // Additional validation based on role
        if ($this->role === 'landlord') {
            $rules = array_merge($rules, [
                'business_name' => 'nullable|string|max:255',
                'business_registration_number' => 'nullable|string|max:255',
                'phone' => 'nullable|string|max:20',
                'address' => 'nullable|string',
                'city' => 'nullable|string|max:255',
                'state' => 'nullable|string|max:255',
                'postal_code' => 'nullable|string|max:20',
                'country' => 'nullable|string|max:255',
            ]);
        }

        if ($this->role === 'tenant') {
            $rules = array_merge($rules, [
                'phone' => 'nullable|string|max:20',
                'date_of_birth' => 'nullable|date|before:today',
                'gender' => 'nullable|in:male,female,other',
                'occupation' => 'nullable|string|max:255',
                'employer' => 'nullable|string|max:255',
                'monthly_income' => 'nullable|numeric|min:0',
                'current_address' => 'nullable|string',
                'emergency_contact_name' => 'nullable|string|max:255',
                'emergency_contact_phone' => 'nullable|string|max:20',
                'emergency_contact_relationship' => 'nullable|string|max:255',
            ]);
        }

        return $rules;
    }

    /**
     * Get custom error messages for validation rules.
     */
    public function messages(): array
    {
        return [
            'firstname.required' => 'First name is required.',
            'lastname.required' => 'Last name is required.',
            'role.required' => 'Please select a role.',
            'role.in' => 'Invalid role selected.',
            'password.confirmed' => 'Password confirmation does not match.',
            'email.unique' => 'This email address is already registered.',
            'date_of_birth.before' => 'Date of birth must be before today.',
        ];
    }
}
