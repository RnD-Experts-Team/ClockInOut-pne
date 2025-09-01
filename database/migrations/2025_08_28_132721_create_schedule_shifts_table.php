<?php
// database/migrations/2024_01_01_000002_create_schedule_shifts_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateScheduleShiftsTable extends Migration
{
    public function up()
    {
        Schema::create('schedule_shifts', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('schedule_id');
            $table->unsignedBigInteger('user_id');
            $table->date('date');
            $table->time('start_time');
            $table->time('end_time');
            $table->time('break_start')->nullable();
            $table->time('break_end')->nullable();
            $table->enum('shift_type', ['regular', 'overtime', 'split', 'oncall'])->default('regular');
            $table->enum('role', ['general', 'supervisor', 'cashier', 'maintenance'])->default('general');
            $table->string('color', 7)->default('#3b82f6'); // Hex color
            $table->text('notes')->nullable();
            $table->unsignedBigInteger('task_id')->nullable(); // Link to maintenance request
            $table->timestamps();

            $table->foreign('schedule_id')->references('id')->on('schedules')->onDelete('cascade');
            $table->foreign('user_id')->references('id')->on('users');
            $table->foreign('task_id')->references('id')->on('maintenance_requests')->onDelete('set null');
            $table->index(['schedule_id', 'date', 'user_id']);
            $table->index(['date', 'shift_type']);
            $table->text('assignment_notes')->nullable();

        });
    }

    public function down()
    {
        Schema::dropIfExists('schedule_shifts');
    }
}
