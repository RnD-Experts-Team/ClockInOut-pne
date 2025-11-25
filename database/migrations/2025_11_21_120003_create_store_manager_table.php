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
        Schema::create('store_manager', function (Blueprint $table) {
            // Foreign keys
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('store_id')->constrained('stores')->onDelete('cascade');
            
            // Tracking fields
            $table->foreignId('assigned_by')->constrained('users')->onDelete('cascade');
            $table->timestamp('assigned_at');
            
            // Composite primary key
            $table->primary(['user_id', 'store_id']);
            
            // Ensure uniqueness (prevents duplicate assignments)
            $table->unique(['user_id', 'store_id']);
            
            // Indexes for queries
            $table->index('user_id');
            $table->index('store_id');
            $table->index('assigned_by');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('store_manager');
    }
};
