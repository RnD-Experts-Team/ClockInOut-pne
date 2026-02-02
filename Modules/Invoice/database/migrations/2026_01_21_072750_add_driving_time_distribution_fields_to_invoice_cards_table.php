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
            $table->decimal('allocated_return_driving_time', 8, 2)->nullable()->after('allocated_return_miles');
            $table->decimal('total_driving_time_hours', 8, 2)->nullable()->after('allocated_return_driving_time');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('invoice_cards', function (Blueprint $table) {
            $table->dropColumn(['allocated_return_driving_time', 'total_driving_time_hours']);
        });
    }
};
