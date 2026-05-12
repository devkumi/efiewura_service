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
        Schema::table('properties', function (Blueprint $table) {
            $table->string('status')->default('active')->after('images');
            $table->unsignedBigInteger('deleted_by')->nullable()->after('status');
            $table->timestamp('deleted_at')->nullable()->after('deleted_by');
            
            $table->foreign('deleted_by')->references('id')->on('users');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('properties', function (Blueprint $table) {
            $table->dropForeign(['deleted_by']);
            $table->dropColumn(['status', 'deleted_by', 'deleted_at']);
        });
    }
};
