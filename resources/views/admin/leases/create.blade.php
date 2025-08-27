@extends('layouts.app')

@section('title', 'Create Lease')

@section('content')
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <div class="flex justify-between items-center mb-6">
            <div>
                <h1 class="text-3xl font-bold text-black-900">Create New Lease</h1>
                <p class="mt-2 text-sm text-black-700">Add a new lease to your portfolio</p>
            </div>
            <a href="{{ route('leases.index') }}"
               class="form-cancel inline-flex items-center px-4 py-2 border border-orange-300 text-sm font-medium rounded-lg text-black-700 bg-orange-50 hover:bg-orange-100 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-orange-500 transition-all duration-200">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                </svg>
                Back to List
            </a>
        </div>

        <form action="{{ route('leases.store') }}" method="POST" class="space-y-8">
            @csrf

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
                            <input type="radio" name="store_option" value="existing" class="form-radio text-black-600 focus:ring-orange-500" checked>
                            <span class="ml-2 text-sm text-black-700">Select Existing Store</span>
                        </label>
                        <label class="flex items-center">
                            <input type="radio" name="store_option" value="new" class="form-radio text-black-600 focus:ring-orange-500">
                            <span class="ml-2 text-sm text-black-700">Create New Store</span>
                        </label>
                    </div>
                </div>

                <!-- Existing Store Selection -->
                <div id="existing-store-section" class="mb-6">
                    <label for="store_id" class="form-label block text-sm font-medium text-black-700 mb-2">Select Store</label>
                    <select name="store_id" id="store_id" class="form-select block w-full rounded-lg border-orange-300 shadow-sm focus:border-orange-500 focus:ring-orange-500 sm:text-sm @error('store_id') border-red-500 @enderror">
                        <option value="">Select a store...</option>
                        @foreach($stores as $store)
                            <option value="{{ $store->id }}" {{ old('store_id') == $store->id ? 'selected' : '' }}>
                                {{ $store->store_number }} - {{ $store->name ?: 'No Name' }}
                            </option>
                        @endforeach
                    </select>
                    @error('store_id')
                    <p class="form-error mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- New Store Creation -->
                <div id="new-store-section" class="hidden">
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
                               value="{{ old('name') }}"
                               placeholder="Lease-specific name or description">
                        @error('name')
                        <p class="form-error mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label for="sqf" class="form-label block text-sm font-medium text-black-700 mb-2">SQF</label>
                        <input type="number" name="sqf" id="sqf"
                               class="form-field block w-full rounded-lg border-orange-300 shadow-sm focus:border-orange-500 focus:ring-orange-500 sm:text-sm @error('sqf') border-red-500 @enderror"
                               value="{{ old('sqf') }}"
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
                                  placeholder="Full store address">{{ old('store_address') }}</textarea>
                        @error('store_address')
                        <p class="form-error mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label for="hvac" class="form-label block text-sm font-medium text-black-700 mb-2">HVAC Available</label>
                        <select name="hvac" id="hvac" class="form-select block w-full rounded-lg border-orange-300 shadow-sm focus:border-orange-500 focus:ring-orange-500 sm:text-sm @error('hvac') border-red-500 @enderror">
                            <option value="">Select HVAC Status</option>
                            <option value="1" {{ old('hvac') == '1' ? 'selected' : '' }}>Available</option>
                            <option value="0" {{ old('hvac') == '0' ? 'selected' : '' }}>Not Available</option>
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
                                   value="{{ old('aws') }}"
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
                                   value="{{ old('base_rent') }}"
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
                                   value="{{ old('percent_increase_per_year') }}"
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
                                   value="{{ old('cam') }}"
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
                                   value="{{ old('insurance') }}"
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
                                   value="{{ old('re_taxes') }}"
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
                                   value="{{ old('others') }}"
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
                                   value="{{ old('security_deposit') }}"
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
                               value="{{ old('initial_lease_expiration_date') }}">
                        @error('initial_lease_expiration_date')
                        <p class="form-error mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label for="renewal_options" class="form-label block text-sm font-medium text-black-700 mb-2">Renewal Options</label>
                        <input type="text" name="renewal_options" id="renewal_options"
                               class="form-field block w-full rounded-lg border-orange-300 shadow-sm focus:border-orange-500 focus:ring-orange-500 sm:text-sm @error('renewal_options') border-red-500 @enderror"
                               value="{{ old('renewal_options') }}"
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
                               value="{{ old('franchise_agreement_expiration_date') }}">
                        @error('franchise_agreement_expiration_date')
                        <p class="form-error mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                    <!-- NEW: Current Term Override Field -->
                    <div>
                        <label for="current_term" class="form-label block text-sm font-medium text-black-700 mb-2">
                            <svg class="w-4 h-4 inline mr-1 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            Current Term Override
                        </label>
                        <select name="current_term" id="current_term" class="form-select block w-full rounded-lg border-orange-300 shadow-sm focus:border-orange-500 focus:ring-orange-500 sm:text-sm @error('current_term') border-red-500 @enderror">
                            <option value="">Auto-calculate from dates</option>
                            <option value="1" {{ old('current_term') == '1' ? 'selected' : '' }}>
                                1 - Initial Term
                            </option>
                            <option value="2" {{ old('current_term') == '2' ? 'selected' : '' }}>
                                2 - First Renewal Term
                            </option>
                            <option value="3" {{ old('current_term') == '3' ? 'selected' : '' }}>
                                3 - Second Renewal Term
                            </option>
                            <option value="4" {{ old('current_term') == '4' ? 'selected' : '' }}>
                                4 - Third Renewal Term
                            </option>
                            <option value="5" {{ old('current_term') == '5' ? 'selected' : '' }}>
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
                               value="{{ old('landlord_name') }}"
                               placeholder="Landlord or company name">
                        @error('landlord_name')
                        <p class="form-error mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label for="landlord_email" class="form-label block text-sm font-medium text-black-700 mb-2">Landlord Email</label>
                        <input type="email" name="landlord_email" id="landlord_email"
                               class="form-field block w-full rounded-lg border-orange-300 shadow-sm focus:border-orange-500 focus:ring-orange-500 sm:text-sm @error('landlord_email') border-red-500 @enderror"
                               value="{{ old('landlord_email') }}"
                               placeholder="landlord@example.com">
                        @error('landlord_email')
                        <p class="form-error mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label for="landlord_phone" class="form-label block text-sm font-medium text-black-700 mb-2">Landlord Phone</label>
                        <input type="text" name="landlord_phone" id="landlord_phone"
                               class="form-field block w-full rounded-lg border-orange-300 shadow-sm focus:border-orange-500 focus:ring-orange-500 sm:text-sm @error('landlord_phone') border-red-500 @enderror"
                               value="{{ old('landlord_phone') }}"
                               placeholder="(555) 123-4567">
                        @error('landlord_phone')
                        <p class="form-error mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label for="landlord_address" class="form-label block text-sm font-medium text-black-700 mb-2">Landlord Address</label>
                        <textarea name="landlord_address" id="landlord_address" rows="3"
                                  class="form-textarea block w-full rounded-lg border-orange-300 shadow-sm focus:border-orange-500 focus:ring-orange-500 sm:text-sm @error('landlord_address') border-red-500 @enderror"
                                  placeholder="Landlord mailing address">{{ old('landlord_address') }}</textarea>
                        @error('landlord_address')
                        <p class="form-error mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                    <div class="md:col-span-2">
                        <label for="landlord_responsibility" class="form-label block text-sm font-medium text-black-700 mb-2">Landlord Responsibilities</label>
                        <textarea name="landlord_responsibility" id="landlord_responsibility" rows="4"
                                  class="form-textarea block w-full rounded-lg border-orange-300 shadow-sm focus:border-orange-500 focus:ring-orange-500 sm:text-sm @error('landlord_responsibility') border-red-500 @enderror"
                                  placeholder="Describe what the landlord is responsible for...">{{ old('landlord_responsibility') }}</textarea>
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
                              placeholder="Any additional notes or comments about this lease...">{{ old('comments') }}</textarea>
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
                    Create Lease
                </button>
            </div>
        </form>
    </div>

    <!-- JavaScript for store selection toggle -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const storeOptions = document.querySelectorAll('input[name="store_option"]');
            const existingStoreSection = document.getElementById('existing-store-section');
            const newStoreSection = document.getElementById('new-store-section');
            const storeSelect = document.getElementById('store_id');
            const newStoreNumber = document.getElementById('new_store_number');

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

            storeOptions.forEach(option => {
                option.addEventListener('change', toggleStoreOptions);
            });

            // Initialize on page load
            toggleStoreOptions();
        });
    </script>
@endsection
