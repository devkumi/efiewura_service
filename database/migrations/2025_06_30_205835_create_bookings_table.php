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
        Schema::create('bookings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('property_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('landlord_id')->constrained('landlords')->onDelete('cascade');
            
            // Booking details
            $table->enum('status', ['pending', 'confirmed', 'cancelled', 'rejected', 'completed'])
                  ->default('pending');
            $table->date('move_in_date');
            $table->date('move_out_date')->nullable();
            $table->integer('lease_duration_months');
            
            // Financial details
            $table->decimal('monthly_rent', 10, 2);
            $table->decimal('security_deposit', 10, 2);
            $table->decimal('total_amount', 10, 2);
            $table->string('currency', 3)->default('GHS');
            
            // Contact and personal info
            $table->string('tenant_name');
            $table->string('tenant_phone');
            $table->string('tenant_email');
            $table->text('tenant_message')->nullable();
            
            // Application details
            $table->string('occupation')->nullable();
            $table->string('employer')->nullable();
            $table->decimal('monthly_income', 10, 2)->nullable();
            $table->text('emergency_contact')->nullable();
            
            // Timestamps for booking lifecycle
            $table->timestamp('confirmed_at')->nullable();
            $table->timestamp('cancelled_at')->nullable();
            $table->timestamp('rejected_at')->nullable();
            
            $table->timestamps();
            
            // Indexes
            $table->index(['property_id', 'status']);
            $table->index(['user_id', 'status']);
            $table->index('move_in_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bookings');
    }
};
