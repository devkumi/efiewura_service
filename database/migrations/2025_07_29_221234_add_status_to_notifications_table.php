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
        Schema::table('notifications', function (Blueprint $table) {
            $table->enum('status', ['active', 'deleted'])->default('active')->after('priority');
            $table->foreignId('deleted_by')->nullable()->after('read_by')->constrained('users')->onDelete('set null');
            $table->timestamp('deleted_at')->nullable()->after('deleted_by');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('notifications', function (Blueprint $table) {
            $table->dropForeign(['deleted_by']);
            $table->dropColumn(['status', 'deleted_by', 'deleted_at']);
        });
    }
};
