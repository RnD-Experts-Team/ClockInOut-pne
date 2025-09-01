<?php
// database/migrations/2024_01_01_000003_create_task_assignments_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTaskAssignmentsTable extends Migration
{
    public function up()
    {
        Schema::create('task_assignments', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('maintenance_request_id');
            $table->unsignedBigInteger('assigned_user_id');
            $table->unsignedBigInteger('schedule_shift_id')->nullable();
            $table->text('assignment_notes')->nullable();
            $table->enum('priority', ['low', 'normal', 'high', 'urgent'])->default('normal');
            $table->timestamp('assigned_at');
            $table->timestamp('due_date')->nullable();
            $table->enum('status', ['pending', 'in_progress', 'completed', 'cancelled'])->default('pending');
            $table->unsignedBigInteger('assigned_by');
            $table->timestamps();

            $table->foreign('maintenance_request_id')->references('id')->on('maintenance_requests')->onDelete('cascade');
            $table->foreign('assigned_user_id')->references('id')->on('users');
            $table->foreign('schedule_shift_id')->references('id')->on('schedule_shifts')->onDelete('set null');
            $table->foreign('assigned_by')->references('id')->on('users');

            $table->index(['assigned_user_id', 'status']);
            $table->index(['maintenance_request_id', 'status']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('task_assignments');
    }
}
