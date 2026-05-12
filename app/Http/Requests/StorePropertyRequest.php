<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StorePropertyRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        // Only landlords can create properties
        return $this->user() && $this->user()->role === 'landlord';
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'property_category_id' => 'required|exists:property_categories,id',
            'title' => 'required|string|max:255',
            'description' => 'required|string|min:10',
            'price' => 'required|numeric|min:0',
            'currency' => 'nullable|string|size:3|in:GHS,USD,EUR,GBP',
            'bedrooms' => 'required|integer|min:0|max:20',
            'bathrooms' => 'required|integer|min:0|max:20',
            'size_sqm' => 'nullable|numeric|min:0',
            'furnished' => 'boolean',
            'address' => 'required|string',
            'city' => 'required|string|max:255',
            'state' => 'nullable|string|max:255',
            'postal_code' => 'nullable|string|max:20',
            'country' => 'nullable|string|max:255',
            'latitude' => 'nullable|numeric|between:-90,90',
            'longitude' => 'nullable|numeric|between:-180,180',
            'amenities' => 'nullable|array',
            'amenities.*' => 'string|max:100',
            'images' => 'nullable|array|max:10',
            'images.*' => 'string|url',
            'availability_status' => 'nullable|in:available,occupied,maintenance,reserved',
            'available_from' => 'nullable|date|after_or_equal:today',
            'pets_allowed' => 'boolean',
            'smoking_allowed' => 'boolean',
            'lease_terms' => 'nullable|string',
            'security_deposit' => 'nullable|numeric|min:0',
            'minimum_lease_months' => 'nullable|integer|min:1|max:60',
            'additional_fees' => 'nullable|string',
        ];
    }

    /**
     * Get custom error messages for validation rules.
     */
    public function messages(): array
    {
        return [
            'property_category_id.required' => 'Please select a property category.',
            'property_category_id.exists' => 'Invalid property category selected.',
            'title.required' => 'Property title is required.',
            'description.required' => 'Property description is required.',
            'description.min' => 'Property description must be at least 10 characters.',
            'price.required' => 'Rental price is required.',
            'price.numeric' => 'Rental price must be a valid number.',
            'price.min' => 'Rental price cannot be negative.',
            'bedrooms.required' => 'Number of bedrooms is required.',
            'bathrooms.required' => 'Number of bathrooms is required.',
            'address.required' => 'Property address is required.',
            'city.required' => 'City is required.',
            'available_from.after_or_equal' => 'Available from date cannot be in the past.',
            'images.max' => 'Maximum 10 images allowed.',
            'images.*.url' => 'Each image must be a valid URL.',
        ];
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        $this->merge([
            'furnished' => $this->boolean('furnished'),
            'pets_allowed' => $this->boolean('pets_allowed'),
            'smoking_allowed' => $this->boolean('smoking_allowed'),
        ]);
    }
}
