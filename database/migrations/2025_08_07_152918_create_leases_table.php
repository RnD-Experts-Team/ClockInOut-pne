<?php
// database/migrations/2025_08_07_000009_create_leases_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('leases', function (Blueprint $table) {
            $table->id();
            $table->foreignId('store_id')->nullable()->constrained('stores')->onDelete('cascade');
            $table->string('store_number')->nullable()->comment('Store identification number');
            $table->string('name')->nullable()->comment('Store name');
            $table->text('store_address')->nullable()->comment('Physical store address');
            $table->decimal('aws', 10, 2)->nullable()->comment('AWS cost (double/float)');
            $table->decimal('base_rent', 10, 2)->nullable()->comment('Base rent cost');
            $table->decimal('percent_increase_per_year', 5, 2)->nullable()->comment('Percentage increase per year');
            $table->decimal('cam', 10, 2)->nullable()->comment('CAM cost (double/float)');
            $table->decimal('insurance', 10, 2)->nullable()->comment('Insurance cost (double/float)');
            $table->decimal('re_taxes', 10, 2)->nullable()->comment('Real Estate taxes cost (double/float)');
            $table->decimal('others', 10, 2)->nullable()->comment('Other costs (double/float)');
            $table->decimal('security_deposit', 10, 2)->nullable()->comment('Security deposit cost (double/float)');
            $table->date('franchise_agreement_expiration_date')->nullable()->comment('Date franchise agreement expires');
            $table->string('renewal_options')->nullable()->comment('Renewal options (e.g., "3,5" means 3 terms, 5 years each)');
            $table->date('initial_lease_expiration_date')->nullable()->comment('Initial lease expiration date');
            $table->integer('sqf')->nullable()->comment('Square footage (number)');
            $table->boolean('hvac')->nullable()->default(false)->comment('HVAC availability (boolean)');
            $table->text('landlord_responsibility')->nullable()->comment('Landlord responsibilities');
            $table->string('landlord_name')->nullable()->comment('Landlord name');
            $table->string('landlord_email')->nullable()->comment('Landlord email');
            $table->string('landlord_phone')->nullable()->comment('Landlord phone number');
            $table->text('landlord_address')->nullable()->comment('Landlord address');
            $table->text('comments')->nullable()->comment('Additional comments');
            $table->timestamps();

            // Add indexes for commonly searched fields
            $table->index('store_number');
            $table->index('name');
            $table->index('franchise_agreement_expiration_date');
            $table->index('initial_lease_expiration_date');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('leases');
    }
};
