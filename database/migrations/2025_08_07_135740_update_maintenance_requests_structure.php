<?php
// database/migrations/2025_08_07_000008_update_maintenance_requests_structure.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('maintenance_requests', function (Blueprint $table) {
            // Remove the old completion_notes column
            $table->dropColumn('completion_notes');
            
            // Change status enum to include new statuses
            $table->dropColumn('status');
        });

        Schema::table('maintenance_requests', function (Blueprint $table) {
            // Add new status column with updated options
            $table->enum('status', ['on_hold', 'in_progress', 'done', 'canceled'])->default('on_hold');
            
            // Add new columns
            $table->decimal('costs', 10, 2)->nullable()->comment('Cost of fixing the issue');
            $table->text('how_we_fixed_it')->nullable()->comment('Description of how the issue was fixed');
        });

        // Update status_histories table
        Schema::table('status_histories', function (Blueprint $table) {
            $table->dropColumn(['old_status', 'new_status']);
        });

        Schema::table('status_histories', function (Blueprint $table) {
            $table->enum('old_status', ['on_hold', 'in_progress', 'done', 'canceled'])->nullable();
            $table->enum('new_status', ['on_hold', 'in_progress', 'done', 'canceled']);
        });
    }

    public function down(): void
    {
        Schema::table('maintenance_requests', function (Blueprint $table) {
            // Restore original structure
            $table->dropColumn(['status', 'costs', 'how_we_fixed_it']);
        });

        Schema::table('maintenance_requests', function (Blueprint $table) {
            $table->enum('status', ['on_hold', 'in_progress', 'complete'])->default('on_hold');
            $table->text('completion_notes')->nullable();
        });

        Schema::table('status_histories', function (Blueprint $table) {
            $table->dropColumn(['old_status', 'new_status']);
        });

        Schema::table('status_histories', function (Blueprint $table) {
            $table->enum('old_status', ['on_hold', 'in_progress', 'complete'])->nullable();
            $table->enum('new_status', ['on_hold', 'in_progress', 'complete']);
        });
    }
};
