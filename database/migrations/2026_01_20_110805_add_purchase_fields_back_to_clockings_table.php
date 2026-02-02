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
        Schema::table('clockings', function (Blueprint $table) {
            // Add back purchase fields for backward compatibility with invoice system
            $table->boolean('bought_something')->default(false)->after('total_salary');
            $table->decimal('purchase_cost', 10, 2)->nullable()->after('bought_something');
            $table->string('purchase_receipt')->nullable()->after('purchase_cost');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('clockings', function (Blueprint $table) {
            $table->dropColumn(['bought_something', 'purchase_cost', 'purchase_receipt']);
        });
    }
};
