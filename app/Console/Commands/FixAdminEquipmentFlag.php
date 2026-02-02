<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Payment;

class FixAdminEquipmentFlag extends Command
{
    protected $signature = 'fix:admin-equipment {--store-id=2}';
    protected $description = 'Fix admin equipment flag for payments with equipment items';

    public function handle()
    {
        $storeId = $this->option('store-id');
        
        $this->info("=== FIXING ADMIN EQUIPMENT FLAGS FOR STORE {$storeId} ===");
        
        // Find all payments with equipment items that aren't marked as admin equipment
        $paymentsToFix = Payment::where('store_id', $storeId)
            ->whereHas('equipmentItems')
            ->where('is_admin_equipment', false)
            ->with('equipmentItems')
            ->get();
        
        $this->info("Found {$paymentsToFix->count()} payments to fix:");
        
        foreach ($paymentsToFix as $payment) {
            $this->line("  Payment #{$payment->id}: {$payment->equipmentItems->count()} items, \${$payment->equipmentTotal()}");
            
            $payment->is_admin_equipment = true;
            $payment->save();
            
            $this->info("    ✅ Fixed!");
        }
        
        // Verify the fix
        $this->info("\n=== VERIFICATION ===");
        $adminPayments = Payment::where('store_id', $storeId)
            ->where('is_admin_equipment', true)
            ->whereHas('equipmentItems')
            ->with('equipmentItems')
            ->get();
        
        $this->info("Admin equipment payments for store {$storeId}: {$adminPayments->count()}");
        foreach ($adminPayments as $payment) {
            $this->line("  Payment #{$payment->id}: {$payment->equipmentItems->count()} items, \${$payment->equipmentTotal()}");
        }
        
        $this->info("\n✅ ALL DONE!");
        
        return 0;
    }
}