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
        Schema::create('admin_settings', function (Blueprint $table) {
            $table->id();
            $table->string('category', 50)->index(); // 'general', 'users', 'properties', etc.
            $table->string('setting_key', 100);
            $table->text('setting_value');
            $table->enum('data_type', ['string', 'boolean', 'integer', 'float', 'json'])->default('string');
            $table->timestamps();
            
            // Ensure unique combination of category and setting_key
            $table->unique(['category', 'setting_key'], 'unique_setting');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('admin_settings');
    }
};
