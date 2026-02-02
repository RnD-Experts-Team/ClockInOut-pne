<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('invoices', function (Blueprint $table) {
            $table->id();
            
            // Invoice Info
            $table->string('invoice_number')->unique();
            $table->foreignId('invoice_card_id')->constrained('invoice_cards')->onDelete('cascade');
            $table->foreignId('store_id')->constrained('stores')->onDelete('cascade');
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade'); // Technician
            
            // Period
            $table->date('period_start');
            $table->date('period_end');
            
            // Labor
            $table->decimal('labor_hours', 10, 2)->default(0);
            $table->decimal('labor_cost', 10, 2)->default(0);
            
            // Materials
            $table->decimal('materials_cost', 10, 2)->default(0);
            
            // Equipment (from payment_equipment_items)
            $table->decimal('equipment_cost', 10, 2)->default(0);
            
            // Mileage
            $table->decimal('total_miles', 10, 2)->default(0);
            $table->decimal('mileage_cost', 10, 2)->default(0);
            
            // Totals
            $table->decimal('subtotal', 10, 2)->default(0);
            $table->decimal('tax_rate', 5, 2)->default(5.00); // 5%
            $table->decimal('tax_amount', 10, 2)->default(0);
            $table->decimal('grand_total', 10, 2)->default(0);
            
            // Status & Files
            $table->enum('status', ['draft', 'sent'])->default('draft');
            $table->string('image_path')->nullable();
            $table->string('pdf_path')->nullable();
            $table->timestamp('sent_at')->nullable();
            $table->string('sent_to_email')->nullable();
            
            $table->timestamps();
            
            // Indexes
            $table->index('invoice_number');
            $table->index('store_id');
            $table->index('user_id');
            $table->index('status');
            $table->index('period_start');
            $table->index('period_end');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('invoices');
    }
};
