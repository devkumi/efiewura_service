<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PropertyResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'description' => $this->description,
            'price' => $this->price,
            'formatted_price' => $this->formatted_price,
            'currency' => $this->currency,
            'bedrooms' => $this->bedrooms,
            'bathrooms' => $this->bathrooms,
            'size_sqm' => $this->size_sqm,
            'furnished' => $this->furnished,
            'address' => $this->address,
            'city' => $this->city,
            'state' => $this->state,
            'postal_code' => $this->postal_code,
            'country' => $this->country,
            'location' => [
                'latitude' => $this->latitude,
                'longitude' => $this->longitude,
            ],
            'amenities' => $this->amenities,
            'images' => $this->images,
            'main_image' => $this->main_image,
            'availability_status' => $this->availability_status,
            'available_from' => $this->available_from,
            'pets_allowed' => $this->pets_allowed,
            'smoking_allowed' => $this->smoking_allowed,
            'lease_terms' => $this->lease_terms,
            'security_deposit' => $this->security_deposit,
            'minimum_lease_months' => $this->minimum_lease_months,
            'additional_fees' => $this->additional_fees,
            'is_active' => $this->is_active,
            'published_at' => $this->published_at,
            'views_count' => $this->views_count,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'category' => $this->whenLoaded('category'),
            'landlord' => $this->when(
                $request->user() && $request->user()->role === 'landlord',
                $this->whenLoaded('landlord')
            ),
        ];
    }
}
