@extends('layouts.app')

@section('title', 'Edit Lease')

@section('content')
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <div class="flex justify-between items-center mb-6">
            <div>
                <h1 class="text-3xl font-bold text-black-900">Edit Lease - {{ $lease->store_number }}</h1>
                <p class="mt-2 text-sm text-black-700">Update lease information and renewal settings</p>
            </div>
            <div class="flex space-x-3">
                <a href="{{ route('leases.show', $lease) }}"
                   class="form-cancel inline-flex items-center px-4 py-2 border border-orange-300 text-sm font-medium rounded-lg text-black-700 bg-orange-50 hover:bg-orange-100 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-orange-500 transition-all duration-200">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                    </svg>
                    View
                </a>
                <a href="{{ route('leases.index') }}"
                   class="form-cancel inline-flex items-center px-4 py-2 border border-orange-300 text-sm font-medium rounded-lg text-black-700 bg-orange-50 hover:bg-orange-100 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-orange-500 transition-all duration-200">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                    </svg>
                    Back to List
                </a>
            </div>
        </div>

        <!-- NEW: Current Renewal Status Display -->
        @if($lease->renewal_date)
            @php $renewalStatusInfo = $lease->renewal_status_info; @endphp
            <div class="mb-6 bg-blue-50 border-l-4 border-blue-400 p-4 rounded-lg shadow-sm">
                <div class="flex items-center justify-between">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <svg class="h-5 w-5 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                        <div class="ml-3">
                            <p class="text-sm text-blue-700">
                                <span class="font-medium">🔔 Renewal Status:</span>
                                <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium {{ $renewalStatusInfo['class'] }}">
                                    {{ $renewalStatusInfo['message'] }}
                                </span>
                                - Due: {{ $lease->formatted_renewal_date }}
                            </p>
                        </div>
                    </div>
                    <div>
                        @if($lease->renewal_status === 'pending')
                            <button onclick="completeRenewal({{ $lease->id }})"
                                    class="inline-flex items-center px-3 py-1 border border-green-300 text-xs font-medium rounded text-green-700 bg-green-100 hover:bg-green-200">
                                ✅ Mark as Completed
                            </button>
                        @endif
                    </div>
                </div>
            </div>
        @endif

        <form action="{{ route('leases.update', $lease) }}" method="POST" class="space-y-8">
            @csrf
            @method('PATCH')

            <!-- Store Selection/Creation -->
            <div class="bg-orange-50 shadow-sm ring-1 ring-orange-900/5 rounded-lg p-6">
                <h2 class="text-lg font-semibold text-black-900 mb-4 flex items-center">
                    <svg class="w-5 h-5 mr-2 text-black-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-2m-14 0h2m0 0V9a2 2 0 012-2h2a2 2 0 012 2v10" />
                    </svg>
                    Store Information
                </h2>

                <!-- Store Selection Options -->
                <div class="mb-6">
                    <label class="form-label block text-sm font-medium text-black-700 mb-3">Choose Store Option</label>
                    <div class="flex gap-4">
                        <label class="flex items-center">
                            <input type="radio" name="store_option" value="existing" class="form-radio text-black-600 focus:ring-orange-500" {{ $lease->store_id ? 'checked' : '' }}>
                            <span class="ml-2 text-sm text-black-700">Select Existing Store</span>
                        </label>
                        <label class="flex items-center">
                            <input type="radio" name="store_option" value="new" class="form-radio text-black-600 focus:ring-orange-500" {{ !$lease->store_id ? 'checked' : '' }}>
                            <span class="ml-2 text-sm text-black-700">Create New Store</span>
                        </label>
                    </div>
                </div>

                <!-- Existing Store Selection -->
                <div id="existing-store-section" class="mb-6 {{ !$lease->store_id ? 'hidden' : '' }}">
                    <label for="store_id" class="form-label block text-sm font-medium text-black-700 mb-2">Select Store</label>
                    <select name="store_id" id="store_id" class="form-select block w-full rounded-lg border-orange-300 shadow-sm focus:border-orange-500 focus:ring-orange-500 sm:text-sm @error('store_id') border-red-500 @enderror">
                        <option value="">Select a store...</option>
                        @foreach($stores as $store)
                            <option value="{{ $store->id }}" {{ old('store_id', $lease->store_id) == $store->id ? 'selected' : '' }}>
                                {{ $store->store_number }} - {{ $store->name ?: 'No Name' }}
                            </option>
                        @endforeach
                    </select>
                    @error('store_id')
                    <p class="form-error mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- New Store Creation -->
                <div id="new-store-section" class="{{ $lease->store_id ? 'hidden' : '' }}">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                        <div>
                            <label for="new_store_number" class="form-label block text-sm font-medium text-black-700 mb-2">New Store Number <span class="text-red-500">*</span></label>
                            <input type="text" name="new_store_number" id="new_store_number"
                                   class="form-field block w-full rounded-lg border-orange-300 shadow-sm focus:border-orange-500 focus:ring-orange-500 sm:text-sm @error('new_store_number') border-red-500 @enderror"
                                   value="{{ old('new_store_number') }}"
                                   placeholder="e.g., 001, Store-A">
                            @error('new_store_number')
                            <p class="form-error mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            <label for="new_store_name" class="form-label block text-sm font-medium text-black-700 mb-2">New Store Name</label>
                            <input type="text" name="new_store_name" id="new_store_name"
                                   class="form-field block w-full rounded-lg border-orange-300 shadow-sm focus:border-orange-500 focus:ring-orange-500 sm:text-sm @error('new_store_name') border-red-500 @enderror"
                                   value="{{ old('new_store_name') }}"
                                   placeholder="Store name or location">
                            @error('new_store_name')
                            <p class="form-error mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </div>

                <!-- Lease-specific Store Info -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label for="name" class="form-label block text-sm font-medium text-black-700 mb-2">Lease Name/Location</label>
                        <input type="text" name="name" id="name"
                               class="form-field block w-full rounded-lg border-orange-300 shadow-sm focus:border-orange-500 focus:ring-orange-500 sm:text-sm @error('name') border-red-500 @enderror"
                               value="{{ old('name', $lease->name) }}"
                               placeholder="Lease-specific name or description">
                        @error('name')
                        <p class="form-error mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label for="sqf" class="form-label block text-sm font-medium text-black-700 mb-2">SQF</label>
                        <input type="number" name="sqf" id="sqf"
                               class="form-field block w-full rounded-lg border-orange-300 shadow-sm focus:border-orange-500 focus:ring-orange-500 sm:text-sm @error('sqf') border-red-500 @enderror"
                               value="{{ old('sqf', $lease->sqf) }}"
                               placeholder="0"
                               min="0">
                        @error('sqf')
                        <p class="form-error mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mt-6">
                    <div class="md:col-span-2">
                        <label for="store_address" class="form-label block text-sm font-medium text-black-700 mb-2">Store Address</label>
                        <textarea name="store_address" id="store_address" rows="3"
                                  class="form-textarea block w-full rounded-lg border-orange-300 shadow-sm focus:border-orange-500 focus:ring-orange-500 sm:text-sm @error('store_address') border-red-500 @enderror"
                                  placeholder="Full store address">{{ old('store_address', $lease->store_address) }}</textarea>
                        @error('store_address')
                        <p class="form-error mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label for="hvac" class="form-label block text-sm font-medium text-black-700 mb-2">HVAC Available</label>
                        <select name="hvac" id="hvac" class="form-select block w-full rounded-lg border-orange-300 shadow-sm focus:border-orange-500 focus:ring-orange-500 sm:text-sm @error('hvac') border-red-500 @enderror">
                            <option value="">Select HVAC Status</option>
                            <option value="1" {{ old('hvac', $lease->hvac) == '1' ? 'selected' : '' }}>Available</option>
                            <option value="0" {{ old('hvac', $lease->hvac) == '0' ? 'selected' : '' }}>Not Available</option>
                        </select>
                        @error('hvac')
                        <p class="form-error mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            </div>

            <!-- Financial Information -->
            <div class="bg-orange-50 shadow-sm ring-1 ring-orange-900/5 rounded-lg p-6">
                <h2 class="text-lg font-semibold text-black-900 mb-4 flex items-center">
                    <svg class="w-5 h-5 mr-2 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1" />
                    </svg>
                    Financial Information
                </h2>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <div>
                        <label for="aws" class="form-label block text-sm font-medium text-black-700 mb-2">AWS (Average Weekly Sales)</label>
                        <div class="relative rounded-lg shadow-sm">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <span class="text-black-500 sm:text-sm">$</span>
                            </div>
                            <input type="number" name="aws" id="aws"
                                   class="form-field block w-full pl-7 rounded-lg border-orange-300 shadow-sm focus:border-orange-500 focus:ring-orange-500 sm:text-sm @error('aws') border-red-500 @enderror"
                                   value="{{ old('aws', $lease->aws) }}"
                                   placeholder="0.00"
                                   step="0.01"
                                   min="0">
                        </div>
                        @error('aws')
                        <p class="form-error mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label for="base_rent" class="form-label block text-sm font-medium text-black-700 mb-2">Base Rent</label>
                        <div class="relative rounded-lg shadow-sm">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <span class="text-black-500 sm:text-sm">$</span>
                            </div>
                            <input type="number" name="base_rent" id="base_rent"
                                   class="form-field block w-full pl-7 rounded-lg border-orange-300 shadow-sm focus:border-orange-500 focus:ring-orange-500 sm:text-sm @error('base_rent') border-red-500 @enderror"
                                   value="{{ old('base_rent', $lease->base_rent) }}"
                                   placeholder="0.00"
                                   step="0.01"
                                   min="0">
                        </div>
                        @error('base_rent')
                        <p class="form-error mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label for="percent_increase_per_year" class="form-label block text-sm font-medium text-black-700 mb-2">% Increase Per Year</label>
                        <div class="relative rounded-lg shadow-sm">
                            <input type="number" name="percent_increase_per_year" id="percent_increase_per_year"
                                   class="form-field block w-full pr-7 rounded-lg border-orange-300 shadow-sm focus:border-orange-500 focus:ring-orange-500 sm:text-sm @error('percent_increase_per_year') border-red-500 @enderror"
                                   value="{{ old('percent_increase_per_year', $lease->percent_increase_per_year) }}"
                                   placeholder="0.00"
                                   step="0.01"
                                   min="0"
                                   max="100">
                            <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                                <span class="text-black-500 sm:text-sm">%</span>
                            </div>
                        </div>
                        @error('percent_increase_per_year')
                        <p class="form-error mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label for="cam" class="form-label block text-sm font-medium text-black-700 mb-2">CAM</label>
                        <div class="relative rounded-lg shadow-sm">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <span class="text-black-500 sm:text-sm">$</span>
                            </div>
                            <input type="number" name="cam" id="cam"
                                   class="form-field block w-full pl-7 rounded-lg border-orange-300 shadow-sm focus:border-orange-500 focus:ring-orange-500 sm:text-sm @error('cam') border-red-500 @enderror"
                                   value="{{ old('cam', $lease->cam) }}"
                                   placeholder="0.00"
                                   step="0.01"
                                   min="0">
                        </div>
                        @error('cam')
                        <p class="form-error mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label for="insurance" class="form-label block text-sm font-medium text-black-700 mb-2">Insurance</label>
                        <div class="relative rounded-lg shadow-sm">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <span class="text-black-500 sm:text-sm">$</span>
                            </div>
                            <input type="number" name="insurance" id="insurance"
                                   class="form-field block w-full pl-7 rounded-lg border-orange-300 shadow-sm focus:border-orange-500 focus:ring-orange-500 sm:text-sm @error('insurance') border-red-500 @enderror"
                                   value="{{ old('insurance', $lease->insurance) }}"
                                   placeholder="0.00"
                                   step="0.01"
                                   min="0">
                        </div>
                        @error('insurance')
                        <p class="form-error mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label for="re_taxes" class="form-label block text-sm font-medium text-black-700 mb-2">RE Taxes</label>
                        <div class="relative rounded-lg shadow-sm">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <span class="text-black-500 sm:text-sm">$</span>
                            </div>
                            <input type="number" name="re_taxes" id="re_taxes"
                                   class="form-field block w-full pl-7 rounded-lg border-orange-300 shadow-sm focus:border-orange-500 focus:ring-orange-500 sm:text-sm @error('re_taxes') border-red-500 @enderror"
                                   value="{{ old('re_taxes', $lease->re_taxes) }}"
                                   placeholder="0.00"
                                   step="0.01"
                                   min="0">
                        </div>
                        @error('re_taxes')
                        <p class="form-error mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label for="others" class="form-label block text-sm font-medium text-black-700 mb-2">Others</label>
                        <div class="relative rounded-lg shadow-sm">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <span class="text-black-500 sm:text-sm">$</span>
                            </div>
                            <input type="number" name="others" id="others"
                                   class="form-field block w-full pl-7 rounded-lg border-orange-300 shadow-sm focus:border-orange-500 focus:ring-orange-500 sm:text-sm @error('others') border-red-500 @enderror"
                                   value="{{ old('others', $lease->others) }}"
                                   placeholder="0.00"
                                   step="0.01"
                                   min="0">
                        </div>
                        @error('others')
                        <p class="form-error mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label for="security_deposit" class="form-label block text-sm font-medium text-black-700 mb-2">Security Deposit</label>
                        <div class="relative rounded-lg shadow-sm">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <span class="text-black-500 sm:text-sm">$</span>
                            </div>
                            <input type="number" name="security_deposit" id="security_deposit"
                                   class="form-field block w-full pl-7 rounded-lg border-orange-300 shadow-sm focus:border-orange-500 focus:ring-orange-500 sm:text-sm @error('security_deposit') border-red-500 @enderror"
                                   value="{{ old('security_deposit', $lease->security_deposit) }}"
                                   placeholder="0.00"
                                   step="0.01"
                                   min="0">
                        </div>
                        @error('security_deposit')
                        <p class="form-error mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            </div>

            <!-- Lease Terms -->
            <div class="bg-orange-50 shadow-sm ring-1 ring-orange-900/5 rounded-lg p-6">
                <h2 class="text-lg font-semibold text-black-900 mb-4 flex items-center">
                    <svg class="w-5 h-5 mr-2 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                    </svg>
                    Lease Terms
                </h2>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label for="initial_lease_expiration_date" class="form-label block text-sm font-medium text-black-700 mb-2">Initial Lease Expiration Date</label>
                        <input type="date" name="initial_lease_expiration_date" id="initial_lease_expiration_date"
                               class="form-field block w-full rounded-lg border-orange-300 shadow-sm focus:border-orange-500 focus:ring-orange-500 sm:text-sm @error('initial_lease_expiration_date') border-red-500 @enderror"
                               value="{{ old('initial_lease_expiration_date', $lease->initial_lease_expiration_date?->format('Y-m-d')) }}">
                        @error('initial_lease_expiration_date')
                        <p class="form-error mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label for="renewal_options" class="form-label block text-sm font-medium text-black-700 mb-2">Renewal Options</label>
                        <input type="text" name="renewal_options" id="renewal_options"
                               class="form-field block w-full rounded-lg border-orange-300 shadow-sm focus:border-orange-500 focus:ring-orange-500 sm:text-sm @error('renewal_options') border-red-500 @enderror"
                               value="{{ old('renewal_options', $lease->renewal_options) }}"
                               placeholder="e.g., 3,5 (3 terms, 5 years each)">
                        <p class="mt-1 text-xs text-black-500">Format: number of terms, years per term (e.g., 3,5)</p>
                        @error('renewal_options')
                        <p class="form-error mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label for="franchise_agreement_expiration_date" class="form-label block text-sm font-medium text-black-700 mb-2">Franchise Agreement Expiration</label>
                        <input type="date" name="franchise_agreement_expiration_date" id="franchise_agreement_expiration_date"
                               class="form-field block w-full rounded-lg border-orange-300 shadow-sm focus:border-orange-500 focus:ring-orange-500 sm:text-sm @error('franchise_agreement_expiration_date') border-red-500 @enderror"
                               value="{{ old('franchise_agreement_expiration_date', $lease->franchise_agreement_expiration_date?->format('Y-m-d')) }}">
                        @error('franchise_agreement_expiration_date')
                        <p class="form-error mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                    <!-- Current Term Override Field -->
                    <div>
                        <label for="current_term" class="form-label block text-sm font-medium text-black-700 mb-2">
                            <svg class="w-4 h-4 inline mr-1 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            Current Term Override
                        </label>
                        <select name="current_term" id="current_term" class="form-select block w-full rounded-lg border-orange-300 shadow-sm focus:border-orange-500 focus:ring-orange-500 sm:text-sm @error('current_term') border-red-500 @enderror">
                            <option value="">Auto-calculate from dates</option>
                            <option value="1" {{ old('current_term', $lease->current_term) == '1' ? 'selected' : '' }}>
                                1 - Initial Term
                            </option>
                            <option value="2" {{ old('current_term', $lease->current_term) == '2' ? 'selected' : '' }}>
                                2 - First Renewal Term
                            </option>
                            <option value="3" {{ old('current_term', $lease->current_term) == '3' ? 'selected' : '' }}>
                                3 - Second Renewal Term
                            </option>
                            <option value="4" {{ old('current_term', $lease->current_term) == '4' ? 'selected' : '' }}>
                                4 - Third Renewal Term
                            </option>
                            <option value="5" {{ old('current_term', $lease->current_term) == '5' ? 'selected' : '' }}>
                                5 - Fourth Renewal Term
                            </option>
                        </select>
                        <p class="mt-1 text-xs text-black-500">
                            <span class="inline-flex items-center">
                                <svg class="w-3 h-3 mr-1 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                                Leave empty to auto-calculate. Set manually if lease terms have been updated in real life.
                            </span>
                        </p>
                        @error('current_term')
                        <p class="form-error mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            </div>

            <!-- NEW: Renewal Management Section -->
            <div class="bg-blue-50 shadow-sm ring-1 ring-blue-900/5 rounded-lg p-6">
                <h2 class="text-lg font-semibold text-black-900 mb-4 flex items-center">
                    <svg class="w-5 h-5 mr-2 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    Renewal Management
                    <span class="ml-2 text-xs bg-blue-100 text-blue-800 px-2 py-1 rounded-full">UPDATED</span>
                </h2>
                <p class="text-sm text-gray-600 mb-6">
                    Update renewal information. Changes to the renewal date will create/update automatic reminders.
                    @if($lease->renewalCreatedBy)
                        <br><span class="text-blue-600">Originally set by: {{ $lease->renewalCreatedBy->name }}</span>
                    @endif
                </p>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label for="renewal_date" class="form-label block text-sm font-medium text-black-700 mb-2">
                            <svg class="w-4 h-4 inline mr-1 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                            </svg>
                            Renewal Date
                        </label>
                        <input type="date" name="renewal_date" id="renewal_date"
                               class="form-field block w-full rounded-lg border-blue-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm @error('renewal_date') border-red-500 @enderror"
                               value="{{ old('renewal_date', $lease->renewal_date?->format('Y-m-d')) }}">
                        <p class="mt-1 text-xs text-blue-600">
                            <svg class="w-3 h-3 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            Automatic reminders: 90, 60, 30, 14, 7, and 1 days before
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
                            Renewal Status
                        </label>
                        <select name="renewal_status" id="renewal_status" class="form-select block w-full rounded-lg border-blue-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm @error('renewal_status') border-red-500 @enderror">
                            <option value="">No Status Set</option>
                            <option value="pending" {{ old('renewal_status', $lease->renewal_status) == 'pending' ? 'selected' : '' }}>
                                📋 Pending - Not Started
                            </option>
                            <option value="in_progress" {{ old('renewal_status', $lease->renewal_status) == 'in_progress' ? 'selected' : '' }}>
                                ⏳ In Progress - Under Review
                            </option>
                            <option value="completed" {{ old('renewal_status', $lease->renewal_status) == 'completed' ? 'selected' : '' }}>
                                ✅ Completed - Renewal Done
                            </option>
                            <option value="declined" {{ old('renewal_status', $lease->renewal_status) == 'declined' ? 'selected' : '' }}>
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
                              placeholder="Add notes about the renewal process, terms to negotiate, deadlines, etc...">{{ old('renewal_notes', $lease->renewal_notes) }}</textarea>
                    @error('renewal_notes')
                    <p class="form-error mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Renewal Status Information -->
                @if($lease->renewal_date)
                    <div class="mt-6 p-4 bg-blue-100 rounded-lg border border-blue-200">
                        <h4 class="text-sm font-semibold text-blue-900 mb-2">
                            <svg class="w-4 h-4 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            Current Renewal Information
                        </h4>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-xs text-blue-800">
                            <div>
                                <strong>Current Date:</strong> {{ $lease->formatted_renewal_date }}
                            </div>
                            <div>
                                <strong>Days Until Renewal:</strong> {{ $lease->days_until_renewal ?? 'N/A' }}
                            </div>
                            @if($lease->renewal_reminder_sent_at)
                                <div class="md:col-span-2">
                                    <strong>Last Reminder Sent:</strong> {{ $lease->renewal_reminder_sent_at->format('M j, Y g:i A') }}
                                </div>
                            @endif
                        </div>
                    </div>
                @endif
            </div>

            <!-- Landlord Information -->
            <div class="bg-orange-50 shadow-sm ring-1 ring-orange-900/5 rounded-lg p-6">
                <h2 class="text-lg font-semibold text-black-900 mb-4 flex items-center">
                    <svg class="w-5 h-5 mr-2 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                    </svg>
                    Landlord Information
                </h2>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label for="landlord_name" class="form-label block text-sm font-medium text-black-700 mb-2">Landlord Name</label>
                        <input type="text" name="landlord_name" id="landlord_name"
                               class="form-field block w-full rounded-lg border-orange-300 shadow-sm focus:border-orange-500 focus:ring-orange-500 sm:text-sm @error('landlord_name') border-red-500 @enderror"
                               value="{{ old('landlord_name', $lease->landlord_name) }}"
                               placeholder="Landlord or company name">
                        @error('landlord_name')
                        <p class="form-error mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label for="landlord_email" class="form-label block text-sm font-medium text-black-700 mb-2">Landlord Email</label>
                        <input type="email" name="landlord_email" id="landlord_email"
                               class="form-field block w-full rounded-lg border-orange-300 shadow-sm focus:border-orange-500 focus:ring-orange-500 sm:text-sm @error('landlord_email') border-red-500 @enderror"
                               value="{{ old('landlord_email', $lease->landlord_email) }}"
                               placeholder="landlord@example.com">
                        @error('landlord_email')
                        <p class="form-error mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label for="landlord_phone" class="form-label block text-sm font-medium text-black-700 mb-2">Landlord Phone</label>
                        <input type="text" name="landlord_phone" id="landlord_phone"
                               class="form-field block w-full rounded-lg border-orange-300 shadow-sm focus:border-orange-500 focus:ring-orange-500 sm:text-sm @error('landlord_phone') border-red-500 @enderror"
                               value="{{ old('landlord_phone', $lease->landlord_phone) }}"
                               placeholder="(555) 123-4567">
                        @error('landlord_phone')
                        <p class="form-error mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label for="landlord_address" class="form-label block text-sm font-medium text-black-700 mb-2">Landlord Address</label>
                        <textarea name="landlord_address" id="landlord_address" rows="3"
                                  class="form-textarea block w-full rounded-lg border-orange-300 shadow-sm focus:border-orange-500 focus:ring-orange-500 sm:text-sm @error('landlord_address') border-red-500 @enderror"
                                  placeholder="Landlord mailing address">{{ old('landlord_address', $lease->landlord_address) }}</textarea>
                        @error('landlord_address')
                        <p class="form-error mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                    <div class="md:col-span-2">
                        <label for="landlord_responsibility" class="form-label block text-sm font-medium text-black-700 mb-2">Landlord Responsibilities</label>
                        <textarea name="landlord_responsibility" id="landlord_responsibility" rows="4"
                                  class="form-textarea block w-full rounded-lg border-orange-300 shadow-sm focus:border-orange-500 focus:ring-orange-500 sm:text-sm @error('landlord_responsibility') border-red-500 @enderror"
                                  placeholder="Describe what the landlord is responsible for...">{{ old('landlord_responsibility', $lease->landlord_responsibility) }}</textarea>
                        @error('landlord_responsibility')
                        <p class="form-error mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            </div>

            <!-- Comments -->
            <div class="bg-orange-50 shadow-sm ring-1 ring-orange-900/5 rounded-lg p-6">
                <h2 class="text-lg font-semibold text-black-900 mb-4 flex items-center">
                    <svg class="w-5 h-5 mr-2 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 8h10M7 12h4m1 8l-4-4H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-1.586z" />
                    </svg>
                    Comments
                </h2>
                <div>
                    <textarea name="comments" id="comments" rows="4"
                              class="form-textarea block w-full rounded-lg border-orange-300 shadow-sm focus:border-orange-500 focus:ring-orange-500 sm:text-sm @error('comments') border-red-500 @enderror"
                              placeholder="Any additional notes or comments about this lease...">{{ old('comments', $lease->comments) }}</textarea>
                    @error('comments')
                    <p class="form-error mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <!-- Submit Button -->
            <div class="flex justify-end space-x-4">
                <a href="{{ route('leases.index') }}"
                   class="form-cancel inline-flex items-center px-6 py-3 border border-orange-300 text-sm font-medium rounded-lg text-black-700 bg-orange-50 hover:bg-orange-100 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-orange-500 transition-all duration-200">
                    Cancel
                </a>
                <button type="submit"
                        class="form-button inline-flex items-center px-6 py-3 border border-transparent text-sm font-medium rounded-lg text-white bg-orange-600 hover:bg-orange-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-orange-500 transition-all duration-200">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                    </svg>
                    Update Lease
                </button>
            </div>
        </form>
    </div>

    <!-- Complete Renewal Modal -->
    <div id="completeRenewalModal" class="hidden fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
        <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
            <div class="mt-3">
                <h3 class="text-lg font-medium text-gray-900 mb-4">Complete Renewal</h3>
                <form id="completeRenewalForm">
                    <input type="hidden" id="complete_lease_id" />
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Completion Notes</label>
                        <textarea id="completion_notes" rows="3"
                                  class="block w-full rounded-md border-gray-300 shadow-sm focus:border-green-500 focus:ring-green-500"
                                  placeholder="Add notes about the completed renewal..."></textarea>
                    </div>
                    <div class="flex justify-end space-x-3">
                        <button type="button" onclick="closeCompleteModal()"
                                class="px-4 py-2 border border-gray-300 rounded-md text-gray-700 hover:bg-gray-50">
                            Cancel
                        </button>
                        <button type="submit"
                                class="px-4 py-2 bg-green-600 text-white rounded-md hover:bg-green-700">
                            Mark as Completed
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Enhanced JavaScript with Renewal Functions -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const storeOptions = document.querySelectorAll('input[name="store_option"]');
            const existingStoreSection = document.getElementById('existing-store-section');
            const newStoreSection = document.getElementById('new-store-section');
            const storeSelect = document.getElementById('store_id');
            const newStoreNumber = document.getElementById('new_store_number');

            // NEW: Renewal functionality
            const renewalDate = document.getElementById('renewal_date');
            const renewalStatus = document.getElementById('renewal_status');

            function toggleStoreOptions() {
                const selectedOption = document.querySelector('input[name="store_option"]:checked').value;

                if (selectedOption === 'existing') {
                    existingStoreSection.classList.remove('hidden');
                    newStoreSection.classList.add('hidden');
                    storeSelect.required = true;
                    newStoreNumber.required = false;
                } else {
                    existingStoreSection.classList.add('hidden');
                    newStoreSection.classList.remove('hidden');
                    storeSelect.required = false;
                    newStoreNumber.required = true;
                }
            }

            // NEW: Auto-set status when renewal date is changed
            function handleRenewalDateChange() {
                if (renewalDate.value && !renewalStatus.value) {
                    renewalStatus.value = 'pending';
                }
            }

            storeOptions.forEach(option => {
                option.addEventListener('change', toggleStoreOptions);
            });

            // NEW: Renewal date event listener
            renewalDate.addEventListener('change', handleRenewalDateChange);

            // Initialize on page load
            toggleStoreOptions();

            // NEW: Validate renewal date
            renewalDate.addEventListener('change', function() {
                const selectedDate = new Date(this.value);
                const today = new Date();
                today.setHours(0, 0, 0, 0);

                if (selectedDate <= today) {
                    console.log('⚠️ Warning: Renewal date should typically be in the future');
                }
            });
        });

        // NEW: Complete Renewal Functions
        function completeRenewal(leaseId) {
            document.getElementById('complete_lease_id').value = leaseId;
            document.getElementById('completeRenewalModal').classList.remove('hidden');
        }

        function closeCompleteModal() {
            document.getElementById('completeRenewalModal').classList.add('hidden');
            document.getElementById('completeRenewalForm').reset();
        }

        // Handle Complete Renewal Form
        document.getElementById('completeRenewalForm').addEventListener('submit', async function(e) {
            e.preventDefault();

            const leaseId = document.getElementById('complete_lease_id').value;
            const completionNotes = document.getElementById('completion_notes').value;

            try {
                const response = await fetch(`/leases/${leaseId}/complete-renewal`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: JSON.stringify({
                        completion_notes: completionNotes
                    })
                });

                if (response.ok) {
                    showNotification('✅ Renewal Completed', 'Lease renewal has been marked as completed', 'success');
                    closeCompleteModal();
                    setTimeout(() => location.reload(), 2000);
                } else {
                    showNotification('❌ Error', 'Failed to complete renewal', 'error');
                }
            } catch (error) {
                showNotification('❌ Network Error', 'Failed to complete renewal', 'error');
            }
        });

        // Notification Function
        function showNotification(title, message, type = 'info') {
            // Remove existing notifications
            const existing = document.querySelectorAll('.lease-notification');
            existing.forEach(n => n.remove());

            const colors = {
                success: 'bg-green-100 border-green-500 text-green-800',
                error: 'bg-red-100 border-red-500 text-red-800',
                warning: 'bg-yellow-100 border-yellow-500 text-yellow-800',
                info: 'bg-blue-100 border-blue-500 text-blue-800'
            };

            const notification = document.createElement('div');
            notification.className = `lease-notification fixed top-4 right-4 max-w-sm ${colors[type]} border-l-4 p-4 rounded-lg shadow-lg z-50`;
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
