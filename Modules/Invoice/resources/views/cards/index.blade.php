@extends('layouts.app')

@section('content')
<div class="min-h-screen bg-gradient-to-br from-orange-50 to-orange-100 py-8" dir="{{ getDirection() }}">
    <div class="mx-auto max-w-md px-4">
        
        <!-- Page Title -->
        <div class="mb-8 text-center">
            <h1 class="text-3xl font-bold text-black-900">{{ __('messages.attendance') }}</h1>
            <p class="mt-2 text-sm text-black-600">Work Session Management</p>
        </div>

        <!-- Demo Notice Banner -->
        <div class="animate-fade-in mb-6 rounded-xl bg-gradient-to-r from-blue-50 to-blue-100 border-2 border-blue-200 p-4 shadow-sm">
            <div class="flex items-start gap-3">
                <div class="flex-shrink-0">
                    <svg class="h-6 w-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
                <div class="flex-1">
                    <h3 class="text-sm font-bold text-blue-900">Invoice Card System</h3>
                    <p class="mt-1 text-xs leading-relaxed text-blue-800">
                        Manage multiple store visits in a single session. Track purchases, upload receipts, and complete work efficiently.
                    </p>
                </div>
            </div>
        </div>

        <!-- Active/In Progress Cards -->
        @foreach($invoiceCards->where('status', 'in_progress') as $card)
        <a href="{{ route('invoice.cards.show', $card->id) }}" class="block animate-fade-in mb-6 space-y-4 rounded-2xl bg-white p-5 shadow-lg ring-1 ring-orange-900/5 transition-all duration-300 hover:shadow-xl hover:scale-[1.02] cursor-pointer" id="card-{{ $card->id }}">
            
            <!-- Store Selection (Read-only for existing cards) -->
            <div class="rounded-lg bg-orange-50 p-4">
                <div class="flex items-start gap-3">
                    <div class="flex-shrink-0">
                        <div class="flex h-10 w-10 items-center justify-center rounded-full bg-orange-100">
                            <svg class="h-5 w-5 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
                            </svg>
                        </div>
                    </div>
                    <div class="flex-1">
                        <p class="text-sm font-semibold text-black-800">{{ $card->store->store_number }} - {{ $card->store->name }}</p>
                        <p class="mt-0.5 text-xs text-black-600">{{ $card->store->address }}</p>
                    </div>
                    <div class="flex-shrink-0">
                        <svg class="h-5 w-5 text-orange-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                        </svg>
                    </div>
                </div>
            </div>

            <!-- Maintenance Requests Summary -->
            @php
                // Get maintenance requests assigned to current user for this store
                $assignedRequests = \App\Models\MaintenanceRequest::where('store_id', $card->store_id)
                    ->where('assigned_to', Auth::id())
                    ->whereIn('status', ['on_hold', 'in_progress', 'pending'])
                    ->with('urgencyLevel')
                    ->orderBy('urgency_level_id', 'asc')
                    ->take(2)
                    ->get();
                    
                $totalRequests = \App\Models\MaintenanceRequest::where('store_id', $card->store_id)
                    ->where('assigned_to', Auth::id())
                    ->whereIn('status', ['on_hold', 'in_progress', 'pending'])
                    ->count();
            @endphp
            
            @if($assignedRequests->count() > 0)
            <div class="rounded-lg">
                <div class="mb-2 flex items-center justify-between">
                    <label class="block text-sm font-medium text-black-700">Your Tasks</label>
                    <span class="rounded-full bg-orange-100 px-2.5 py-0.5 text-xs font-medium text-orange-800">
                        {{ $totalRequests }} {{ $totalRequests == 1 ? 'Task' : 'Tasks' }}
                    </span>
                </div>
                @foreach($assignedRequests as $mr)
                <div class="mb-2 space-y-2 rounded-xl border border-orange-200 bg-gradient-to-br from-orange-50 to-white p-3">
                    <div class="flex items-start justify-between gap-2">
                        <div class="flex-1">
                            <p class="text-xs font-semibold text-black-800">{{ $mr->equipment_with_issue }}</p>
                            <p class="mt-1 text-xs leading-relaxed text-black-600">{{ Str::limit($mr->description_of_issue, 80) }}</p>
                        </div>
                        <span class="rounded-full bg-orange-600 px-2 py-0.5 text-xs font-bold text-white">#{{ $mr->id }}</span>
                    </div>
                </div>
                @endforeach
                @if($totalRequests > 2)
                <p class="text-xs text-center text-orange-600 font-medium">+ {{ $totalRequests - 2 }} more tasks</p>
                @endif
            </div>
            @else
            <div class="rounded-lg border-2 border-dashed border-orange-200 bg-orange-50/30 p-4 text-center">
                <p class="text-xs text-black-500">No tasks assigned yet</p>
            </div>
            @endif

            <!-- Bought Items Summary -->
            <div class="rounded-lg">
                <div class="mb-2 flex items-center justify-between">
                    <label class="block text-sm font-medium text-black-700">{{ __('invoice.bought_items') }}</label>
                    <span class="text-xs font-medium text-black-500">{{ $card->materials->count() }} items</span>
                </div>
                
                @if($card->materials->count() > 0)
                <div class="space-y-1">
                    @foreach($card->materials->take(2) as $material)
                    <div class="flex gap-2 items-center bg-orange-50 p-2 rounded-lg">
                        <div class="flex-1">
                            <p class="text-xs font-medium text-black-800">{{ $material->item_name }}</p>
                        </div>
                        <p class="text-xs text-black-600 font-semibold">${{ number_format($material->cost, 2) }}</p>
                    </div>
                    @endforeach
                    @if($card->materials->count() > 2)
                    <p class="text-xs text-center text-orange-600 font-medium">+ {{ $card->materials->count() - 2 }} more items</p>
                    @endif
                </div>
                @else
                <p class="text-xs text-black-500 text-center py-2">No items added yet</p>
                @endif
            </div>

            <!-- Click to Open Hint -->
            <div class="rounded-lg bg-blue-50 border border-blue-200 p-3 text-center">
                <p class="text-xs font-medium text-blue-800">
                    <svg class="inline h-4 w-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 15l-2 5L9 9l11 4-5 2zm0 0l5 5M7.188 2.239l.777 2.897M5.136 7.965l-2.898-.777M13.95 4.05l-2.122 2.122m-5.657 5.656l-2.12 2.122"></path>
                    </svg>
                    Click to open and manage this card
                </p>
            </div>
        </a>
        @endforeach

        <!-- Completed Cards (Collapsed) -->
        @foreach($invoiceCards->whereIn('status', ['completed', 'not_done']) as $card)
        <div class="animate-fade-in mb-6 overflow-hidden rounded-2xl bg-white shadow-md ring-1 ring-{{ $card->status === 'completed' ? 'green' : 'red' }}-900/10 transition-all duration-300 hover:shadow-lg">
            <div class="cursor-pointer p-5" onclick="this.parentElement.querySelector('.card-details').classList.toggle('hidden')">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <div class="flex h-10 w-10 flex-shrink-0 items-center justify-center rounded-full bg-{{ $card->status === 'completed' ? 'green' : 'red' }}-100">
                            <svg class="h-6 w-6 text-{{ $card->status === 'completed' ? 'green' : 'red' }}-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                @if($card->status === 'completed')
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                @else
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                @endif
                            </svg>
                        </div>
                        <div>
                            <p class="text-sm font-bold text-black-800">{{ $card->store->store_number }} - {{ $card->store->name }}</p>
                            <p class="text-xs text-black-500">{{ $card->maintenanceRequests->count() }} requests • {{ $card->materials->count() }} items • ${{ number_format($card->total_cost, 2) }}</p>
                        </div>
                    </div>
                    <svg class="h-5 w-5 text-black-400 transition-transform duration-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                    </svg>
                </div>
            </div>
            <div class="card-details hidden border-t border-{{ $card->status === 'completed' ? 'green' : 'red' }}-100 bg-{{ $card->status === 'completed' ? 'green' : 'red' }}-50/30 p-5">
                <p class="text-xs text-black-600">Completed at {{ $card->end_time->format('g:i A') }}</p>
            </div>
        </div>
        @endforeach

        <!-- Add New Store Card Button -->
        <button type="button" onclick="document.getElementById('createCardModal').classList.remove('hidden')"
                class="mb-6 w-full rounded-xl border-2 border-dashed border-orange-300 bg-white py-4 text-center shadow-sm transition-all duration-300 hover:border-orange-400 hover:bg-orange-50 hover:shadow-md">
            <svg class="mx-auto mb-1 h-6 w-6 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
            </svg>
            <span class="text-sm font-semibold text-orange-700">Add Another Store</span>
        </button>

        <!-- Clock Out Button -->
        <div class="rounded-xl bg-gradient-to-r from-orange-100 to-orange-50 p-5 shadow-md">
            <div class="text-center">
                <h3 class="text-lg font-bold text-black-900">Ready to Clock Out?</h3>
                <p class="mt-1 text-sm text-black-600">Complete all cards before clocking out</p>
                <a href="{{ route('clocking.index') }}" 
                   class="mt-4 inline-flex items-center px-6 py-3 bg-black-800 text-white rounded-xl font-bold shadow-lg hover:bg-black-900 transition-all">
                    Proceed to Clock Out
                    <svg class="ml-2 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path>
                    </svg>
                </a>
            </div>
        </div>
    </div>
</div>

<!-- Create Card Modal -->
<div id="createCardModal" class="hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
    <div class="bg-white rounded-2xl p-6 max-w-md w-full mx-4">
        <h3 class="text-xl font-bold text-black-900 mb-4">Create New Invoice Card</h3>
        
        <form action="{{ route('invoice.cards.store') }}" method="POST">
            @csrf
            
            <!-- Store Selection -->
            <div class="mb-4">
                <label class="block text-sm font-medium text-black-700 mb-2">Select Store</label>
                <select name="store_id" required class="block w-full rounded-lg border-orange-300 shadow-sm focus:border-orange-500 focus:ring-orange-500">
                    <option value="">Choose a store...</option>
                    @foreach($stores as $store)
                        <option value="{{ $store->id }}">{{ $store->store_number }} - {{ $store->name }}</option>
                    @endforeach
                </select>
            </div>
            
            <!-- Arrival Odometer (Only if using car) -->
            @if($clocking->using_car)
            <div class="mb-4">
                @php
                    // Get previous odometer reading
                    $previousOdometer = null;
                    $lastCard = \Modules\Invoice\Models\InvoiceCard::where('clocking_id', $clocking->id)
                        ->whereNotNull('arrival_odometer')
                        ->orderBy('start_time', 'desc')
                        ->first();
                    
                    if ($lastCard) {
                        $previousOdometer = $lastCard->arrival_odometer;
                    } else {
                        $previousOdometer = $clocking->miles_in;
                    }
                @endphp
                
                <label class="block text-sm font-medium text-black-700 mb-2">
                    Arrival Odometer
                    <span class="text-red-500">*</span>
                </label>
                
                <!-- Previous Odometer Display -->
                @if($previousOdometer)
                <div class="mb-2 rounded-lg bg-blue-50 border border-blue-200 p-3">
                    <p class="text-xs text-blue-800">
                        <span class="font-semibold">Previous Reading:</span>
                        <span class="font-mono">{{ number_format($previousOdometer, 1) }}</span> miles
                    </p>
                </div>
                @endif
                
                <input type="number" 
                       name="arrival_odometer" 
                       step="0.1" 
                       min="{{ $previousOdometer ?? 0 }}" 
                       required
                       class="block w-full rounded-lg border-orange-300 shadow-sm focus:border-orange-500 focus:ring-orange-500"
                       placeholder="Enter current odometer reading">
                <p class="mt-1 text-xs text-black-500">
                    Enter your current odometer reading. Distance will be calculated automatically.
                </p>
            </div>
            @endif
            
            <!-- Buttons -->
            <div class="flex gap-3">
                <button type="button" onclick="document.getElementById('createCardModal').classList.add('hidden')"
                        class="flex-1 px-4 py-2 border-2 border-orange-200 rounded-lg text-orange-700 font-medium hover:bg-orange-50">
                    Cancel
                </button>
                <button type="submit" class="flex-1 px-4 py-2 bg-orange-600 text-white rounded-lg font-medium hover:bg-orange-700">
                    Create Card
                </button>
            </div>
        </form>
    </div>
</div>

@if(session('success'))
<div class="fixed bottom-4 right-4 bg-green-500 text-white px-6 py-3 rounded-lg shadow-lg">
    {{ session('success') }}
</div>
@endif

@if($errors->any())
<div class="fixed bottom-4 right-4 bg-red-500 text-white px-6 py-3 rounded-lg shadow-lg">
    {{ $errors->first() }}
</div>
@endif
@endsection
