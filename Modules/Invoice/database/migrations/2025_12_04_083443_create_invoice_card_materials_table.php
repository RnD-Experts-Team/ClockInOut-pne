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
        Schema::create('invoice_card_materials', function (Blueprint $table) {
            $table->id();
            
            // Foreign Key
            $table->foreignId('invoice_card_id')->constrained('invoice_cards')->onDelete('cascade');
            
            // Material Details
            $table->string('item_name');
            $table->decimal('cost', 10, 2);
            $table->json('receipt_photos')->nullable();
            
            $table->timestamps();
            
            // Index
            $table->index('invoice_card_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('invoice_card_materials');
    }
};
