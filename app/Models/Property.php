<?php

namespace App\Models;

use App\Traits\SoftDeletable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Property extends Model
{
    use HasFactory, SoftDeletable;

    protected $fillable = [
        'landlord_id',
        'property_category_id',
        'title',
        'description',
        'price',
        'currency',
        'bedrooms',
        'bathrooms',
        'size_sqm',
        'furnished',
        'address',
        'city',
        'state',
        'postal_code',
        'country',
        'latitude',
        'longitude',
        'amenities',
        'images',
        'availability_status',
        'available_from',
        'pets_allowed',
        'smoking_allowed',
        'lease_terms',
        'security_deposit',
        'minimum_lease_months',
        'additional_fees',
        'is_active',
        'published_at',
        'views_count',
        'admin_notes',
        'status',
        'deleted_by',
        'deleted_at',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'size_sqm' => 'decimal:2',
        'latitude' => 'decimal:8',
        'longitude' => 'decimal:8',
        'security_deposit' => 'decimal:2',
        'furnished' => 'boolean',
        'pets_allowed' => 'boolean',
        'smoking_allowed' => 'boolean',
        'is_active' => 'boolean',
        'amenities' => 'array',
        'images' => 'array',
        'available_from' => 'date',
        'published_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    public function softDelete(): static
    {
        $this->update([
            'status' => 'deleted',
            'deleted_by' => \Illuminate\Support\Facades\Auth::id(),
            'deleted_at' => now(),
            'is_active' => false,
        ]);
        return $this;
    }

    public function restore(): static
    {
        $this->update([
            'status' => 'active',
            'deleted_by' => null,
            'deleted_at' => null,
            'is_active' => true,
        ]);
        return $this;
    }

    /**
     * Get the landlord that owns the property.
     */
    public function landlord()
    {
        return $this->belongsTo(Landlord::class);
    }

    /**
     * Get the property category.
     */
    public function category()
    {
        return $this->belongsTo(PropertyCategory::class, 'property_category_id');
    }

    /**
     * Get the bookings for this property.
     */
    public function bookings()
    {
        return $this->hasMany(Booking::class)->notDeleted();
    }

    /**
     * Get all bookings including deleted ones (admin use)
     */
    public function allBookings()
    {
        return $this->hasMany(Booking::class);
    }

    /**
     * Scope a query to only include available properties.
     */
    public function scopeAvailable(Builder $query): Builder
    {
        return $query->where('availability_status', 'available')
                    ->where('is_active', true)
                    ->notDeleted();
    }

    /**
     * Scope a query to only include active properties.
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true)->notDeleted();
    }

    /**
     * Scope a query to filter by city.
     */
    public function scopeInCity(Builder $query, string $city): Builder
    {
        return $query->where('city', 'LIKE', "%{$city}%");
    }

    /**
     * Scope a query to filter by price range.
     */
    public function scopePriceBetween(Builder $query, float $min, float $max): Builder
    {
        return $query->whereBetween('price', [$min, $max]);
    }

    /**
     * Scope a query to filter by number of bedrooms.
     */
    public function scopeWithBedrooms(Builder $query, int $bedrooms): Builder
    {
        return $query->where('bedrooms', '>=', $bedrooms);
    }

    /**
     * Check if property is available for rent.
     */
    public function isAvailable(): bool
    {
        return $this->availability_status === 'available' && $this->is_active;
    }

    /**
     * Mark property as occupied.
     */
    public function markAsOccupied(): void
    {
        $this->update(['availability_status' => 'occupied']);
    }

    /**
     * Mark property as available.
     */
    public function markAsAvailable(): void
    {
        $this->update(['availability_status' => 'available']);
    }

    /**
     * Increment views count.
     */
    public function incrementViews(): void
    {
        $this->increment('views_count');
    }

    /**
     * Get formatted price with currency.
     */
    public function getFormattedPriceAttribute(): string
    {
        return $this->currency . ' ' . number_format($this->price, 2);
    }

    /**
     * Get the main image URL.
     */
    public function getMainImageAttribute(): ?string
    {
        return !empty($this->images) ? $this->images[0] : null;
    }
}
