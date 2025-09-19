<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('workbook_rows', function (Blueprint $t) {
            $t->foreignId('store_id')
              ->nullable()
              ->after('id')
              ->constrained('stores')
              ->nullOnDelete();

            $t->index(['store_id', 'position']);
        });
    }

    public function down(): void
    {
        Schema::table('workbook_rows', function (Blueprint $t) {
            $t->dropIndex(['store_id', 'position']);
            $t->dropConstrainedForeignId('store_id'); // drops FK + column in newer Laravel
        });
    }
};
