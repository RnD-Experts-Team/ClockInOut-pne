<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('maintenance_requests', function (Blueprint $table) {
            // Source of the maintenance request
            $table->enum('source', ['cognito', 'manual'])->default('cognito')->after('equipment_id');

            // Who created this record (for manual fixes)
            $table->foreignId('created_by_user_id')
                ->nullable()
                ->after('source')
                ->constrained('users')
                ->nullOnDelete();

            // Make Cognito-specific columns nullable
            $table->string('form_id')->nullable()->change();
            $table->string('entry_number')->nullable()->change();
            $table->unsignedBigInteger('requester_id')->nullable()->change();
            $table->unsignedBigInteger('reviewed_by_manager_id')->nullable()->change();
            $table->unsignedBigInteger('urgency_level_id')->nullable()->change();
        });

        // webhook_id unique index — drop and recreate allowing nulls
        // (MySQL unique index already allows multiple NULLs, but we make the column nullable)
        Schema::table('maintenance_requests', function (Blueprint $table) {
            $table->string('webhook_id')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('maintenance_requests', function (Blueprint $table) {
            $table->dropForeign(['created_by_user_id']);
            $table->dropColumn(['source', 'created_by_user_id']);

            // Revert nullable changes
            $table->string('form_id')->nullable(false)->change();
            $table->string('entry_number')->nullable(false)->change();
            $table->unsignedBigInteger('requester_id')->nullable(false)->change();
            $table->unsignedBigInteger('reviewed_by_manager_id')->nullable(false)->change();
            $table->unsignedBigInteger('urgency_level_id')->nullable(false)->change();
            $table->string('webhook_id')->nullable(false)->change();
        });
    }
};
