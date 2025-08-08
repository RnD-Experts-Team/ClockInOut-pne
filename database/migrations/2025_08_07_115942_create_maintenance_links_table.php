<?php
// database/migrations/2025_08_07_000006_create_maintenance_links_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('maintenance_links', function (Blueprint $table) {
            $table->id();
            $table->foreignId('maintenance_request_id')->constrained('maintenance_requests')->onDelete('cascade');
            $table->enum('link_type', ['public_link', 'internal_link', 'document1', 'document2']);
            $table->text('download_url'); // All links are download links
            $table->string('description')->nullable();
            $table->timestamps();
            
            $table->index(['maintenance_request_id', 'link_type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('maintenance_links');
    }
};
