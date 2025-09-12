<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Step 1: First expand the enum to include BOTH 'reserved' and 'received'
        Schema::table('maintenance_requests', function (Blueprint $table) {
            $table->enum('status', [
                'on_hold',
                'reserved',      // Keep existing
                'received',      // Add new
                'in_progress',
                'complete',
                'done',
                'canceled'
            ])->default('on_hold')->change();
        });

        // Step 2: Now update the data (both values exist in enum)
        DB::table('maintenance_requests')
            ->where('status', 'reserved')
            ->update(['status' => 'received']);

        // Step 3: Finally remove 'reserved' from the enum
        Schema::table('maintenance_requests', function (Blueprint $table) {
            $table->enum('status', [
                'on_hold',
                'received',      // Only keep 'received'
                'in_progress',
                'complete',
                'done',
                'canceled'
            ])->default('on_hold')->change();
        });
    }

    public function down(): void
    {
        // Step 1: Add 'reserved' back to enum
        Schema::table('maintenance_requests', function (Blueprint $table) {
            $table->enum('status', [
                'on_hold',
                'reserved',      // Add back
                'received',      // Keep existing
                'in_progress',
                'complete',
                'done',
                'canceled'
            ])->default('on_hold')->change();
        });

        // Step 2: Update data back to 'reserved'
        DB::table('maintenance_requests')
            ->where('status', 'received')
            ->update(['status' => 'reserved']);

        // Step 3: Remove 'received' from enum
        Schema::table('maintenance_requests', function (Blueprint $table) {
            $table->enum('status', [
                'on_hold',
                'reserved',      // Only keep 'reserved'
                'in_progress',
                'complete',
                'done',
                'canceled'
            ])->default('on_hold')->change();
        });
    }
};
