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
        Schema::table('landlords', function (Blueprint $table) {
            $table->integer('overdue_release_days')->default(30)->after('status')
                  ->comment('Number of days after payment due date to auto-release property');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('landlords', function (Blueprint $table) {
            $table->dropColumn('overdue_release_days');
        });
    }
};
