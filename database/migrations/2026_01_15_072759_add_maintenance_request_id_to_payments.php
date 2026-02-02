<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * Adds maintenance_request_id to link admin purchases to specific tickets.
     * This allows admin purchases to be included in ticket-specific invoices.
     */
    public function up(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            // Add foreign key to link payment to maintenance request (ticket)
            $table->unsignedBigInteger('maintenance_request_id')->nullable()->after('store_id');
            
            // Add foreign key constraint
            $table->foreign('maintenance_request_id')
                  ->references('id')
                  ->on('maintenance_requests')
                  ->onDelete('set null');
            
            // Add index for faster queries
            $table->index('maintenance_request_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            // Drop foreign key and index
            $table->dropForeign(['maintenance_request_id']);
            $table->dropIndex(['maintenance_request_id']);
            $table->dropColumn('maintenance_request_id');
        });
    }
};
