<?php

namespace App\Services\Api\Admin;

use App\Models\Native\NativeRequest;
use App\Models\Native\NativeRequestAttachment;
use App\Models\Native\NativeUrgencyLevel;
use App\Models\Store;

class NativeRequestService
{

    
    public function store($request)
    {

        $nativeRequest = NativeRequest::create([
            'store_id' => $request->store_id,
            'requester_id' => auth()->id(),
            'is_from_cognito' => false,
            'equipment_with_issue' => $request->equipment_with_issue,
            'description_of_issue' => $request->description_of_issue,
            'urgency_level_id' => $request->urgency_level_id,
            'basic_troubleshoot_done' => $request->basic_troubleshoot_done,
            'request_date' => today(),
            'status' => NativeRequest::STATUS_PENDING,
        ]);
        // Handle file attachments if present

        if ($request->hasFile('attachments')) {

            foreach ($request->file('attachments') as $file) {
                // Generate unique filename

                $filename = time().'_'.uniqid().'.'.$file->getClientOriginalExtension();
                // Store in native-requests/{request_id}/ directory

                $path = $file->storeAs(
                    'native-requests/'.$nativeRequest->id,
                    $filename,
                    'public'
                );

                NativeRequestAttachment::create([
                    'native_request_id' => $nativeRequest->id,
                    'file_name' => $file->getClientOriginalName(),
                    'file_path' => $path,
                    'file_size' => $file->getSize(),
                    'mime_type' => $file->getMimeType(),
                ]);
            }
        }

        return $nativeRequest;
    }

    public function index($request)
    {

        $user = auth()->user();

        if ($user->is_global_store_manager) {
            // Global managers see ALL tickets from ALL stores

            $query = NativeRequest::with(['store','urgencyLevel','assignedTo','attachments']);
            // Get all stores for filter dropdown

        } else {
            // Regular managers only see tickets from their assigned stores

            $managedStoreIds = $user->managedStores->pluck('id')->toArray();

            $query = NativeRequest::whereIn('store_id',$managedStoreIds)
                ->with(['store','urgencyLevel','assignedTo','attachments']);
            // Get only managed stores for filter dropdown
            $availableStores = $user->managedStores()->orderBy('store_number')->get();
        }

        if ($request->filled('store_filter') && $request->store_filter !== 'all') {
            $query->where('store_id',$request->store_filter);
        }

        if ($request->filled('status') && $request->status !== 'all') {
            $query->where('status',$request->status);
        }

        if ($request->filled('search')) {

            $search = $request->search;

            $query->where(function ($q) use ($search) {

                $q->where('equipment_with_issue','LIKE',"%{$search}%")
                  ->orWhere('description_of_issue','LIKE',"%{$search}%");

            });
        }

        if ($request->filled('date_from')) {
            $query->where('request_date','>=',$request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->where('request_date','<=',$request->date_to);
        }

        $requests = $query->orderBy('request_date','desc')
            ->orderBy('created_at','desc')
            ->paginate(20);

        $urgencyLevels = NativeUrgencyLevel::orderBy('level')->get();

        return [
            'requests' => $requests,
            'urgency_levels' => $urgencyLevels,
            'availableStores'=>$availableStores
        ];
    }

    public function show($request)
    {

        $user = auth()->user();
        
        // Authorization check
        if ($user->is_global_store_manager) {
            // Global managers can view any ticket
            // No additional check needed
        } else {
            // Regular managers can only view tickets from their assigned stores
            $managedStoreIds = $user->managedStores->pluck('id')->toArray();
            
            if (!in_array($request->store_id, $managedStoreIds)) {
                abort(403, 'Unauthorized access to this request.');
            }
        }
        $request->load([
            'store',
            'requester',
            'urgencyLevel',
            'assignedTo',
            'attachments'
        ]);

        return $request;
    }
}