<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * Adds odometer tracking fields to invoice_cards table for automatic distance calculation.
     * Each invoice card represents a store visit, and we capture the odometer reading
     * when arriving at that store.
     */
    public function up(): void
    {
        Schema::table('invoice_cards', function (Blueprint $table) {
            // Odometer reading when arriving at this store
            $table->decimal('arrival_odometer', 10, 2)->nullable()->after('miles_to_store');
            
            // Odometer reading when leaving this store (optional - for future use)
            $table->decimal('departure_odometer', 10, 2)->nullable()->after('arrival_odometer');
            
            // Auto-calculated distance for this segment
            // This is calculated as: arrival_odometer - previous_odometer
            $table->decimal('calculated_miles', 10, 2)->nullable()->after('departure_odometer');
            
            // Add indexes for faster queries
            $table->index('arrival_odometer');
            $table->index('calculated_miles');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('invoice_cards', function (Blueprint $table) {
            $table->dropIndex(['arrival_odometer']);
            $table->dropIndex(['calculated_miles']);
            $table->dropColumn(['arrival_odometer', 'departure_odometer', 'calculated_miles']);
        });
    }
};
