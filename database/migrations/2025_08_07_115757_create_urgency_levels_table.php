<?php
// database/migrations/2025_08_07_000001_create_urgency_levels_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('urgency_levels', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->text('description')->nullable();
            $table->integer('priority_order');
            $table->timestamps();
        });

        // Seed urgency levels
        DB::table('urgency_levels')->insert([
            [
                'name' => 'Impacts Sales',
                'description' => 'Critical issue that directly impacts sales operations',
                'priority_order' => 1,
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'name' => 'High',
                'description' => 'High priority maintenance issue',
                'priority_order' => 2,
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'name' => 'Medium',
                'description' => 'Medium priority maintenance issue',
                'priority_order' => 3,
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'name' => 'Low',
                'description' => 'Low priority maintenance issue',
                'priority_order' => 4,
                'created_at' => now(),
                'updated_at' => now()
            ]
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('urgency_levels');
    }
};
