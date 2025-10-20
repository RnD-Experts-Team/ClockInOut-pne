@extends('layouts.app')

@section('content')
    <div class="container mx-auto px-4 py-8">
        <div class="max-w-4xl mx-auto">
            <div class="bg-orange-50 shadow-sm ring-1 ring-orange-900/5 rounded-lg overflow-hidden">
                <div class="bg-orange-600 px-6 py-4">
                    <h1 class="text-2xl font-bold text-white">Edit Apartment Lease</h1>
                    <p class="text-orange-100 text-sm mt-1">Update apartment lease details and manage renewals</p>
                </div>

                <!-- NEW: Renewal Status Alert -->
                @if($apartmentLease->renewal_date)
                    @php $renewalStatusInfo = $apartmentLease->renewal_status_info; @endphp
                    <div class="m-6 mb-0 {{ $apartmentLease->is_renewal_overdue ? 'bg-red-50 border-l-4 border-red-400' : 'bg-blue-50 border-l-4 border-blue-400' }} p-4 rounded-lg shadow-sm">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center">
                                <div class="flex-shrink-0">
                                    <svg class="h-5 w-5 {{ $apartmentLease->is_renewal_overdue ? 'text-red-400' : 'text-blue-400' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        @if($apartmentLease->is_renewal_overdue)
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                                        @else
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                        @endif
                                    </svg>
                                </div>
                                <div class="ml-3">
                                    <h3 class="text-sm font-medium {{ $apartmentLease->is_renewal_overdue ? 'text-red-800' : 'text-blue-800' }}">
                                        {{ $apartmentLease->is_renewal_overdue ? '🚨 Apartment Renewal Overdue' : '🔔 Apartment Renewal Scheduled' }}
                                    </h3>
                                    <div class="mt-2 text-sm {{ $apartmentLease->is_renewal_overdue ? 'text-red-700' : 'text-blue-700' }}">
                                        <p>
                                            <strong>Status:</strong>
                                            <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium {{ $renewalStatusInfo['class'] }}">
                                                {{ $renewalStatusInfo['message'] }}
                                            </span>
                                        </p>
                                        <p class="mt-1">
                                            <strong>Due Date:</strong> {{ $apartmentLease->formatted_renewal_date }}
                                            @if($apartmentLease->days_until_renewal !== null)
                                                ({{ abs($apartmentLease->days_until_renewal) }} days {{ $apartmentLease->days_until_renewal < 0 ? 'overdue' : 'remaining' }})
                                            @endif
                                        </p>
                                        @if($apartmentLease->renewalCreatedBy)
                                            <p class="mt-1"><strong>Set by:</strong> {{ $apartmentLease->renewalCreatedBy->name }}</p>
                                        @endif
                                    </div>
                                </div>
                            </div>
                            <div class="flex space-x-2">
                                @if($apartmentLease->renewal_status === 'pending')
                                    <button onclick="sendApartmentRenewalReminder({{ $apartmentLease->id }})"
                                            class="inline-flex items-center px-3 py-1 border border-blue-300 text-xs font-medium rounded text-blue-700 bg-blue-100 hover:bg-blue-200">
                                        🔔 Send Reminder
                                    </button>
                                    <button onclick="completeApartmentRenewal({{ $apartmentLease->id }})"
                                            class="inline-flex items-center px-3 py-1 border border-green-300 text-xs font-medium rounded text-green-700 bg-green-100 hover:bg-green-200">
                                        ✅ Complete Renewal
                                    </button>
                                @endif
                            </div>
                        </div>
                    </div>
                @endif

                <form action="{{ route('admin.apartment-leases.update', $apartmentLease) }}" method="POST" class="p-6">
                    @csrf
                    @method('PUT')

                    <!-- Store Selection Section -->
                    <div class="bg-orange-50 rounded-lg p-4 mb-6">
                        <h3 class="text-lg font-medium text-black-900 mb-4 flex items-center">
                            <svg class="w-5 h-5 mr-2 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-2m-14 0h2m0 0V9a2 2 0 012-2h2a2 2 0 012 2v10" />
                            </svg>
                            Store Information
                        </h3>

                        <!-- Store Selection Options -->
                        <div class="mb-4">
                            <label class="form-label block text-sm font-medium text-black-700 mb-3">Choose Store Option</label>
                            <div class="flex gap-4">
                                <label class="flex items-center">
                                    <input type="radio" name="store_option" value="existing" class="form-radio text-black-600 focus:ring-orange-500" {{ $apartmentLease->store_id ? 'checked' : '' }}>
                                    <span class="ml-2 text-sm text-black-700">Select Existing Store</span>
                                </label>
                                <label class="flex items-center">
                                    <input type="radio" name="store_option" value="new" class="form-radio text-black-600 focus:ring-orange-500">
                                    <span class="ml-2 text-sm text-black-700">Create New Store</span>
                                </label>
                                <label class="flex items-center">
                                    <input type="radio" name="store_option" value="manual" class="form-radio text-black-600 focus:ring-orange-500" {{ !$apartmentLease->store_id ? 'checked' : '' }}>
                                    <span class="ml-2 text-sm text-black-700">Enter Store Number Manually</span>
                                </label>
                            </div>
                        </div>

                        <!-- Existing Store Selection -->
                        <div id="existing-store-section" class="mb-4 {{ !$apartmentLease->store_id ? 'hidden' : '' }}">
                            <label for="store_id" class="form-label block text-sm font-medium text-black-700 mb-2">Select Store</label>
                            <select name="store_id" id="store_id" class="form-select block w-full rounded-lg border-orange-200 shadow-sm focus:border-orange-500 focus:ring-orange-500 sm:text-sm @error('store_id') border-orange-500 @enderror">
                                <option value="">Select a store...</option>
                                @if(isset($stores))
                                    @foreach($stores as $store)
                                        <option value="{{ $store->id }}" {{ old('store_id', $apartmentLease->store_id) == $store->id ? 'selected' : '' }}>
                                            {{ $store->store_number }} - {{ $store->name ?: 'No Name' }}
                                        </option>
                                    @endforeach
                                @endif
                            </select>
                            @error('store_id')
                            <p class="form-error mt-1 text-sm text-black-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- New Store Creation -->
                        <div id="new-store-section" class="hidden mb-4">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <label for="new_store_number" class="form-label block text-sm font-medium text-black-700 mb-2">New Store Number <span class="text-red-500">*</span></label>
                                    <input type="text" name="new_store_number" id="new_store_number"
                                           class="form-field block w-full rounded-lg border-orange-200 shadow-sm focus:border-orange-500 focus:ring-orange-500 sm:text-sm @error('new_store_number') border-orange-500 @enderror"
                                           value="{{ old('new_store_number') }}"
                                           placeholder="e.g., 001, Store-A">
                                    @error('new_store_number')
                                    <p class="form-error mt-1 text-sm text-black-600">{{ $message }}</p>
                                    @enderror
                                </div>
                                <div>
                                    <label for="new_store_name" class="form-label block text-sm font-medium text-black-700 mb-2">New Store Name</label>
                                    <input type="text" name="new_store_name" id="new_store_name"
                                           class="form-field block w-full rounded-lg border-orange-200 shadow-sm focus:border-orange-500 focus:ring-orange-500 sm:text-sm @error('new_store_name') border-orange-500 @enderror"
                                           value="{{ old('new_store_name') }}"
                                           placeholder="Store name or location">
                                    @error('new_store_name')
                                    <p class="form-error mt-1 text-sm text-black-600">{{ $message }}</p>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <!-- Manual Store Number -->
                        <div id="manual-store-section" class="mb-4 {{ $apartmentLease->store_id ? 'hidden' : '' }}">
                            <label for="store_number" class="form-label block text-sm font-medium text-black-700 mb-2">Store Number</label>
                            <input type="number" name="store_number" id="store_number"
                                   class="form-field block w-full rounded-lg border-orange-200 shadow-sm focus:border-orange-500 focus:ring-orange-500 sm:text-sm @error('store_number') border-orange-300 @enderror"
                                   value="{{ old('store_number', $apartmentLease->store_number) }}"
                                   placeholder="1" min="1">
                            @error('store_number')
                            <p class="form-error mt-1 text-sm text-black-600">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <!-- Basic Apartment Information -->
                    <div class="bg-white rounded-lg p-4 mb-6 border border-gray-200">
                        <h3 class="text-lg font-medium text-black-900 mb-4 flex items-center">
                            <svg class="w-5 h-5 mr-2 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2H5a2 2 0 00-2 2z" />
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V5a2 2 0 012-2h4a2 2 0 012 2v2m-6 4h6" />
                            </svg>
                            Apartment Details
                        </h3>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <!-- Apartment Address -->
                            <div>
                                <label for="apartment_address" class="form-label block text-sm font-medium text-black-700 mb-2">Apartment Address <span class="text-red-500">*</span></label>
                                <textarea name="apartment_address" id="apartment_address" rows="3" required
                                          class="form-textarea block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm @error('apartment_address') border-red-300 @enderror"
                                          placeholder="Enter full apartment address">{{ old('apartment_address', $apartmentLease->apartment_address) }}</textarea>
                                @error('apartment_address')
                                <p class="form-error mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Lease Holder -->
                            <div>
                                <label for="lease_holder" class="form-label block text-sm font-medium text-black-700 mb-2">Lease Holder <span class="text-red-500">*</span></label>
                                <textarea name="lease_holder" id="lease_holder" rows="3" required
                                          class="form-textarea block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm @error('lease_holder') border-red-300 @enderror"
                                          placeholder="Enter lease holder name">{{ old('lease_holder', $apartmentLease->lease_holder) }}</textarea>
                                @error('lease_holder')
                                <p class="form-error mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Rent -->
                            <div>
                                <label for="rent" class="form-label block text-sm font-medium text-black-700 mb-2">Rent <span class="text-red-500">*</span></label>
                                <div class="relative rounded-lg shadow-sm">
                                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                        <span class="text-gray-500 sm:text-sm">$</span>
                                    </div>
                                    <input type="number" name="rent" id="rent" step="0.01" min="0" required
                                           class="form-field block w-full pl-7 rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm @error('rent') border-red-300 @enderror"
                                           value="{{ old('rent', $apartmentLease->rent) }}"
                                           placeholder="1200.00">
                                </div>
                                @error('rent')
                                <p class="form-error mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Utilities -->
                            <div>
                                <label for="utilities" class="form-label block text-sm font-medium text-black-700 mb-2">Utilities</label>
                                <div class="relative rounded-lg shadow-sm">
                                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                        <span class="text-gray-500 sm:text-sm">$</span>
                                    </div>
                                    <input type="number" name="utilities" id="utilities" step="0.01" min="0"
                                           class="form-field block w-full pl-7 rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm @error('utilities') border-red-300 @enderror"
                                           value="{{ old('utilities', $apartmentLease->utilities) }}"
                                           placeholder="150.00">
                                </div>
                                @error('utilities')
                                <p class="form-error mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Number of AT -->
                            <div>
                                <label for="number_of_AT" class="form-label block text-sm font-medium text-black-700 mb-2">Number of AT <span class="text-red-500">*</span></label>
                                <input type="number" name="number_of_AT" id="number_of_AT" min="1" required
                                       class="form-field block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm @error('number_of_AT') border-red-300 @enderror"
                                       value="{{ old('number_of_AT', $apartmentLease->number_of_AT ?? 1) }}"
                                       placeholder="1">
                                <p class="mt-1 text-xs text-gray-500">Number of Adult Tenants</p>
                                @error('number_of_AT')
                                <p class="form-error mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Number of Cars -->
                            <div>
                                <label for="has_car" class="form-label block text-sm font-medium text-black-700 mb-2">Number of Cars <span class="text-red-500">*</span></label>
                                <input type="number" name="has_car" id="has_car" min="0" max="10" required
                                       class="form-field block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm @error('has_car') border-red-300 @enderror"
                                       value="{{ old('has_car', $apartmentLease->has_car ?? 0) }}"
                                       placeholder="0">
                                <p class="mt-1 text-xs text-gray-500">Enter the number of cars (0 if no cars)</p>
                                @error('has_car')
                                <p class="form-error mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Is Family -->
                            <div>
                                <label for="is_family" class="form-label block text-sm font-medium text-black-700 mb-2">Is Family</label>
                                <select name="is_family" id="is_family"
                                        class="form-select block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm @error('is_family') border-red-300 @enderror">
                                    <option value="">Select...</option>
                                    <option value="Yes" {{ old('is_family', $apartmentLease->is_family) == 'Yes' ? 'selected' : '' }}>Yes</option>
                                    <option value="No" {{ old('is_family', $apartmentLease->is_family) == 'No' ? 'selected' : '' }}>No</option>
                                </select>
                                @error('is_family')
                                <p class="form-error mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Expiration Date -->
                            <div>
                                <label for="expiration_date" class="form-label block text-sm font-medium text-black-700 mb-2">Expiration Date</label>
                                <input type="date" name="expiration_date" id="expiration_date"
                                       class="form-field block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm @error('expiration_date') border-red-300 @enderror"
                                       value="{{ old('expiration_date', $apartmentLease->expiration_date ? $apartmentLease->expiration_date->format('Y-m-d') : '') }}">
                                @error('expiration_date')
                                <p class="form-error mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Drive Time -->
                            <div>
                                <label for="drive_time" class="form-label block text-sm font-medium text-black-700 mb-2">Drive Time</label>
                                <input type="text" name="drive_time" id="drive_time"
                                       class="form-field block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm @error('drive_time') border-red-300 @enderror"
                                       value="{{ old('drive_time', $apartmentLease->drive_time) }}"
                                       placeholder="e.g., 15 minutes">
                                @error('drive_time')
                                <p class="form-error mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Notes -->
                            <div class="md:col-span-2">
                                <label for="notes" class="form-label block text-sm font-medium text-black-700 mb-2">Notes</label>
                                <textarea name="notes" id="notes" rows="4"
                                          class="form-textarea block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm @error('notes') border-red-300 @enderror"
                                          placeholder="Additional notes or comments">{{ old('notes', $apartmentLease->notes) }}</textarea>
                                @error('notes')
                                <p class="form-error mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <!-- NEW: Renewal Management Section -->
                    <div class="bg-blue-50 rounded-lg p-4 mb-6 border border-blue-200">
                        <h3 class="text-lg font-medium text-black-900 mb-4 flex items-center">
                            <svg class="w-5 h-5 mr-2 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            Renewal Management
                        </h3>
                        <p class="text-sm text-gray-600 mb-6">
                            Manage apartment lease renewal scheduling and tracking. Leave empty to remove renewal management.
                        </p>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label for="renewal_date" class="form-label block text-sm font-medium text-black-700 mb-2">
                                    <svg class="w-4 h-4 inline mr-1 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                    </svg>
                                    Renewal Date
                                </label>
                                @php
                                    $renewalDateValue = old('renewal_date', $apartmentLease->renewal_date);
                                    if ($renewalDateValue instanceof \Carbon\Carbon) {
                                        $renewalDateValue = $renewalDateValue->format('Y-m-d');
                                    } elseif ($renewalDateValue && !is_string($renewalDateValue)) {
                                        $renewalDateValue = \Carbon\Carbon::parse($renewalDateValue)->format('Y-m-d');
                                    }
                                @endphp
                                <input type="date" name="renewal_date" id="renewal_date"
                                       class="form-field block w-full rounded-lg border-blue-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm @error('renewal_date') border-red-500 @enderror"
                                       value="{{ $renewalDateValue }}"
                                       min="{{ date('Y-m-d', strtotime('+1 day')) }}">
                                <p class="mt-1 text-xs text-blue-600">
                                    <svg class="w-3 h-3 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                    Changing date will update reminders automatically
                                </p>
                                @error('renewal_date')
                                <p class="form-error mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label for="renewal_status" class="form-label block text-sm font-medium text-black-700 mb-2">
                                    <svg class="w-4 h-4 inline mr-1 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                    Current Status
                                </label>
                                <select name="renewal_status" id="renewal_status" class="form-select block w-full rounded-lg border-blue-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm @error('renewal_status') border-red-500 @enderror">
                                    <option value="">No Status (Remove Renewal)</option>
                                    <option value="pending" {{ old('renewal_status', $apartmentLease->renewal_status) == 'pending' ? 'selected' : '' }}>
                                        📋 Pending - Not Started
                                    </option>
                                    <option value="in_progress" {{ old('renewal_status', $apartmentLease->renewal_status) == 'in_progress' ? 'selected' : '' }}>
                                        ⏳ In Progress - Under Review
                                    </option>
                                    <option value="completed" {{ old('renewal_status', $apartmentLease->renewal_status) == 'completed' ? 'selected' : '' }}>
                                        ✅ Completed - Renewal Done
                                    </option>
                                    <option value="declined" {{ old('renewal_status', $apartmentLease->renewal_status) == 'declined' ? 'selected' : '' }}>
                                        ❌ Declined - Not Renewing
                                    </option>
                                </select>
                                @error('renewal_status')
                                <p class="form-error mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>

                        <div class="mt-6">
                            <label for="renewal_notes" class="form-label block text-sm font-medium text-black-700 mb-2">
                                <svg class="w-4 h-4 inline mr-1 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 8h10M7 12h4m1 8l-4-4H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-1.586z" />
                                </svg>
                                Renewal Notes
                            </label>
                            <textarea name="renewal_notes" id="renewal_notes" rows="3"
                                      class="form-textarea block w-full rounded-lg border-blue-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm @error('renewal_notes') border-red-500 @enderror"
                                      placeholder="Add notes about the apartment renewal process, terms to negotiate, deadlines, etc...">{{ old('renewal_notes', $apartmentLease->renewal_notes) }}</textarea>
                            @error('renewal_notes')
                            <p class="form-error mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Renewal Tracking Info -->
                        @if($apartmentLease->renewal_date)
                            <div class="mt-6 p-4 bg-blue-100 rounded-lg border border-blue-200">
                                <h4 class="text-sm font-semibold text-blue-900 mb-2">
                                    <svg class="w-4 h-4 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                                    </svg>
                                    Current Apartment Renewal Information
                                </h4>
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-xs text-blue-800">
                                    @if($apartmentLease->renewalCreatedBy)
                                        <div><strong>Set by:</strong> {{ $apartmentLease->renewalCreatedBy->name }}</div>
                                    @endif
                                    @if($apartmentLease->renewal_reminder_sent_at)
                                        <div><strong>Last reminder:</strong> {{ $apartmentLease->renewal_reminder_sent_at->format('M j, Y g:i A') }}</div>
                                    @endif
                                    @if($apartmentLease->renewal_completed_at)
                                        <div class="md:col-span-2"><strong>Completed:</strong> {{ $apartmentLease->renewal_completed_at->format('M j, Y g:i A') }}</div>
                                    @endif
                                </div>
                            </div>
                        @endif
                    </div>

                    <!-- Form Actions -->
                    <div class="flex justify-end space-x-4 mt-8 pt-6 border-t border-gray-200">
                        <a href="{{ route('admin.apartment-leases.show', $apartmentLease) }}"
                           class="form-cancel px-6 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                            Cancel
                        </a>
                        <button type="submit"
                                class="form-button px-6 py-2 bg-orange-600 text-white rounded-lg hover:bg-orange-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-orange-500">
                            <svg class="w-4 h-4 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                            </svg>
                            Update Apartment Lease
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Enhanced JavaScript with Renewal Management -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const storeOptions = document.querySelectorAll('input[name="store_option"]');
            const existingStoreSection = document.getElementById('existing-store-section');
            const newStoreSection = document.getElementById('new-store-section');
            const manualStoreSection = document.getElementById('manual-store-section');
            const storeSelect = document.getElementById('store_id');
            const newStoreNumber = document.getElementById('new_store_number');
            const manualStoreNumber = document.getElementById('store_number');

            // NEW: Renewal functionality
            const renewalDate = document.getElementById('renewal_date');
            const renewalStatus = document.getElementById('renewal_status');

            function toggleStoreOptions() {
                const selectedOption = document.querySelector('input[name="store_option"]:checked').value;

                // Hide all sections
                existingStoreSection.classList.add('hidden');
                newStoreSection.classList.add('hidden');
                manualStoreSection.classList.add('hidden');

                // Reset required attributes
                if (storeSelect) storeSelect.required = false;
                if (newStoreNumber) newStoreNumber.required = false;
                if (manualStoreNumber) manualStoreNumber.required = false;

                // Show appropriate section
                if (selectedOption === 'existing') {
                    existingStoreSection.classList.remove('hidden');
                    if (storeSelect) storeSelect.required = true;
                } else if (selectedOption === 'new') {
                    newStoreSection.classList.remove('hidden');
                    if (newStoreNumber) newStoreNumber.required = true;
                } else if (selectedOption === 'manual') {
                    manualStoreSection.classList.remove('hidden');
                    if (manualStoreNumber) manualStoreNumber.required = false; // Not required for apartment leases
                }
            }

            // NEW: Handle renewal date changes
            function handleRenewalDateChange() {
                if (renewalDate.value && !renewalStatus.value) {
                    renewalStatus.value = 'pending';
                    showRenewalNotification('Status automatically set to "Pending" since you updated the renewal date.');
                }
            }

            // NEW: Validate renewal date
            function validateRenewalDate() {
                if (renewalDate.value) {
                    const selectedDate = new Date(renewalDate.value);
                    const today = new Date();
                    today.setHours(0, 0, 0, 0);

                    if (selectedDate <= today) {
                        showRenewalNotification('⚠️ Warning: Apartment renewal date should typically be in the future', 'warning');
                    } else {
                        const daysUntilRenewal = Math.ceil((selectedDate - today) / (1000 * 60 * 60 * 24));
                        showRenewalNotification(`✅ Apartment renewal scheduled for ${daysUntilRenewal} days from now`, 'success');
                    }
                }
            }

            // Event Listeners
            storeOptions.forEach(option => {
                option.addEventListener('change', toggleStoreOptions);
            });

            // NEW: Renewal date event listeners
            renewalDate.addEventListener('change', function() {
                handleRenewalDateChange();
                validateRenewalDate();
            });

            // Initialize on page load
            toggleStoreOptions();

            console.log('🏢 Apartment lease edit with renewal management initialized');
        });

        // NEW: Apartment renewal action functions (for the alert banner)
        async function sendApartmentRenewalReminder(leaseId) {
            try {
                const response = await fetch(`/admin/apartment-leases/${leaseId}/send-renewal-reminder`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    }
                });

                const data = await response.json();

                if (data.success) {
                    showRenewalNotification('✅ Apartment Renewal Reminder Sent', data.message, 'success');
                } else {
                    showRenewalNotification('❌ Error', data.error, 'error');
                }
            } catch (error) {
                showRenewalNotification('❌ Network Error', 'Failed to send apartment renewal reminder', 'error');
            }
        }

        async function completeApartmentRenewal(leaseId) {
            if (confirm('Are you sure you want to mark this apartment renewal as completed?')) {
                try {
                    const response = await fetch(`/admin/apartment-leases/${leaseId}/complete-renewal`, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                        }
                    });

                    if (response.ok) {
                        showRenewalNotification('✅ Apartment Renewal Completed', 'Apartment lease renewal has been marked as completed', 'success');
                        setTimeout(() => location.reload(), 2000);
                    } else {
                        showRenewalNotification('❌ Error', 'Failed to complete apartment renewal', 'error');
                    }
                } catch (error) {
                    showRenewalNotification('❌ Network Error', 'Failed to complete apartment renewal', 'error');
                }
            }
        }

        // NEW: Apartment renewal notification function
        function showRenewalNotification(title, message, type = 'info') {
            // Remove existing notifications
            const existing = document.querySelectorAll('.apartment-lease-notification');
            existing.forEach(n => n.remove());

            const colors = {
                success: 'bg-green-100 border-green-500 text-green-800',
                error: 'bg-red-100 border-red-500 text-red-800',
                warning: 'bg-yellow-100 border-yellow-500 text-yellow-800',
                info: 'bg-blue-100 border-blue-500 text-blue-800'
            };

            const notification = document.createElement('div');
            notification.className = `apartment-lease-notification fixed top-4 right-4 max-w-sm ${colors[type]} border-l-4 p-4 rounded-lg shadow-lg z-50`;
            notification.innerHTML = `
                <div class="flex justify-between items-start">
                    <div>
                        <h4 class="font-semibold text-sm">${title}</h4>
                        <p class="text-sm mt-1">${message}</p>
                    </div>
                    <button onclick="this.parentElement.parentElement.remove()" class="text-lg font-bold ml-4">&times;</button>
                </div>
            `;

            document.body.appendChild(notification);

            // Auto remove after 5 seconds
            setTimeout(() => {
                if (notification.parentElement) {
                    notification.remove();
                }
            }, 5000);
        }
    </script>
@endsection
