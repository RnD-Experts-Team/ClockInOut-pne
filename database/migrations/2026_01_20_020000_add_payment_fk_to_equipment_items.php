<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('payment_equipment_items', function (Blueprint $table) {
            if (Schema::hasTable('payments') && Schema::hasColumn('payment_equipment_items', 'payment_id')) {
                try {
                    $table->foreign('payment_id')->references('id')->on('payments')->onDelete('cascade');
                } catch (\Exception $e) {
                    // ignore if can't add fk
                }
            }
        });
    }

    public function down()
    {
        Schema::table('payment_equipment_items', function (Blueprint $table) {
            if (Schema::hasColumn('payment_equipment_items', 'payment_id')) {
                $table->dropForeign(['payment_id']);
            }
        });
    }
};