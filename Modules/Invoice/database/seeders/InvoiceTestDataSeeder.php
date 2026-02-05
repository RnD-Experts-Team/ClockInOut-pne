<?php

namespace Modules\Invoice\Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\Invoice\Models\Invoice;
use Modules\Invoice\Models\InvoiceCard;
use Modules\Invoice\Models\InvoiceCardMaterial;
use App\Models\User;
use App\Models\Store;
use App\Models\Clocking;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class InvoiceTestDataSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get first admin user and first store
        $admin = User::where('role', 'admin')->first();
        $technician = User::where('role', 'user')->first() ?? $admin;
        $store = Store::first();

        if (!$admin || !$store) {
            $this->command->error('Please ensure you have at least one admin user and one store in the database.');
            return;
        }

        $this->command->info('Creating test invoice data...');

        // Create a clocking session
        $clocking = Clocking::create([
            'user_id' => $technician->id,
            'clock_in' => Carbon::now()->subDays(7)->setTime(8, 0),
            'clock_out' => Carbon::now()->subDays(7)->setTime(17, 0),
            'starting_miles' => 10.5,
            'return_miles' => 12.3,
            'total_session_miles' => 45.8,
        ]);

        // Create invoice card
        $invoiceCard = InvoiceCard::create([
            'clocking_id' => $clocking->id,
            'store_id' => $store->id,
            'user_id' => $technician->id,
            'start_time' => Carbon::now()->subDays(7)->setTime(9, 0),
            'end_time' => Carbon::now()->subDays(7)->setTime(16, 30),
            'miles_to_store' => 15.5,
            'allocated_return_miles' => 12.3,
            'total_miles' => 27.8,
            'mileage_payment' => 27.8 * 0.50, // $0.50 per mile
            'labor_hours' => 7.5,
            'labor_cost' => 7.5 * 50, // $50/hour
            'materials_cost' => 235.00,
            'total_cost' => 0,
            'status' => 'completed',
            'notes' => 'Completed maintenance work at store',
        ]);

        // Add materials
        InvoiceCardMaterial::create([
            'invoice_card_id' => $invoiceCard->id,
            'item_name' => 'Front and rear brake cables',
            'cost' => 100.00,
            'receipt_photos' => json_encode([]),
        ]);

        InvoiceCardMaterial::create([
            'invoice_card_id' => $invoiceCard->id,
            'item_name' => 'New set of pedal arms',
            'cost' => 50.00,
            'receipt_photos' => json_encode([]),
        ]);

        InvoiceCardMaterial::create([
            'invoice_card_id' => $invoiceCard->id,
            'item_name' => 'HVAC Filter Replacement',
            'cost' => 85.00,
            'receipt_photos' => json_encode([]),
        ]);

        // Update materials cost
        $invoiceCard->update([
            'materials_cost' => $invoiceCard->materials()->sum('cost'),
        ]);

        // Create equipment purchase (if payments table exists)
        if (DB::getSchemaBuilder()->hasTable('payments')) {
            // Check if company exists
            $company = DB::table('companies')->first();
            
            if ($company) {
                $payment = DB::table('payments')->insertGetId([
                    'store_id' => $store->id,
                    'company_id' => $company->id,
                    'date' => Carbon::now()->subDays(7),
                    'cost' => 3405.00,
                    'notes' => 'Equipment purchase for store',
                    'paid' => true,
                    'is_admin_equipment' => true,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                // Add equipment items if table exists
                if (DB::getSchemaBuilder()->hasTable('payment_equipment_items')) {
                    DB::table('payment_equipment_items')->insert([
                        [
                            'payment_id' => $payment,
                            'item_name' => 'Commercial Freezer - Bibline Model X500',
                            'quantity' => 1,
                            'unit_cost' => 2850.00,
                            'total_cost' => 2850.00,
                            'created_at' => now(),
                            'updated_at' => now(),
                        ],
                        [
                            'payment_id' => $payment,
                            'item_name' => 'Stainless Steel Shelving Units',
                            'quantity' => 3,
                            'unit_cost' => 185.00,
                            'total_cost' => 555.00,
                            'created_at' => now(),
                            'updated_at' => now(),
                        ],
                    ]);
                }
            }
        }

        // Calculate totals
        $laborCost = $invoiceCard->labor_cost;
        $materialsCost = $invoiceCard->materials_cost;
        $equipmentCost = 3405.00; // From equipment purchase above
        $mileageCost = $invoiceCard->mileage_payment;
        
        $subtotal = $laborCost + $materialsCost + $equipmentCost + $mileageCost;
        $taxRate = 0;
        $taxAmount = $subtotal * ($taxRate / 100);
        $grandTotal = $subtotal + $taxAmount;

        // Create invoice
        $invoice = Invoice::create([
            'invoice_number' => 'INT-0001',
            'invoice_card_id' => $invoiceCard->id,
            'store_id' => $store->id,
            'user_id' => $technician->id,
            'period_start' => Carbon::now()->subDays(7)->startOfDay(),
            'period_end' => Carbon::now()->subDays(7)->endOfDay(),
            'labor_hours' => $invoiceCard->labor_hours,
            'labor_cost' => $laborCost,
            'materials_cost' => $materialsCost,
            'equipment_cost' => $equipmentCost,
            'total_miles' => $invoiceCard->total_miles,
            'mileage_cost' => $mileageCost,
            'subtotal' => $subtotal,
            'tax_rate' => $taxRate,
            'tax_amount' => $taxAmount,
            'grand_total' => $grandTotal,
            'status' => 'draft',
        ]);

        // Create a second invoice (sent)
        $clocking2 = Clocking::create([
            'user_id' => $technician->id,
            'clock_in' => Carbon::now()->subDays(14)->setTime(8, 0),
            'clock_out' => Carbon::now()->subDays(14)->setTime(16, 0),
            'starting_miles' => 8.2,
            'return_miles' => 9.5,
            'total_session_miles' => 32.7,
        ]);

        $invoiceCard2 = InvoiceCard::create([
            'clocking_id' => $clocking2->id,
            'store_id' => $store->id,
            'user_id' => $technician->id,
            'start_time' => Carbon::now()->subDays(14)->setTime(9, 0),
            'end_time' => Carbon::now()->subDays(14)->setTime(15, 0),
            'miles_to_store' => 12.0,
            'allocated_return_miles' => 9.5,
            'total_miles' => 21.5,
            'mileage_payment' => 21.5 * 0.50,
            'labor_hours' => 6.0,
            'labor_cost' => 6.0 * 50,
            'materials_cost' => 150.00,
            'total_cost' => 0,
            'status' => 'completed',
        ]);

        InvoiceCardMaterial::create([
            'invoice_card_id' => $invoiceCard2->id,
            'item_name' => 'Air Filter',
            'cost' => 75.00,
        ]);

        InvoiceCardMaterial::create([
            'invoice_card_id' => $invoiceCard2->id,
            'item_name' => 'Cleaning Supplies',
            'cost' => 75.00,
        ]);

        $invoiceCard2->update([
            'materials_cost' => $invoiceCard2->materials()->sum('cost'),
        ]);

        $subtotal2 = $invoiceCard2->labor_cost + $invoiceCard2->materials_cost + $invoiceCard2->mileage_payment;
        $taxAmount2 = $subtotal2 * 0;
        $grandTotal2 = $subtotal2 + $taxAmount2;

        Invoice::create([
            'invoice_number' => 'INT-0002',
            'invoice_card_id' => $invoiceCard2->id,
            'store_id' => $store->id,
            'user_id' => $technician->id,
            'period_start' => Carbon::now()->subDays(14)->startOfDay(),
            'period_end' => Carbon::now()->subDays(14)->endOfDay(),
            'labor_hours' => $invoiceCard2->labor_hours,
            'labor_cost' => $invoiceCard2->labor_cost,
            'materials_cost' => $invoiceCard2->materials_cost,
            'equipment_cost' => 0,
            'total_miles' => $invoiceCard2->total_miles,
            'mileage_cost' => $invoiceCard2->mileage_payment,
            'subtotal' => $subtotal2,
            'tax_rate' => 5.00,
            'tax_amount' => $taxAmount2,
            'grand_total' => $grandTotal2,
            'status' => 'sent',
            'sent_at' => Carbon::now()->subDays(13),
            'sent_to_email' => 'customer@example.com',
        ]);

        $this->command->info('✅ Test invoice data created successfully!');
        $this->command->info('   - 2 Invoices created');
        $this->command->info('   - 1 Draft invoice (INT-0001)');
        $this->command->info('   - 1 Sent invoice (INT-0002)');
        $this->command->info('   - Visit: /Invoice/invoices to view them');
    }
}
