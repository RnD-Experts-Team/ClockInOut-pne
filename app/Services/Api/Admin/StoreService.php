<?php

namespace App\Services\Api\Admin;

use App\Models\Store;

class StoreService
{

    public function index()
    {
        return Store::with([
            'maintenanceRequests',
            'leases',
            'apartmentLeases',
            'payments'
        ])
        ->orderBy('store_number')
        ->paginate(15);
    }


    public function store(array $data)
    {
        return Store::create($data);
    }


    public function show(Store $store)
    {
        $store->load([
            'maintenanceRequests.urgencyLevel',
            'leases',
            'apartmentLeases',
            'payments.company'
        ]);

        return $store;
    }


    public function update(Store $store, array $data)
    {
        $data['is_active'] = isset($data['is_active']);

        $store->update($data);

        return $store;
    }


    public function destroy(Store $store)
    {
        if (
            $store->maintenanceRequests()->count() > 0 ||
            $store->leases()->count() > 0 ||
            $store->apartmentLeases()->count() > 0 ||
            $store->payments()->count() > 0
        ) {
            throw new \Exception('Cannot delete store with existing records. Please delete related records first.');
        }

        $store->delete();
    }


    public function toggleStatus(Store $store)
    {
        $store->update([
            'is_active' => !$store->is_active
        ]);

        return $store;
    }

}