<?php
// database/migrations/2025_08_19_000001_create_stores_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('stores', function (Blueprint $table) {
            $table->id();
            $table->string('store_number')->unique()->comment('Store identification number');
            $table->string('name')->nullable()->comment('Store name/location');
            $table->text('address')->nullable()->comment('Physical store address');
            $table->boolean('is_active')->default(true)->comment('Whether store is currently active');
            $table->timestamps();

            $table->index('store_number');
            $table->index('is_active');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('stores');
    }
};
