<?php
// database/migrations/2025_08_07_000005_create_maintenance_attachments_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('maintenance_attachments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('maintenance_request_id')->constrained('maintenance_requests')->onDelete('cascade');
            $table->string('content_type');
            $table->string('file_name');
            $table->bigInteger('file_size'); // in bytes
            $table->text('download_url'); // Changed to download_url for clarity
            $table->timestamps();
            
            $table->index('maintenance_request_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('maintenance_attachments');
    }
};
