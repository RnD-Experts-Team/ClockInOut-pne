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
        Schema::table('invoices', function (Blueprint $table) {
            $table->decimal('total_distance_miles', 10, 2)->default(0)->after('total_miles');
            $table->decimal('driving_time_hours', 10, 2)->default(0)->after('total_distance_miles');
            $table->decimal('driving_time_payment', 10, 2)->default(0)->after('driving_time_hours');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            $table->dropColumn(['total_distance_miles', 'driving_time_hours', 'driving_time_payment']);
        });
    }
};
