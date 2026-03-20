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
        Schema::table('payments', function (Blueprint $table) {
            if (!Schema::hasColumn('payments', 'clocking_id')) {
                $table->unsignedBigInteger('clocking_id')->nullable()->after('maintenance_type');
                $table->foreign('clocking_id')->references('id')->on('clockings')->onDelete('set null');
                $table->index('clocking_id');
            }

            if (!Schema::hasColumn('payments', 'invoice_card_id')) {
                $table->unsignedBigInteger('invoice_card_id')->nullable()->after('clocking_id');
                $table->foreign('invoice_card_id')->references('id')->on('invoice_cards')->onDelete('set null');
                $table->index('invoice_card_id');
            }

            if (!Schema::hasColumn('payments', 'source_system')) {
                $table->enum('source_system', ['invoice_system', 'clocking_system'])->default('invoice_system')->after('invoice_card_id');
                $table->index('source_system');
            }

            if (!Schema::hasColumn('payments', 'sync_status')) {
                $table->enum('sync_status', ['synced', 'pending', 'failed'])->default('pending')->after('source_system');
                $table->index('sync_status');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            if (Schema::hasColumn('payments', 'sync_status')) {
                $table->dropIndex(['sync_status']);
                $table->dropColumn('sync_status');
            }

            if (Schema::hasColumn('payments', 'source_system')) {
                $table->dropIndex(['source_system']);
                $table->dropColumn('source_system');
            }

            if (Schema::hasColumn('payments', 'invoice_card_id')) {
                $table->dropForeign(['invoice_card_id']);
                $table->dropIndex(['invoice_card_id']);
                $table->dropColumn('invoice_card_id');
            }

            if (Schema::hasColumn('payments', 'clocking_id')) {
                $table->dropForeign(['clocking_id']);
                $table->dropIndex(['clocking_id']);
                $table->dropColumn('clocking_id');
            }
        });
    }
};