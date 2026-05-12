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
            // First, let's migrate existing name data to firstname/lastname if needed
            // This will be handled by a separate data migration step
            
            // Drop the name column
            $table->dropColumn('name');
            
            // Make firstname and lastname NOT NULL since they're now required
            $table->string('firstname')->nullable(false)->change();
            $table->string('lastname')->nullable(false)->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Add back the name column
            $table->string('name')->after('id');
            
            // Make firstname and lastname nullable again
            $table->string('firstname')->nullable()->change();
            $table->string('lastname')->nullable()->change();
        });
    }
};
