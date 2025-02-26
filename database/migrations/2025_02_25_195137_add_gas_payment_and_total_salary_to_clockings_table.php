<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
{
    Schema::table('clockings', function (Blueprint $table) {
        $table->decimal('gas_payment', 8, 2)->nullable()->after('purchase_receipt');
        $table->decimal('total_salary', 8, 2)->nullable()->after('gas_payment');
    });
}

public function down()
{
    Schema::table('clockings', function (Blueprint $table) {
        $table->dropColumn(['gas_payment', 'total_salary']);
    });
}
};
