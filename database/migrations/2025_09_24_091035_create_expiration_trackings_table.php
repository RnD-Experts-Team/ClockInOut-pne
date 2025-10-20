<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('expiration_trackings', function (Blueprint $table) {
            $table->id();
            $table->string('model_type'); // ApartmentLease, Officer, Department
            $table->unsignedBigInteger('model_id');
            $table->date('expiration_date');
            $table->enum('expiration_type', [
                'lease_end',
                'officer_term',
                'department_closure',
                'contract_end',
                'license_expiry'
            ]);
            $table->integer('warning_days')->default(30); // days before expiration to warn
            $table->enum('status', ['active', 'expired', 'renewed', 'extended'])->default('active');
            $table->timestamp('last_reminder_sent')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            // Indexes
            $table->index(['model_type', 'model_id']);
            $table->index('expiration_date');
            $table->index('status');
            $table->index('expiration_type');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('expiration_trackings');
    }
};
