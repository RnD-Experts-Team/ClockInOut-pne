<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreNativeRequestRequest;
use App\Models\Native\NativeRequest;
use App\Models\Native\NativeRequestAttachment;
use App\Models\Native\NativeUrgencyLevel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class NativeRequestController extends Controller
{
    /**
     * Display the form to create a new request.
     */
    public function create()
    {
        // Check if user is a global store manager
        if (auth()->user()->is_global_store_manager) {
            // Global managers can create requests for ANY store
            $stores = \App\Models\Store::orderBy('store_number')->get();
        } else {
            // Regular managers only see their assigned stores
            $stores = auth()->user()->managedStores()->orderBy('store_number')->get();
        }
        
        // Get all urgency levels
        $urgencyLevels = NativeUrgencyLevel::orderBy('level')->get();
        
        return view('native-requests.create', compact('stores', 'urgencyLevels'));
    }

    /**
     * Store a newly created request in storage.
     */
    public function store(StoreNativeRequestRequest $request)
    {
        // Create the native request (from native page, not CognitoForms)
        $nativeRequest = NativeRequest::create([
            'store_id' => $request->store_id,
            'requester_id' => auth()->id(),
            'is_from_cognito' => false, // Native page submission
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
                $filename = time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
                
                // Store in native-requests/{request_id}/ directory
                $path = $file->storeAs(
                    'native-requests/' . $nativeRequest->id,
                    $filename,
                    'public'
                );

                // Save attachment record
                NativeRequestAttachment::create([
                    'native_request_id' => $nativeRequest->id,
                    'file_name' => $file->getClientOriginalName(),
                    'file_path' => $path,
                    'file_size' => $file->getSize(),
                    'mime_type' => $file->getMimeType(),
                ]);
            }
        }

        return redirect()->route('native.requests.index')
            ->with('success', 'Ticket Submitted Successfully!')
            ->with('ticket_id', $nativeRequest->id);
    }

    /**
     * Display a listing of user's requests (My Tickets).
     */
    public function index(Request $request)
    {
        // Build query based on user type
        $user = auth()->user();
        
        if ($user->is_global_store_manager) {
            // Global managers see ALL tickets from ALL stores
            $query = NativeRequest::query()
                ->with(['store', 'urgencyLevel', 'assignedTo', 'attachments']);
            
            // Get all stores for filter dropdown
            $availableStores = \App\Models\Store::orderBy('store_number')->get();
        } else {
            // Regular managers only see tickets from their assigned stores
            $managedStoreIds = $user->managedStores->pluck('id')->toArray();
            
            $query = NativeRequest::query()
                ->whereIn('store_id', $managedStoreIds)
                ->with(['store', 'urgencyLevel', 'assignedTo', 'attachments']);
            
            // Get only managed stores for filter dropdown
            $availableStores = $user->managedStores()->orderBy('store_number')->get();
        }
        
        // Apply store filter if selected
        if ($request->filled('store_filter') && $request->store_filter !== 'all') {
            $query->where('store_id', $request->store_filter);
        }

        // Apply status filter
        if ($request->filled('status') && $request->status !== 'all') {
            $query->where('status', $request->status);
        }

        // Apply search
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('equipment_with_issue', 'LIKE', "%{$search}%")
                  ->orWhere('description_of_issue', 'LIKE', "%{$search}%");
            });
        }

        // Apply date filters
        if ($request->filled('date_from')) {
            $query->where('request_date', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->where('request_date', '<=', $request->date_to);
        }

        // Order and paginate
        $requests = $query->orderBy('request_date', 'desc')
            ->orderBy('created_at', 'desc')
            ->paginate(20)
            ->withQueryString();

        // Get filter options
        $urgencyLevels = NativeUrgencyLevel::orderBy('level')->get();

        return view('native-requests.index', compact('requests', 'urgencyLevels', 'availableStores'));
    }

    /**
     * Display the specified request.
     */
    public function show(NativeRequest $request)
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

        // Eager load relationships
        $request->load(['store', 'requester', 'urgencyLevel', 'assignedTo', 'attachments']);

        return view('native-requests.show', compact('request'));
    }
}
