@extends('layouts.app')

@section('content')
    <div class="min-h-screen bg-gradient-to-br from-orange-50 to-orange-100 py-8" dir="{{ getDirection() }}">
        <div class="mx-auto max-w-md px-4">
            <div class="mb-8 text-center">
                <h1 class="text-3xl font-bold text-black-900">{{ __('messages.attendance') }}</h1>
            </div>

            {{-- Invoice Cards Section (After Clock-In) --}}
            @if($clocking)
                {{-- Session Info Banner --}}
                <div class="animate-fade-in mb-6 rounded-xl bg-gradient-to-r from-blue-50 to-blue-100 border-2 border-blue-200 p-4 shadow-sm">
                    <div class="flex items-start gap-3">
                        <div class="flex-shrink-0">
                            <svg class="h-6 w-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                        </div>
                        <div class="flex-1">
                            <h3 class="text-sm font-bold text-blue-900">Work Session Active</h3>
                            <p class="mt-1 text-xs leading-relaxed text-blue-800">
                                Create cards for each store you visit. Add materials, track work, then clock out when done.
                            </p>
                        </div>
                    </div>
                </div>

                {{-- Assigned Stores Section (Quick Access) --}}
                @if($assignedStores->count() > 0)
                <div class="animate-fade-in mb-6 rounded-xl bg-gradient-to-r from-green-50 to-green-100 border-2 border-green-200 p-5 shadow-sm">
                    <div class="mb-3 flex items-center justify-between">
                        <h3 class="text-sm font-bold text-green-900">Your Assigned Stores</h3>
                        <span class="rounded-full bg-green-600 px-2.5 py-0.5 text-xs font-bold text-white">
                            {{ $assignedStores->count() }}
                        </span>
                    </div>
                    <div class="space-y-2">
                        @foreach($assignedStores as $assignedStore)
                        <div class="rounded-lg bg-white border border-green-200 p-3 shadow-sm">
                            <div class="flex items-start justify-between gap-3">
                                <div class="flex-1">
                                    <div class="flex items-center gap-2">
                                        <svg class="h-4 w-4 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path>
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                        </svg>
                                        <p class="text-sm font-bold text-black-800">{{ $assignedStore->store_number }} - {{ $assignedStore->name }}</p>
                                    </div>
                                    @if($assignedStore->address)
                                    <p class="mt-1 text-xs text-black-600">{{ $assignedStore->address }}</p>
                                    @endif
                                    <div class="mt-2 flex items-center gap-2">
                                        <span class="rounded-full bg-orange-100 px-2 py-0.5 text-xs font-medium text-orange-800">
                                            {{ $assignedStore->pending_tasks_count }} {{ $assignedStore->pending_tasks_count == 1 ? 'Task' : 'Tasks' }}
                                        </span>
                                    </div>
                                </div>
                                <button type="button" 
                                        onclick="createCardForStore({{ $assignedStore->id }})"
                                        class="flex-shrink-0 rounded-lg bg-green-600 px-3 py-2 text-xs font-bold text-white hover:bg-green-700 transition-colors">
                                    Visit Store
                                </button>
                            </div>
                        </div>
                        @endforeach
                    </div>
                    <p class="mt-3 text-xs text-center text-green-700 italic">
                        Or use "Add Store Visit" below to visit any store
                    </p>
                </div>
                @endif

                {{-- Invoice Cards (Clickable) --}}
                <div id="invoice-cards-container">
                    @foreach($invoiceCards->where('status', 'in_progress') as $index => $card)
                    <a href="{{ route('invoice.cards.show', $card->id) }}" class="block animate-fade-in mb-6 rounded-2xl bg-white p-5 shadow-lg transition-all duration-300 hover:shadow-xl hover:scale-[1.02] cursor-pointer" id="card-{{ $card->id }}">
                        
                        <!-- Store Header with Arrow and Location Icon -->
                        <div class="mb-4 rounded-xl bg-gradient-to-r from-orange-50 to-orange-100 p-4">
                            <div class="flex items-center justify-between">
                                <div class="flex items-center gap-3 flex-1">
                                    <svg class="h-6 w-6 text-orange-600 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                                    </svg>
                                    <div class="flex-1 text-center">
                                        <h3 class="text-lg font-bold text-gray-800">{{ $card->store->name }} - {{ $card->store->store_number }}</h3>
                                        @if($card->store->address)
                                        <p class="text-sm text-gray-600 mt-1">{{ $card->store->address }}</p>
                                        @endif
                                    </div>
                                </div>
                                <svg class="h-6 w-6 text-orange-600 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                </svg>
                            </div>
                        </div>

                        <!-- Tasks Section -->
                        @php
                            // Get all maintenance requests for this store assigned to current user
                            $storeMRs = \App\Models\MaintenanceRequest::where('store_id', $card->store_id)
                                ->where('assigned_to', Auth::id())
                                ->whereIn('status', ['on_hold', 'in_progress', 'pending'])
                                ->orderBy('urgency_level_id', 'asc')
                                ->get();
                            
                            // Get native requests for this store assigned to current user
                            $storeNRs = \App\Models\Native\NativeRequest::where('store_id', $card->store_id)
                                ->where('assigned_to', Auth::id())
                                ->whereIn('status', ['pending', 'in_progress', 'received'])
                                ->orderBy('urgency_level_id', 'asc')
                                ->get();
                            
                            // Combine both
                            $allStoreTasks = $storeMRs->concat($storeNRs);
                        @endphp
                        <div class="mb-4 rounded-xl border-2 border-dashed border-orange-200 bg-orange-50/30 p-4">
                            @if($allStoreTasks->count() > 0)
                                <div class="space-y-2">
                                    @foreach($allStoreTasks->take(3) as $task)
                                    <div class="text-sm text-gray-700">
                                        <span class="font-medium">•</span> {{ Str::limit($task->description_of_issue ?? $task->equipment_with_issue, 50) }}
                                    </div>
                                    @endforeach
                                    @if($allStoreTasks->count() > 3)
                                    <p class="text-xs text-gray-500 text-center mt-2">
                                        +{{ $allStoreTasks->count() - 3 }} more tasks
                                    </p>
                                    @endif
                                </div>
                            @else
                                <p class="text-center text-sm font-medium text-gray-500">No tasks assigned yet</p>
                            @endif
                        </div>

                        <!-- Admin Equipment Purchases (Read-only) -->
                        @php
                            $adminEquipment = \App\Models\Payment::where('store_id', $card->store_id)
                                ->where('is_admin_equipment', true)
                                ->latest()
                                ->take(3)
                                ->get();
                        @endphp
                        @if($adminEquipment->count() > 0)
                        <div class="mb-4 rounded-xl bg-purple-50 border-2 border-purple-200 p-4">
                            <div class="flex items-center justify-between mb-3">
                                <div class="flex items-center gap-2">
                                    <svg class="h-5 w-5 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"></path>
                                    </svg>
                                    <span class="text-sm font-bold text-purple-900">Admin Equipment</span>
                                </div>
                                <span class="text-xs font-medium text-purple-600">{{ $adminEquipment->count() }} items</span>
                            </div>
                            <div class="space-y-1">
                                @foreach($adminEquipment as $equipment)
                                <div class="text-xs text-purple-800">
                                    • {{ Str::limit($equipment->what_got_fixed ?? 'Equipment', 35) }} - ${{ number_format($equipment->cost, 2) }}
                                </div>
                                @endforeach
                            </div>
                            <p class="mt-2 text-xs text-purple-600 italic text-center">For your information only</p>
                        </div>
                        @endif

                        <!-- Bought Items Section -->
                        <div class="mb-4">
                            <div class="flex items-center justify-between mb-2">
                                <span class="text-sm font-medium text-gray-600">items {{ $card->materials->count() }}</span>
                                <span class="text-lg font-bold text-gray-800">{{ __('invoice.bought_items') }}</span>
                            </div>
                            @if($card->materials->count() > 0)
                                <p class="text-center text-sm text-gray-700 py-2">
                                    {{ $card->materials->count() }} {{ $card->materials->count() == 1 ? 'item' : 'items' }} added
                                </p>
                            @else
                                <p class="text-center text-sm text-gray-500 py-2">No items added yet</p>
                            @endif
                        </div>

                        <!-- Click to Open Button -->
                        <div class="rounded-xl bg-blue-50 border border-blue-200 p-3 text-center">
                            <p class="text-sm font-medium text-blue-700 flex items-center justify-center gap-2">
                                Click to open and manage this card
                                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 15l-2 5L9 9l11 4-5 2zm0 0l5 5M7.188 2.239l.777 2.897M5.136 7.965l-2.898-.777M13.95 4.05l-2.122 2.122m-5.657 5.656l-2.12 2.122"></path>
                                </svg>
                            </p>
                        </div>
                    </a>
                    @endforeach

                    {{-- Completed Cards (Collapsed) --}}
                    @foreach($invoiceCards->whereIn('status', ['completed', 'not_done']) as $card)
                    <div class="animate-fade-in mb-6 overflow-hidden rounded-2xl bg-white shadow-md ring-1 ring-green-900/10 transition-all duration-300 hover:shadow-lg">
                        <div class="cursor-pointer p-5" onclick="this.parentElement.querySelector('.card-details').classList.toggle('hidden')">
                            <div class="flex items-center justify-between">
                                <div class="flex items-center gap-3">
                                    <div class="flex h-10 w-10 flex-shrink-0 items-center justify-center rounded-full bg-green-100">
                                        <svg class="h-6 w-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                        </svg>
                                    </div>
                                    <div>
                                        <p class="text-sm font-bold text-black-800">{{ $card->store->store_number }} - {{ $card->store->name }}</p>
                                        <p class="text-xs text-black-500">Completed • {{ $card->maintenanceRequests->count() }} requests • {{ $card->materials->count() }} items</p>
                                    </div>
                                </div>
                                <svg class="h-5 w-5 text-black-400 transition-transform duration-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                                </svg>
                            </div>
                        </div>
                        <div class="card-details hidden border-t border-green-100 bg-green-50/30 p-5">
                            <p class="text-xs text-black-600">Completed at {{ $card->end_time->format('g:i A') }}</p>
                        </div>
                    </div>
                    @endforeach
                </div>

                {{-- Add New Store Card Button --}}
                <button type="button" onclick="document.getElementById('createCardModal').classList.remove('hidden')"
                        class="animate-fade-in mb-6 w-full rounded-xl border-2 border-dashed border-orange-300 bg-white py-4 text-center shadow-sm transition-all duration-300 hover:border-orange-400 hover:bg-orange-50 hover:shadow-md focus:outline-none focus:ring-2 focus:ring-orange-500 focus:ring-offset-2">
                    <svg class="mx-auto mb-1 h-6 w-6 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                    </svg>
                    <span class="text-sm font-semibold text-orange-700">Add Store Visit</span>
                </button>

                {{-- Clock-Out Section --}}
                <div class="mb-6 rounded-lg bg-orange-100 p-4 text-center">
                    <h2 class="text-xl font-semibold text-black-800">{{ __('messages.clock_out_registration') }}</h2>
                    <p class="text-black-600">{{ __('messages.record_end_shift_details') }}</p>
                </div>

                <form class="animate-fade-in space-y-6" id="clockOutForm" action="{{ route('clocking.clockOut') }}"
                      method="POST" enctype="multipart/form-data">
                    @csrf

                    <!-- Clock-Out Error Message (initially hidden) -->
                    <div class="mb-2 hidden rounded-lg bg-orange-100 p-3 text-black-800" id="clockOutError">
                        <!-- Will be populated by JS if there's an error -->
                    </div>

                    <div class="space-y-4">
                        @if($using_car)
                            <!-- Clock Out Miles -->
                            <div class="rounded-lg transition-all duration-300 hover:shadow-md">
                                <label class="mb-1 block text-sm font-medium text-black-700" for="miles_out">
                                    {{ __('messages.clock_out_miles') }}
                                </label>
                                <div class="relative rounded-md ring-1 ring-orange-900/5">
                                    <input
                                        class="block w-full rounded-lg border border-orange-200 py-3 {{ isRtl() ? 'pr-10 pl-3' : 'pl-10 pr-3' }} transition-all duration-300 focus:border-orange-500 focus:ring-orange-500"
                                        id="miles_out" name="miles_out" type="number" placeholder="{{ __('messages.enter_miles_placeholder') }}" required>
                                </div>
                            </div>

                        
                            <!-- Clock Out Image -->
                            <div class="rounded-lg transition-all duration-300 hover:shadow-md">
                                <label class="mb-1 block text-sm font-medium text-black-700" for="image_out">
                                    📸 Take Photo of Odometer
                                </label>
                                <div class="mt-1">
                                    <div
                                        class="flex justify-center rounded-lg border-2 border-dashed border-orange-200 px-6 pb-6 pt-5 transition-all duration-300 hover:border-orange-500">
                                        <div class="text-center">
                                            <label
                                                class="relative cursor-pointer rounded-md font-medium text-black-600 transition-colors duration-300 focus-within:outline-none focus-within:ring-2 focus-within:ring-orange-500 focus-within:ring-offset-2 hover:text-black-500"
                                                for="image_out">
                                                <span>📷 Open Camera</span>
                                                <input class="sr-only" id="image_out" name="image_out" type="file"
                                                       accept="image/*" capture="environment" required>
                                            </label>
                                            <p class="mt-2 text-xs text-black-500">Take a clear photo of your car's odometer at end of shift</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endif

                    
                        <button
                            class="flex w-full transform items-center justify-center rounded-lg border border-transparent bg-orange-600 px-4 py-3 text-base font-medium text-white transition-all duration-300 hover:scale-105 hover:bg-orange-700 focus:outline-none focus:ring-2 focus:ring-orange-500 focus:ring-offset-2"
                            type="button" onclick="handleClockOutClicked()">
                            <svg class="{{ isRtl() ? 'ml-2' : 'mr-2' }} h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                      d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1">
                                </path>
                            </svg>
                            {{ __('messages.clock_out_registration') }}
                        </button>
                    </div>
                </form>

                {{-- Clock-In Form --}}
            @else
                <div class="mb-6 rounded-lg bg-orange-100 p-4 text-center">
                    <h2 class="text-xl font-semibold text-black-800">{{ __('messages.clock_in_registration') }}</h2>
                    <p class="text-black-600">{{ __('messages.start_shift_registration') }}</p>
                </div>

                <form class="animate-fade-in space-y-6" id="clockInForm" action="{{ route('clocking.clockIn') }}"
                      method="POST" enctype="multipart/form-data">
                    @csrf

                    <!-- Clock-In Error Message (initially hidden) -->
                    <div class="mb-2 hidden rounded-lg bg-orange-100 p-3 text-black-800" id="clockInError">
                        <!-- Will be populated by JS if there's an error -->
                    </div>

                    <div class="space-y-4">
                        <!-- Radio button to check if car is used -->
                        <div class="mb-4">
                            <label class="mb-2 block text-sm font-medium text-black-700">{{ __('messages.using_car_question') }}</label>
                            <div class="flex items-center {{ isRtl() ? 'space-x-reverse' : '' }} space-x-4">
                                <div class="flex items-center">
                                    <input class="h-4 w-4 border-orange-200 text-black-600 focus:ring-orange-500"
                                           id="using_car_yes" name="using_car" type="radio" value="1">
                                    <label class="{{ isRtl() ? 'mr-2' : 'ml-2' }}" for="using_car_yes">{{ __('messages.yes') }}</label>
                                </div>
                                <div class="flex items-center">
                                    <input class="h-4 w-4 border-orange-200 text-black-600 focus:ring-orange-500"
                                           id="using_car_no" name="using_car" type="radio" value="0" checked>
                                    <label class="{{ isRtl() ? 'mr-2' : 'ml-2' }}" for="using_car_no">{{ __('messages.no') }}</label>
                                </div>
                            </div>
                        </div>

                        <!-- Clock In Miles -->
                        <div class="rounded-lg transition-all duration-300 hover:shadow-md" id="miles_in_container"
                             style="display: none;">
                            <label class="mb-1 block text-sm font-medium text-black-700" for="miles_in">
                                {{ __('messages.clock_in_miles') }}
                            </label>
                            <div class="relative rounded-md ring-1 ring-orange-900/5">
                                <input
                                    class="block w-full rounded-lg border border-orange-200 py-3 {{ isRtl() ? 'pr-10 pl-3' : 'pl-10 pr-3' }} transition-all duration-300 focus:border-orange-500 focus:ring-orange-500"
                                    id="miles_in" name="miles_in" type="number" placeholder="{{ __('messages.enter_miles_placeholder') }}">
                            </div>
                            <p class="mt-1 text-xs text-orange-600 flex items-center">
                                <svg class="h-4 w-4 {{ isRtl() ? 'ml-1' : 'mr-1' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                                Enter the exact number shown on your car's odometer
                            </p>
                        </div>

                        <!-- Clock In Image -->
                        <div class="rounded-lg transition-all duration-300 hover:shadow-md" id="image_in_container"
                             style="display: none;">
                            <label class="mb-1 block text-sm font-medium text-black-700" for="image_in">
                                📸 Take Photo of Odometer
                            </label>
                            <div class="mt-1">
                                <div
                                    class="flex justify-center rounded-lg border-2 border-dashed border-orange-200 px-6 pb-6 pt-5 transition-all duration-300 hover:border-orange-500">
                                    <div class="text-center">
                                        <label
                                            class="relative cursor-pointer rounded-md font-medium text-black-600 transition-colors duration-300 focus-within:outline-none focus-within:ring-2 focus-within:ring-orange-500 focus-within:ring-offset-2 hover:text-black-500"
                                            for="image_in">
                                            <span>📷 Open Camera</span>
                                            <input class="sr-only" id="image_in" name="image_in" type="file"
                                                   accept="image/*" capture="environment">
                                        </label>
                                        <p class="mt-2 text-xs text-black-500">Take a clear photo of your car's odometer reading</p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <button
                            class="flex w-full transform items-center justify-center rounded-lg border border-transparent bg-orange-600 px-4 py-3 text-base font-medium text-white transition-all duration-300 hover:scale-105 hover:bg-orange-700 focus:outline-none focus:ring-2 focus:ring-orange-500 focus:ring-offset-2"
                            type="button" onclick="handleClockInClicked()">
                            <svg class="{{ isRtl() ? 'ml-2' : 'mr-2' }} h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                      d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1">
                                </path>
                            </svg>
                            {{ __('messages.clock_in_registration') }}
                        </button>
                    </div>
                </form>
            @endif
        </div>
    </div>

    <!-- Confirmation Modal -->
    <div class="animate-fade-in fixed inset-0 z-50 flex hidden items-center justify-center" id="confirmationModal"
         role="dialog" aria-labelledby="modal-title" aria-modal="true">
        <div class="fixed  bg-orange-100 bg-opacity-75 transition-opacity"></div>
        <div class="m-4 transform rounded-lg bg-orange-50 shadow-xl transition-all sm:w-full sm:max-w-lg">
            <div class="bg-orange-50 px-4 pb-4 pt-5 sm:p-6 sm:pb-4">
                <div class="sm:flex sm:items-start">
                    <div
                        class="mx-auto flex h-12 w-12 flex-shrink-0 items-center justify-center rounded-full bg-orange-100 sm:mx-0 sm:h-10 sm:w-10">
                        <svg class="h-6 w-6 text-black-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                        </svg>
                    </div>
                    <div class="mt-3 text-center {{ isRtl() ? 'sm:mr-4' : 'sm:ml-4' }} sm:mt-0 {{ isRtl() ? 'sm:text-right' : 'sm:text-left' }}">
                        <h3 class="text-lg font-medium leading-6 text-black-900" id="modal-title">
                            {{ __('messages.confirm_action') }}
                        </h3>
                        <div class="mt-2">
                            <p class="text-sm text-black-500" id="confirmationMessage">
                                {{ __('messages.confirm_proceed') }}
                            </p>
                        </div>
                    </div>
                </div>
            </div>
            <div class="bg-orange-50 px-4 py-3 sm:flex {{ isRtl() ? 'sm:flex-row' : 'sm:flex-row-reverse' }} sm:px-6">
                <button
                    class="inline-flex w-full justify-center rounded-md border border-transparent bg-orange-600 px-4 py-2 text-base font-medium text-white ring-1 ring-orange-900/5 transition-colors duration-300 hover:bg-orange-700 focus:outline-none focus:ring-2 focus:ring-orange-500 focus:ring-offset-2 {{ isRtl() ? 'sm:mr-3' : 'sm:ml-3' }} sm:w-auto sm:text-sm"
                    id="confirmButton" type="button">
                    {{ __('messages.confirm') }}
                </button>
                <button
                    class="mt-3 inline-flex w-full justify-center rounded-md border border-orange-200 bg-orange-50 px-4 py-2 text-base font-medium text-black-700 ring-1 ring-orange-900/5 transition-colors duration-300 hover:bg-orange-100 focus:outline-none focus:ring-2 focus:ring-orange-500 focus:ring-offset-2 {{ isRtl() ? 'sm:mr-3' : 'sm:ml-3' }} sm:mt-0 sm:w-auto sm:text-sm"
                    type="button" onclick="hideConfirmation()">
                    {{ __('messages.cancel') }}
                </button>
            </div>
        </div>
    </div>

    <script>
        // -----------------------------------------
        // CLOCK IN: Show/Hide fields based on "using_car"
        // -----------------------------------------
        function toggleCarFields(isUsingCar) {
            const inContainers = ['miles_in_container', 'image_in_container'];
            const inInputs = ['miles_in', 'image_in'];

            inContainers.forEach(id => {
                const container = document.getElementById(id);
                if (container) {
                    container.style.display = isUsingCar ? 'block' : 'none';
                }
            });

            inInputs.forEach(id => {
                const input = document.getElementById(id);
                if (input) {
                    if (isUsingCar) {
                        input.setAttribute('required', 'required');
                    } else {
                        input.removeAttribute('required');
                    }
                }
            });
        }

        // -----------------------------------------
        // Create Card for Assigned Store (Quick Action)
        // -----------------------------------------
        function createCardForStore(storeId) {
            // Open the create card modal and pre-select the store
            const modal = document.getElementById('createCardModal');
            const storeSelect = modal.querySelector('select[name="store_id"]');
            
            // Pre-select the store
            if (storeSelect) {
                storeSelect.value = storeId;
            }
            
            // Show the modal
            modal.classList.remove('hidden');
        }

        // -----------------------------------------
        // Confirmation Modal
        // -----------------------------------------
        function showConfirmation(action) {
            const modal = document.getElementById('confirmationModal');
            const confirmButton = document.getElementById('confirmButton');
            const message = document.getElementById('confirmationMessage');

            console.log('Showing confirmation modal for action:', action);

            if (action === 'in') {
                message.textContent = '{{ __('messages.confirm_clock_in') }}';
                confirmButton.onclick = () => {
                    console.log('Clock-in form submission confirmed');
                    document.getElementById('clockInForm').submit();
                };
            } else {
                message.textContent = '{{ __('messages.confirm_clock_out') }}';
                confirmButton.onclick = () => {
                    console.log('=== CLOCK-OUT FORM SUBMISSION CONFIRMED ===');
                    const form = document.getElementById('clockOutForm');
                    const formData = new FormData(form);
                    console.log('Final form data before submission:');
                    for (let [key, value] of formData.entries()) {
                        console.log(`  ${key}:`, value);
                    }
                    form.submit();
                };
            }

            // Show the modal
            modal.classList.remove('hidden');
            setTimeout(() => {
                modal.classList.add('opacity-100');
            }, 50);
        }

        function hideConfirmation() {
            const modal = document.getElementById('confirmationModal');
            modal.classList.remove('opacity-100');
            setTimeout(() => {
                modal.classList.add('hidden');
            }, 300);
        }

        // -----------------------------------------
        // Validate Clock-In submission
        // -----------------------------------------
        function handleClockInClicked() {
            const clockInErrorBox = document.getElementById('clockInError');
            const usingCarYes = document.getElementById('using_car_yes');

            // Clear any previous error
            clockInErrorBox.innerText = '';
            clockInErrorBox.classList.add('hidden');

            // If user selected "Yes" for car
            if (usingCarYes && usingCarYes.checked) {
                const milesIn = document.getElementById('miles_in').value.trim();
                const imageIn = document.getElementById('image_in').value; // the file path

                // Validate all required fields
                if (!milesIn || !imageIn) {
                    clockInErrorBox.innerText = '{{ __('messages.car_usage_validation') }}';
                    clockInErrorBox.classList.remove('hidden');
                    return;
                }

                // Validate odometer is a positive number
                const odometerValue = parseFloat(milesIn);
                if (isNaN(odometerValue) || odometerValue < 0) {
                    clockInErrorBox.innerText = 'Please enter a valid odometer reading (must be a positive number)';
                    clockInErrorBox.classList.remove('hidden');
                    return;
                }
            }
            // If all good, show confirmation modal
            showConfirmation('in');
        }

        // -----------------------------------------
        // Validate Clock-Out submission
        // -----------------------------------------
        function handleClockOutClicked() {
            const usingCar = {{ $using_car ? 'true' : 'false' }};
            const clockOutErrorBox = document.getElementById('clockOutError');

            console.log('=== CLOCK-OUT VALIDATION STARTED ===');
            console.log('Using car:', usingCar);

            // Clear any previous error
            clockOutErrorBox.innerText = '';
            clockOutErrorBox.classList.add('hidden');

            // Get all form data for logging
            const formData = new FormData(document.getElementById('clockOutForm'));
            const formDataObj = {};
            for (let [key, value] of formData.entries()) {
                formDataObj[key] = value;
            }
            console.log('Form data being submitted:', formDataObj);

            // Check radio button states
            const didBuyYes = document.getElementById('didBuyYes');
            const didBuyNo = document.getElementById('didBuyNo');
            console.log('didBuyYes checked:', didBuyYes ? didBuyYes.checked : 'element not found');
            console.log('didBuyNo checked:', didBuyNo ? didBuyNo.checked : 'element not found');
            console.log('bought_something value in form:', formData.get('bought_something'));

            // If using car => check miles_out + image_out
            if (usingCar) {
                const milesOut = document.getElementById('miles_out').value.trim();
                const imageOut = document.getElementById('image_out').value; // file path
                console.log('Car validation - miles_out:', milesOut, 'image_out:', imageOut);

                if (!milesOut || !imageOut) {
                    console.log('Car validation failed');
                    clockOutErrorBox.innerText = '{{ __('messages.car_clock_out_validation') }}';
                    clockOutErrorBox.classList.remove('hidden');
                    return;
                }
            }

            // Also validate the "did you buy anything?" question
            if (didBuyYes && didBuyYes.checked) {
                const costVal = document.getElementById('purchase_cost').value.trim();
                const receiptVal = document.getElementById('purchase_receipt').value;
                console.log('Purchase validation - cost:', costVal, 'receipt:', receiptVal);

                if (!costVal || !receiptVal) {
                    console.log('Purchase validation failed');
                    clockOutErrorBox.innerText = '{{ __('messages.purchase_validation') }}';
                    clockOutErrorBox.classList.remove('hidden');
                    return;
                }
            }

            console.log('All validations passed, showing confirmation');
            // If all good
            showConfirmation('out');
        }

        // -----------------------------------------
        // On page load, attach event listeners
        // -----------------------------------------
        document.addEventListener('DOMContentLoaded', function() {
            // Existing clock-in event listeners...
            const usingCarYes = document.getElementById('using_car_yes');
            const usingCarNo = document.getElementById('using_car_no');

            if (usingCarYes) {
                usingCarYes.addEventListener('change', () => toggleCarFields(true));
            }
            if (usingCarNo) {
                usingCarNo.addEventListener('change', () => toggleCarFields(false));
            }

            // Initial state for clock-in
            if (usingCarYes && usingCarYes.checked) {
                toggleCarFields(true);
            } else {
                toggleCarFields(false);
            }
        });

        // Updated clock-out validation - simplified
        function handleClockOutClicked() {
            const usingCar = {{ $using_car ? 'true' : 'false' }};
            const clockOutErrorBox = document.getElementById('clockOutError');

            console.log('=== CLOCK-OUT VALIDATION STARTED ===');
            console.log('Using car:', usingCar);

            // Clear any previous error
            clockOutErrorBox.innerText = '';
            clockOutErrorBox.classList.add('hidden');

            // If using car => check miles_out + image_out
            if (usingCar) {
                const milesOut = document.getElementById('miles_out').value.trim();
                const imageOut = document.getElementById('image_out').value;
                console.log('Car validation - miles_out:', milesOut, 'image_out:', imageOut);

                if (!milesOut || !imageOut) {
                    console.log('Car validation failed');
                    clockOutErrorBox.innerText = '{{ __('messages.car_clock_out_validation') }}';
                    clockOutErrorBox.classList.remove('hidden');
                    return;
                }
            }

            console.log('All validations passed, showing confirmation');
            showConfirmation('out');
        }


    </script>

    <style>
        @keyframes fadeIn {
            from {
                opacity: 0;
            }

            to {
                opacity: 1;
            }
        }

        .animate-fade-in {
            animation: fadeIn 0.3s ease-out;
        }
    </style>

    <script>
        function markNotDone(cardId) {
            const reason = prompt('Please provide a reason for not completing this card:');
            if (!reason) return;
            
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = '/invoice/cards/' + cardId + '/complete';
            
            const csrf = document.createElement('input');
            csrf.type = 'hidden';
            csrf.name = '_token';
            csrf.value = '{{ csrf_token() }}';
            form.appendChild(csrf);
            
            const status = document.createElement('input');
            status.type = 'hidden';
            status.name = 'status';
            status.value = 'not_done';
            form.appendChild(status);
            
            const reasonInput = document.createElement('input');
            reasonInput.type = 'hidden';
            reasonInput.name = 'not_done_reason';
            reasonInput.value = reason;
            form.appendChild(reasonInput);
            
            const notes = document.getElementById('notes-' + cardId).value;
            const notesInput = document.createElement('input');
            notesInput.type = 'hidden';
            notesInput.name = 'notes';
            notesInput.value = notes;
            form.appendChild(notesInput);
            
            document.body.appendChild(form);
            form.submit();
        }

        function showAddMaterialModal(cardId) {
            alert('Material addition will be implemented. Card ID: ' + cardId);
            // TODO: Implement material addition modal
        }

        // Fetch maintenance requests for a store and populate the multi-select when creating a card
        async function fetchStoreTasks(storeId) {
            const select = document.getElementById('createTaskSelector');
            select.innerHTML = '';

            if (!storeId) return;

            try {
                const res = await fetch('/api/maintenance-requests/by-store/' + storeId);
                const json = await res.json();

                if (res.ok) {
                    json.forEach(function(mr) {
                        const opt = document.createElement('option');
                        opt.value = mr.id;
                        opt.textContent = `#${mr.id} - ${mr.equipment_with_issue}`;
                        select.appendChild(opt);
                    });
                } else {
                    console.error('Failed to load maintenance requests for store', json);
                }
            } catch (e) {
                console.error(e);
            }
        }
    </script>

    {{-- Create Invoice Card Modal --}}
    <div id="createCardModal" class="hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
        <div class="bg-white rounded-2xl p-6 max-w-md w-full mx-4">
            <h3 class="text-xl font-bold text-black-900 mb-4">{{ __('invoice.create_store_visit_card') }}</h3>
            
            <form action="{{ route('invoice.cards.store') }}" method="POST" enctype="multipart/form-data">
                @csrf
                
                <!-- Store Selection -->
                <div class="mb-4">
                    <label class="block text-sm font-medium text-black-700 mb-2">{{ __('invoice.select_store') ?? __('Select Store') }}</label>
                    <select id="createCardStoreSelector" name="store_id" required class="block w-full rounded-lg border-orange-300 shadow-sm focus:border-orange-500 focus:ring-orange-500" onchange="fetchStoreTasks(this.value)">
                        <option value="">Choose a store...</option>
                        @if(isset($stores))
                            @foreach($stores as $store)
                                <option value="{{ $store->id }}">{{ $store->store_number }} - {{ $store->name }}</option>
                            @endforeach
                        @endif
                    </select>
                </div>

                <!-- Incomplete Cards Notification -->
                @php
                    $incompleteCards = \Modules\Invoice\Models\InvoiceCard::where('user_id', Auth::id())
                        ->where('status', 'not_done')
                        ->whereNotNull('end_time')
                        ->with('store')
                        ->get();
                @endphp
                
                @if($incompleteCards->count() > 0)
                <div class="mb-4 p-3 bg-blue-50 border border-blue-200 rounded-lg">
                    <div class="flex items-start gap-2">
                        <svg class="h-5 w-5 text-blue-600 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        <div>
                            <p class="text-sm font-semibold text-blue-800">You have {{ $incompleteCards->count() }} incomplete work card(s)</p>
                            <p class="text-xs text-blue-600 mt-1">If you select a store where you have incomplete work, your previous progress will be automatically restored!</p>
                            <div class="mt-2 space-y-1">
                                @foreach($incompleteCards as $incompleteCard)
                                <p class="text-xs text-blue-700">• {{ $incompleteCard->store->store_number }} - {{ $incompleteCard->store->name }}</p>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>
                @endif
                <!-- Arrival Odometer (Only if using car) -->
                @if($clocking && $clocking->using_car)
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
                
                <!-- Odometer Photo -->
                <div class="mb-4">
                    <label class="block text-sm font-medium text-black-700 mb-2">
                        <svg class="inline h-4 w-4 text-orange-600 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"></path>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"></path>
                        </svg>
                        📸 Take Photo of Odometer
                        <span class="text-red-500">*</span>
                    </label>
                    <div class="border-2 border-dashed border-orange-300 rounded-lg p-4 text-center hover:border-orange-400 transition-all bg-orange-50">
                        <div class="mb-2">
                            <svg class="mx-auto h-12 w-12 text-orange-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"></path>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"></path>
                            </svg>
                        </div>
                        <label for="arrival_odometer_image" class="cursor-pointer">
                            <span class="inline-block px-4 py-2 bg-orange-600 text-white rounded-lg font-semibold hover:bg-orange-700 transition-all">
                                📷 Open Camera
                            </span>
                            <input type="file" 
                                   id="arrival_odometer_image"
                                   name="arrival_odometer_image" 
                                   accept="image/*" 
                                   capture="environment" 
                                   required
                                   class="hidden">
                        </label>
                    </div>
                    <p class="mt-2 text-xs text-orange-700 text-center font-medium">
                        📸 Take a clear photo of your car's odometer reading
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
@endsection
