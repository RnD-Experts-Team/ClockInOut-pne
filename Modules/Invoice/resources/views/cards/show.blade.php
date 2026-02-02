@extends('layouts.app')

@section('content')
<div class="min-h-screen bg-gradient-to-br from-orange-50 to-orange-100 py-4 sm:py-8" dir="{{ getDirection() }}">
    <div class="mx-auto max-w-sm sm:max-w-2xl lg:max-w-4xl xl:max-w-6xl px-4 sm:px-6 lg:px-8">
        
        <!-- Back Button -->
        <div class="mb-4 sm:mb-6">
            @if(Auth::user()->role === 'admin')
                <a href="{{ route('invoice.cards.index') }}" class="inline-flex items-center text-orange-600 hover:text-orange-700 transition-colors text-sm sm:text-base">
                    <svg class="h-4 w-4 sm:h-5 sm:w-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                    </svg>
                    {{ __('invoice.back_to_invoice_cards') }}
                </a>
            @else
                <a href="{{ route('clocking.index') }}" class="inline-flex items-center text-orange-600 hover:text-orange-700 transition-colors text-sm sm:text-base">
                    <svg class="h-4 w-4 sm:h-5 sm:w-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                    </svg>
                    {{ __('invoice.back_to_clocking') }}
                </a>
            @endif
        </div>

        <!-- Error Messages -->
        @if($errors->any())
        <div class="mb-4 sm:mb-6 rounded-xl bg-red-50 border-2 border-red-300 p-4 sm:p-5 shadow-lg animate-shake" id="errorAlert">
            <div class="flex items-start gap-3">
                <div class="flex-shrink-0">
                    <svg class="h-6 w-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                    </svg>
                </div>
                <div class="flex-1">
                    <h3 class="text-base sm:text-lg font-bold text-red-900 mb-2">⚠️ Cannot Complete Card</h3>
                    @foreach($errors->all() as $error)
                        <p class="text-sm sm:text-base text-red-800 leading-relaxed">{{ $error }}</p>
                    @endforeach
                    <div class="mt-3 pt-3 border-t border-red-200">
                        <p class="text-xs sm:text-sm text-red-700 font-medium">What to do:</p>
                        <ul class="mt-2 space-y-1 text-xs sm:text-sm text-red-700">
                            <li class="flex items-start gap-2">
                                <span class="text-red-500 font-bold">•</span>
                                <span>Click "Mark Complete" button on each incomplete task below</span>
                            </li>
                            <li class="flex items-start gap-2">
                                <span class="text-red-500 font-bold">•</span>
                                <span>Or click "Not Done" button if you cannot finish the work</span>
                            </li>
                        </ul>
                    </div>
                </div>
                <button onclick="document.getElementById('errorAlert').remove()" class="flex-shrink-0 text-red-400 hover:text-red-600 transition-colors">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>
        </div>
        @endif

        <!-- Store Card -->
        <div class="mb-4 sm:mb-6 space-y-4 rounded-2xl bg-white p-4 sm:p-5 lg:p-6 shadow-lg ring-1 ring-orange-900/5">
            
            <!-- Store Info -->
            <div class="rounded-lg bg-orange-50 p-3 sm:p-4">
                <div class="flex items-start gap-3">
                    <div class="flex-shrink-0">
                        <div class="flex h-8 w-8 sm:h-10 sm:w-10 items-center justify-center rounded-full bg-orange-100">
                            <svg class="h-4 w-4 sm:h-5 sm:w-5 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
                            </svg>
                        </div>
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="text-sm sm:text-base font-semibold text-black-800 truncate">{{ $card->store->store_number }} - {{ $card->store->name }}</p>
                        <p class="mt-0.5 text-xs sm:text-sm text-black-600">{{ $card->store->address }}</p>
                        <p class="mt-2 text-xs sm:text-sm text-black-500">Started: {{ $card->start_time->format('M d, Y g:i A') }}</p>
                    </div>
                </div>
            </div>

            <!-- Admin Viewing Indicator -->
            @if(Auth::user()->role === 'admin' && $card->user_id !== Auth::id())
            <div class="rounded-lg bg-blue-50 p-3 sm:p-4 border border-blue-200">
                <div class="flex items-center gap-3">
                    <div class="flex-shrink-0">
                        <div class="flex h-8 w-8 sm:h-10 sm:w-10 items-center justify-center rounded-full bg-blue-100">
                            <svg class="h-4 w-4 sm:h-5 sm:w-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                            </svg>
                        </div>
                    </div>
                    <div class="flex-1">
                        <p class="text-sm sm:text-base font-semibold text-blue-800">Admin View</p>
                        <p class="mt-0.5 text-xs sm:text-sm text-blue-600">Viewing {{ $card->user->name }}'s work card (Read-only)</p>
                    </div>
                </div>
            </div>
            @endif

            <!-- Odometer Information (if using car) -->
            @if($card->arrival_odometer || $card->arrival_odometer_image)
            <div class="rounded-lg bg-gradient-to-br from-blue-50 to-blue-100 p-4 border-2 border-blue-200">
                <div class="flex items-center gap-2 mb-3">
                    <svg class="h-5 w-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                    </svg>
                    <h4 class="text-sm font-bold text-blue-900">Arrival Odometer</h4>
                </div>
                
                <div class="flex items-start gap-4">
                    <!-- Odometer Reading -->
                    <div class="flex-1">
                        @if($card->arrival_odometer)
                        <div class="mb-3">
                            <p class="text-xs text-blue-700 mb-1">Odometer Reading</p>
                            <p class="text-2xl font-bold text-blue-900">{{ number_format($card->arrival_odometer, 1) }}</p>
                            <p class="text-xs text-blue-600 mt-1">miles</p>
                        </div>
                        @endif
                        
                        @if($card->calculated_miles)
                        <div class="pt-3 border-t border-blue-300">
                            <p class="text-xs text-blue-700 mb-1">Distance Traveled</p>
                            <p class="text-lg font-bold text-blue-900">{{ number_format($card->calculated_miles, 1) }} miles</p>
                        </div>
                        @endif
                        
                        @if($card->total_driving_time_hours || $card->driving_time_hours)
                        <div class="pt-3 border-t border-blue-300 mt-3">
                            <p class="text-xs text-blue-700 mb-1">Total Driving Time</p>
                            <p class="text-lg font-bold text-blue-900">{{ number_format($card->total_driving_time_hours ?? $card->driving_time_hours, 2) }} hours</p>
                            @if($card->allocated_return_driving_time > 0)
                            <p class="text-xs text-blue-600 mt-1">
                                {{ number_format($card->driving_time_hours ?? 0, 2) }}h to store + {{ number_format($card->allocated_return_driving_time, 2) }}h return
                            </p>
                            @endif
                        </div>
                        @endif
                    </div>
                    
                    <!-- Odometer Photo -->
                    @if($card->arrival_odometer_image)
                    <div class="flex-shrink-0">
                        <a href="{{ Storage::url($card->arrival_odometer_image) }}" target="_blank" class="block group">
                            <img src="{{ Storage::url($card->arrival_odometer_image) }}" 
                                 alt="Odometer Reading" 
                                 class="w-32 h-32 object-cover rounded-lg border-2 border-blue-300 group-hover:border-blue-500 transition-all cursor-pointer shadow-md group-hover:shadow-lg group-hover:scale-105">
                        </a>
                        <p class="text-xs text-center text-blue-600 mt-2 font-medium">📸 Click to enlarge</p>
                    </div>
                    @endif
                </div>
            </div>
            @endif

            <!-- Available Tasks - Card Interface -->
            @if(isset($allRequests) && $allRequests->count() > 0)
            <div class="rounded-lg transition-all duration-300">
                <div class="mb-4">
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between mb-3 gap-2">
                        <h4 class="text-lg sm:text-xl font-bold text-black-800">
                            @if(Auth::user()->role === 'admin')
                                Tasks Assigned to {{ $card->user->name }}
                            @else
                                Available Tasks
                            @endif
                        </h4>
                        <div class="flex items-center gap-2">
                            <span class="rounded-full bg-orange-100 px-3 py-1 text-sm font-medium text-orange-800">
                                {{ $allRequests->count() }} {{ Auth::user()->role === 'admin' ? 'assigned' : 'available' }}
                            </span>
                        </div>
                    </div>
                    
                    <p class="text-sm text-orange-700 flex items-start sm:items-center gap-2 bg-orange-50 px-3 sm:px-4 py-3 rounded-lg border border-orange-200">
                        <svg class="h-5 w-5 flex-shrink-0 mt-0.5 sm:mt-0" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"></path>
                        </svg>
                        <span>
                            @if(Auth::user()->role === 'admin')
                                These are the tasks that were assigned to {{ $card->user->name }} at this store. Green cards indicate tasks that were added to this work card.
                            @else
                                Click on any task card to view details and add it to your work list. You can select multiple tasks.
                            @endif
                        </span>
                    </p>
                </div>

                <!-- Task Cards Grid -->
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3 sm:gap-4 max-h-96 sm:max-h-[500px] lg:max-h-[600px] overflow-y-auto">
                    @foreach($allRequests as $mr)
                        @php
                            $isNative = $mr instanceof \App\Models\Native\NativeRequest;
                            $isLinked = in_array($mr->id, $linkedRequestIds);
                            $urgencyName = null;
                            if ($isNative && $mr->urgencyLevel) {
                                $urgencyName = $mr->urgencyLevel->name;
                            } elseif (!$isNative && $mr->urgencyLevel) {
                                $urgencyName = $mr->urgencyLevel->name;
                            }
                        @endphp
                        
                        <div class="task-card {{ Auth::user()->role === 'admin' ? 'cursor-default' : 'cursor-pointer' }} rounded-xl border-2 {{ $isLinked ? 'border-green-300 bg-green-50 opacity-60' : 'border-orange-200 bg-white hover:shadow-md hover:border-orange-300' }} p-3 sm:p-4 shadow-sm transition-all duration-200 {{ $isLinked || Auth::user()->role === 'admin' ? 'pointer-events-none' : '' }}"
                             data-task-id="{{ $mr->id }}"
                             data-type="{{ $isNative ? 'native' : 'regular' }}"
                             data-equipment="{{ $mr->equipment_with_issue }}"
                             data-description="{{ $mr->description_of_issue }}"
                             data-urgency="{{ $urgencyName ?? 'Normal' }}"
                             data-status="{{ $mr->status }}"
                             data-due-date="{{ $isNative ? ($mr->request_date ?? '') : ($mr->due_date ?? $mr->effective_due_date ?? '') }}"
                             data-requester="{{ $isNative ? $mr->display_requester_name : ($mr->requester->name ?? 'Unknown') }}"
                             data-requester-date="{{ $isNative ? ($mr->request_date ?? '') : ($mr->created_at ?? '') }}"
                             data-basic-check="{{ $mr->basic_troubleshoot_done ? '1' : '0' }}"
                             data-is-native="{{ $isNative ? '1' : '0' }}"
                             data-requester-type="{{ $isNative ? $mr->requester_type : '' }}"
                             data-is-linked="{{ $isLinked ? '1' : '0' }}"
                             onclick="{{ $isLinked || Auth::user()->role === 'admin' ? '' : 'toggleTaskSelection(this)' }}">
                            
                            <!-- Task Header -->
                            <div class="flex items-start justify-between gap-2 sm:gap-3 mb-3">
                                <div class="flex-1 min-w-0">
                                    <div class="flex flex-wrap items-center gap-1 sm:gap-2 mb-1">
                                        <span class="task-number rounded-full {{ $isLinked ? 'bg-green-600' : 'bg-orange-600' }} px-2 sm:px-2.5 py-1 text-xs font-bold text-white whitespace-nowrap">
                                            #{{ $mr->id }}{{ $isLinked ? ' (Added)' : '' }}
                                        </span>
                                        
                                        @if($urgencyName && str_contains(strtolower($urgencyName), 'impact'))
                                            <span class="inline-flex items-center gap-1 rounded-lg bg-red-100 px-1.5 sm:px-2 py-1 text-xs font-bold text-red-800">
                                                <span class="hidden sm:inline">⚠️</span> IMPACTS SALES
                                            </span>
                                        @elseif($urgencyName && str_contains(strtolower($urgencyName), 'urgent'))
                                            <span class="inline-flex items-center gap-1 rounded-lg bg-orange-100 px-1.5 sm:px-2 py-1 text-xs font-bold text-orange-800">
                                                <span class="hidden sm:inline">🔥</span> URGENT
                                            </span>
                                        @elseif($urgencyName && $urgencyName !== 'Normal')
                                            <span class="inline-flex items-center gap-1 rounded-lg bg-blue-100 px-1.5 sm:px-2 py-1 text-xs font-bold text-blue-800">
                                                {{ $urgencyName }}
                                            </span>
                                        @endif
                                        
                                        @if($isNative)
                                            <span class="inline-flex items-center gap-1 rounded-md bg-blue-50 px-1.5 sm:px-2 py-1 text-xs font-medium text-blue-700">
                                                <svg class="h-3 w-3 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                                    <path d="M2.003 5.884L10 9.882l7.997-3.998A2 2 0 0016 4H4a2 2 0 00-1.997 1.884z"></path>
                                                    <path d="M18 8.118l-8 4-8-4V14a2 2 0 002 2h12a2 2 0 002-2V8.118z"></path>
                                                </svg>
                                                <span class="truncate">{{ $mr->requester_type }}</span>
                                            </span>
                                        @endif
                                    </div>
                                    
                                    <h5 class="text-sm sm:text-base font-semibold text-black-800 mb-1 line-clamp-1">{{ $mr->equipment_with_issue }}</h5>
                                    <p class="text-xs sm:text-sm text-black-600 line-clamp-2">{{ $mr->description_of_issue }}</p>
                                </div>
                                
                                <!-- Selection Indicator -->
                                <div class="task-selection-indicator flex-shrink-0 w-5 h-5 sm:w-6 sm:h-6 rounded-full border-2 border-orange-300 flex items-center justify-center">
                                    <svg class="h-3 w-3 sm:h-4 sm:w-4 text-white hidden" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                                    </svg>
                                </div>
                            </div>
                            
                            <!-- Task Footer -->
                            <div class="flex flex-col sm:flex-row sm:items-center justify-between text-xs text-black-500 pt-2 border-t border-orange-100 gap-1 sm:gap-3">
                                <div class="flex flex-col sm:flex-row sm:items-center gap-1 sm:gap-3 min-w-0">
                                    <span class="truncate">{{ $isNative ? $mr->display_requester_name : ($mr->requester->name ?? 'Unknown') }}</span>
                                    @if($mr->basic_troubleshoot_done)
                                        <span class="inline-flex items-center gap-1 text-green-700 flex-shrink-0">
                                            <svg class="h-3 w-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                            </svg>
                                            <span class="hidden sm:inline">Basic Check Done</span>
                                            <span class="sm:hidden">✓ Done</span>
                                        </span>
                                    @endif
                                </div>
                                <span class="flex-shrink-0 capitalize">{{ $mr->status }}</span>
                            </div>
                        </div>
                    @endforeach
                </div>
                
                <!-- Selected Tasks Actions (Only for non-admin users) -->
                @if(Auth::user()->role !== 'admin')
                <div id="selectedTasksActions" class="hidden mt-4 p-3 sm:p-4 bg-gradient-to-r from-orange-50 to-orange-100 rounded-xl border border-orange-200">
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                        <div>
                            <p class="text-sm font-semibold text-orange-900">
                                <span id="selectedTasksCount">0</span> task(s) selected
                            </p>
                            <p class="text-xs text-orange-700">Ready to add to your work list</p>
                        </div>
                        <div class="flex flex-col sm:flex-row gap-2">
                            <button onclick="clearTaskSelection()" class="px-4 py-2 text-sm text-orange-700 hover:text-orange-800 transition-colors text-center">
                                Clear Selection
                            </button>
                            <button onclick="addSelectedTasksToCard()" class="px-6 py-2 bg-orange-600 text-white text-sm font-semibold rounded-lg hover:bg-orange-700 transition-all text-center">
                                Add Selected Tasks
                            </button>
                        </div>
                    </div>
                </div>
                @endif
                
                <!-- Task Details Panel (Expandable) -->
                <div id="taskDetailsPanel" class="hidden mt-4 space-y-4 rounded-xl border border-orange-200 bg-gradient-to-br from-orange-50 to-white p-4 sm:p-5 shadow-sm">
                    <div class="flex items-center justify-between">
                        <h5 class="text-lg font-bold text-orange-900">Task Details</h5>
                        <button onclick="closeTaskDetails()" class="text-orange-600 hover:text-orange-700">
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                            </svg>
                        </button>
                    </div>
                    
                    <!-- Equipment & Urgency -->
                    <div class="flex items-start justify-between gap-4">
                        <div class="flex-1">
                            <p class="text-sm font-medium text-black-500 mb-1">Equipment</p>
                            <p id="detailEquipment" class="text-lg font-semibold text-black-800 mb-2"></p>
                            <span id="detailNativeBadge" class="hidden inline-flex items-center gap-1 rounded-md bg-blue-50 px-2 py-1 text-sm font-medium text-blue-700">
                                <svg class="h-4 w-4" fill="currentColor" viewBox="0 0 20 20">
                                    <path d="M2.003 5.884L10 9.882l7.997-3.998A2 2 0 0016 4H4a2 2 0 00-1.997 1.884z"></path>
                                    <path d="M18 8.118l-8 4-8-4V14a2 2 0 002 2h12a2 2 0 002-2V8.118z"></path>
                                </svg>
                                <span id="detailRequesterType"></span>
                            </span>
                        </div>
                        <div class="flex flex-col items-end gap-2">
                            <span id="detailNumber" class="rounded-full bg-orange-600 px-3 py-1.5 text-sm font-bold text-white shadow-sm"></span>
                            <span id="detailUrgencyBadge" class="hidden"></span>
                        </div>
                    </div>

                    <!-- Description -->
                    <div>
                        <p class="text-sm font-medium text-black-500 mb-2">Description</p>
                        <p id="detailDescription" class="text-sm leading-relaxed text-black-700 bg-white p-3 rounded-lg border border-orange-100"></p>
                    </div>

                    <!-- Status & Due Date -->
                    <div class="grid grid-cols-2 gap-4 pt-3 border-t border-orange-200">
                        <div>
                            <p class="text-sm font-medium text-black-500 mb-1">Status</p>
                            <p id="detailStatus" class="text-sm font-semibold text-black-800 capitalize"></p>
                        </div>
                        <div class="text-right">
                            <p id="detailDateLabel" class="text-sm font-medium text-black-500 mb-1"></p>
                            <p id="detailDate" class="text-sm font-semibold text-black-800"></p>
                        </div>
                    </div>

                    <!-- Basic Troubleshoot -->
                    <div id="detailBasicCheck" class="hidden flex items-center gap-2 rounded-lg bg-green-100 px-3 py-2 shadow-sm">
                        <svg class="h-5 w-5 text-green-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                        </svg>
                        <span class="text-sm font-semibold text-green-800">Basic Troubleshooting Completed</span>
                    </div>

                    <!-- Requester Info -->
                    <div class="border-t border-orange-200 pt-3">
                        <p class="text-sm font-medium text-black-500 mb-1">Requested By</p>
                        <p id="detailRequester" class="text-sm font-medium text-black-700"></p>
                    </div>
                    
                    <!-- Admin Equipment for This Task -->
                    <div id="detailAdminEquipment" class="hidden border-t border-purple-200 pt-3">
                        <div class="rounded-lg bg-purple-50 p-4 border border-purple-200">
                            <div class="flex items-center gap-2 mb-3">
                                <svg class="h-5 w-5 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"></path>
                                </svg>
                                <label class="text-sm font-semibold text-purple-900">Admin Purchases for This Task</label>
                            </div>
                            <div id="detailAdminEquipmentList" class="space-y-2">
                                <!-- Will be populated by JavaScript -->
                            </div>
                        </div>
                    </div>
                    
                    <!-- Quick Actions -->
                    <div class="flex gap-3 pt-3 border-t border-orange-200">
                        @if(Auth::user()->role !== 'admin')
                        <button onclick="addSingleTaskToCard()" class="flex-1 bg-orange-600 text-white px-4 py-2 rounded-lg font-semibold hover:bg-orange-700 transition-all">
                            Add This Task
                        </button>
                        <button onclick="openTaskMaterialModal()" class="px-4 py-2 bg-purple-600 text-white rounded-lg font-medium hover:bg-purple-700 transition-all">
                            Purchase Materials
                        </button>
                        @endif
                        <button onclick="closeTaskDetails()" class="px-4 py-2 border-2 border-orange-200 text-orange-700 rounded-lg font-medium hover:bg-orange-50 transition-all {{ Auth::user()->role === 'admin' ? 'flex-1' : '' }}">
                            Close
                        </button>
                    </div>
                </div>
            </div>
            <div class="rounded-lg mt-4 bg-white p-3 sm:p-4 border border-orange-100">
                <h4 class="text-sm sm:text-base font-semibold text-black-800 mb-3">{{ __('invoice.current_tasks_on_card') }}</h4>
                <div id="currentTasksList" class="space-y-2">
                    @foreach($card->tasks as $task)
                        @php $mr = $task->maintenanceRequest; @endphp
                        <div id="card-task-{{ $task->id }}" class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 rounded-lg p-3 border border-orange-200 bg-orange-50">
                            <div class="flex-1 min-w-0">
                                <p class="text-sm font-medium text-black-800 truncate">#{{ $mr->id }} - {{ $mr->equipment_with_issue }}</p>
                                <p class="text-xs text-black-600 mt-1 line-clamp-2">{{ $mr->description_of_issue }}</p>
                                <p class="text-xs mt-2">
                                    {{ __('invoice.status') }}: <span class="font-semibold" id="task-status-{{ $task->id }}">{{ $task->task_status }}</span>
                                    @if($task->completed_at)
                                        • Completed at: <span class="text-xs">{{ $task->completed_at->format('M d, Y g:i A') }}</span>
                                    @endif
                                </p>
                            </div>
                            <div class="flex flex-col sm:flex-row sm:items-center gap-2 mt-2 sm:mt-0">
                                @if($task->task_status !== 'completed')
                                    @if(Auth::user()->role !== 'admin')
                                    <div class="flex flex-col sm:flex-row gap-2">
                                        <button class="rounded-lg bg-purple-600 text-white px-3 py-1 text-xs hover:bg-purple-700 transition-all text-center" onclick="openTaskMaterialModal({{ $mr->id }}, '{{ $mr->equipment_with_issue }}')">
                                            Purchase Materials
                                        </button>
                                        <button class="rounded-lg bg-green-600 text-white px-3 py-1 text-xs hover:bg-green-700 transition-all text-center" onclick="markTaskComplete({{ $card->id }}, {{ $mr->id }}, {{ $task->id }})">{{ __('invoice.mark_complete') }}</button>
                                    </div>
                                    @else
                                    <span class="text-xs font-medium text-orange-700 text-center">{{ __('invoice.in_progress') }}</span>
                                    @endif
                                @else
                                    <span class="text-xs font-bold text-green-700 text-center">✔ {{ __('invoice.completed') }}</span>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
            @else
            <div class="rounded-lg border-2 border-dashed border-orange-200 bg-orange-50/30 p-4 sm:p-6 text-center">
                <svg class="mx-auto h-10 w-10 sm:h-12 sm:w-12 text-orange-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                </svg>
                <p class="mt-2 text-sm font-medium text-black-600">{{ __('invoice.no_maintenance_requests') }}</p>
                <p class="mt-1 text-xs text-black-500">{{ __('invoice.no_maintenance_requests') }}</p>
            </div>
            @endif

            <script>
                // Add CSS for enhanced task cards
                const style = document.createElement('style');
                style.textContent = `
                    .task-card {
                        transition: all 0.2s ease;
                    }
                    .task-card:hover {
                        transform: translateY(-2px);
                    }
                    .task-card.selected {
                        border-color: #ea580c !important;
                        background-color: #fff7ed !important;
                        box-shadow: 0 0 0 2px #ea580c;
                    }
                    .task-selection-indicator {
                        transition: all 0.2s ease;
                    }
                    .line-clamp-2 {
                        display: -webkit-box;
                        -webkit-line-clamp: 2;
                        -webkit-box-orient: vertical;
                        overflow: hidden;
                    }
                    @keyframes shake {
                        0%, 100% { transform: translateX(0); }
                        10%, 30%, 50%, 70%, 90% { transform: translateX(-5px); }
                        20%, 40%, 60%, 80% { transform: translateX(5px); }
                    }
                    .animate-shake {
                        animation: shake 0.5s ease-in-out;
                    }
                `;
                document.head.appendChild(style);
                
                // Auto-scroll to error alert if present
                document.addEventListener('DOMContentLoaded', function() {
                    const errorAlert = document.getElementById('errorAlert');
                    if (errorAlert) {
                        setTimeout(() => {
                            errorAlert.scrollIntoView({ behavior: 'smooth', block: 'center' });
                        }, 300);
                        
                        // Highlight incomplete tasks
                        const currentTasksList = document.getElementById('currentTasksList');
                        if (currentTasksList) {
                            const taskStatuses = currentTasksList.querySelectorAll('[id^="task-status-"]');
                            taskStatuses.forEach(statusEl => {
                                const statusText = statusEl.textContent.trim().toLowerCase();
                                if (statusText !== 'completed') {
                                    const taskElement = statusEl.closest('[id^="card-task-"]');
                                    if (taskElement) {
                                        taskElement.style.border = '2px solid #dc2626';
                                        taskElement.style.backgroundColor = '#fef2f2';
                                        taskElement.style.boxShadow = '0 0 0 3px rgba(220, 38, 38, 0.1)';
                                    }
                                }
                            });
                            
                            // Also check newly added tasks
                            const newTasks = currentTasksList.querySelectorAll('[id^="new-card-task-"]');
                            newTasks.forEach(taskEl => {
                                const statusSpan = taskEl.querySelector('p .font-semibold');
                                if (statusSpan && statusSpan.textContent.trim().toLowerCase() !== 'completed') {
                                    taskEl.style.border = '2px solid #dc2626';
                                    taskEl.style.backgroundColor = '#fef2f2';
                                    taskEl.style.boxShadow = '0 0 0 3px rgba(220, 38, 38, 0.1)';
                                }
                            });
                        }
                    }
                });
                
                // Global variables
                let selectedTasks = new Set();
                let currentDetailTask = null;
                // Handle selection details for multiple select - show details of last selected
                function handleTaskSelection(select) {
                    const selected = Array.from(select.selectedOptions);
                    if (selected.length === 0) {
                        document.getElementById('taskDetails').classList.add('hidden');
                        return;
                    }
                    const last = selected[selected.length - 1];
                    showTaskDetails(last.value, last);
                }

                function showTaskDetails(value, optionEl = null) {
                    if (!value) {
                        document.getElementById('taskDetails').classList.add('hidden');
                        return;
                    }

                    let el = optionEl;
                    if (!el) {
                        el = document.querySelector('#taskSelector option[value="' + value + '"]');
                    }

                    if (!el) return;

                    document.getElementById('taskEquipment').textContent = el.dataset.equipment || '';
                    document.getElementById('taskDescription').textContent = el.dataset.description || '';
                    document.getElementById('taskStatus').textContent = el.dataset.status || '';
                    document.getElementById('taskRequester').textContent = el.dataset.requester || '';
                    document.getElementById('taskNumber').textContent = '#' + el.value;

                    if (el.dataset.basicCheck === '1') {
                        document.getElementById('taskBasicCheck').classList.remove('hidden');
                    } else {
                        document.getElementById('taskBasicCheck').classList.add('hidden');
                    }

                    if (el.dataset.isNative === '1') {
                        document.getElementById('taskNativeBadge').classList.remove('hidden');
                        document.getElementById('taskRequesterType').textContent = el.dataset.requesterType || 'Native';
                    } else {
                        document.getElementById('taskNativeBadge').classList.add('hidden');
                    }

                    // Load admin equipment list for this task if present in page's preloaded data
                    const adminEquipmentMap = @json($adminEquipmentByRequest ?? []);
                    const list = adminEquipmentMap[el.value] ?? [];
                    const listContainer = document.getElementById('taskAdminEquipmentList');
                    listContainer.innerHTML = '';

                    if (list.length > 0) {
                        document.getElementById('taskAdminEquipment').classList.remove('hidden');
                        list.forEach(function(item) {
                            const div = document.createElement('div');
                            div.className = 'flex items-center justify-between';
                            div.innerHTML = `<div class="text-xs text-purple-900 font-medium">${item.item_name} x${item.quantity}</div><div class="text-xs text-purple-800 font-semibold">$${Number(item.total_cost).toFixed(2)}</div>`;
                            listContainer.appendChild(div);
                        });
                    } else {
                        document.getElementById('taskAdminEquipment').classList.add('hidden');
                    }

                    document.getElementById('taskDetails').classList.remove('hidden');
                }

                // Add selected maintenance requests to card via AJAX. Calls single-add endpoint for each selection.
                const InvoiceLang = @json([
                    'all_tasks_complete_alert' => __('invoice.all_tasks_complete_alert'),
                    'please_select_at_least_one_task' => __('invoice.please_select_at_least_one_task')
                ]);

                async function addSelectedTasks() {
                    const select = document.getElementById('taskSelector');
                    const selected = Array.from(select.selectedOptions).map(s => s.value);
                    if (selected.length === 0) {
                        alert(InvoiceLang.please_select_at_least_one_task);
                    }

                    const csrf = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

                    for (const id of selected) {
                        try {
                            const res = await fetch("{{ route('invoice.cards.add-task', $card->id) }}", {
                                method: 'POST',
                                headers: {
                                    'Content-Type': 'application/json',
                                    'X-CSRF-TOKEN': csrf,
                                    'Accept': 'application/json'
                                },
                                body: JSON.stringify({ maintenance_request_id: id })
                            });

                            const json = await res.json();
                            if (res.ok && json.success) {
                                // Append to tasks list using option data
                                const opt = document.querySelector('#taskSelector option[value="' + id + '"]');
                                if (opt) {
                                    appendTaskToList(id, opt.dataset.equipment, opt.dataset.description);
                                    // Mark the option as disabled and show it's added
                                    opt.disabled = true;
                                    opt.textContent = opt.textContent + ' (Added)';
                                    opt.selected = false;
                                }
                            } else {
                                console.error('Failed to add task', json);
                            }
                        } catch (e) {
                            console.error(e);
                        }
                    }
                }

                function appendTaskToList(mrId, equipment, description) {
                    const container = document.getElementById('currentTasksList');
                    const tmpId = 'new-card-task-' + mrId + '-' + Math.floor(Math.random() * 10000);

                    const div = document.createElement('div');
                    div.id = tmpId;
                    div.className = 'flex items-center justify-between gap-3 rounded-lg p-3 border border-orange-200 bg-orange-50';
                    div.innerHTML = `
                        <div>
                            <p class="text-sm font-medium text-black-800">#${mrId} - ${equipment}</p>
                            <p class="text-xs text-black-600 mt-1">${description}</p>
                            <p class="text-xs mt-2">Status: <span class="font-semibold">pending</span></p>
                        </div>
                        <div class="flex flex-col items-end gap-2">
                            <div class="flex gap-2">
                                <button class="rounded-lg bg-purple-600 text-white px-3 py-1 text-xs hover:bg-purple-700 transition-all" onclick="openTaskMaterialModal(${mrId}, '${equipment}')">
                                    Purchase Materials
                                </button>
                                <button class="rounded-lg bg-green-600 text-white px-3 py-1 text-xs hover:bg-green-700 transition-all" onclick="markTaskComplete({{ $card->id }}, ${mrId}, null, '${tmpId}')">Mark Complete</button>
                            </div>
                        </div>
                    `;

                    container.prepend(div);
                }

                async function markTaskComplete(cardId, maintenanceRequestId, taskPivotId = null, tempDivId = null) {
                    const csrf = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
                    try {
                        const res = await fetch("{{ route('invoice.cards.complete-task', $card->id) }}", {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': csrf,
                                'Accept': 'application/json'
                            },
                            body: JSON.stringify({ maintenance_request_id: maintenanceRequestId })
                        });

                        const json = await res.json();
                        if (res.ok && json.success) {
                            // Update UI: find the element
                            if (taskPivotId) {
                                const statusEl = document.getElementById('task-status-' + taskPivotId);
                                if (statusEl) statusEl.textContent = 'completed';
                                const parent = document.getElementById('card-task-' + taskPivotId);
                                if (parent) parent.querySelector('button')?.remove();
                                if (parent) parent.querySelector('.flex-col')?.insertAdjacentHTML('beforeend', '<span class="text-xs font-bold text-green-700">✔ Completed</span>');
                            }

                            if (tempDivId) {
                                const temp = document.getElementById(tempDivId);
                                if (temp) {
                                    temp.querySelector('button')?.remove();
                                    temp.querySelector('.flex-col')?.insertAdjacentHTML('beforeend', '<span class="text-xs font-bold text-green-700">✔ Completed</span>');
                                    temp.querySelector('p .font-semibold').textContent = 'completed';
                                }
                            }

                            // If all tasks complete, optionally finalize
                            if (json.all_tasks_complete) {
                                alert('All tasks complete — card will be finalized');
                                // Optionally refresh page to show finalized state
                                setTimeout(() => location.reload(), 800);
                            }
                        } else {
                            console.error('Failed to mark task complete', json);
                        }
                    } catch (e) {
                        console.error(e);
                    }
                }

                // New Enhanced Task Card Functions
                function toggleTaskSelection(taskCard) {
                    // Check if task is already linked to this card
                    if (taskCard.dataset.isLinked === '1') {
                        return; // Don't allow selection of already linked tasks
                    }
                    
                    const taskId = taskCard.dataset.taskId;
                    const indicator = taskCard.querySelector('.task-selection-indicator');
                    const checkIcon = indicator.querySelector('svg');
                    
                    if (selectedTasks.has(taskId)) {
                        // Deselect
                        selectedTasks.delete(taskId);
                        taskCard.classList.remove('ring-2', 'ring-orange-500', 'bg-orange-50');
                        indicator.classList.remove('bg-orange-600');
                        indicator.classList.add('border-orange-300');
                        checkIcon.classList.add('hidden');
                    } else {
                        // Select
                        selectedTasks.add(taskId);
                        taskCard.classList.add('ring-2', 'ring-orange-500', 'bg-orange-50');
                        indicator.classList.add('bg-orange-600');
                        indicator.classList.remove('border-orange-300');
                        checkIcon.classList.remove('hidden');
                    }
                    
                    updateSelectedTasksUI();
                    showTaskDetailsPanel(taskCard);
                }

                function clearTaskSelection() {
                    selectedTasks.clear();
                    document.querySelectorAll('.task-card').forEach(card => {
                        const indicator = card.querySelector('.task-selection-indicator');
                        const checkIcon = indicator.querySelector('svg');
                        
                        card.classList.remove('ring-2', 'ring-orange-500', 'bg-orange-50');
                        indicator.classList.remove('bg-orange-600');
                        indicator.classList.add('border-orange-300');
                        checkIcon.classList.add('hidden');
                    });
                    updateSelectedTasksUI();
                }

                function updateSelectedTasksUI() {
                    const actionsDiv = document.getElementById('selectedTasksActions');
                    const countSpan = document.getElementById('selectedTasksCount');
                    
                    if (selectedTasks.size > 0) {
                        actionsDiv.classList.remove('hidden');
                        countSpan.textContent = selectedTasks.size;
                    } else {
                        actionsDiv.classList.add('hidden');
                    }
                }

                function showTaskDetailsPanel(taskCard) {
                    currentDetailTask = taskCard.dataset.taskId;
                    const panel = document.getElementById('taskDetailsPanel');
                    
                    // Populate details
                    document.getElementById('detailEquipment').textContent = taskCard.dataset.equipment;
                    document.getElementById('detailDescription').textContent = taskCard.dataset.description;
                    document.getElementById('detailStatus').textContent = taskCard.dataset.status.replace('_', ' ');
                    document.getElementById('detailNumber').textContent = '#' + taskCard.dataset.taskId;
                    
                    // Handle native badge
                    const nativeBadge = document.getElementById('detailNativeBadge');
                    if (taskCard.dataset.isNative === '1' && taskCard.dataset.requesterType) {
                        document.getElementById('detailRequesterType').textContent = taskCard.dataset.requesterType;
                        nativeBadge.classList.remove('hidden');
                    } else {
                        nativeBadge.classList.add('hidden');
                    }
                    
                    // Handle urgency badge
                    const urgencyBadge = document.getElementById('detailUrgencyBadge');
                    const urgency = taskCard.dataset.urgency;
                    if (urgency.toLowerCase().includes('impact')) {
                        urgencyBadge.className = 'inline-flex items-center gap-1 rounded-lg bg-red-100 px-3 py-1 text-sm font-bold text-red-800 shadow-sm';
                        urgencyBadge.innerHTML = `⚠️ Impacts Sales`;
                        urgencyBadge.classList.remove('hidden');
                    } else if (urgency.toLowerCase().includes('urgent')) {
                        urgencyBadge.className = 'inline-flex items-center gap-1 rounded-lg bg-orange-100 px-3 py-1 text-sm font-bold text-orange-800 shadow-sm';
                        urgencyBadge.innerHTML = `🔥 Urgent`;
                        urgencyBadge.classList.remove('hidden');
                    } else if (urgency && urgency !== 'Normal') {
                        urgencyBadge.className = 'inline-flex items-center gap-1 rounded-lg bg-blue-100 px-3 py-1 text-sm font-bold text-blue-800 shadow-sm';
                        urgencyBadge.innerHTML = urgency;
                        urgencyBadge.classList.remove('hidden');
                    } else {
                        urgencyBadge.classList.add('hidden');
                    }
                    
                    // Set date information
                    const isNative = taskCard.dataset.isNative === '1';
                    const dateLabel = isNative ? 'Request Date' : 'Due Date';
                    document.getElementById('detailDateLabel').textContent = dateLabel;
                    
                    const dueDate = taskCard.dataset.dueDate;
                    if (dueDate) {
                        const formattedDate = new Date(dueDate).toLocaleDateString('en-US', { 
                            month: 'short', day: 'numeric', year: 'numeric' 
                        });
                        document.getElementById('detailDate').textContent = formattedDate;
                    } else {
                        document.getElementById('detailDate').textContent = 'Not set';
                    }
                    
                    // Basic check badge
                    const basicCheckBadge = document.getElementById('detailBasicCheck');
                    if (taskCard.dataset.basicCheck === '1') {
                        basicCheckBadge.classList.remove('hidden');
                    } else {
                        basicCheckBadge.classList.add('hidden');
                    }
                    
                    // Requester info
                    let requesterText = taskCard.dataset.requester;
                    const requesterDate = taskCard.dataset.requesterDate;
                    if (requesterDate) {
                        const formattedRequesterDate = new Date(requesterDate).toLocaleDateString('en-US', { 
                            month: 'short', day: 'numeric', year: 'numeric' 
                        });
                        requesterText += ' • ' + formattedRequesterDate;
                    }
                    document.getElementById('detailRequester').textContent = requesterText;
                    
                    // Admin equipment
                    const adminEquipmentSection = document.getElementById('detailAdminEquipment');
                    const adminEquipmentList = document.getElementById('detailAdminEquipmentList');
                    
                    if (adminEquipmentByRequest[taskCard.dataset.taskId] && adminEquipmentByRequest[taskCard.dataset.taskId].length > 0) {
                        adminEquipmentList.innerHTML = '';
                        
                        adminEquipmentByRequest[taskCard.dataset.taskId].forEach(item => {
                            const itemDiv = document.createElement('div');
                            itemDiv.className = 'flex items-center justify-between bg-white p-2 rounded border border-purple-200';
                            itemDiv.innerHTML = `
                                <div class="text-sm text-purple-900 font-medium">${item.item_name} x${item.quantity}</div>
                                <div class="text-sm text-purple-800 font-semibold">$${parseFloat(item.total_cost).toFixed(2)}</div>
                            `;
                            adminEquipmentList.appendChild(itemDiv);
                        });
                        
                        adminEquipmentSection.classList.remove('hidden');
                    } else {
                        adminEquipmentSection.classList.add('hidden');
                    }
                    
                    // Show the panel
                    panel.classList.remove('hidden');
                    panel.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
                }

                function closeTaskDetails() {
                    document.getElementById('taskDetailsPanel').classList.add('hidden');
                    currentDetailTask = null;
                }

                async function addSelectedTasksToCard() {
                    if (selectedTasks.size === 0) {
                        alert(InvoiceLang.please_select_at_least_one_task);
                        return;
                    }

                    const csrf = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

                    for (const taskId of selectedTasks) {
                        try {
                            const res = await fetch("{{ route('invoice.cards.add-task', $card->id) }}", {
                                method: 'POST',
                                headers: {
                                    'Content-Type': 'application/json',
                                    'X-CSRF-TOKEN': csrf,
                                    'Accept': 'application/json'
                                },
                                body: JSON.stringify({ maintenance_request_id: taskId })
                            });

                            const json = await res.json();
                            if (res.ok && json.success) {
                                // Find the task card and get its data
                                const taskCard = document.querySelector(`[data-task-id="${taskId}"]`);
                                if (taskCard) {
                                    appendTaskToList(taskId, taskCard.dataset.equipment, taskCard.dataset.description);
                                    // Mark the task card as linked/added
                                    taskCard.dataset.isLinked = '1';
                                    taskCard.classList.remove('border-orange-200', 'bg-white', 'hover:shadow-md', 'hover:border-orange-300');
                                    taskCard.classList.add('border-green-300', 'bg-green-50', 'opacity-60', 'pointer-events-none');
                                    taskCard.querySelector('.task-number').classList.remove('bg-orange-600');
                                    taskCard.querySelector('.task-number').classList.add('bg-green-600');
                                    taskCard.querySelector('.task-number').textContent = '#' + taskId + ' (Added)';
                                    taskCard.onclick = null; // Remove click handler
                                }
                            } else {
                                console.error('Failed to add task', json);
                            }
                        } catch (e) {
                            console.error(e);
                        }
                    }
                    
                    // Clear selection after adding
                    clearTaskSelection();
                    closeTaskDetails();
                }

                async function addSingleTaskToCard() {
                    if (!currentDetailTask) return;
                    
                    selectedTasks.clear();
                    selectedTasks.add(currentDetailTask);
                    await addSelectedTasksToCard();
                }

                // Material Modal Functions
                function openTaskMaterialModal(taskId = null, taskName = null) {
                    const modal = document.getElementById('addMaterialModal');
                    const modalTitle = document.getElementById('modalTitle');
                    const modalTaskInfo = document.getElementById('modalTaskInfo');
                    const modalTaskId = document.getElementById('modalTaskId');
                    
                    // Use current detail task if no specific task provided
                    if (!taskId && currentDetailTask) {
                        const taskCard = document.querySelector(`[data-task-id="${currentDetailTask}"]`);
                        if (taskCard) {
                            taskId = currentDetailTask;
                            taskName = taskCard.dataset.equipment;
                        }
                    }
                    
                    if (taskId && taskName) {
                        modalTitle.textContent = 'Purchase Materials';
                        modalTaskInfo.textContent = `For Task #${taskId}: ${taskName}`;
                        modalTaskInfo.classList.remove('hidden');
                        modalTaskId.value = taskId;
                    } else {
                        modalTitle.textContent = 'Add Material';
                        modalTaskInfo.classList.add('hidden');
                        modalTaskId.value = '';
                    }
                    
                    // Clear form
                    const form = modal.querySelector('form');
                    form.reset();
                    
                    modal.classList.remove('hidden');
                }

                function closeMaterialModal() {
                    const modal = document.getElementById('addMaterialModal');
                    modal.classList.add('hidden');
                    
                    // Clear form
                    const form = modal.querySelector('form');
                    form.reset();
                    document.getElementById('modalTaskId').value = '';
                }

                // Fix the task completion to only complete individual tasks
                async function markTaskComplete(cardId, maintenanceRequestId, taskPivotId = null, tempDivId = null) {
                    const csrf = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
                    try {
                        const res = await fetch("{{ route('invoice.cards.complete-task', $card->id) }}", {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': csrf,
                                'Accept': 'application/json'
                            },
                            body: JSON.stringify({ 
                                maintenance_request_id: maintenanceRequestId,
                                complete_single_task: true  // Flag to indicate single task completion
                            })
                        });

                        const json = await res.json();
                        if (res.ok && json.success) {
                            // Update UI: find the element
                            if (taskPivotId) {
                                const statusEl = document.getElementById('task-status-' + taskPivotId);
                                if (statusEl) statusEl.textContent = 'completed';
                                const parent = document.getElementById('card-task-' + taskPivotId);
                                if (parent) {
                                    const button = parent.querySelector('button[onclick*="markTaskComplete"]');
                                    if (button) button.remove();
                                    const actionsDiv = parent.querySelector('.flex.gap-2');
                                    if (actionsDiv) {
                                        actionsDiv.innerHTML = '<span class="text-xs font-bold text-green-700">✔ Completed</span>';
                                    }
                                }
                            }

                            if (tempDivId) {
                                const temp = document.getElementById(tempDivId);
                                if (temp) {
                                    const button = temp.querySelector('button[onclick*="markTaskComplete"]');
                                    if (button) button.remove();
                                    const actionsDiv = temp.querySelector('.flex-col');
                                    if (actionsDiv) {
                                        actionsDiv.innerHTML = '<span class="text-xs font-bold text-green-700">✔ Completed</span>';
                                    }
                                    const statusSpan = temp.querySelector('p .font-semibold');
                                    if (statusSpan) statusSpan.textContent = 'completed';
                                }
                            }

                            // Only show completion message if ALL tasks are complete
                            if (json.all_tasks_complete) {
                                alert('All tasks complete — card will be finalized');
                                setTimeout(() => location.reload(), 800);
                            } else {
                                // Show success message for individual task completion
                                showSuccessMessage(`Task #${maintenanceRequestId} marked as complete!`);
                            }
                        } else {
                            console.error('Failed to mark task complete', json);
                            alert('Failed to mark task as complete. Please try again.');
                        }
                    } catch (e) {
                        console.error(e);
                        alert('An error occurred. Please try again.');
                    }
                }

                // Helper function to show success messages
                function showSuccessMessage(message) {
                    const successDiv = document.createElement('div');
                    successDiv.className = 'fixed bottom-4 right-4 bg-green-500 text-white px-6 py-3 rounded-lg shadow-lg z-50 transition-all';
                    successDiv.textContent = message;
                    document.body.appendChild(successDiv);
                    
                    setTimeout(() => {
                        successDiv.style.opacity = '0';
                        setTimeout(() => {
                            document.body.removeChild(successDiv);
                        }, 300);
                    }, 3000);
                }

                // Add event listener for form submission to show material purchase feedback
                document.addEventListener('DOMContentLoaded', function() {
                    const materialForm = document.querySelector('#addMaterialModal form');
                    if (materialForm) {
                        materialForm.addEventListener('submit', function(e) {
                            const taskId = document.getElementById('modalTaskId').value;
                            const itemName = materialForm.querySelector('input[name="item_name"]').value;
                            
                            if (taskId) {
                                showSuccessMessage(`Material "${itemName}" will be added to Task #${taskId} and synced with clocking record`);
                            } else {
                                showSuccessMessage(`Material "${itemName}" will be added and synced with clocking record`);
                            }
                        });
                    }
                });

                // Add event listener for form submission to show material purchase feedback
                document.addEventListener('DOMContentLoaded', function() {
                    const materialForm = document.querySelector('#addMaterialModal form');
                    if (materialForm) {
                        materialForm.addEventListener('submit', function(e) {
                            const taskId = document.getElementById('modalTaskId').value;
                            const itemName = materialForm.querySelector('input[name="item_name"]').value;
                            
                            if (taskId) {
                                console.log(`🛒 Purchasing "${itemName}" for Task #${taskId}`);
                            } else {
                                console.log(`🛒 Adding material "${itemName}" to card`);
                            }
                        });
                    }
                });

                // Initialize page: mark already-linked tasks in any dropdown that exists
                document.addEventListener('DOMContentLoaded', function() {
                    const linkedTaskIds = @json($linkedRequestIds ?? []);
                    const taskSelector = document.getElementById('taskSelector');
                    
                    if (taskSelector && linkedTaskIds.length > 0) {
                        // Mark already-linked tasks as disabled in dropdown
                        linkedTaskIds.forEach(function(taskId) {
                            const option = taskSelector.querySelector('option[value="' + taskId + '"]');
                            if (option) {
                                option.disabled = true;
                                option.textContent = option.textContent + ' (Added)';
                            }
                        });
                    }
                });
            </script>
                    @forelse($card->materials as $material)
                    <div class="flex gap-2 items-start bg-orange-50 p-3 rounded-lg border border-orange-200 transition-all duration-300 hover:bg-orange-100">
                        <div class="flex-1">
                            <div class="flex items-center gap-2 mb-1">
                                <p class="text-sm font-medium text-black-800">{{ $material->item_name }}</p>
                                @if($material->maintenance_request_id)
                                    <span class="inline-flex items-center gap-1 rounded-md bg-purple-50 px-2 py-0.5 text-xs font-medium text-purple-700">
                                        <svg class="h-3 w-3" fill="currentColor" viewBox="0 0 20 20">
                                            <path d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                        </svg>
                                        Task #{{ $material->maintenance_request_id }}
                                    </span>
                                @endif
                            </div>
                            <p class="text-xs text-black-600 font-semibold">${{ number_format($material->cost, 2) }}</p>
                            
                            @if($material->receipt_photos && count($material->receipt_photos) > 0)
                            <!-- Receipt Photos Gallery -->
                            <div class="mt-3">
                                <p class="text-xs text-orange-700 font-medium mb-2 flex items-center gap-1">
                                    <svg class="h-3 w-3" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M4 3a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V5a2 2 0 00-2-2H4zm12 12H4l4-8 3 6 2-4 3 6z" clip-rule="evenodd"></path>
                                    </svg>
                                    {{ count($material->receipt_photos) }} {{ count($material->receipt_photos) == 1 ? 'Receipt Photo' : 'Receipt Photos' }}
                                </p>
                                <div class="flex gap-2 flex-wrap">
                                    @foreach($material->receipt_photos as $photo)
                                    <a href="{{ Storage::url($photo) }}" target="_blank" class="block group">
                                        <img src="{{ Storage::url($photo) }}" 
                                             alt="Receipt" 
                                             class="w-20 h-20 object-cover rounded-lg border-2 border-orange-200 group-hover:border-orange-400 transition-all cursor-pointer shadow-sm group-hover:shadow-md group-hover:scale-105">
                                    </a>
                                    @endforeach
                                </div>
                                <p class="text-xs text-orange-600 mt-2 italic">Click photos to view full size</p>
                            </div>
                            @endif
                        </div>
                        <form action="{{ route('invoice.cards.materials.delete', $material->id) }}" method="POST" class="inline">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="text-red-600 hover:text-red-700 p-2 rounded-lg hover:bg-red-50 transition-all" onclick="return confirm('Delete this item?')">
                                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                </svg>
                            </button>
                        </form>
                    </div>
                    @empty
                    <div class="text-center py-6 rounded-lg border-2 border-dashed border-orange-200 bg-orange-50/30">
                        <svg class="mx-auto h-10 w-10 text-orange-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"></path>
                        </svg>
                        <p class="mt-2 text-sm font-medium text-black-600">No items added yet</p>
                        <p class="mt-1 text-xs text-black-500">Click "Add Item" to record purchased materials</p>
                    </div>
                    @endforelse
                    
                </div>
            </div>

            <!-- Notes -->
            <div class="rounded-lg">
                <label class="mb-2 block text-sm font-medium text-black-700">Notes <span class="font-normal text-black-500">(Optional)</span></label>
                <textarea id="notes" rows="3" 
                    class="block w-full rounded-lg border border-orange-200 bg-orange-50 px-3 py-2.5 text-sm focus:border-orange-500 focus:ring-2 focus:ring-orange-500" 
                    placeholder="Write any notes about the maintenance...">{{ $card->notes }}</textarea>
            </div>

            <!-- Work Duration (if completed) -->
            @if($card->end_time)
            <div class="rounded-lg bg-purple-50 p-4 border border-purple-200">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-xs text-purple-700 mb-1">Work Duration</p>
                        <p class="text-lg font-bold text-purple-900">
                            @php
                                $duration = $card->start_time->diff($card->end_time);
                                $hours = $duration->h + ($duration->days * 24);
                                $minutes = $duration->i;
                            @endphp
                            {{ $hours }}h {{ $minutes }}m
                        </p>
                    </div>
                    <div class="text-right">
                        <p class="text-xs text-purple-700 mb-1">Completed</p>
                        <p class="text-sm font-semibold text-purple-900">{{ $card->end_time->format('g:i A') }}</p>
                    </div>
                </div>
            </div>
            @endif

            <!-- Cost Breakdown (Admin Only - Completed Cards) -->
            @if(Auth::user()->role === 'admin' && $card->status === 'completed')
            @php
                // Calculate admin equipment cost for this card
                $adminEquipmentCost = \App\Models\Payment::where('store_id', $card->store_id)
                    ->where('is_admin_equipment', true)
                    ->whereHas('equipmentItems')
                    ->get()
                    ->sum(function($payment) {
                        return $payment->equipmentTotal();
                    });
            @endphp
            <div class="rounded-xl bg-gradient-to-br from-green-50 to-white p-5 border-2 border-green-300 shadow-md">
                <h4 class="text-base font-bold text-green-900 mb-4 flex items-center">
                    <svg class="h-5 w-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    Cost Breakdown
                </h4>
                <div class="space-y-3">
                    <!-- Labor Cost -->
                    <div class="flex justify-between items-center text-sm pb-2 border-b border-green-200">
                        <div>
                            <span class="text-black-700">Labor Cost</span>
                            @php
                            
                                $totalLaborHours = ($card->labor_hours ?? 0) + ($card->accumulated_labor_hours ?? 0);
                            @endphp
                            @if($totalLaborHours > 0)
                            <p class="text-xs text-black-500 mt-0.5">
                                {{ number_format($totalLaborHours, 2) }} hours @ ${{ number_format($card->user->hourly_pay ?? 0, 2) }}/hr
                                @if($card->accumulated_labor_hours > 0)
                                
                                    <span class="text-black-400">({{ number_format($card->accumulated_labor_hours, 2) }} + {{ number_format($card->labor_hours ?? 0, 2) }})</span>

                                @endif
                            </p>
                            @endif
                        </div>
                        <span class="font-bold text-black-900">${{ number_format($card->labor_cost ?? 0, 2) }}</span>
                    </div>
                    
                    <!-- Materials Cost -->
                    <div class="flex justify-between items-center text-sm pb-2 border-b border-green-200">
                        <div>
                            <span class="text-black-700">Materials Cost</span>
                            <p class="text-xs text-black-500 mt-0.5">{{ $card->materials->count() }} items</p>
                        </div>
                        <span class="font-bold text-black-900">${{ number_format($card->materials_cost ?? 0, 2) }}</span>
                    </div>
                    
                    <!-- Admin Equipment Cost -->
                    @if($adminEquipmentCost > 0)
                    <div class="flex justify-between items-center text-sm pb-2 border-b border-green-200">
                        <div>
                            <span class="text-black-700">Admin Equipment Cost</span>
                            <p class="text-xs text-black-500 mt-0.5">Equipment purchased by admin</p>
                        </div>
                        <span class="font-bold text-black-900">${{ number_format($adminEquipmentCost, 2) }}</span>
                    </div>
                    @endif
                    
                    <!-- Mileage Payment -->
                    @if($card->mileage_payment > 0)
                    <div class="flex justify-between items-center text-sm pb-2 border-b border-green-200">
                        <div>
                            <span class="text-black-700">Mileage Payment</span>
                            @if($card->total_miles)
                            <p class="text-xs text-black-500 mt-0.5">{{ number_format($card->total_miles, 2) }} miles</p>
                            @endif
                        </div>
                        <span class="font-bold text-black-900">${{ number_format($card->mileage_payment, 2) }}</span>
                    </div>
                    @endif
                    
                    <!-- Driving Time Payment -->
                    @if($card->driving_time_payment > 0)
                    <div class="flex justify-between items-center text-sm pb-2 border-b border-green-200">
                        <div>
                            <span class="text-black-700">Driving Time Payment</span>
                            @if($card->total_driving_time_hours)
                            <p class="text-xs text-black-500 mt-0.5">{{ number_format($card->total_driving_time_hours, 2) }} hours total</p>
                            @elseif($card->driving_time_hours)
                            <p class="text-xs text-black-500 mt-0.5">{{ number_format($card->driving_time_hours, 2) }} hours</p>
                            @endif
                        </div>
                        <span class="font-bold text-black-900">${{ number_format($card->driving_time_payment, 2) }}</span>
                    </div>
                    @endif
                    
                    <!-- Total -->
                    <div class="flex justify-between items-center text-base font-bold pt-3 border-t-2 border-green-400">
                        <span class="text-green-900">Total Cost</span>
                        <span class="text-xl text-green-600">${{ number_format(($card->total_cost ?? 0) + $adminEquipmentCost, 2) }}</span>
                    </div>
                </div>
            </div>
            @endif

            <!-- Admin Equipment Purchases for This Store -->
            @php
                // Get admin equipment purchases for this store
                // Include purchases from the card's time period and any linked to the card's maintenance requests
                $linkedRequestIds = $card->maintenanceRequests->pluck('id')->toArray();
                
                $adminEquipmentQuery = \App\Models\Payment::where('store_id', $card->store_id)
                    ->where('is_admin_equipment', true)
                    ->whereHas('equipmentItems')
                    ->with(['equipmentItems', 'company']);
                
                // Get purchases from the card's time period OR linked to card's maintenance requests
                if (!empty($linkedRequestIds)) {
                    $adminEquipmentQuery->where(function($query) use ($card, $linkedRequestIds) {
                        $query->whereIn('maintenance_request_id', $linkedRequestIds)
                              ->orWhereBetween('date', [
                                  $card->start_time->startOfDay(),
                                  ($card->end_time ?? now())->endOfDay()
                              ]);
                    });
                } else {
                    // If no linked requests, get purchases from the card's time period
                    $adminEquipmentQuery->whereBetween('date', [
                        $card->start_time->startOfDay(),
                        ($card->end_time ?? now())->endOfDay()
                    ]);
                }
                
                $adminEquipmentPurchases = $adminEquipmentQuery->orderBy('date', 'desc')->get();
                
                // Debug: Also get all admin equipment for this store (for testing)
                $allAdminEquipment = \App\Models\Payment::where('store_id', $card->store_id)
                    ->where('is_admin_equipment', true)
                    ->whereHas('equipmentItems')
                    ->with(['equipmentItems', 'company'])
                    ->orderBy('date', 'desc')
                    ->get();
            @endphp
            
            <!-- Debug Section (remove after testing) -->
            @if(Auth::user()->role === 'admin')
            <div class="rounded-xl bg-yellow-50 p-4 border border-yellow-200 mb-4">
                <h5 class="text-sm font-bold text-yellow-800 mb-2">Debug Info (Admin Only)</h5>
                <p class="text-xs text-yellow-700">
                    Store ID: {{ $card->store_id }} | 
                    Card Period: {{ $card->start_time->format('M d, Y') }} - {{ ($card->end_time ?? now())->format('M d, Y') }} |
                    Linked Requests: {{ implode(', ', $linkedRequestIds) ?: 'None' }}
                </p>
                <p class="text-xs text-yellow-700 mt-1">
                    Filtered Admin Equipment: {{ $adminEquipmentPurchases->count() }} | 
                    All Admin Equipment for Store: {{ $allAdminEquipment->count() }}
                </p>
            </div>
            @endif
            
            @if($adminEquipmentPurchases->count() > 0 || $allAdminEquipment->count() > 0)
            <div class="rounded-xl bg-gradient-to-br from-purple-50 to-white p-5 border-2 border-purple-300 shadow-md">
                <h4 class="text-base font-bold text-purple-900 mb-4 flex items-center">
                    <svg class="h-5 w-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"></path>
                    </svg>
                    Admin Equipment Purchases
                    <span class="ml-2 inline-flex items-center px-2 py-0.5 rounded-full text-xs font-bold bg-purple-100 text-purple-800">
                        {{ $adminEquipmentPurchases->count() }} for this work
                    </span>
                </h4>
                
                <div class="space-y-3 max-h-64 overflow-y-auto">
                    @foreach(($adminEquipmentPurchases->count() > 0 ? $adminEquipmentPurchases : $allAdminEquipment) as $purchase)
                    <div class="bg-white rounded-lg p-3 border border-purple-200 shadow-sm">
                        <div class="flex items-start justify-between mb-2">
                            <div class="flex-1">
                                <div class="flex items-center gap-2 mb-1">
                                    <span class="text-xs font-medium text-purple-700">
                                        {{ $purchase->date->format('M d, Y') }}
                                    </span>
                                    @if($purchase->company)
                                    <span class="text-xs text-gray-600">• {{ $purchase->company->name }}</span>
                                    @endif
                                    @if($purchase->maintenance_request_id)
                                    <span class="inline-flex items-center px-1.5 py-0.5 rounded text-xs font-medium bg-blue-100 text-blue-800">
                                        Task #{{ $purchase->maintenance_request_id }}
                                    </span>
                                    @endif
                                </div>
                                @if($purchase->what_got_fixed)
                                <p class="text-sm text-gray-700 mb-2">{{ $purchase->what_got_fixed }}</p>
                                @endif
                            </div>
                            <div class="text-right">
                                <p class="text-sm font-bold text-purple-900">${{ number_format($purchase->equipmentTotal(), 2) }}</p>
                            </div>
                        </div>
                        
                        <!-- Equipment Items -->
                        <div class="space-y-1">
                            @foreach($purchase->equipmentItems as $item)
                            <div class="flex items-center justify-between text-xs bg-purple-50 px-2 py-1 rounded">
                                <span class="text-gray-700">
                                    {{ $item->quantity }}x {{ $item->item_name }}
                                </span>
                                <span class="font-medium text-purple-800">
                                    ${{ number_format($item->total_cost, 2) }}
                                </span>
                            </div>
                            @endforeach
                        </div>
                    </div>
                    @endforeach
                </div>
                
                <div class="mt-3 pt-3 border-t border-purple-200">
                    <div class="flex items-center justify-between text-sm">
                        <span class="text-purple-700">
                            <svg class="inline h-4 w-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 00-1-1H9z" clip-rule="evenodd"></path>
                            </svg>
                            These purchases will be included in invoices for this store
                        </span>
                        <span class="font-bold text-purple-900">
                            Total: ${{ number_format(($adminEquipmentPurchases->count() > 0 ? $adminEquipmentPurchases : $allAdminEquipment)->sum(function($p) { return $p->equipmentTotal(); }), 2) }}
                        </span>
                    </div>
                </div>
            </div>
            @endif



            <!-- Action Buttons -->
            @if($card->status === 'in_progress')
            <div class="flex gap-3 pt-2">
                <button type="button" onclick="completeCard('completed')"
                    class="flex-1 rounded-xl bg-gradient-to-r from-green-500 to-green-600 px-4 py-3.5 text-base font-bold text-white shadow-lg hover:shadow-xl transition-all">
                    <span class="flex items-center justify-center">
                        <svg class="mr-2 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                        </svg>
                        Done
                    </span>
                </button>
                <button type="button" onclick="completeCard('not_done')"
                    class="flex-1 rounded-xl border-2 border-orange-200 bg-white px-4 py-3.5 text-base font-bold text-orange-700 shadow-sm hover:bg-orange-50 transition-all">
                    <span class="flex items-center justify-center">
                        <svg class="mr-2 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                        Not Done
                    </span>
                </button>
            </div>
            @endif
        </div>
    </div>
</div>

<!-- Add Material Modal -->
<!-- Add Material Modal -->
<div id="addMaterialModal" class="hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4">
    <div class="bg-white rounded-2xl p-4 sm:p-6 max-w-sm sm:max-w-md w-full mx-4">
        <h3 class="text-lg sm:text-xl font-bold text-black-900 mb-4">
            <span id="modalTitle">Add Material</span>
            <span id="modalTaskInfo" class="hidden text-sm font-normal text-gray-600 block mt-1"></span>
        </h3>
        
        <form action="{{ route('invoice.cards.materials.add', $card->id) }}" method="POST" enctype="multipart/form-data">
            @csrf
            
            <!-- Hidden field for task association -->
            <input type="hidden" name="maintenance_request_id" id="modalTaskId" value="">
            
            <div class="mb-4">
                <label class="block text-sm font-medium text-black-700 mb-2">Item Name</label>
                <input type="text" name="item_name" required 
                       class="block w-full rounded-lg border-orange-300 shadow-sm focus:border-orange-500 focus:ring-orange-500"
                       placeholder="e.g., Pipe, Filter, etc.">
            </div>
            
            <div class="mb-4">
                <label class="block text-sm font-medium text-black-700 mb-2">Cost</label>
                <div class="relative">
                    <span class="absolute left-3 top-2.5 text-black-500">$</span>
                    <input type="number" name="cost" step="0.01" min="0" required 
                           class="block w-full pl-8 rounded-lg border-orange-300 shadow-sm focus:border-orange-500 focus:ring-orange-500"
                           placeholder="0.00">
                </div>
            </div>
            
            <div class="mb-4">
                <label class="block text-sm font-medium text-black-700 mb-2">
                    📸 Receipt Photos (Optional)
                </label>
                <div class="border-2 border-dashed border-orange-300 rounded-lg p-4 text-center hover:border-orange-400 transition-all bg-orange-50">
                    <div class="mb-2">
                        <svg class="mx-auto h-12 w-12 text-orange-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                        </svg>
                    </div>
                    <label for="receipt_photos" class="cursor-pointer">
                        <span class="inline-block px-4 py-2 bg-orange-600 text-white rounded-lg font-semibold hover:bg-orange-700 transition-all">
                            📷 Take Receipt Photo
                        </span>
                        <input type="file" 
                               id="receipt_photos"
                               name="receipt_photos[]" 
                               multiple 
                               accept="image/*" 
                               capture="environment"
                               class="hidden">
                    </label>
                    <p class="mt-2 text-xs text-orange-700">You can take multiple photos if needed</p>
                </div>
            </div>
            
            <div class="flex flex-col sm:flex-row gap-3">
                <button type="button" onclick="closeMaterialModal()"
                        class="flex-1 px-4 py-2 border-2 border-orange-200 rounded-lg text-orange-700 font-medium hover:bg-orange-50 text-center">
                    Cancel
                </button>
                <button type="submit" class="flex-1 px-4 py-2 bg-orange-600 text-white rounded-lg font-medium hover:bg-orange-700 text-center">
                    Add Material
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Complete Card Form -->
<form id="completeCardForm" action="{{ route('invoice.cards.complete', $card->id) }}" method="POST" style="display: none;">
    @csrf
    <input type="hidden" name="status" id="completeStatus">
    <input type="hidden" name="notes" id="completeNotes">
    <input type="hidden" name="not_done_reason" id="notDoneReason">
    <input type="hidden" name="selected_maintenance_request_id" id="selectedMaintenanceRequestId">
</form>

<script>
// Admin equipment data by maintenance request ID
const adminEquipmentByRequest = @json($adminEquipmentByRequest ?? []);

// LocalStorage key for persisting selected task
const STORAGE_KEY = 'selected_task_{{ $card->id }}';

// On page load, restore selected task from localStorage
document.addEventListener('DOMContentLoaded', function() {
    const savedTask = localStorage.getItem(STORAGE_KEY);
    if (savedTask) {
        const selector = document.getElementById('taskSelector');
        if (selector) {
            selector.value = savedTask;
            showTaskDetails(savedTask);
        }
    }
});

function showTaskDetails(taskId) {
    const taskDetails = document.getElementById('taskDetails');
    
    if (!taskId) {
        taskDetails.classList.add('hidden');
        // Remove from localStorage when deselected
        localStorage.removeItem(STORAGE_KEY);
        return;
    }
    
    // Save selected task to localStorage
    localStorage.setItem(STORAGE_KEY, taskId);
    
    // Get selected option
    const selector = document.getElementById('taskSelector');
    const selectedOption = selector.options[selector.selectedIndex];
    
    // Extract data from option attributes
    const equipment = selectedOption.dataset.equipment;
    const description = selectedOption.dataset.description;
    const urgency = selectedOption.dataset.urgency;
    const status = selectedOption.dataset.status;
    const dueDate = selectedOption.dataset.dueDate;
    const requester = selectedOption.dataset.requester;
    const requesterDate = selectedOption.dataset.requesterDate;
    const basicCheck = selectedOption.dataset.basicCheck === '1';
    const isNative = selectedOption.dataset.isNative === '1';
    const requesterType = selectedOption.dataset.requesterType;
    
    // Populate task details
    document.getElementById('taskEquipment').textContent = equipment;
    document.getElementById('taskDescription').textContent = description;
    document.getElementById('taskStatus').textContent = status.replace('_', ' ').toUpperCase();
    document.getElementById('taskNumber').textContent = '#' + taskId;
    
    // Show/hide native badge
    const nativeBadge = document.getElementById('taskNativeBadge');
    if (isNative && requesterType) {
        document.getElementById('taskRequesterType').textContent = requesterType;
        nativeBadge.classList.remove('hidden');
    } else {
        nativeBadge.classList.add('hidden');
    }
    
    // Set urgency badge
    const urgencyBadge = document.getElementById('taskUrgencyBadge');
    if (urgency.toLowerCase().includes('impact')) {
        urgencyBadge.className = 'inline-flex items-center gap-1 rounded-lg bg-red-100 px-2.5 py-1 text-xs font-bold text-red-800 shadow-sm';
        urgencyBadge.innerHTML = `
            <svg class="h-3 w-3" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path>
            </svg>
            Impacts Sales
        `;
        urgencyBadge.classList.remove('hidden');
    } else if (urgency.toLowerCase().includes('urgent')) {
        urgencyBadge.className = 'inline-flex items-center gap-1 rounded-lg bg-orange-100 px-2.5 py-1 text-xs font-bold text-orange-800 shadow-sm';
        urgencyBadge.innerHTML = `
            <svg class="h-3 w-3" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z" clip-rule="evenodd"></path>
            </svg>
            Urgent
        `;
        urgencyBadge.classList.remove('hidden');
    } else if (urgency && urgency !== 'Normal') {
        urgencyBadge.className = 'inline-flex items-center gap-1 rounded-lg bg-blue-100 px-2.5 py-1 text-xs font-bold text-blue-800 shadow-sm';
        urgencyBadge.innerHTML = `
            <svg class="h-3 w-3" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z" clip-rule="evenodd"></path>
            </svg>
            ${urgency}
        `;
        urgencyBadge.classList.remove('hidden');
    } else {
        urgencyBadge.classList.add('hidden');
    }
    
    // Set date label and value
    const dateLabel = isNative ? 'Request Date' : 'Due Date';
    document.getElementById('taskDateLabel').textContent = dateLabel;
    if (dueDate) {
        const formattedDate = new Date(dueDate).toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' });
        document.getElementById('taskDate').textContent = formattedDate;
    } else {
        document.getElementById('taskDate').textContent = 'Not set';
    }
    
    // Show/hide basic check badge
    const basicCheckBadge = document.getElementById('taskBasicCheck');
    if (basicCheck) {
        basicCheckBadge.classList.remove('hidden');
    } else {
        basicCheckBadge.classList.add('hidden');
    }
    
    // Set requester info
    let requesterText = requester;
    if (requesterDate) {
        const formattedRequesterDate = new Date(requesterDate).toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' });
        requesterText += ' • ' + formattedRequesterDate;
    }
    document.getElementById('taskRequester').textContent = requesterText;
    
    // Show admin equipment for this task
    const adminEquipmentSection = document.getElementById('taskAdminEquipment');
    const adminEquipmentList = document.getElementById('taskAdminEquipmentList');
    
    if (adminEquipmentByRequest[taskId] && adminEquipmentByRequest[taskId].length > 0) {
        // Clear previous items
        adminEquipmentList.innerHTML = '';
        
        // Add items
        adminEquipmentByRequest[taskId].forEach(item => {
            const itemDiv = document.createElement('div');
            itemDiv.className = 'text-xs text-purple-800';
            itemDiv.textContent = `• ${item.item_name} (Qty: ${item.quantity}) - $${parseFloat(item.total_cost).toFixed(2)}`;
            adminEquipmentList.appendChild(itemDiv);
        });
        
        adminEquipmentSection.classList.remove('hidden');
    } else {
        adminEquipmentSection.classList.add('hidden');
    }
    
    // Show the details section
    taskDetails.classList.remove('hidden');
}

function completeCard(status) {
    const notes = document.getElementById('notes').value;
    const selectedTask = document.getElementById('taskSelector') ? document.getElementById('taskSelector').value : '';
    
    // Check if there are tasks available
    const taskSelector = document.getElementById('taskSelector');
    const hasTasks = taskSelector && taskSelector.options.length > 1; // More than just the default "Select a task" option
    
    // Validate: If tasks are available and status is 'completed', require task selection
    if (hasTasks && !selectedTask && status === 'completed') {
        alert('⚠️ Please select a task before marking this card as complete.\n\nYou must choose which task you worked on from the dropdown above.\n\nIf you didn\'t work on any tasks, please mark the card as "Not Done" instead.');
        return; // Stop the form submission
    }
    
    if (status === 'not_done') {
        const reason = prompt('Please provide a reason for not completing this card:');
        if (!reason) return;
        document.getElementById('notDoneReason').value = reason;
    }
    
    document.getElementById('completeStatus').value = status;
    document.getElementById('completeNotes').value = notes;
    document.getElementById('selectedMaintenanceRequestId').value = selectedTask || '';
    
    // Clear saved task from localStorage when card is completed
    localStorage.removeItem(STORAGE_KEY);
    
    document.getElementById('completeCardForm').submit();
}
</script>

@if(session('success'))
<div class="fixed bottom-4 right-4 bg-green-500 text-white px-6 py-3 rounded-lg shadow-lg z-50">
    {{ session('success') }}
</div>
@endif
@endsection
