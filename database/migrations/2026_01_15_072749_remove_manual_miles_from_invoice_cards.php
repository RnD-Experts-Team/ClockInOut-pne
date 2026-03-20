<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * Removes manual miles entry field (replaced by automatic calculation)
     * and departure_odometer field (not needed in the workflow).
     */
    public function up(): void
    {
        Schema::table('invoice_cards', function (Blueprint $table) {
            // Remove manual miles entry (now calculated automatically)
            $table->dropColumn('miles_to_store');
            
            // Remove departure odometer (not needed)
            $table->dropColumn('departure_odometer');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('invoice_cards', function (Blueprint $table) {
            // Restore manual miles field
            $table->decimal('miles_to_store', 10, 2)->nullable()->after('end_time');
            
            // Restore departure odometer field
            $table->decimal('departure_odometer', 10, 2)->nullable()->after('arrival_odometer');
        });
    }
};
