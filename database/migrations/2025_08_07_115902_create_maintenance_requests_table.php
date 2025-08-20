<?php
// database/migrations/2025_08_07_000004_create_maintenance_requests_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('maintenance_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('store_id')->nullable()->constrained('stores')->onDelete('cascade');
            $table->string('form_id');
            $table->string('store')->nullable()->change();            $table->text('description_of_issue');
            $table->foreignId('urgency_level_id')->constrained('urgency_levels');
            $table->string('equipment_with_issue');
            $table->boolean('basic_troubleshoot_done')->default(false);
            $table->date('request_date');
            $table->timestamp('date_submitted');
            $table->integer('entry_number');
            $table->enum('status', ['on_hold', 'in_progress', 'complete'])->default('on_hold');
            $table->text('completion_notes')->nullable();
            $table->foreignId('requester_id')->constrained('requesters');
            $table->foreignId('reviewed_by_manager_id')->constrained('managers');
            $table->string('webhook_id')->unique(); // Store the original "971-955" ID
            $table->timestamps();

            $table->index(['status', 'urgency_level_id']);
            $table->index('webhook_id');
            $table->index('entry_number');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('maintenance_requests');
    }
};
