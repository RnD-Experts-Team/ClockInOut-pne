<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Add 'received' alongside existing values (safe for production)
        DB::statement("ALTER TABLE status_histories MODIFY COLUMN old_status ENUM('on_hold', 'reserved', 'received', 'in_progress', 'done', 'canceled')");
        DB::statement("ALTER TABLE status_histories MODIFY COLUMN new_status ENUM('on_hold', 'reserved', 'received', 'in_progress', 'done', 'canceled')");
    }

    public function down(): void
    {
        // Remove 'received' from enum (revert)
        DB::statement("ALTER TABLE status_histories MODIFY COLUMN old_status ENUM('on_hold', 'reserved', 'in_progress', 'done', 'canceled')");
        DB::statement("ALTER TABLE status_histories MODIFY COLUMN new_status ENUM('on_hold', 'reserved', 'in_progress', 'done', 'canceled')");
    }
};
