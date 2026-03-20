<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * Adds odometer tracking and driving time payment fields to invoice_cards.
     * These fields enable automatic distance calculation and separate driving time compensation.
     */
    public function up(): void
    {
        Schema::table('invoice_cards', function (Blueprint $table) {
            // Only add driving_time_hours if it doesn't already exist
            if (!Schema::hasColumn('invoice_cards', 'driving_time_hours')) {
                $table->decimal('driving_time_hours', 10, 2)->nullable()->after('calculated_miles');
            }
            
            // Only add driving_time_payment if it doesn't already exist
            if (!Schema::hasColumn('invoice_cards', 'driving_time_payment')) {
                $table->decimal('driving_time_payment', 10, 2)->default(0)->after('driving_time_hours');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('invoice_cards', function (Blueprint $table) {
            $table->dropIndex(['arrival_odometer']);
            $table->dropColumn([
                'driving_time_hours',
                'driving_time_payment',
            ]);
            
            // Only drop if they were added by this migration
            if (Schema::hasColumn('invoice_cards', 'calculated_miles')) {
                $table->dropColumn('calculated_miles');
            }
            if (Schema::hasColumn('invoice_cards', 'arrival_odometer')) {
                $table->dropColumn('arrival_odometer');
            }
        });
    }
};
