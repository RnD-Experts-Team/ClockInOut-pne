<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('calendar_events', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('description')->nullable();
            $table->enum('event_type', [
                'maintenance_request',
                'admin_action',
                'clock_event',
                'reminder',
                'expiration',
                'custom'
            ]);
            $table->date('start_date');
            $table->date('end_date')->nullable();
            $table->time('start_time')->nullable();
            $table->time('end_time')->nullable();
            $table->boolean('is_all_day')->default(false);
            $table->string('color_code', 7)->default('#007bff'); // hex color

            // Polymorphic relationship
            $table->string('related_model_type')->nullable();
            $table->unsignedBigInteger('related_model_id')->nullable();

            $table->unsignedBigInteger('created_by');
            $table->timestamps();

            // Indexes
            $table->index(['start_date', 'end_date']);
            $table->index('event_type');
            $table->index(['related_model_type', 'related_model_id']);
            $table->index('created_by');

            // Foreign key
            $table->foreign('created_by')->references('id')->on('users')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('calendar_events');
    }
};
