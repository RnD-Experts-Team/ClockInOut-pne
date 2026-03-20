<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('invoice_recipients', function (Blueprint $table) {
            $table->id();
            $table->foreignId('store_id')->constrained('stores')->onDelete('cascade');
            $table->string('email');
            $table->string('name');
            $table->boolean('is_default')->default(false);
            $table->timestamps();
            
            // Indexes
            $table->index('store_id');
            $table->index('email');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('invoice_recipients');
    }
};
