<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * Removes unnecessary/duplicate fields from clockings table:
     * - starting_odometer: duplicate of miles_in
     * - final_odometer: duplicate of miles_out
     * - starting_miles: not needed (calculated)
     * - return_miles: not needed (calculated)
     * - bought_something: tracked in invoice cards
     * - purchase_cost: tracked in invoice cards
     * - purchase_receipt: tracked in invoice cards
     * - fixed_something: tracked in invoice cards
     * - fix_description: tracked in invoice cards
     * - fix_image: tracked in invoice cards
     */
    public function up(): void
    {
        Schema::table('clockings', function (Blueprint $table) {
            // Drop indexes first if they exist
            $table->dropIndex(['starting_odometer']);
            $table->dropIndex(['final_odometer']);
            
            // Remove duplicate odometer fields (miles_in and miles_out are used instead)
            $table->dropColumn([
                'starting_odometer',
                'final_odometer',
                'starting_miles',
                'return_miles',
            ]);
            
            // Remove purchase tracking fields (now tracked in invoice cards)
            $table->dropColumn([
                'bought_something',
                'purchase_cost',
                'purchase_receipt',
            ]);
            
            // Remove fix tracking fields (now tracked in invoice cards)
            $table->dropColumn([
                'fixed_something',
                'fix_description',
                'fix_image',
            ]);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('clockings', function (Blueprint $table) {
            // Restore odometer fields
            $table->decimal('starting_odometer', 10, 2)->nullable()->after('starting_miles');
            $table->decimal('final_odometer', 10, 2)->nullable()->after('return_miles');
            $table->decimal('starting_miles', 10, 2)->nullable();
            $table->decimal('return_miles', 10, 2)->nullable();
            
            // Restore purchase fields
            $table->boolean('bought_something')->default(false);
            $table->decimal('purchase_cost', 10, 2)->nullable();
            $table->string('purchase_receipt')->nullable();
            
            // Restore fix fields
            $table->boolean('fixed_something')->default(false);
            $table->text('fix_description')->nullable();
            $table->string('fix_image')->nullable();
            
            // Restore indexes
            $table->index('starting_odometer');
            $table->index('final_odometer');
        });
    }
};
