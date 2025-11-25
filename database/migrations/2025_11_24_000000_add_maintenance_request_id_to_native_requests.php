<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (Schema::hasTable('native_requests') && ! Schema::hasColumn('native_requests', 'maintenance_request_id')) {
            Schema::table('native_requests', function (Blueprint $table) {
                $table->unsignedBigInteger('maintenance_request_id')->nullable()->after('id');
                $table->unique('maintenance_request_id', 'native_requests_maintenance_request_id_unique');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('native_requests') && Schema::hasColumn('native_requests', 'maintenance_request_id')) {
            Schema::table('native_requests', function (Blueprint $table) {
                $table->dropUnique('native_requests_maintenance_request_id_unique');
                $table->dropColumn('maintenance_request_id');
            });
        }
    }
};
