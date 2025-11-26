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
        Schema::create('native_request_attachments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('native_request_id')->constrained('native_requests')->onDelete('cascade');
            
            // File details
            $table->string('file_name'); // Original filename
            $table->string('file_path'); // Path in storage: native-requests/{request_id}/filename
            $table->bigInteger('file_size'); // Size in bytes
            $table->string('mime_type'); // image/jpeg, image/png, application/pdf, etc.
            
            $table->timestamps();

            // Index for quick lookups
            $table->index('native_request_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('native_request_attachments');
    }
};
