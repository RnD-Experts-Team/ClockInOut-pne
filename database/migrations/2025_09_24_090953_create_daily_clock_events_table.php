<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('daily_clock_events', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->enum('event_type', ['clock_in', 'clock_out', 'break_start', 'break_end']);
            $table->timestamp('event_timestamp');
            $table->string('location')->nullable(); // GPS/IP location
            $table->string('ip_address');
            $table->text('notes')->nullable();
            $table->decimal('latitude', 10, 8)->nullable();
            $table->decimal('longitude', 11, 8)->nullable();
            $table->timestamps();

            // Indexes
            $table->index(['user_id', 'event_timestamp']);
            $table->index('event_type');
            $table->index('event_timestamp');

            // Foreign key
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('daily_clock_events');
    }
};
