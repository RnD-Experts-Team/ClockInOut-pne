<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('workbook_columns', function (Blueprint $t) {
            $t->id();
            $t->string('name');
            $t->string('slug')->unique();
            $t->enum('type', ['string','number','date','bool','json'])->default('string');
            $t->unsignedInteger('position')->default(0);
            $t->boolean('required')->default(false);
            $t->boolean('is_unique')->default(false);
            $t->json('options')->nullable();
            $t->timestamps();
            $t->index('position');
        });

        Schema::create('workbook_rows', function (Blueprint $t) {
            $t->id();
            $t->unsignedInteger('position')->default(0);
            $t->timestamps();
            $t->index('position');
        });

        Schema::create('workbook_cells', function (Blueprint $t) {
            $t->id();
            $t->foreignId('row_id')->constrained('workbook_rows')->cascadeOnDelete();
            $t->foreignId('column_id')->constrained('workbook_columns')->cascadeOnDelete();

            $t->text('value_text')->nullable();
            $t->decimal('value_number', 20, 6)->nullable();
            $t->dateTime('value_date')->nullable();
            $t->boolean('value_bool')->nullable();
            $t->json('value_json')->nullable();

            $t->timestamps();

            $t->unique(['row_id','column_id']);
            $t->index(['column_id','value_number']);
            $t->index(['column_id','value_date']);
            // ❌ don't add index(['column_id','value_text']) here for MySQL
        });

        // Add a MySQL-safe prefix index for value_text
        if (Schema::getConnection()->getDriverName() === 'mysql') {
            DB::statement('CREATE INDEX workbook_cells_col_text ON workbook_cells (column_id, value_text(191))');
        } else {
            // On Postgres/SQLite, a normal composite index is fine
            Schema::table('workbook_cells', function (Blueprint $t) {
                $t->index(['column_id','value_text']);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('workbook_cells');
        Schema::dropIfExists('workbook_rows');
        Schema::dropIfExists('workbook_columns');
    }
};
