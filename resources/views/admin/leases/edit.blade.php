{{-- resources/views/leases/edit.blade.php --}}
@extends('layouts.app')

@section('title', 'Edit Lease')

@section('content')
<div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <div class="flex justify-between items-center mb-6">
        <div>
            <h1 class="text-3xl font-bold text-gray-900">Edit Lease - {{ $lease->store_number }}</h1>
            <p class="mt-2 text-sm text-gray-700">Update lease information</p>
        </div>
        <div class="flex space-x-3">
            <a href="{{ route('leases.show', $lease) }}" 
               class="inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-lg text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500 transition-all duration-200">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                </svg>
                View
            </a>
            <a href="{{ route('leases.index') }}" 
               class="inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-lg text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500 transition-all duration-200">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                </svg>
                Back to List
            </a>
        </div>
    </div>

    <form action="{{ route('leases.update', $lease) }}" method="POST" class="space-y-8">
        @csrf
        @method('PATCH')
        
        <!-- Store Information -->
        <div class="bg-white shadow-sm ring-1 ring-gray-900/5 rounded-lg p-6">
            <h2 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
                <svg class="w-5 h-5 mr-2 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-2m-14 0h2m0 0V9a2 2 0 012-2h2a2 2 0 012 2v10" />
                </svg>
                Store Information
            </h2>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label for="store_number" class="block text-sm font-medium text-gray-700 mb-2">Store Number</label>
                    <input type="text" name="store_number" id="store_number" 
                           class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm @error('store_number') border-red-500 @enderror" 
                           value="{{ old('store_number', $lease->store_number) }}"
                           placeholder="e.g., 001, Store-A">
                    @error('store_number')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
                <div>
                    <label for="name" class="block text-sm font-medium text-gray-700 mb-2">Store Name</label>
                    <input type="text" name="name" id="name" 
                           class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm @error('name') border-red-500 @enderror" 
                           value="{{ old('name', $lease->name) }}"
                           placeholder="Store name or location">
                    @error('name')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
                <div class="md:col-span-2">
                    <label for="store_address" class="block text-sm font-medium text-gray-700 mb-2">Store Address</label>
                    <textarea name="store_address" id="store_address" rows="3" 
                              class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm @error('store_address') border-red-500 @enderror"
                              placeholder="Full store address">{{ old('store_address', $lease->store_address) }}</textarea>
                    @error('store_address')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
                <div>
                    <label for="sqf" class="block text-sm font-medium text-gray-700 mb-2">SQF</label>
                    <input type="number" name="sqf" id="sqf" 
                           class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm @error('sqf') border-red-500 @enderror" 
                           value="{{ old('sqf', $lease->sqf) }}"
                           placeholder="0"
                           min="0">
                    @error('sqf')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
                <div>
                    <label for="hvac" class="block text-sm font-medium text-gray-700 mb-2">HVAC Available</label>
                    <select name="hvac" id="hvac" class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm @error('hvac') border-red-500 @enderror">
                        <option value="">Select HVAC Status</option>
                        <option value="1" {{ old('hvac', $lease->hvac) == '1' ? 'selected' : '' }}>Available</option>
                        <option value="0" {{ old('hvac', $lease->hvac) == '0' ? 'selected' : '' }}>Not Available</option>
                    </select>
                    @error('hvac')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
            </div>
        </div>

        <!-- Financial Information -->
        <div class="bg-white shadow-sm ring-1 ring-gray-900/5 rounded-lg p-6">
            <h2 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
                <svg class="w-5 h-5 mr-2 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1" />
                </svg>
                Financial Information
            </h2>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <div>
                    <label for="aws" class="block text-sm font-medium text-gray-700 mb-2">AWS (Average Weekly Sales)</label>
                    <div class="relative rounded-lg shadow-sm">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <span class="text-gray-500 sm:text-sm">$</span>
                        </div>
                        <input type="number" name="aws" id="aws" 
                               class="block w-full pl-7 rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm @error('aws') border-red-500 @enderror" 
                               value="{{ old('aws', $lease->aws) }}"
                               placeholder="0.00"
                               step="0.01"
                               min="0">
                    </div>
                    @error('aws')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
                <div>
                    <label for="base_rent" class="block text-sm font-medium text-gray-700 mb-2">Base Rent</label>
                    <div class="relative rounded-lg shadow-sm">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <span class="text-gray-500 sm:text-sm">$</span>
                        </div>
                        <input type="number" name="base_rent" id="base_rent" 
                               class="block w-full pl-7 rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm @error('base_rent') border-red-500 @enderror" 
                               value="{{ old('base_rent', $lease->base_rent) }}"
                               placeholder="0.00"
                               step="0.01"
                               min="0">
                    </div>
                    @error('base_rent')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
                <div>
                    <label for="percent_increase_per_year" class="block text-sm font-medium text-gray-700 mb-2">% Increase Per Year</label>
                    <div class="relative rounded-lg shadow-sm">
                        <input type="number" name="percent_increase_per_year" id="percent_increase_per_year" 
                               class="block w-full pr-7 rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm @error('percent_increase_per_year') border-red-500 @enderror" 
                               value="{{ old('percent_increase_per_year', $lease->percent_increase_per_year) }}"
                               placeholder="0.00"
                               step="0.01"
                               min="0"
                               max="100">
                        <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                            <span class="text-gray-500 sm:text-sm">%</span>
                        </div>
                    </div>
                    @error('percent_increase_per_year')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
                <div>
                    <label for="cam" class="block text-sm font-medium text-gray-700 mb-2">CAM</label>
                    <div class="relative rounded-lg shadow-sm">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <span class="text-gray-500 sm:text-sm">$</span>
                        </div>
                        <input type="number" name="cam" id="cam" 
                               class="block w-full pl-7 rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm @error('cam') border-red-500 @enderror" 
                               value="{{ old('cam', $lease->cam) }}"
                               placeholder="0.00"
                               step="0.01"
                               min="0">
                    </div>
                    @error('cam')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
                <div>
                    <label for="insurance" class="block text-sm font-medium text-gray-700 mb-2">Insurance</label>
                    <div class="relative rounded-lg shadow-sm">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <span class="text-gray-500 sm:text-sm">$</span>
                        </div>
                        <input type="number" name="insurance" id="insurance" 
                               class="block w-full pl-7 rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm @error('insurance') border-red-500 @enderror" 
                               value="{{ old('insurance', $lease->insurance) }}"
                               placeholder="0.00"
                               step="0.01"
                               min="0">
                    </div>
                    @error('insurance')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
                <div>
                    <label for="re_taxes" class="block text-sm font-medium text-gray-700 mb-2">RE Taxes</label>
                    <div class="relative rounded-lg shadow-sm">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <span class="text-gray-500 sm:text-sm">$</span>
                        </div>
                        <input type="number" name="re_taxes" id="re_taxes" 
                               class="block w-full pl-7 rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm @error('re_taxes') border-red-500 @enderror" 
                               value="{{ old('re_taxes', $lease->re_taxes) }}"
                               placeholder="0.00"
                               step="0.01"
                               min="0">
                    </div>
                    @error('re_taxes')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
                <div>
                    <label for="others" class="block text-sm font-medium text-gray-700 mb-2">Others</label>
                    <div class="relative rounded-lg shadow-sm">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <span class="text-gray-500 sm:text-sm">$</span>
                        </div>
                        <input type="number" name="others" id="others" 
                               class="block w-full pl-7 rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm @error('others') border-red-500 @enderror" 
                               value="{{ old('others', $lease->others) }}"
                               placeholder="0.00"
                               step="0.01"
                               min="0">
                    </div>
                    @error('others')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
                <div>
                    <label for="security_deposit" class="block text-sm font-medium text-gray-700 mb-2">Security Deposit</label>
                    <div class="relative rounded-lg shadow-sm">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <span class="text-gray-500 sm:text-sm">$</span>
                        </div>
                        <input type="number" name="security_deposit" id="security_deposit" 
                               class="block w-full pl-7 rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm @error('security_deposit') border-red-500 @enderror" 
                               value="{{ old('security_deposit', $lease->security_deposit) }}"
                               placeholder="0.00"
                               step="0.01"
                               min="0">
                    </div>
                    @error('security_deposit')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
            </div>
        </div>

        <!-- Lease Terms -->
        <div class="bg-white shadow-sm ring-1 ring-gray-900/5 rounded-lg p-6">
            <h2 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
                <svg class="w-5 h-5 mr-2 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                </svg>
                Lease Terms
            </h2>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <div>
                    <label for="initial_lease_expiration_date" class="block text-sm font-medium text-gray-700 mb-2">Initial Lease Expiration Date</label>
                    <input type="date" name="initial_lease_expiration_date" id="initial_lease_expiration_date" 
                           class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm @error('initial_lease_expiration_date') border-red-500 @enderror" 
                           value="{{ old('initial_lease_expiration_date', $lease->initial_lease_expiration_date?->format('Y-m-d')) }}">
                    @error('initial_lease_expiration_date')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
                <div>
                    <label for="renewal_options" class="block text-sm font-medium text-gray-700 mb-2">Renewal Options</label>
                    <input type="text" name="renewal_options" id="renewal_options" 
                           class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm @error('renewal_options') border-red-500 @enderror" 
                           value="{{ old('renewal_options', $lease->renewal_options) }}"
                           placeholder="e.g., 3,5 (3 terms, 5 years each)">
                    <p class="mt-1 text-xs text-gray-500">Format: number of terms, years per term (e.g., 3,5)</p>
                    @error('renewal_options')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
                <div>
                    <label for="franchise_agreement_expiration_date" class="block text-sm font-medium text-gray-700 mb-2">Franchise Agreement Expiration</label>
                    <input type="date" name="franchise_agreement_expiration_date" id="franchise_agreement_expiration_date" 
                           class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm @error('franchise_agreement_expiration_date') border-red-500 @enderror" 
                           value="{{ old('franchise_agreement_expiration_date', $lease->franchise_agreement_expiration_date?->format('Y-m-d')) }}">
                    @error('franchise_agreement_expiration_date')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
            </div>
        </div>

        <!-- Landlord Information -->
        <div class="bg-white shadow-sm ring-1 ring-gray-900/5 rounded-lg p-6">
            <h2 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
                <svg class="w-5 h-5 mr-2 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                </svg>
                Landlord Information
            </h2>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label for="landlord_name" class="block text-sm font-medium text-gray-700 mb-2">Landlord Name</label>
                    <input type="text" name="landlord_name" id="landlord_name" 
                           class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm @error('landlord_name') border-red-500 @enderror" 
                           value="{{ old('landlord_name', $lease->landlord_name) }}"
                           placeholder="Landlord or company name">
                    @error('landlord_name')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
                <div>
                    <label for="landlord_email" class="block text-sm font-medium text-gray-700 mb-2">Landlord Email</label>
                    <input type="email" name="landlord_email" id="landlord_email" 
                           class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm @error('landlord_email') border-red-500 @enderror" 
                           value="{{ old('landlord_email', $lease->landlord_email) }}"
                           placeholder="landlord@example.com">
                    @error('landlord_email')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
                <div>
                    <label for="landlord_phone" class="block text-sm font-medium text-gray-700 mb-2">Landlord Phone</label>
                    <input type="text" name="landlord_phone" id="landlord_phone" 
                           class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm @error('landlord_phone') border-red-500 @enderror" 
                           value="{{ old('landlord_phone', $lease->landlord_phone) }}"
                           placeholder="(555) 123-4567">
                    @error('landlord_phone')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
                <div>
                    <label for="landlord_address" class="block text-sm font-medium text-gray-700 mb-2">Landlord Address</label>
                    <textarea name="landlord_address" id="landlord_address" rows="3" 
                              class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm @error('landlord_address') border-red-500 @enderror"
                              placeholder="Landlord mailing address">{{ old('landlord_address', $lease->landlord_address) }}</textarea>
                    @error('landlord_address')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
                <div class="md:col-span-2">
                    <label for="landlord_responsibility" class="block text-sm font-medium text-gray-700 mb-2">Landlord Responsibilities</label>
                    <textarea name="landlord_responsibility" id="landlord_responsibility" rows="4" 
                              class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm @error('landlord_responsibility') border-red-500 @enderror"
                              placeholder="Describe what the landlord is responsible for...">{{ old('landlord_responsibility', $lease->landlord_responsibility) }}</textarea>
                    @error('landlord_responsibility')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
            </div>
        </div>

        <!-- Comments -->
        <div class="bg-white shadow-sm ring-1 ring-gray-900/5 rounded-lg p-6">
            <h2 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
                <svg class="w-5 h-5 mr-2 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 8h10M7 12h4m1 8l-4-4H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-1.586z" />
                </svg>
                Comments
            </h2>
            <div>
                <textarea name="comments" id="comments" rows="4" 
                          class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm @error('comments') border-red-500 @enderror"
                          placeholder="Any additional notes or comments about this lease...">{{ old('comments', $lease->comments) }}</textarea>
                @error('comments')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>
        </div>

        <!-- Submit Button -->
        <div class="flex justify-end space-x-4">
            <a href="{{ route('leases.show', $lease) }}" 
               class="inline-flex items-center px-6 py-3 border border-gray-300 text-sm font-medium rounded-lg text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500 transition-all duration-200">
                Cancel
            </a>
            <button type="submit" 
                    class="inline-flex items-center px-6 py-3 border border-transparent text-sm font-medium rounded-lg text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-all duration-200">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                </svg>
                Update Lease
            </button>
        </div>
    </form>
</div>
@endsection
