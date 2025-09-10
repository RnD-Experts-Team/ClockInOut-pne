<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('status_histories', function (Blueprint $table) {
            // Update old_status column
            $table->enum('old_status', [
                'on_hold',
                'reserved',      // ADD THIS
                'in_progress',
                'complete',
                'done',
                'canceled'
            ])->nullable()->change();

            // Update new_status column
            $table->enum('new_status', [
                'on_hold',
                'reserved',      // ADD THIS
                'in_progress',
                'complete',
                'done',
                'canceled'
            ])->change();
        });
    }

    public function down(): void
    {
        Schema::table('status_histories', function (Blueprint $table) {
            $table->enum('old_status', ['on_hold', 'in_progress', 'complete'])->nullable()->change();
            $table->enum('new_status', ['on_hold', 'in_progress', 'complete'])->change();
        });
    }
};
