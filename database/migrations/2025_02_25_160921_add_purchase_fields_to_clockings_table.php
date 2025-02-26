<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddPurchaseFieldsToClockingsTable extends Migration
{
    public function up()
    {
        Schema::table('clockings', function (Blueprint $table) {
            // Indicates if user bought something
            $table->boolean('bought_something')->default(false);

            // Cost of the purchase
            // Adjust precision/scale as needed (8,2) => up to 999,999.99
            $table->decimal('purchase_cost', 8, 2)->nullable();

            // Path to the uploaded receipt image
            $table->string('purchase_receipt')->nullable();
        });
    }

    public function down()
    {
        Schema::table('clockings', function (Blueprint $table) {
            $table->dropColumn(['bought_something', 'purchase_cost', 'purchase_receipt']);
        });
    }
}
