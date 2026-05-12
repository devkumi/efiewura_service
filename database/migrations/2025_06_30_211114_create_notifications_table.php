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
        Schema::create('notifications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            
            // Notification details
            $table->string('type'); // booking_confirmed, booking_rejected, new_booking_request, etc.
            $table->string('title');
            $table->text('message');
            $table->json('data')->nullable(); // Additional data like property_id, booking_id, etc.
            
            // Related entities (nullable for flexibility)
            $table->foreignId('property_id')->nullable()->constrained()->onDelete('cascade');
            $table->foreignId('booking_id')->nullable()->constrained()->onDelete('cascade');
            $table->foreignId('triggered_by_user_id')->nullable()->constrained('users')->onDelete('set null');
            
            // Status and priority
            $table->enum('priority', ['low', 'medium', 'high', 'urgent'])->default('medium');
            $table->boolean('is_read')->default(false);
            $table->timestamp('read_at')->nullable();
            
            // Delivery channels
            $table->boolean('email_sent')->default(false);
            $table->boolean('sms_sent')->default(false);
            $table->boolean('push_sent')->default(false);
            
            // Scheduling
            $table->timestamp('scheduled_for')->nullable();
            $table->boolean('is_sent')->default(true); // false for scheduled notifications
            
            $table->timestamps();
            
            // Indexes for performance
            $table->index(['user_id', 'is_read', 'created_at']);
            $table->index(['type', 'created_at']);
            $table->index(['scheduled_for', 'is_sent']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('notifications');
    }
};
