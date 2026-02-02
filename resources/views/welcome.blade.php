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
                        <h3 class="text-sm font-bold text-blue-900">Client Demo - New Feature Preview</h3>
                        <p class="mt-1 text-xs leading-relaxed text-blue-800">
                            This demonstrates the new card-based workflow. Users can manage multiple store visits in a single session, track purchases, upload receipts, and complete work efficiently.
                        </p>
                    </div>
                </div>
            </div>

            <!-- Store Card 1 - Active/In Progress -->
            <div class="animate-fade-in mb-6 space-y-4 rounded-2xl bg-white p-5 shadow-lg ring-1 ring-orange-900/5 transition-all duration-300">
                
                <!-- Store Selection -->
                <div class="rounded-lg transition-all duration-300 hover:shadow-md">
                    <label class="mb-2 block text-sm font-medium text-black-700">Select Store</label>
                    <div class="relative">
                        <select class="block w-full appearance-none rounded-lg border border-orange-200 bg-orange-50 px-4 py-3 pr-10 text-base font-medium text-black-800 transition-all duration-300 focus:border-orange-500 focus:ring-2 focus:ring-orange-500">
                            <option>Store 5 - Mall Rd</option>
                            <option>Store 3 - Downtown</option>
                            <option>Store 7 - Airport</option>
                        </select>
                        <div class="pointer-events-none absolute inset-y-0 {{ isRtl() ? 'left-0 pl-3' : 'right-0 pr-3' }} flex items-center text-black-700">
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                            </svg>
                        </div>
                    </div>
                </div>

                <!-- Maintenance Request - Detailed View -->
                <div class="rounded-lg transition-all duration-300 hover:shadow-md">
                    <div class="mb-2 flex items-center justify-between">
                        <label class="block text-sm font-medium text-black-700">Maintenance Request</label>
                        <span class="rounded-full bg-orange-600 px-2.5 py-1 text-xs font-bold text-white shadow-sm">#123</span>
                    </div>
                    <div class="space-y-3 rounded-xl border border-orange-200 bg-gradient-to-br from-orange-50 to-white p-4 ring-1 ring-orange-900/5">
                        
                        <!-- Equipment & Urgency -->
                        <div class="flex items-start justify-between gap-3">
                            <div class="flex-1">
                                <p class="text-xs font-medium text-black-500">Equipment</p>
                                <p class="mt-0.5 text-sm font-semibold text-black-800">Sink</p>
                            </div>
                            <div class="flex-shrink-0">
                                <span class="inline-flex items-center gap-1 rounded-lg bg-red-100 px-2.5 py-1 text-xs font-bold text-red-800 shadow-sm">
                                    <svg class="h-3 w-3" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path>
                                    </svg>
                                    Impacts Sales
                                </span>
                            </div>
                        </div>

                        <!-- Description -->
                        <div>
                            <p class="text-xs font-medium text-black-500">Description</p>
                            <p class="mt-1 text-xs leading-relaxed text-black-700">The Drainage for the sink needs replacement and the tube that connects to it from underneath because it's causing the water to overflow and a bad smell is coming out of the water drainage cesspool when the sink pours water</p>
                        </div>

                        <!-- Due Date & Basic Troubleshoot -->
                        <div class="flex items-center justify-between gap-3 border-t border-orange-200 pt-3">
                            <div>
                                <p class="text-xs font-medium text-black-500">Due Date</p>
                                <p class="mt-0.5 text-sm font-semibold text-black-800">Nov 10, 2025</p>
                            </div>
                            <div class="flex items-center gap-1.5 rounded-lg bg-green-100 px-2.5 py-1 shadow-sm">
                                <svg class="h-4 w-4 text-green-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                </svg>
                                <span class="text-xs font-semibold text-green-800">Basic Check Done</span>
                            </div>
                        </div>

                        <!-- Requester Info -->
                        <div class="border-t border-orange-200 pt-3">
                            <p class="text-xs font-medium text-black-500">Requested By</p>
                            <p class="mt-0.5 text-sm font-medium text-black-700">Billy Foster • Nov 07, 2025</p>
                        </div>
                    </div>
                </div>

                <!-- Bought Items -->
                <div class="rounded-lg transition-all duration-300 hover:shadow-md">
                    <label class="mb-2 block text-sm font-medium text-black-700">{{ __('invoice.bought_items') }}</label>
                    <div class="space-y-2">
                        <!-- Item 1 -->
                        <div class="flex gap-2">
                            <input type="text" placeholder="{{ __('messages.item_name_placeholder') ?? 'Item name' }}" value="Pipe" 
                                class="flex-1 rounded-lg border border-orange-200 bg-orange-50 px-3 py-2.5 text-sm transition-all duration-300 focus:border-orange-500 focus:ring-2 focus:ring-orange-500">
                            <input type="text" placeholder="{{ __('messages.price_placeholder') ?? 'Price' }}" value="$8" 
                                class="w-24 rounded-lg border border-orange-200 bg-orange-50 px-3 py-2.5 text-sm transition-all duration-300 focus:border-orange-500 focus:ring-2 focus:ring-orange-500">
                        </div>
                        <!-- Add More Button -->
                        <button type="button" class="w-full rounded-lg border-2 border-dashed border-orange-300 bg-orange-50/50 py-2.5 text-sm font-medium text-orange-700 transition-all duration-300 hover:bg-orange-100 hover:border-orange-400">
                            {{ __('invoice.add_another_item') }}
                        </button>
                    </div>
                </div>

                <!-- Upload Receipts -->
                <div class="rounded-lg transition-all duration-300 hover:shadow-md">
                    <div class="mb-2 flex items-center justify-between">
                        <label class="block text-sm font-medium text-black-700">Upload Receipts</label>
                        <span class="rounded-full bg-orange-100 px-2.5 py-0.5 text-xs font-medium text-orange-800">Multi</span>
                    </div>
                    
                    <!-- Upload Area -->
                    <div class="rounded-lg border-2 border-dashed border-orange-200 bg-orange-50/30 p-4 text-center transition-all duration-300 hover:border-orange-500 hover:bg-orange-50">
                        <label class="cursor-pointer">
                            <input type="file" multiple accept="image/*" class="hidden">
                            <div class="flex flex-col items-center">
                                <svg class="mb-2 h-8 w-8 text-orange-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"></path>
                                </svg>
                                <span class="text-sm font-medium text-black-600">{{ __('messages.upload_file') ?? 'Click to upload' }}</span>
                                <span class="text-xs text-black-500">{{ __('messages.or_drag_drop') ?? 'or drag and drop' }}</span>
                            </div>
                        </label>
                    </div>
                    
                    <!-- Preview Uploaded Images -->
                    <div class="mt-3 grid grid-cols-2 gap-3">
                        <div class="text-center">
                            <div class="mb-1 h-20 overflow-hidden rounded-lg border-2 border-orange-200 bg-orange-100 transition-all duration-300 hover:border-orange-400">
                                <div class="flex h-full items-center justify-center">
                                    <svg class="h-10 w-10 text-orange-300" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M4 3a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V5a2 2 0 00-2-2H4zm12 12H4l4-8 3 6 2-4 3 6z" clip-rule="evenodd"></path>
                                    </svg>
                                </div>
                            </div>
                            <span class="text-xs text-black-600">pipe.jpg</span>
                        </div>
                        <div class="text-center">
                            <div class="mb-1 h-20 overflow-hidden rounded-lg border-2 border-orange-200 bg-orange-100 transition-all duration-300 hover:border-orange-400">
                                <div class="flex h-full items-center justify-center">
                                    <svg class="h-10 w-10 text-orange-300" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M4 3a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V5a2 2 0 00-2-2H4zm12 12H4l4-8 3 6 2-4 3 6z" clip-rule="evenodd"></path>
                                    </svg>
                                </div>
                            </div>
                            <span class="text-xs text-black-600">receipt1.png</span>
                        </div>
                    </div>
                </div>

                <!-- Notes (Optional) -->
                <div class="rounded-lg transition-all duration-300 hover:shadow-md">
                    <label class="mb-2 block text-sm font-medium text-black-700">
                        {{ __('messages.notes') ?? 'Notes' }} 
                        <span class="font-normal text-black-500">({{ __('messages.optional') ?? 'Optional' }})</span>
                    </label>
                    <textarea rows="3" 
                        class="block w-full rounded-lg border border-orange-200 bg-orange-50 px-3 py-2.5 text-sm transition-all duration-300 focus:border-orange-500 focus:ring-2 focus:ring-orange-500" 
                        placeholder="{{ __('messages.notes_placeholder') ?? 'Write any notes about the maintenance you did at this store...' }}"></textarea>
                </div>

                <!-- Store Location - Text Based -->
                <div class="rounded-lg transition-all duration-300 hover:shadow-md">
                    <label class="mb-2 block text-sm font-medium text-black-700">Store Location</label>
                    <div class="rounded-lg border border-orange-200 bg-orange-50 p-4 ring-1 ring-orange-900/5">
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
                                <p class="text-sm font-semibold text-black-800">Store 5 - Mall Rd</p>
                                <p class="mt-0.5 text-xs text-black-600">123 Mall Road, Shopping District</p>
                                <p class="mt-0.5 text-xs text-black-500">City Center, State 12345</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Action Buttons -->
                <div class="flex gap-3 pt-2">
                    <button type="button"
                        class="flex-1 transform rounded-xl border border-transparent bg-gradient-to-r from-orange-500 to-orange-600 px-4 py-3.5 text-base font-bold text-white shadow-lg transition-all duration-300 hover:scale-105 hover:shadow-xl focus:outline-none focus:ring-2 focus:ring-orange-500 focus:ring-offset-2">
                        <span class="flex items-center justify-center">
                            <svg class="{{ isRtl() ? 'ml-2' : 'mr-2' }} h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                            </svg>
                            Done
                        </span>
                    </button>
                    <button type="button"
                        class="flex-1 transform rounded-xl border-2 border-orange-200 bg-white px-4 py-3.5 text-base font-bold text-orange-700 shadow-sm transition-all duration-300 hover:scale-105 hover:bg-orange-50 focus:outline-none focus:ring-2 focus:ring-orange-500 focus:ring-offset-2">
                        <span class="flex items-center justify-center">
                            <svg class="{{ isRtl() ? 'ml-2' : 'mr-2' }} h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                            </svg>
                            Not Done
                        </span>
                    </button>
                </div>
            </div>

            <!-- Store Card 2 - Completed Example -->
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
                                <p class="text-sm font-bold text-black-800">Store 3 - Downtown</p>
                                <p class="text-xs text-black-500">Completed • Ticket #124 • 2 items purchased</p>
                            </div>
                        </div>
                        <svg class="h-5 w-5 text-black-400 transition-transform duration-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                        </svg>
                    </div>
                </div>
                <!-- Expandable Details -->
                <div class="card-details hidden border-t border-green-100 bg-green-50/30 p-5">
                    <p class="text-xs text-black-600">This card shows a completed store visit. Click to expand/collapse details.</p>
                </div>
            </div>

            <!-- Add New Store Card Button -->
            <button type="button" 
                class="animate-fade-in mb-6 w-full rounded-xl border-2 border-dashed border-orange-300 bg-white py-4 text-center shadow-sm transition-all duration-300 hover:border-orange-400 hover:bg-orange-50 hover:shadow-md focus:outline-none focus:ring-2 focus:ring-orange-500 focus:ring-offset-2">
                <svg class="mx-auto mb-1 h-6 w-6 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                </svg>
                <span class="text-sm font-semibold text-orange-700">Add Another Store</span>
            </button>

            <!-- Clock Out Section (Bottom) -->
            <div class="animate-fade-in rounded-xl bg-gradient-to-r from-orange-100 to-orange-50 p-5 shadow-md ring-1 ring-orange-900/5">
                <div class="text-center">
                    <svg class="mx-auto mb-3 h-12 w-12 text-orange-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    <h3 class="text-lg font-bold text-black-900">Ready to Clock Out?</h3>
                    <p class="mt-1 text-sm text-black-600">All stores completed. Proceed to complete your shift.</p>
                    <button type="button"
                        class="mt-4 w-full transform rounded-xl bg-black-800 px-6 py-3.5 text-base font-bold text-white shadow-lg transition-all duration-300 hover:scale-105 hover:bg-black-900 focus:outline-none focus:ring-2 focus:ring-black-500 focus:ring-offset-2">
                        <span class="flex items-center justify-center">
                            <svg class="{{ isRtl() ? 'ml-2' : 'mr-2' }} h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path>
                            </svg>
                            Proceed to Clock Out
                        </span>
                    </button>
                </div>
            </div>

        </div>
    </div>

    <style>
        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(10px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .animate-fade-in {
            animation: fadeIn 0.5s ease-out;
        }

        /* Smooth transitions for all interactive elements */
        button, input, textarea, select {
            transition: all 0.3s ease-in-out;
        }
        
        input:focus, textarea:focus, select:focus {
            outline: none;
        }

        /* Hover effect for cards */
        .rounded-2xl:hover {
            transform: translateY(-2px);
        }
    </style>
@endsection
