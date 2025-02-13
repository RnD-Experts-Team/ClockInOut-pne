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
        Schema::create('clockings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->timestamp('clock_in')->nullable();
            $table->timestamp('clock_out')->nullable();
            $table->integer('miles_in')->nullable();
            $table->integer('miles_out')->nullable();
            $table->string('image_in')->nullable(); // for clock-in image
            $table->string('image_out')->nullable(); // for clock-out image
            $table->boolean('is_clocked_in')->default(false); // to track if employee is clocked in
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('clockings');
    }
};
