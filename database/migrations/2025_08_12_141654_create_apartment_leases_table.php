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
        Schema::create('apartment_leases', function (Blueprint $table) {
            $table->id();
            $table->integer('store_number')->nullable(); // Changed from decimal to integer
            $table->text('apartment_address');
            $table->decimal('rent', 10, 2);
            $table->decimal('utilities', 10, 2)->nullable();
            $table->integer('number_of_AT'); // Fixed: consistent naming and default value
            $table->integer('has_car')->default(0);
            $table->enum('is_family', ['yes', 'no']); // Added more options for flexibility
            $table->date('expiration_date')->nullable();
            $table->string('drive_time')->nullable();
            $table->text('notes')->nullable();
            $table->text('lease_holder');
            $table->unsignedBigInteger('created_by')->nullable();
            $table->foreign('created_by')
                ->references('id')
                ->on('users')
                ->onDelete('set null');
            $table->timestamps();

            // Add indexes for better performance
            $table->index('expiration_date');
            $table->index('store_number');
            $table->index('is_family');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('apartment_leases', function (Blueprint $table) {
            // Drop the foreign key first
            $table->dropForeign(['created_by']);
        });
        Schema::dropIfExists('apartment_leases');
    }
};
