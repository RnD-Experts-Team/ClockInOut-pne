@extends('layouts.app')

@section('content')
    <div class="container mx-auto px-4 py-8">
        <div class="max-w-4xl mx-auto">
            <div class="bg-orange-50 shadow-sm ring-1 ring-orange-900/5 rounded-lg overflow-hidden">
                <div class="bg-orange-600 px-6 py-4">
                    <h1 class="text-2xl font-bold text-white">Edit Apartment Lease</h1>
                </div>

                <form action="{{ route('admin.apartment-leases.update', $apartmentLease) }}" method="POST" class="p-6">
                    @csrf
                    @method('PUT')

                    <!-- Store Selection Section -->
                    <div class="bg-orange-50 rounded-lg p-4 mb-6">
                        <h3 class="text-lg font-medium text-black-900 mb-4">Store Information</h3>

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
                                    <label for="new_store_number" class="form-label block text-sm font-medium text-black-700 mb-2">New Store Number <span class="text-black-100">*</span></label>
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

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- Apartment Address -->
                        <div>
                            <label for="apartment_address" class="form-label block text-sm font-medium text-black-700 mb-2">Apartment Address <span class="text-black-100">*</span></label>
                            <textarea name="apartment_address" id="apartment_address" rows="3" required
                                      class="form-textarea block w-full rounded-lg border-orange-200 shadow-sm focus:border-orange-500 focus:ring-orange-500 sm:text-sm @error('apartment_address') border-orange-300 @enderror"
                                      placeholder="Enter full apartment address">{{ old('apartment_address', $apartmentLease->apartment_address) }}</textarea>
                            @error('apartment_address')
                            <p class="form-error mt-1 text-sm text-black-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Lease Holder -->
                        <div>
                            <label for="lease_holder" class="form-label block text-sm font-medium text-black-700 mb-2">Lease Holder <span class="text-black-100">*</span></label>
                            <textarea name="lease_holder" id="lease_holder" rows="3" required
                                      class="form-textarea block w-full rounded-lg border-orange-200 shadow-sm focus:border-orange-500 focus:ring-orange-500 sm:text-sm @error('lease_holder') border-orange-300 @enderror"
                                      placeholder="Enter lease holder name">{{ old('lease_holder', $apartmentLease->lease_holder) }}</textarea>
                            @error('lease_holder')
                            <p class="form-error mt-1 text-sm text-black-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Rent -->
                        <div>
                            <label for="rent" class="form-label block text-sm font-medium text-black-700 mb-2">Rent <span class="text-black-100">*</span></label>
                            <div class="relative rounded-lg shadow-sm">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <span class="text-black-500 sm:text-sm">$</span>
                                </div>
                                <input type="number" name="rent" id="rent" step="0.01" min="0" required
                                       class="form-field block w-full pl-7 rounded-lg border-orange-200 shadow-sm focus:border-orange-500 focus:ring-orange-500 sm:text-sm @error('rent') border-orange-300 @enderror"
                                       value="{{ old('rent', $apartmentLease->rent) }}"
                                       placeholder="1200.00">
                            </div>
                            @error('rent')
                            <p class="form-error mt-1 text-sm text-black-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Utilities -->
                        <div>
                            <label for="utilities" class="form-label block text-sm font-medium text-black-700 mb-2">Utilities</label>
                            <div class="relative rounded-lg shadow-sm">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <span class="text-black-500 sm:text-sm">$</span>
                                </div>
                                <input type="number" name="utilities" id="utilities" step="0.01" min="0"
                                       class="form-field block w-full pl-7 rounded-lg border-orange-200 shadow-sm focus:border-orange-500 focus:ring-orange-500 sm:text-sm @error('utilities') border-orange-300 @enderror"
                                       value="{{ old('utilities', $apartmentLease->utilities) }}"
                                       placeholder="150.00">
                            </div>
                            @error('utilities')
                            <p class="form-error mt-1 text-sm text-black-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Number of AT -->
                        <div>
                            <label for="number_of_AT" class="form-label block text-sm font-medium text-black-700 mb-2">Number of AT <span class="text-black-100">*</span></label>
                            <input type="number" name="number_of_AT" id="number_of_AT" min="1" required
                                   class="form-field block w-full rounded-lg border-orange-200 shadow-sm focus:border-orange-500 focus:ring-orange-500 sm:text-sm @error('number_of_AT') border-orange-300 @enderror"
                                   value="{{ old('number_of_AT', $apartmentLease->number_of_AT ?? 1) }}"
                                   placeholder="1">
                            <p class="mt-1 text-xs text-black-500">Number of Adult Tenants</p>
                            @error('number_of_AT')
                            <p class="form-error mt-1 text-sm text-black-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Number of Cars -->
                        <div>
                            <label for="has_car" class="form-label block text-sm font-medium text-black-700 mb-2">Number of Cars <span class="text-black-100">*</span></label>
                            <input type="number" name="has_car" id="has_car" min="0" max="10" required
                                   class="form-field block w-full rounded-lg border-orange-200 shadow-sm focus:border-orange-500 focus:ring-orange-500 sm:text-sm @error('has_car') border-orange-300 @enderror"
                                   value="{{ old('has_car', $apartmentLease->has_car ?? 0) }}"
                                   placeholder="0">
                            <p class="mt-1 text-xs text-black-500">Enter the number of cars (0 if no cars)</p>
                            @error('has_car')
                            <p class="form-error mt-1 text-sm text-black-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Is Family -->
                        <div>
                            <label for="is_family" class="form-label block text-sm font-medium text-black-700 mb-2">Is Family</label>
                            <select name="is_family" id="is_family"
                                    class="form-select block w-full rounded-lg border-orange-200 shadow-sm focus:border-orange-500 focus:ring-orange-500 sm:text-sm @error('is_family') border-orange-300 @enderror">
                                <option value="">Select...</option>
                                <option value="Yes" {{ old('is_family', $apartmentLease->is_family) == 'Yes' ? 'selected' : '' }}>Yes</option>
                                <option value="No" {{ old('is_family', $apartmentLease->is_family) == 'No' ? 'selected' : '' }}>No</option>
                            </select>
                            @error('is_family')
                            <p class="form-error mt-1 text-sm text-black-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Expiration Date -->
                        <div>
                            <label for="expiration_date" class="form-label block text-sm font-medium text-black-700 mb-2">Expiration Date</label>
                            <input type="date" name="expiration_date" id="expiration_date"
                                   class="form-field block w-full rounded-lg border-orange-200 shadow-sm focus:border-orange-500 focus:ring-orange-500 sm:text-sm @error('expiration_date') border-orange-300 @enderror"
                                   value="{{ old('expiration_date', $apartmentLease->expiration_date ? $apartmentLease->expiration_date->format('Y-m-d') : '') }}">
                            @error('expiration_date')
                            <p class="form-error mt-1 text-sm text-black-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Drive Time -->
                        <div>
                            <label for="drive_time" class="form-label block text-sm font-medium text-black-700 mb-2">Drive Time</label>
                            <input type="text" name="drive_time" id="drive_time"
                                   class="form-field block w-full rounded-lg border-orange-200 shadow-sm focus:border-orange-500 focus:ring-orange-500 sm:text-sm @error('drive_time') border-orange-300 @enderror"
                                   value="{{ old('drive_time', $apartmentLease->drive_time) }}"
                                   placeholder="e.g., 15 minutes">
                            @error('drive_time')
                            <p class="form-error mt-1 text-sm text-black-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Notes -->
                        <div class="md:col-span-2">
                            <label for="notes" class="form-label block text-sm font-medium text-black-700 mb-2">Notes</label>
                            <textarea name="notes" id="notes" rows="4"
                                      class="form-textarea block w-full rounded-lg border-orange-200 shadow-sm focus:border-orange-500 focus:ring-orange-500 sm:text-sm @error('notes') border-orange-300 @enderror"
                                      placeholder="Additional notes or comments">{{ old('notes', $apartmentLease->notes) }}</textarea>
                            @error('notes')
                            <p class="form-error mt-1 text-sm text-black-600">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <!-- Form Actions -->
                    <div class="flex justify-end space-x-4 mt-8 pt-6 border-t border-orange-200">
                        <a href="{{ route('admin.apartment-leases.show', $apartmentLease) }}"
                           class="form-cancel px-6 py-2 border border-orange-200 rounded-lg text-black-700 hover:bg-orange-100 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-orange-500">
                            Cancel
                        </a>
                        <button type="submit"
                                class="form-button px-6 py-2 bg-orange-600 text-white rounded-lg hover:bg-orange-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-orange-500">
                            Update Lease
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- JavaScript for store selection toggle -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const storeOptions = document.querySelectorAll('input[name="store_option"]');
            const existingStoreSection = document.getElementById('existing-store-section');
            const newStoreSection = document.getElementById('new-store-section');
            const manualStoreSection = document.getElementById('manual-store-section');
            const storeSelect = document.getElementById('store_id');
            const newStoreNumber = document.getElementById('new_store_number');
            const manualStoreNumber = document.getElementById('store_number');

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

            storeOptions.forEach(option => {
                option.addEventListener('change', toggleStoreOptions);
            });

            // Initialize on page load
            toggleStoreOptions();
        });
    </script>
@endsection
