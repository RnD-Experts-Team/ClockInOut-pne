<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('cell_values', function (Blueprint $table) {
            $table->id();
            $table->foreignId('row_id')->constrained()->onDelete('cascade');
            $table->foreignId('column_id')->constrained()->onDelete('cascade');
            $table->text('value')->nullable();
            $table->timestamps();
            
            $table->unique(['row_id', 'column_id']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('cell_values');
    }
};