@extends('layouts.app')

@section('title', 'Add Payment')

@section('content')
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <div class="bg-orange-50 shadow-lg rounded-xl overflow-hidden">
            <div class="px-6 py-4 border-b border-orange-200">
                <div class="flex justify-between items-center">
                    <h2 class="text-2xl font-bold text-black-900">Add New Payment</h2>
                    <a href="{{ route('payments.index') }}"
                       class="form-cancel inline-flex items-center px-4 py-2 border border-orange-300 rounded-lg text-sm font-medium text-black-700 bg-white hover:bg-orange-100 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-orange-500">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                        </svg>
                        Back to Payments
                    </a>
                </div>
            </div>

            <form action="{{ route('payments.store') }}" method="POST" class="p-6 space-y-6">
                @csrf

                <!-- Store Selection Section -->
                <div class="bg-orange-100 rounded-lg p-4">
                    <h3 class="text-lg font-medium text-black-900 mb-4">Store Information</h3>

                    <!-- Store Selection Options -->
                    <div class="mb-4">
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
                    <div id="existing-store-section" class="mb-4">
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
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
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
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Date -->
                    <div>
                        <label for="date" class="form-label block text-sm font-medium text-black-700 mb-2">Date</label>
                        <input type="date" name="date" id="date"
                               class="form-field block w-full border-orange-300 rounded-lg shadow-sm focus:ring-orange-500 focus:border-orange-500 sm:text-sm @error('date') border-red-300 @enderror"
                               value="{{ old('date', date('Y-m-d')) }}">
                        @error('date')
                        <p class="form-error mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Cost -->
                    <div>
                        <label for="cost" class="form-label block text-sm font-medium text-black-700 mb-2">Cost</label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <span class="text-black-500 sm:text-sm">$</span>
                            </div>
                            <input type="number" name="cost" id="cost" step="0.01" min="0"
                                   class="form-field block w-full pl-7 border-orange-300 rounded-lg shadow-sm focus:ring-orange-500 focus:border-orange-500 sm:text-sm @error('cost') border-red-300 @enderror"
                                   value="{{ old('cost') }}"
                                   placeholder="0.00">
                        </div>
                        @error('cost')
                        <p class="form-error mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Company -->
                    <div>
                        <label for="company_id" class="form-label block text-sm font-medium text-black-700 mb-2">Company</label>
                        <select name="company_id" id="company_id"
                                class="form-select block w-full border-orange-300 rounded-lg shadow-sm focus:ring-orange-500 focus:border-orange-500 sm:text-sm @error('company_id') border-red-300 @enderror">
                            <option value="">Select a Company</option>
                            @foreach($companies as $company)
                                <option value="{{ $company->id }}" {{ old('company_id') == $company->id ? 'selected' : '' }}>
                                    {{ $company->name }}
                                </option>
                            @endforeach
                        </select>
                        @error('company_id')
                        <p class="form-error mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Maintenance Type -->
                    <div>
                        <label for="maintenance_type" class="form-label block text-sm font-medium text-black-700 mb-2">Maintenance Type</label>
                        <input type="text" name="maintenance_type" id="maintenance_type"
                               class="form-field block w-full border-orange-300 rounded-lg shadow-sm focus:ring-orange-500 focus:border-orange-500 sm:text-sm"
                               value="{{ old('maintenance_type') }}"
                               placeholder="e.g., Equipment/Parts">
                    </div>
                </div>

                <!-- What Got Fixed -->
                <div>
                    <label for="what_got_fixed" class="form-label block text-sm font-medium text-black-700 mb-2">What Got Fixed</label>
                    <select id="what_got_fixed" name="what_got_fixed"
                            class="form-select block w-full border-orange-300 rounded-md py-2 px-3 shadow-sm focus:outline-none focus:ring-orange-500 focus:border-orange-500 sm:text-sm">
                        <option value="">Select or type to add new option</option>
                        @foreach($fixedOptions as $option)
                            <option value="{{ $option }}" {{ old('what_got_fixed') == $option ? 'selected' : '' }}>{{ $option }}</option>
                        @endforeach
                    </select>
                </div>

                <!-- Payment Method -->
                <div>
                    <label for="payment_method" class="form-label block text-sm font-medium text-black-700 mb-2">Payment Method</label>
                    <select name="payment_method" id="payment_method"
                            class="form-select block w-full border-orange-300 rounded-lg shadow-sm focus:ring-orange-500 focus:border-orange-500 sm:text-sm">
                        <option value="">Select Payment Method</option>
                        <option value="CC" {{ old('payment_method') === 'CC' ? 'selected' : '' }}>Credit Card (CC)</option>
                        <option value="Cash" {{ old('payment_method') === 'Cash' ? 'selected' : '' }}>Cash</option>
                        <option value="Check" {{ old('payment_method') === 'Check' ? 'selected' : '' }}>Check</option>
                        <option value="ACH" {{ old('payment_method') === 'ACH' ? 'selected' : '' }}>ACH</option>
                        <option value="Wire" {{ old('payment_method') === 'Wire' ? 'selected' : '' }}>Wire Transfer</option>
                    </select>
                </div>

                <!-- Notes -->
                <div>
                    <label for="notes" class="form-label block text-sm font-medium text-black-700 mb-2">Notes</label>
                    <textarea name="notes" id="notes" rows="3"
                              class="form-textarea block w-full border-orange-300 rounded-lg shadow-sm focus:ring-orange-500 focus:border-orange-500 sm:text-sm"
                              placeholder="Additional notes or invoice links...">{{ old('notes') }}</textarea>
                </div>

                <!-- Payment Status -->
                <div class="flex items-center">
                    <input type="checkbox" name="paid" id="paid" value="1"
                           class="h-4 w-4 text-black-600 focus:ring-orange-500 border-orange-300 rounded"
                        {{ old('paid') ? 'checked' : '' }}>
                    <label for="paid" class="ml-2 block text-sm text-black-700">
                        Payment has been made
                    </label>
                </div>

                <!-- Submit Button -->
                <div class="flex justify-end space-x-3">
                    <a href="{{ route('payments.index') }}"
                       class="form-cancel inline-flex items-center px-4 py-2 border border-orange-300 rounded-lg text-sm font-medium text-black-700 bg-white hover:bg-orange-100 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-orange-500">
                        Cancel
                    </a>
                    <button type="submit"
                            class="form-button inline-flex items-center px-4 py-2 border border-transparent rounded-lg text-sm font-medium text-white bg-orange-600 hover:bg-orange-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-orange-500">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                        </svg>
                        Create Payment
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- TomSelect CSS and JS -->
    <link href="https://cdn.jsdelivr.net/npm/tom-select@2.0.0/dist/css/tom-select.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/tom-select@2.0.0/dist/js/tom-select.complete.min.js"></script>

    <!-- JavaScript for store selection toggle and TomSelect -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Store selection toggle
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

            // Initialize TomSelect for "What Got Fixed"
            new TomSelect('#what_got_fixed', {
                create: true,
                placeholder: 'Start typing to select or add new option',
                sortField: {
                    field: "text",
                    direction: "asc"
                },
                render: {
                    option: function(data, escape) {
                        return '<div class="flex items-center">' + escape(data.text) + '</div>';
                    },
                    item: function(data, escape) {
                        return '<div>' + escape(data.text) + '</div>';
                    }
                },
                onInitialize: function() {
                    // Force show placeholder if no value is selected
                    if(!this.getValue()) {
                        this.inputState();
                    }
                }
            });
        });
    </script>
@endsection
