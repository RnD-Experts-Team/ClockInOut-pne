<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('calendar_reminders', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('calendar_event_id')->nullable();
            $table->unsignedBigInteger('admin_user_id');
            $table->string('title');
            $table->text('description')->nullable();
            $table->date('reminder_date');
            $table->time('reminder_time');
            $table->enum('reminder_type', [
                'maintenance_followup',
                'custom_reminder',
                'expiration_alert',
                'lease_renewal',
                'payment_due'
            ]);
            $table->boolean('is_recurring')->default(false);
            $table->enum('recurrence_pattern', ['daily', 'weekly', 'monthly', 'yearly'])->nullable();
            $table->enum('status', ['pending', 'sent', 'dismissed', 'snoozed'])->default('pending');

            // Polymorphic relationship
            $table->string('related_model_type')->nullable();
            $table->unsignedBigInteger('related_model_id')->nullable();

            $table->json('notification_methods')->nullable(); // email, browser, sms
            $table->timestamp('last_sent_at')->nullable();
            $table->timestamp('snooze_until')->nullable();
            $table->timestamps();

            // Indexes
            $table->index(['reminder_date', 'reminder_time']);
            $table->index('status');
            $table->index('admin_user_id');
            $table->index(['related_model_type', 'related_model_id']);

            // Foreign keys
            $table->foreign('calendar_event_id')->references('id')->on('calendar_events')->onDelete('cascade');
            $table->foreign('admin_user_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('calendar_reminders');
    }
};
