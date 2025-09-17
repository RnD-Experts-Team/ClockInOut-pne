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
        Schema::create('webhook_notifications', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('maintenance_request_id');
            $table->string('type'); // 'new_request', 'urgent_request', etc.
            $table->text('message');
            $table->timestamp('read_at')->nullable();
            $table->boolean('is_broadcast')->default(false);
            $table->timestamps();

            $table->foreign('maintenance_request_id')->references('id')->on('maintenance_requests');
            $table->index(['read_at']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('webhook_notifications');
    }

};
