<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('schedule_shifts', function (Blueprint $table) {
            // Increase column lengths to 255 characters
            $table->string('shift_type', 255)->nullable()->change();
            $table->string('role', 255)->nullable()->change();
        });
    }

    public function down()
    {
        Schema::table('schedule_shifts', function (Blueprint $table) {
            // Revert to smaller lengths if needed
            $table->string('shift_type', 50)->nullable()->change();
            $table->string('role', 50)->nullable()->change();
        });
    }
};
