<?php
// database/migrations/2025_08_07_000007_create_status_histories_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('status_histories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('maintenance_request_id')->constrained('maintenance_requests')->onDelete('cascade');
            $table->enum('old_status', ['on_hold', 'in_progress', 'complete'])->nullable();
            $table->enum('new_status', ['on_hold', 'in_progress', 'complete']);
            $table->foreignId('changed_by_user_id')->nullable()->constrained('users');
            $table->text('notes')->nullable();
            $table->timestamp('changed_at');
            $table->timestamps();
            
            $table->index(['maintenance_request_id', 'changed_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('status_histories');
    }
};
