<?php

namespace Modules\Invoice\Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\Invoice\Models\InvoiceRecipient;
use App\Models\Store;

class InvoiceRecipientSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get all stores
        $stores = Store::all();

        foreach ($stores as $store) {
            // Check if recipient already exists for this store
            if (InvoiceRecipient::where('store_id', $store->id)->exists()) {
                continue;
            }

            // Create a default recipient for each store
            InvoiceRecipient::create([
                'store_id' => $store->id,
                'name' => 'Store Manager',
                'email' => 'manager.store' . $store->store_number . '@example.com',
                'is_default' => true,
            ]);
        }
    }
}
