<?php
// database/migrations/2024_01_01_000001_create_schedules_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSchedulesTable extends Migration
{
    public function up()
    {
        Schema::create('schedules', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->date('start_date');
            $table->date('end_date');
            $table->enum('status', ['draft', 'published', 'active', 'archived'])->default('draft');
            $table->unsignedBigInteger('created_by');
            $table->json('settings')->nullable(); // Store schedule settings
            $table->timestamps();

            $table->foreign('created_by')->references('id')->on('users');
            $table->index(['start_date', 'end_date', 'status']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('schedules');
    }
}
