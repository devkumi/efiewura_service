<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('properties', function (Blueprint $table) {
            $table->id();
            $table->foreignId('landlord_id')->constrained()->onDelete('cascade');
            $table->foreignId('property_category_id')->constrained()->onDelete('restrict');
            $table->string('title');
            $table->text('description');
            $table->decimal('price', 10, 2); // Monthly rent
            $table->string('currency', 3)->default('GHS'); // Ghana Cedis
            $table->integer('bedrooms')->default(0);
            $table->integer('bathrooms')->default(0);
            $table->decimal('size_sqm', 8, 2)->nullable(); // Size in square meters
            $table->boolean('furnished')->default(false);
            $table->text('address');
            $table->string('city');
            $table->string('state')->nullable();
            $table->string('postal_code')->nullable();
            $table->string('country')->default('Ghana');
            $table->decimal('latitude', 10, 8)->nullable();
            $table->decimal('longitude', 11, 8)->nullable();
            $table->json('amenities')->nullable(); // JSON array of amenities
            $table->json('images')->nullable(); // JSON array of image URLs
            $table->enum('availability_status', ['available', 'occupied', 'maintenance', 'reserved'])->default('available');
            $table->date('available_from')->nullable();
            $table->boolean('pets_allowed')->default(false);
            $table->boolean('smoking_allowed')->default(false);
            $table->text('lease_terms')->nullable();
            $table->decimal('security_deposit', 10, 2)->nullable();
            $table->integer('minimum_lease_months')->default(12);
            $table->text('additional_fees')->nullable(); // JSON or text for utility fees, etc.
            $table->boolean('is_active')->default(true);
            $table->timestamp('published_at')->nullable();
            $table->integer('views_count')->default(0);
            $table->timestamps();
            
            // Indexes for better query performance
            $table->index(['availability_status', 'is_active']);
            $table->index(['city', 'price']);
            $table->index(['property_category_id', 'availability_status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('properties');
    }
};
