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
        Schema::create('payment_equipment_items', function (Blueprint $table) {
            $table->id();
            // Use unsignedBigInteger here to avoid a hard FK when payments migration may run later in some environments
            $table->unsignedBigInteger('payment_id');
            $table->string('item_name');
            $table->integer('quantity');
            $table->decimal('unit_cost', 10, 2);
            $table->decimal('total_cost', 10, 2);
            $table->timestamps();

            // Add index for better query performance
            $table->index('payment_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payment_equipment_items');
    }
};
