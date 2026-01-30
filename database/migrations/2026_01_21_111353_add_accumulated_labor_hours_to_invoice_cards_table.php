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
        Schema::table('invoice_cards', function (Blueprint $table) {
            // Track accumulated labor hours from previous "not done" sessions
            $table->decimal('accumulated_labor_hours', 8, 2)->default(0)->after('labor_hours');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('invoice_cards', function (Blueprint $table) {
            $table->dropColumn('accumulated_labor_hours');
        });
    }
};
