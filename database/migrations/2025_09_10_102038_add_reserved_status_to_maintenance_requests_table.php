<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('maintenance_requests', function (Blueprint $table) {
            // Add reason field first
            $table->text('reason')->nullable()->after('status');

            // Update enum to include ALL existing values + new 'reserved'
            $table->enum('status', [
                'on_hold',
                'reserved',      // NEW VALUE
                'in_progress',
                'complete',      // Keep existing value
                'done',          // Keep existing value
                'canceled'       // Keep existing value
            ])->default('on_hold')->change();
        });
    }

    public function down(): void
    {
        Schema::table('maintenance_requests', function (Blueprint $table) {
            $table->dropColumn('reason');
            $table->enum('status', ['on_hold', 'in_progress', 'complete'])
                ->default('on_hold')
                ->change();
        });
    }
};
