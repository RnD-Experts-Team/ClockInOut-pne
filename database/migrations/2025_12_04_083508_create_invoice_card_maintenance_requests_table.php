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
        Schema::create('invoice_card_maintenance_requests', function (Blueprint $table) {
            $table->id();
            
            // Foreign Keys
            $table->foreignId('invoice_card_id')->constrained('invoice_cards')->onDelete('cascade');
            $table->foreignId('maintenance_request_id')->constrained('maintenance_requests')->onDelete('cascade');
            
            // Status
            $table->enum('status', ['done', 'not_done'])->default('done');
            $table->text('notes')->nullable();
            
            $table->timestamps();
            
            // Unique constraint to prevent duplicate associations
            $table->unique(['invoice_card_id', 'maintenance_request_id'], 'unique_card_request');
            
            // Indexes
            $table->index('invoice_card_id');
            $table->index('maintenance_request_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('invoice_card_maintenance_requests');
    }
};
