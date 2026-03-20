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
        Schema::create('invoice_cards', function (Blueprint $table) {
            $table->id();
            
            // Foreign Keys
            $table->foreignId('clocking_id')->constrained('clockings')->onDelete('cascade');
            $table->foreignId('store_id')->constrained('stores')->onDelete('cascade');
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            
            // Timing
            $table->timestamp('start_time');
            $table->timestamp('end_time')->nullable();
            
            // Mileage
            $table->decimal('miles_to_store', 10, 2)->nullable();
            $table->decimal('allocated_return_miles', 10, 2)->default(0);
            $table->decimal('total_miles', 10, 2)->nullable();
            $table->decimal('mileage_payment', 10, 2)->default(0);
            
            // Costs
            $table->decimal('labor_hours', 10, 2)->nullable();
            $table->decimal('labor_cost', 10, 2)->default(0);
            $table->decimal('materials_cost', 10, 2)->default(0);
            $table->decimal('total_cost', 10, 2)->default(0);
            
            // Status
            $table->enum('status', ['in_progress', 'completed', 'not_done'])->default('in_progress');
            $table->text('notes')->nullable();
            $table->text('not_done_reason')->nullable();
            
            $table->timestamps();
            
            // Indexes
            $table->index('clocking_id');
            $table->index('store_id');
            $table->index('user_id');
            $table->index('status');
            $table->index('start_time');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('invoice_cards');
    }
};
