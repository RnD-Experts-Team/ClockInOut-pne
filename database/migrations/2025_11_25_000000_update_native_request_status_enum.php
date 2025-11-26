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
        // Add 'received' to the status enum if the table exists
        if (Schema::hasTable('native_requests')) {
            Schema::table('native_requests', function (Blueprint $table) {
                // Change the enum to include 'received' as a valid status
                $table->enum('status', ['pending', 'in_progress', 'done', 'canceled', 'received'])
                    ->default('pending')
                    ->change();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('native_requests')) {
            Schema::table('native_requests', function (Blueprint $table) {
                // Remove 'received' from the enum
                $table->enum('status', ['pending', 'in_progress', 'done', 'canceled'])
                    ->default('pending')
                    ->change();
            });
        }
    }
};
