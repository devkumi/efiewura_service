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
        Schema::create('user_preferences', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            
            // Display preferences
            $table->string('timezone')->default('Africa/Lagos');
            $table->enum('date_format', ['DD/MM/YYYY', 'MM/DD/YYYY', 'YYYY-MM-DD'])->default('DD/MM/YYYY');
            $table->enum('theme', ['light', 'dark'])->default('light');
            $table->integer('dashboard_refresh_interval')->default(30); // seconds
            
            // Notification preferences
            $table->boolean('email_notifications')->default(true);
            $table->boolean('sms_notifications')->default(false);
            $table->boolean('new_bookings')->default(true);
            $table->boolean('property_updates')->default(true);
            $table->boolean('user_registrations')->default(false);
            $table->boolean('system_alerts')->default(true);
            $table->boolean('weekly_reports')->default(false);
            
            // Profile information (additional fields beyond core user data)
            $table->string('phone')->nullable();
            $table->text('bio')->nullable();
            
            $table->timestamps();
            
            // Ensure one preference record per user
            $table->unique('user_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_preferences');
    }
};
