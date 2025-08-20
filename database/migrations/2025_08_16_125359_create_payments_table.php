<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('store_id')->nullable()->constrained('stores')->onDelete('cascade');
            $table->string('store')->nullable();
            $table->date('date');
            $table->string('what_got_fixed')->nullable();
            $table->foreignId('company_id')->constrained()->onDelete('cascade');
            $table->decimal('cost', 10, 2);
            $table->text('notes')->nullable();
            $table->boolean('paid')->default(false);
            $table->string('payment_method')->nullable();
            $table->string('maintenance_type')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('payments');
    }
};
