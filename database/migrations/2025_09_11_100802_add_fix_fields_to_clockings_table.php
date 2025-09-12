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
            $table->boolean('fixed_something')->default(false)->after('purchase_receipt');
            $table->text('fix_description')->nullable()->after('fixed_something');
            $table->string('fix_image')->nullable()->after('fix_description');

            // Add index for reporting purposes
            $table->index('fixed_something');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('clockings', function (Blueprint $table) {
            $table->dropIndex(['fixed_something']);
            $table->dropColumn(['fixed_something', 'fix_description', 'fix_image']);
        });
    }
};
