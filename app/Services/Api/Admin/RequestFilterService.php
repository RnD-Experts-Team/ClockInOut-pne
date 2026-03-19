<?php

namespace App\Services\Api\Admin;


use App\Models\NativeRequest;

class RequestFilterService
{
    
    public function applyFilters($query, $request)
    {
        if ($request->filled('status') && $request->status !== 'all') {
            $query->where('status', $request->status);
        }

        if ($request->filled('urgency') && $request->urgency !== 'all') {
            $query->where('urgency_level_id', $request->urgency);
        }

        if ($request->filled('store') && $request->store !== 'all') {
            $query->where('store_id', $request->store);
        }

        if ($request->filled('assigned_to') && $request->assigned_to !== 'all') {
            if ($request->assigned_to === 'unassigned') {
                $query->whereNull('assigned_to');
            } else {
                $query->where('assigned_to', $request->assigned_to);
            }
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('equipment_with_issue', 'LIKE', "%{$search}%")
                    ->orWhere('description_of_issue', 'LIKE', "%{$search}%")
                    ->orWhereHas('store', function ($storeQuery) use ($search) {
                        $storeQuery->where('store_number', 'LIKE', "%{$search}%")
                            ->orWhere('name', 'LIKE', "%{$search}%");
                    })
                    ->orWhereHas('requester', function ($requesterQuery) use ($search) {
                        $requesterQuery->where('name', 'LIKE', "%{$search}%");
                    });
            });
        }

        return $query;
    }
}

  