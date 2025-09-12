<?php
// app/Http/Controllers/StoreController.php

namespace App\Http\Controllers;

use App\Models\Store;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class StoreController extends Controller
{
    /**
     * Display a listing of the stores.
     */
    public function index(): View
    {
        $stores = Store::with(['maintenanceRequests', 'leases', 'apartmentLeases', 'payments'])
            ->orderBy('store_number')
            ->paginate(15);

        return view('admin.stores.index', compact('stores'));
    }

    /**
     * Show the form for creating a new store.
     */
    public function create(): View
    {
        return view('admin.stores.create');
    }

    /**
     * Store a newly created store in storage.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'store_number' => 'required|string|max:255|unique:stores,store_number',
            'name' => 'nullable|string|max:255',
            'address' => 'nullable|string',
            'is_active' => 'boolean',
        ]);

        Store::create($validated);

        return redirect()->route('stores.index')
            ->with('success', 'Store created successfully!');
    }

    /**
     * Display the specified store.
     */
    public function show(Store $store): View
    {
        $store->load([
            'maintenanceRequests.urgencyLevel',
            'leases',
            'apartmentLeases',
            'payments.company'
        ]);

        return view('admin.stores.show', compact('store'));
    }

    /**
     * Show the form for editing the specified store.
     */
    public function edit(Store $store): View
    {
        return view('admin.stores.edit', compact('store'));
    }

    /**
     * Update the specified store in storage.
     */
    public function update(Request $request, Store $store): RedirectResponse
    {
        $validated = $request->validate([
            'store_number' => 'required|string|max:255|unique:stores,store_number,' . $store->id,
            'name' => 'nullable|string|max:255',
            'address' => 'nullable|string',
            'is_active' => 'nullable',
            ]);
        $validated['is_active'] = $request->has('is_active') ? true : false;


        $store->update($validated);

        return redirect()->route('stores.index')
            ->with('success', 'Store updated successfully!');
    }

    /**
     * Remove the specified store from storage.
     */
    public function destroy(Store $store): RedirectResponse
    {
        // Check if store has related records
        if ($store->maintenanceRequests()->count() > 0 ||
            $store->leases()->count() > 0 ||
            $store->apartmentLeases()->count() > 0 ||
            $store->payments()->count() > 0) {

            return redirect()->route('admin.stores.index')
                ->with('error', 'Cannot delete store with existing records. Please delete related records first.');
        }

        $store->delete();

        return redirect()->route('stores.index')
            ->with('success', 'Store deleted successfully!');
    }

    /**
     * Toggle store active status
     */
    public function toggleStatus(Store $store): RedirectResponse
    {
        $store->update(['is_active' => !$store->is_active]);

        $status = $store->is_active ? 'activated' : 'deactivated';

        return redirect()->back()
            ->with('success', "Store {$status} successfully!");
    }
}
