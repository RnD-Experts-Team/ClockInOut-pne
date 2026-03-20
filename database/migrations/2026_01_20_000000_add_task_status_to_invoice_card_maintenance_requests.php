<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('invoice_card_maintenance_requests', function (Blueprint $table) {
            if (!Schema::hasColumn('invoice_card_maintenance_requests', 'task_status')) {
                $table->enum('task_status', ['pending', 'in_progress', 'completed'])->default('pending')->after('maintenance_request_id');
            }

            if (!Schema::hasColumn('invoice_card_maintenance_requests', 'completed_at')) {
                $table->timestamp('completed_at')->nullable()->after('task_status');
            }

            $table->index('task_status');
            $table->index('completed_at');
        });

        // Migrate old status values if present
        try {
            DB::table('invoice_card_maintenance_requests')
                ->where('status', 'done')
                ->update(['task_status' => 'completed', 'completed_at' => now()]);

            DB::table('invoice_card_maintenance_requests')
                ->where('status', 'not_done')
                ->update(['task_status' => 'pending']);
        } catch (\Exception $e) {
            // If table doesn't exist yet or columns missing, ignore migration mapping
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('invoice_card_maintenance_requests', function (Blueprint $table) {
            if (Schema::hasColumn('invoice_card_maintenance_requests', 'task_status')) {
                $table->dropIndex(['task_status']);
                $table->dropColumn('task_status');
            }

            if (Schema::hasColumn('invoice_card_maintenance_requests', 'completed_at')) {
                $table->dropIndex(['completed_at']);
                $table->dropColumn('completed_at');
            }
        });
    }
};