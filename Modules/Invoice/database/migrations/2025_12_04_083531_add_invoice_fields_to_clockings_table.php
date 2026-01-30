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
        Schema::table('clockings', function (Blueprint $table) {
            $table->decimal('starting_miles', 10, 2)->nullable()->after('miles_in');
            $table->decimal('return_miles', 10, 2)->nullable()->after('miles_out');
            $table->decimal('total_session_miles', 10, 2)->nullable()->after('return_miles');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('clockings', function (Blueprint $table) {
            $table->dropColumn(['starting_miles', 'return_miles', 'total_session_miles']);
        });
    }
};
