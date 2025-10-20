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
        Schema::table('apartment_leases', function (Blueprint $table) {
            // Add renewal date column after expiration_date
            $table->date('renewal_date')->nullable()->after('expiration_date');

            // Add boolean flag to track if renewal reminder was sent
            $table->boolean('renewal_reminder_sent')->default(false)->after('renewal_date');

            // Add notes field for renewal information
            $table->text('renewal_notes')->nullable()->after('renewal_reminder_sent');

            // Add renewal status enum
            $table->enum('renewal_status', ['pending', 'in_progress', 'completed', 'declined'])
                ->default('pending')->after('renewal_notes');

            // Add who created the renewal reminder
            $table->unsignedBigInteger('renewal_created_by')->nullable()->after('renewal_status');

            // Add timestamps for renewal tracking
            $table->timestamp('renewal_reminder_sent_at')->nullable()->after('renewal_created_by');
            $table->timestamp('renewal_completed_at')->nullable()->after('renewal_reminder_sent_at');

            // Foreign key constraint for renewal_created_by
            $table->foreign('renewal_created_by')->references('id')->on('users')->onDelete('set null');

            // Add index for performance
            $table->index('renewal_date');
            $table->index(['renewal_date', 'renewal_reminder_sent']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('apartment_leases', function (Blueprint $table) {
            // Drop foreign key first
            $table->dropForeign(['renewal_created_by']);

            // Drop indexes
            $table->dropIndex(['apartment_leases_renewal_date_index']);
            $table->dropIndex(['apartment_leases_renewal_date_renewal_reminder_sent_index']);

            // Drop columns
            $table->dropColumn([
                'renewal_date',
                'renewal_reminder_sent',
                'renewal_notes',
                'renewal_status',
                'renewal_created_by',
                'renewal_reminder_sent_at',
                'renewal_completed_at'
            ]);
        });
    }
};
