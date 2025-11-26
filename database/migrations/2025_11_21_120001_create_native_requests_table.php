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
        Schema::create('native_requests', function (Blueprint $table) {
            $table->id();
            
            // Foreign keys
            $table->foreignId('store_id')->constrained('stores')->onDelete('cascade');
            $table->foreignId('requester_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('urgency_level_id')->constrained('native_urgency_levels')->onDelete('restrict');
            
            // Request details
            $table->string('equipment_with_issue');
            $table->text('description_of_issue');
            $table->boolean('basic_troubleshoot_done')->default(false);
            $table->date('request_date'); // Set in controller with today()
            
            // Status and assignment
            $table->enum('status', ['pending', 'in_progress', 'done', 'canceled'])->default('pending');
            $table->foreignId('assigned_to')->nullable()->constrained('users')->onDelete('set null');
            
            // Resolution details
            $table->decimal('costs', 10, 2)->nullable();
            $table->text('how_we_fixed_it')->nullable();
            
            $table->timestamps();

            // Indexes for performance
            $table->index('store_id');
            $table->index('requester_id');
            $table->index('status');
            $table->index('assigned_to');
            $table->index('request_date');
            $table->index(['status', 'urgency_level_id']); // Composite index for filtering
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('native_requests');
    }
};
