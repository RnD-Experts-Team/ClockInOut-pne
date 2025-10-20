<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('calendar_reminders', function (Blueprint $table) {
            // Add notification tracking columns
            $table->timestamp('notified_at')->nullable()->after('reminder_time');
            $table->enum('notification_status', ['pending', 'shown', 'dismissed', 'completed'])
                ->default('pending')
                ->after('notified_at');

            // Add index for efficient polling queries - FIXED to use admin_user_id
            $table->index(['admin_user_id', 'notification_status', 'reminder_date'], 'idx_reminders_notifications');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('calendar_reminders', function (Blueprint $table) {
            // Drop the index first
            $table->dropIndex('idx_reminders_notifications');

            // Drop the new columns
            $table->dropColumn(['notified_at', 'notification_status']);
        });
    }
};
