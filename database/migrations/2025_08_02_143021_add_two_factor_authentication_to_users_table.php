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
        Schema::table('users', function (Blueprint $table) {
            // 2FA Admin Controls
            $table->boolean('two_factor_enabled')->default(false)->after('email_verified_at');
            $table->boolean('two_factor_required')->default(false)->after('two_factor_enabled');
            
            // 2FA User Setup
            $table->text('two_factor_secret')->nullable()->after('two_factor_required');
            $table->text('two_factor_recovery_codes')->nullable()->after('two_factor_secret');
            $table->timestamp('two_factor_confirmed_at')->nullable()->after('two_factor_recovery_codes');
            
            // 2FA Method (SMS, APP, etc.)
            $table->enum('two_factor_method', ['app', 'sms'])->default('app')->after('two_factor_confirmed_at');
            $table->string('two_factor_phone')->nullable()->after('two_factor_method');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'two_factor_enabled',
                'two_factor_required', 
                'two_factor_secret',
                'two_factor_recovery_codes',
                'two_factor_confirmed_at',
                'two_factor_method',
                'two_factor_phone'
            ]);
        });
    }
};
