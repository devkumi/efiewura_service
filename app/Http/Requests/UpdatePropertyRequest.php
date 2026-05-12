<?php

namespace App\Http\Requests;

use App\Models\Property;
use Illuminate\Foundation\Http\FormRequest;

class UpdatePropertyRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        // Only the landlord who owns the property can update it
        $property = $this->route('property');
        return $this->user() && 
               $this->user()->role === 'landlord' && 
               $property && 
               $property->landlord_id === $this->user()->landlord->id;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'property_category_id' => 'sometimes|exists:property_categories,id',
            'title' => 'sometimes|string|max:255',
            'description' => 'sometimes|string|min:10',
            'price' => 'sometimes|numeric|min:0',
            'currency' => 'sometimes|string|size:3|in:GHS,USD,EUR,GBP',
            'bedrooms' => 'sometimes|integer|min:0|max:20',
            'bathrooms' => 'sometimes|integer|min:0|max:20',
            'size_sqm' => 'nullable|numeric|min:0',
            'furnished' => 'sometimes|boolean',
            'address' => 'sometimes|string',
            'city' => 'sometimes|string|max:255',
            'state' => 'nullable|string|max:255',
            'postal_code' => 'nullable|string|max:20',
            'country' => 'nullable|string|max:255',
            'latitude' => 'nullable|numeric|between:-90,90',
            'longitude' => 'nullable|numeric|between:-180,180',
            'amenities' => 'nullable|array',
            'amenities.*' => 'string|max:100',
            'images' => 'nullable|array|max:10',
            'images.*' => 'string|url',
            'availability_status' => 'sometimes|in:available,occupied,maintenance,reserved',
            'available_from' => 'nullable|date|after_or_equal:today',
            'pets_allowed' => 'sometimes|boolean',
            'smoking_allowed' => 'sometimes|boolean',
            'lease_terms' => 'nullable|string',
            'security_deposit' => 'nullable|numeric|min:0',
            'minimum_lease_months' => 'nullable|integer|min:1|max:60',
            'additional_fees' => 'nullable|string',
            'is_active' => 'sometimes|boolean',
        ];
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        if ($this->has('furnished')) {
            $this->merge(['furnished' => $this->boolean('furnished')]);
        }
        if ($this->has('pets_allowed')) {
            $this->merge(['pets_allowed' => $this->boolean('pets_allowed')]);
        }
        if ($this->has('smoking_allowed')) {
            $this->merge(['smoking_allowed' => $this->boolean('smoking_allowed')]);
        }
        if ($this->has('is_active')) {
            $this->merge(['is_active' => $this->boolean('is_active')]);
        }
    }
}
