<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;


class CreateConfigurationsTable extends Migration
{
    public function up()
    {
        Schema::create('configurations', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();  // To store key like 'gas_payment_rate'
            $table->string('value');          // To store the value of the key
            $table->timestamps();
        });
        
        // Set default gas payment rate
        DB::table('configurations')->insert([
            'key' => 'gas_payment_rate',
            'value' => '10',  // default $10 per mile
        ]);
    }

    public function down()
    {
        Schema::dropIfExists('configurations');
    }
}
