<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * Adds odometer tracking fields to clockings table for automatic distance calculation.
     * These fields replace manual "miles driven" input with actual odometer readings.
     */
    public function up(): void
    {
        Schema::table('clockings', function (Blueprint $table) {
            // Odometer reading at clock-in (start of shift)
            $table->decimal('starting_odometer', 10, 2)->nullable()->after('starting_miles');
            
            // Odometer reading at clock-out (end of shift)
            $table->decimal('final_odometer', 10, 2)->nullable()->after('return_miles');
            
            // Add indexes for faster queries
            $table->index('starting_odometer');
            $table->index('final_odometer');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('clockings', function (Blueprint $table) {
            $table->dropIndex(['starting_odometer']);
            $table->dropIndex(['final_odometer']);
            $table->dropColumn(['starting_odometer', 'final_odometer']);
        });
    }
};
