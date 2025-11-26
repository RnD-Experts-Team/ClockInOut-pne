<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * This migration adds tracking fields to distinguish native requests
     * created from CognitoForms webhooks vs. native page submissions.
     */
    public function up(): void
    {
        Schema::table('native_requests', function (Blueprint $table) {
            // Store the original requester name/info from CognitoForms submission
            $table->string('external_requester')->nullable()->after('requester_id');
            
            // Flag to indicate if this native request originated from CognitoForms
            $table->boolean('is_from_cognito')->default(false)->after('external_requester');
            
            // Add index for filtering by Cognito origin
            $table->index('is_from_cognito');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('native_requests', function (Blueprint $table) {
            $table->dropIndex(['is_from_cognito']);
            $table->dropColumn(['external_requester', 'is_from_cognito']);
        });
    }
};
