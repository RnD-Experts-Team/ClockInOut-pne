@extends('layouts.app')

@section('title', 'Add Payment')

@section('content')
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <div class="bg-orange-50 shadow-lg rounded-xl overflow-hidden">
            <div class="px-6 py-4 border-b border-orange-200">
                <div class="flex justify-between items-center">
                    <h2 class="text-2xl font-bold text-black-900">Add New Payment - Demo</h2>
                    <a href="#"
                       class="form-cancel inline-flex items-center px-4 py-2 border border-orange-300 rounded-lg text-sm font-medium text-black-700 bg-white hover:bg-orange-100 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-orange-500">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                        </svg>
                        Back to Payments
                    </a>
                </div>
            </div>

            <form action="#" method="POST" class="p-6 space-y-6">

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
                        <select name="store_id" id="store_id" class="form-select block w-full rounded-lg border-orange-300 shadow-sm focus:border-orange-500 focus:ring-orange-500 sm:text-sm">
                            <option value="">Select a store...</option>
                            <option value="1">001 - Store 5 Mall Rd</option>
                            <option value="2" selected>003 - Store 3 Downtown</option>
                            <option value="3">007 - Store 7 Airport</option>
                            <option value="4">012 - Store 12 West Side</option>
                        </select>
                    </div>

                    <!-- New Store Creation -->
                    <div id="new-store-section" class="hidden">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label for="new_store_number" class="form-label block text-sm font-medium text-black-700 mb-2">New Store Number <span class="text-red-500">*</span></label>
                                <input type="text" name="new_store_number" id="new_store_number"
                                       class="form-field block w-full rounded-lg border-orange-300 shadow-sm focus:border-orange-500 focus:ring-orange-500 sm:text-sm"
                                       value=""
                                       placeholder="e.g., 001, Store-A">
                            </div>
                            <div>
                                <label for="new_store_name" class="form-label block text-sm font-medium text-black-700 mb-2">New Store Name</label>
                                <input type="text" name="new_store_name" id="new_store_name"
                                       class="form-field block w-full rounded-lg border-orange-300 shadow-sm focus:border-orange-500 focus:ring-orange-500 sm:text-sm"
                                       value=""
                                       placeholder="Store name or location">
                            </div>
                        </div>
                    </div>
                </div>

                <!-- NEW: Equipment Purchases Section (Demo) -->
                <div class="bg-white rounded-lg p-5 shadow-md ring-1 ring-purple-900/10 border-2 border-purple-200">
                    <div class="flex items-center justify-between mb-4">
                        <div class="flex items-center gap-3">
                            <div class="flex h-10 w-10 items-center justify-center rounded-full bg-purple-600 shadow-lg">
                                <svg class="h-6 w-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"></path>
                                </svg>
                            </div>
                            <div>
                                <h3 class="text-lg font-bold text-black-900">Admin Equipment Purchases</h3>
                                <div class="flex items-center gap-2">
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-bold bg-purple-100 text-purple-800">
                                        Admin Purchase
                                    </span>
                                    <span class="text-xs text-black-500">• Equipment, Parts & Supplies</span>
                                </div>
                            </div>
                        </div>
                        <button type="button" class="inline-flex items-center px-3 py-1.5 border-2 border-dashed border-purple-300 rounded-lg text-xs font-medium text-purple-700 bg-purple-50 hover:bg-purple-100 transition-all">
                            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                            </svg>
                            Add Item
                        </button>
                    </div>

                    <!-- Demo Notice -->
                    <div class="mb-4 rounded-lg bg-purple-50 border border-purple-200 p-3">
                        <p class="text-xs text-purple-800">
                            <strong>New Feature Demo:</strong> Admin can purchase equipment (freezers, shelving, tools) for stores. Items auto-tracked with "Admin Purchase" badge.
                        </p>
                    </div>

                    <div class="space-y-3">
                        <!-- Item 1 -->
                        <div class="bg-gradient-to-br from-purple-50 to-white p-4 rounded-lg border border-purple-200">
                            <div class="flex items-center justify-between mb-3">
                                <span class="text-xs font-bold text-purple-700 bg-purple-200 px-2 py-1 rounded">Item #1</span>
                                <button type="button" class="text-red-600 hover:text-red-800 text-xs font-medium">Remove</button>
                            </div>
                            <div class="grid grid-cols-1 md:grid-cols-12 gap-3">
                                <div class="md:col-span-5">
                                    <label class="block text-xs font-medium text-black-700 mb-1">Item Name</label>
                                    <input type="text" value="Commercial Freezer - Bibline X500" class="form-field block w-full rounded-lg border-purple-300 shadow-sm focus:border-purple-500 focus:ring-purple-500 text-sm" placeholder="Equipment name">
                                </div>
                                <div class="md:col-span-2">
                                    <label class="block text-xs font-medium text-black-700 mb-1">Qty</label>
                                    <input type="number" value="1" min="1" class="form-field block w-full rounded-lg border-purple-300 shadow-sm focus:border-purple-500 focus:ring-purple-500 text-sm">
                                </div>
                                <div class="md:col-span-3">
                                    <label class="block text-xs font-medium text-black-700 mb-1">Unit Cost</label>
                                    <div class="relative">
                                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                            <span class="text-black-500 text-sm">$</span>
                                        </div>
                                        <input type="number" value="2850.00" step="0.01" class="form-field block w-full pl-7 rounded-lg border-purple-300 shadow-sm focus:border-purple-500 focus:ring-purple-500 text-sm">
                                    </div>
                                </div>
                                <div class="md:col-span-2">
                                    <label class="block text-xs font-medium text-black-700 mb-1">Total</label>
                                    <div class="px-3 py-2 bg-purple-100 rounded-lg text-sm font-bold text-purple-900">$2,850.00</div>
                                </div>
                            </div>
                        </div>

                        <!-- Item 2 -->
                        <div class="bg-gradient-to-br from-purple-50 to-white p-4 rounded-lg border border-purple-200">
                            <div class="flex items-center justify-between mb-3">
                                <span class="text-xs font-bold text-purple-700 bg-purple-200 px-2 py-1 rounded">Item #2</span>
                                <button type="button" class="text-red-600 hover:text-red-800 text-xs font-medium">Remove</button>
                            </div>
                            <div class="grid grid-cols-1 md:grid-cols-12 gap-3">
                                <div class="md:col-span-5">
                                    <label class="block text-xs font-medium text-black-700 mb-1">Item Name</label>
                                    <input type="text" value="Stainless Steel Shelving Units" class="form-field block w-full rounded-lg border-purple-300 shadow-sm focus:border-purple-500 focus:ring-purple-500 text-sm">
                                </div>
                                <div class="md:col-span-2">
                                    <label class="block text-xs font-medium text-black-700 mb-1">Qty</label>
                                    <input type="number" value="3" min="1" class="form-field block w-full rounded-lg border-purple-300 shadow-sm focus:border-purple-500 focus:ring-purple-500 text-sm">
                                </div>
                                <div class="md:col-span-3">
                                    <label class="block text-xs font-medium text-black-700 mb-1">Unit Cost</label>
                                    <div class="relative">
                                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                            <span class="text-black-500 text-sm">$</span>
                                        </div>
                                        <input type="number" value="185.00" step="0.01" class="form-field block w-full pl-7 rounded-lg border-purple-300 shadow-sm focus:border-purple-500 focus:ring-purple-500 text-sm">
                                    </div>
                                </div>
                                <div class="md:col-span-2">
                                    <label class="block text-xs font-medium text-black-700 mb-1">Total</label>
                                    <div class="px-3 py-2 bg-purple-100 rounded-lg text-sm font-bold text-purple-900">$555.00</div>
                                </div>
                            </div>
                        </div>

                        <!-- Total & Photos -->
                        <div class="flex items-center justify-between pt-3 border-t-2 border-purple-200">
                            <div>
                                <p class="text-xs text-black-600 mb-1">Receipt Photos</p>
                                <div class="flex gap-2">
                                    <div class="relative group h-16 w-16 rounded-lg border-2 border-purple-200 bg-purple-100 overflow-hidden">
                                        <div class="flex h-full items-center justify-center">
                                            <svg class="h-8 w-8 text-purple-300" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M4 3a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V5a2 2 0 00-2-2H4zm12 12H4l4-8 3 6 2-4 3 6z" clip-rule="evenodd"></path>
                                            </svg>
                                        </div>
                                    </div>
                                    <div class="relative group h-16 w-16 rounded-lg border-2 border-purple-200 bg-purple-100 overflow-hidden">
                                        <div class="flex h-full items-center justify-center">
                                            <svg class="h-8 w-8 text-purple-300" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M4 3a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V5a2 2 0 00-2-2H4zm12 12H4l4-8 3 6 2-4 3 6z" clip-rule="evenodd"></path>
                                            </svg>
                                        </div>
                                    </div>
                                    <button type="button" class="h-16 w-16 rounded-lg border-2 border-dashed border-purple-300 bg-purple-50 flex items-center justify-center hover:bg-purple-100 transition-all">
                                        <svg class="h-6 w-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                                        </svg>
                                    </button>
                                </div>
                            </div>
                            <div class="text-right">
                                <p class="text-sm text-black-600 mb-1">Equipment Total</p>
                                <p class="text-2xl font-bold text-purple-600">$3,405.00</p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Date -->
                    <div>
                        <label for="date" class="form-label block text-sm font-medium text-black-700 mb-2">Date</label>
                        <input type="date" name="date" id="date"
                               class="form-field block w-full border-orange-300 rounded-lg shadow-sm focus:ring-orange-500 focus:border-orange-500 sm:text-sm"
                               value="2025-11-30">
                    </div>

                    <!-- Cost -->
                    <div>
                        <label for="cost" class="form-label block text-sm font-medium text-black-700 mb-2">Cost</label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <span class="text-black-500 sm:text-sm">$</span>
                            </div>
                            <input type="number" name="cost" id="cost" step="0.01" min="0"
                                   class="form-field block w-full pl-7 border-orange-300 rounded-lg shadow-sm focus:ring-orange-500 focus:border-orange-500 sm:text-sm"
                                   value=""
                                   placeholder="0.00">
                        </div>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Company -->
                    <div>
                        <label for="company_id" class="form-label block text-sm font-medium text-black-700 mb-2">Company</label>
                        <select name="company_id" id="company_id"
                                class="form-select block w-full border-orange-300 rounded-lg shadow-sm focus:ring-orange-500 focus:border-orange-500 sm:text-sm">
                            <option value="">Select a Company</option>
                            <option value="1">ABC Maintenance Co.</option>
                            <option value="2" selected>XYZ Repair Services</option>
                            <option value="3">Quality Fix Inc.</option>
                        </select>
                    </div>

                    <!-- Maintenance Type -->
                    <div>
                        <label for="maintenance_type" class="form-label block text-sm font-medium text-black-700 mb-2">Maintenance Type</label>
                        <input type="text" name="maintenance_type" id="maintenance_type"
                               class="form-field block w-full border-orange-300 rounded-lg shadow-sm focus:ring-orange-500 focus:border-orange-500 sm:text-sm"
                               value=""
                               placeholder="e.g., Equipment/Parts">
                    </div>
                </div>

                <!-- What Got Fixed -->
                <div>
                    <label for="what_got_fixed" class="form-label block text-sm font-medium text-black-700 mb-2">What Got Fixed</label>

                    <!-- Custom Dropdown Container -->
                    <div class="custom-select-container relative">
                        <!-- Hidden select for form submission -->
                        <select id="what_got_fixed" name="what_got_fixed" class="hidden">
                            <option value="">Select or type to add new option</option>
                            <option value="Plumbing">Plumbing</option>
                            <option value="HVAC">HVAC</option>
                            <option value="Electrical">Electrical</option>
                            <option value="Equipment">Equipment</option>
                            <option value="Flooring">Flooring</option>
                        </select>

                        <!-- Custom input/display -->
                        <div class="custom-select-input relative">
                            <input type="text"
                                   id="custom_what_got_fixed"
                                   class="form-field block w-full pr-10 border-orange-300 rounded-lg shadow-sm focus:ring-orange-500 focus:border-orange-500 sm:text-sm"
                                   placeholder="Select or type to add new option..."
                                   autocomplete="off">

                            <!-- Dropdown arrow -->
                            <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none">
                                <svg class="w-4 h-4 text-orange-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                                </svg>
                            </div>
                        </div>

                        <!-- Dropdown options -->
                        <div class="custom-select-dropdown absolute z-50 w-full mt-1 bg-white border border-orange-300 rounded-lg shadow-lg max-h-60 overflow-auto hidden">
                            <div class="py-1">
                                <!-- Options will be populated here -->
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Payment Method -->
                <div>
                    <label for="payment_method" class="form-label block text-sm font-medium text-black-700 mb-2">Payment Method</label>
                    <select name="payment_method" id="payment_method"
                            class="form-select block w-full border-orange-300 rounded-lg shadow-sm focus:ring-orange-500 focus:border-orange-500 sm:text-sm">
                        <option value="">Select Payment Method</option>
                        <option value="CC" selected>Credit Card (CC)</option>
                        <option value="Cash">Cash</option>
                        <option value="Check">Check</option>
                        <option value="ACH">ACH</option>
                        <option value="Wire">Wire Transfer</option>
                        <option value="Zelle">Zelle</option>
                        <option value="Bill Payment">Bill Payment / Check</option>
                        <option value="Paychex">Paychex</option>
                        <option value="DashCard">DashCard</option>

                    </select>
                </div>

                <!-- Notes -->
                <div>
                    <label for="notes" class="form-label block text-sm font-medium text-black-700 mb-2">Notes</label>
                    <textarea name="notes" id="notes" rows="3"
                              class="form-textarea block w-full border-orange-300 rounded-lg shadow-sm focus:ring-orange-500 focus:border-orange-500 sm:text-sm"
                              placeholder="Additional notes or invoice links..."></textarea>
                </div>

                <!-- Payment Status -->
                <div class="flex items-center">
                    <input type="checkbox" name="paid" id="paid" value="1"
                           class="h-4 w-4 text-black-600 focus:ring-orange-500 border-orange-300 rounded">
                    <label for="paid" class="ml-2 block text-sm text-black-700">
                        Payment has been made
                    </label>
                </div>

                <!-- Submit Button -->
                <div class="flex justify-end space-x-3">
                    <a href="#"
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

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            console.log('=== FORM JAVASCRIPT STARTING ===');

            // Store selection toggle (keep your existing code)
            const storeOptions = document.querySelectorAll('input[name="store_option"]');
            const existingStoreSection = document.getElementById('existing-store-section');
            const newStoreSection = document.getElementById('new-store-section');
            const storeSelect = document.getElementById('store_id');
            const newStoreNumber = document.getElementById('new_store_number');

            function toggleStoreOptions() {
                const selectedOption = document.querySelector('input[name="store_option"]:checked');

                if (selectedOption && selectedOption.value === 'existing') {
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

            toggleStoreOptions();

            // CUSTOM TOMSELECT-STYLE DROPDOWN
            console.log('Setting up custom dropdown...');

            const hiddenSelect = document.getElementById('what_got_fixed');
            const customInput = document.getElementById('custom_what_got_fixed');
            const dropdown = document.querySelector('.custom-select-dropdown');
            const dropdownContent = dropdown.querySelector('.py-1');

            if (!hiddenSelect || !customInput || !dropdown) {
                console.error('❌ Custom dropdown elements not found');
                return;
            }

            let options = [];
            let filteredOptions = [];
            let selectedIndex = -1;

            // Load initial options from the select element
            function loadInitialOptions() {
                options = [];
                Array.from(hiddenSelect.options).forEach(option => {
                    if (option.value) { // Skip empty option
                        options.push({
                            value: option.value,
                            text: option.textContent,
                            selected: option.selected
                        });
                    }
                });

                // Set initial value if there's a selected option
                const selectedOption = options.find(opt => opt.selected);
                if (selectedOption) {
                    customInput.value = selectedOption.text;
                    hiddenSelect.value = selectedOption.value;
                }

                console.log('✅ Loaded', options.length, 'initial options');
            }

            // Render options in dropdown
            function renderOptions(optionsToRender = options) {
                dropdownContent.innerHTML = '';
                filteredOptions = optionsToRender;

                if (filteredOptions.length === 0) {
                    // Show "Add new" option when no matches
                    const addNewDiv = document.createElement('div');
                    addNewDiv.className = 'px-3 py-2 text-orange-600 cursor-pointer hover:bg-orange-50 font-medium';
                    addNewDiv.innerHTML = `<strong>+ Add:</strong> "${customInput.value.trim()}"`;
                    addNewDiv.addEventListener('click', () => {
                        selectNewOption(customInput.value.trim());
                    });
                    dropdownContent.appendChild(addNewDiv);
                } else {
                    // Render existing options
                    filteredOptions.forEach((option, index) => {
                        const optionDiv = document.createElement('div');
                        optionDiv.className = 'px-3 py-2 cursor-pointer hover:bg-orange-50 text-sm';
                        optionDiv.textContent = option.text;
                        optionDiv.addEventListener('click', () => {
                            selectOption(option);
                        });
                        dropdownContent.appendChild(optionDiv);
                    });

                    // Add "Add new" option if user is typing something new
                    const inputValue = customInput.value.trim();
                    if (inputValue && !filteredOptions.some(opt => opt.text.toLowerCase() === inputValue.toLowerCase())) {
                        const addNewDiv = document.createElement('div');
                        addNewDiv.className = 'px-3 py-2 text-orange-600 cursor-pointer hover:bg-orange-50 font-medium border-t border-gray-200';
                        addNewDiv.innerHTML = `<strong>+ Add:</strong> "${inputValue}"`;
                        addNewDiv.addEventListener('click', () => {
                            selectNewOption(inputValue);
                        });
                        dropdownContent.appendChild(addNewDiv);
                    }
                }

                selectedIndex = -1; // Reset selection
            }

            // Filter options based on input
            function filterOptions(searchTerm) {
                if (!searchTerm) {
                    return options;
                }

                return options.filter(option =>
                    option.text.toLowerCase().includes(searchTerm.toLowerCase())
                );
            }

            // Select an existing option
            function selectOption(option) {
                customInput.value = option.text;
                hiddenSelect.value = option.value;
                closeDropdown();
                console.log('✅ Selected option:', option.text);
            }

            // Select/create a new option
            function selectNewOption(text) {
                if (!text) return;

                // Add to options array
                const newOption = { value: text, text: text, selected: false };
                options.push(newOption);

                // Add to hidden select
                const newSelectOption = document.createElement('option');
                newSelectOption.value = text;
                newSelectOption.textContent = text;
                newSelectOption.selected = true;
                hiddenSelect.appendChild(newSelectOption);

                // Set values
                customInput.value = text;
                hiddenSelect.value = text;
                closeDropdown();

                console.log('✅ Added new option:', text);
            }

            // Open dropdown
            function openDropdown() {
                const filteredOpts = filterOptions(customInput.value);
                renderOptions(filteredOpts);
                dropdown.classList.remove('hidden');
                console.log('Dropdown opened');
            }

            // Close dropdown
            function closeDropdown() {
                dropdown.classList.add('hidden');
                selectedIndex = -1;
                console.log('Dropdown closed');
            }

            // Event listeners
            customInput.addEventListener('focus', () => {
                openDropdown();
            });

            customInput.addEventListener('input', (e) => {
                const filteredOpts = filterOptions(e.target.value);
                renderOptions(filteredOpts);

                if (dropdown.classList.contains('hidden')) {
                    openDropdown();
                }
            });

            customInput.addEventListener('keydown', (e) => {
                const visibleOptions = dropdownContent.children;

                if (e.key === 'ArrowDown') {
                    e.preventDefault();
                    selectedIndex = Math.min(selectedIndex + 1, visibleOptions.length - 1);
                    updateHighlight();
                } else if (e.key === 'ArrowUp') {
                    e.preventDefault();
                    selectedIndex = Math.max(selectedIndex - 1, 0);
                    updateHighlight();
                } else if (e.key === 'Enter') {
                    e.preventDefault();
                    if (selectedIndex >= 0 && visibleOptions[selectedIndex]) {
                        visibleOptions[selectedIndex].click();
                    } else if (customInput.value.trim()) {
                        selectNewOption(customInput.value.trim());
                    }
                } else if (e.key === 'Escape') {
                    closeDropdown();
                }
            });

            // Update keyboard navigation highlight
            function updateHighlight() {
                const visibleOptions = dropdownContent.children;
                Array.from(visibleOptions).forEach((opt, index) => {
                    if (index === selectedIndex) {
                        opt.classList.add('bg-orange-100');
                    } else {
                        opt.classList.remove('bg-orange-100');
                    }
                });
            }

            // Close dropdown when clicking outside
            document.addEventListener('click', (e) => {
                if (!e.target.closest('.custom-select-container')) {
                    closeDropdown();
                }
            });

            // Initialize
            loadInitialOptions();
            console.log('✅ Custom dropdown initialized');

            // Form submission handler (keep your existing code)
            const form = document.querySelector('form');
            if (form) {
                form.addEventListener('submit', function(e) {
                    console.log('Form submitting...');
                    console.log('What Got Fixed value:', hiddenSelect.value);

                    // Validation
                    const storeOption = document.querySelector('input[name="store_option"]:checked').value;
                    if (storeOption === 'existing' && !document.getElementById('store_id').value) {
                        e.preventDefault();
                        alert('Please select a store.');
                        return false;
                    }
                    if (storeOption === 'new' && !document.getElementById('new_store_number').value.trim()) {
                        e.preventDefault();
                        alert('Please enter a store number.');
                        return false;
                    }
                });
            }

            console.log('=== FORM JAVASCRIPT COMPLETE ===');
        });
    </script>


@endsection
