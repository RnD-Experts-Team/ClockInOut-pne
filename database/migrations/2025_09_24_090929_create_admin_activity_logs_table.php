<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('admin_activity_logs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('admin_user_id');
            $table->string('action_type'); // create, update, delete, view
            $table->string('model_type'); // ApartmentLease, Store, User, etc.
            $table->unsignedBigInteger('model_id');
            $table->string('field_name')->nullable(); // specific field changed
            $table->json('old_value')->nullable(); // previous values
            $table->json('new_value')->nullable(); // new values
            $table->string('ip_address');
            $table->text('user_agent');
            $table->timestamp('performed_at');
            $table->text('description'); // human-readable action description
            $table->timestamps();

            // Indexes for better performance
            $table->index(['admin_user_id', 'performed_at']);
            $table->index(['model_type', 'model_id']);
            $table->index('action_type');
            $table->index('performed_at');

            // Foreign key
            $table->foreign('admin_user_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('admin_activity_logs');
    }
};
