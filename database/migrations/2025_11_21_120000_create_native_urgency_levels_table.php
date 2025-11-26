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
        Schema::create('native_urgency_levels', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // Critical, High, Medium, Low
            $table->integer('level'); // 1-4 for sorting (1 = highest)
            $table->string('color', 7); // Hex color code for UI display
            $table->timestamps();

            // Index for ordering by level
            $table->index('level');
        });

        // Seed default urgency levels
        DB::table('native_urgency_levels')->insert([
            ['name' => 'Critical', 'level' => 1, 'color' => '#dc2626', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'High', 'level' => 2, 'color' => '#ea580c', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Medium', 'level' => 3, 'color' => '#ca8a04', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Low', 'level' => 4, 'color' => '#16a34a', 'created_at' => now(), 'updated_at' => now()],
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('native_urgency_levels');
    }
};
