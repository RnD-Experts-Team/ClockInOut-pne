<?php
// database/migrations/2025_09_03_000001_add_assignment_tracking_to_maintenance_requests.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('maintenance_requests', function (Blueprint $table) {
            // Add fields to track assignment source and current assignment
            $table->enum('assignment_source', ['direct', 'task_assignment'])->default('direct')->after('assigned_to');
            $table->unsignedBigInteger('current_task_assignment_id')->nullable()->after('assignment_source');

            $table->foreign('current_task_assignment_id')->references('id')->on('task_assignments')->onDelete('set null');
            $table->index('current_task_assignment_id');
        });
    }

    public function down(): void
    {
        Schema::table('maintenance_requests', function (Blueprint $table) {
            $table->dropForeign(['current_task_assignment_id']);
            $table->dropColumn(['assignment_source', 'current_task_assignment_id']);
        });
    }
};
