@extends('layouts.app')

@section('content')
    <div class="min-h-screen bg-gradient-to-br from-orange-50 to-orange-100 py-8" dir="{{ getDirection() }}">
        <div class="mx-auto max-w-md px-4">
            <div class="mb-8 text-center">
                <h1 class="text-3xl font-bold text-black-900">{{ __('messages.attendance') }}</h1>
            </div>

            {{-- Clock-Out Form --}}
            @if($clocking)
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
                                <label class="mb-1 block text-sm font-medium text-black-700" for="image_out">{{ __('messages.upload_image') }}</label>
                                <div class="mt-1">
                                    <div
                                        class="flex justify-center rounded-lg border-2 border-dashed border-orange-200 px-6 pb-6 pt-5 transition-all duration-300 hover:border-orange-500">
                                        <div class="text-center">
                                            <label
                                                class="relative cursor-pointer rounded-md font-medium text-black-600 transition-colors duration-300 focus-within:outline-none focus-within:ring-2 focus-within:ring-orange-500 focus-within:ring-offset-2 hover:text-black-500"
                                                for="image_out">
                                                <span>{{ __('messages.upload_file') }}</span>
                                                <input class="sr-only" id="image_out" name="image_out" type="file"
                                                       accept="image/*" capture="environment" required>
                                            </label>
                                            <p class="mt-1 {{ isRtl() ? 'pr-1' : 'pl-1' }}">{{ __('messages.or_drag_drop') }}</p>
                                            <p class="mt-2 text-xs text-black-500">{{ __('messages.camera_gallery_instruction') }}</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endif

                        <!-- Purchase Question -->
                        <div class="mb-4">
                            <label class="mb-2 block text-sm font-medium text-black-700">{{ __('messages.purchase_question') }}</label>
                            <div class="flex items-center {{ isRtl() ? 'space-x-reverse' : '' }} space-x-4">
                                <div class="flex items-center">
                                    <input class="h-4 w-4 border-orange-200 text-black-600 focus:ring-orange-500"
                                           id="didBuyYes" name="did_buy" type="radio" value="1">
                                    <label class="{{ isRtl() ? 'mr-2' : 'ml-2' }}" for="didBuyYes">{{ __('messages.yes') }}</label>
                                </div>
                                <div class="flex items-center">
                                    <input class="h-4 w-4 border-orange-200 text-black-600 focus:ring-orange-500"
                                           id="didBuyNo" name="did_buy" type="radio" value="0" checked>
                                    <label class="{{ isRtl() ? 'mr-2' : 'ml-2' }}" for="didBuyNo">{{ __('messages.no') }}</label>
                                </div>
                            </div>
                        </div>

                        <!-- Purchase Cost (hidden by default) -->
                        <div class="hidden rounded-lg transition-all duration-300 hover:shadow-md"
                             id="purchase_cost_container">
                            <label class="mb-1 block text-sm font-medium text-black-700" for="purchase_cost">
                                {{ __('messages.purchase_cost') }}
                            </label>
                            <div class="relative rounded-md ring-1 ring-orange-900/5">
                                <input
                                    class="block w-full rounded-lg border border-orange-200 py-3 {{ isRtl() ? 'pr-10 pl-3' : 'pl-10 pr-3' }} transition-all duration-300 focus:border-orange-500 focus:ring-orange-500"
                                    id="purchase_cost" name="purchase_cost" type="number" step="0.01"
                                    placeholder="{{ __('messages.purchase_cost_example') }}">
                            </div>
                        </div>

                        <!-- NEW: Purchase receipt container -->
                        <div class="hidden rounded-lg transition-all duration-300 hover:shadow-md"
                             id="purchase_receipt_container">
                            <label class="mb-1 block text-sm font-medium text-black-700" for="purchase_receipt">
                                {{ __('messages.receipt_image') }}
                            </label>
                            <div class="mt-1">
                                <div
                                    class="flex justify-center rounded-lg border-2 border-dashed border-orange-200 px-6 pb-6 pt-5 transition-all duration-300 hover:border-orange-500">
                                    <div class="text-center">
                                        <label
                                            class="relative cursor-pointer rounded-md font-medium text-black-600 transition-colors duration-300 focus-within:outline-none focus-within:ring-2 focus-within:ring-orange-500 focus-within:ring-offset-2 hover:text-black-500"
                                            for="purchase_receipt">
                                            <span>{{ __('messages.upload_file') }}</span>
                                            <input class="sr-only" id="purchase_receipt" name="purchase_receipt" type="file"
                                                   accept="image/*" capture="environment">
                                        </label>
                                        <p class="mt-1 {{ isRtl() ? 'pr-1' : 'pl-1' }}">{{ __('messages.or_drag_drop') }}</p>
                                        <p class="mt-2 text-xs text-black-500">{{ __('messages.camera_gallery_instruction') }}</p>
                                    </div>
                                </div>
                            </div>
                        </div>

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
                        </div>

                        <!-- Clock In Image -->
                        <div class="rounded-lg transition-all duration-300 hover:shadow-md" id="image_in_container"
                             style="display: none;">
                            <label class="mb-1 block text-sm font-medium text-black-700" for="image_in">{{ __('messages.upload_image') }}</label>
                            <div class="mt-1">
                                <div
                                    class="flex justify-center rounded-lg border-2 border-dashed border-orange-200 px-6 pb-6 pt-5 transition-all duration-300 hover:border-orange-500">
                                    <div class="text-center">
                                        <label
                                            class="relative cursor-pointer rounded-md font-medium text-black-600 transition-colors duration-300 focus-within:outline-none focus-within:ring-2 focus-within:ring-orange-500 focus-within:ring-offset-2 hover:text-black-500"
                                            for="image_in">
                                            <span>{{ __('messages.upload_file') }}</span>
                                            <input class="sr-only" id="image_in" name="image_in" type="file"
                                                   accept="image/*" capture="environment">
                                        </label>
                                        <p class="mt-1 {{ isRtl() ? 'pr-1' : 'pl-1' }}">{{ __('messages.or_drag_drop') }}</p>
                                        <p class="mt-2 text-xs text-black-500">{{ __('messages.camera_gallery_instruction') }}</p>
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
        <div class="fixed inset-0 bg-orange-100 bg-opacity-75 transition-opacity"></div>
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
        // CLOCK OUT: Show/Hide purchase fields if user bought something
        // -----------------------------------------
        function togglePurchaseFields(didBuy) {
            const costContainer = document.getElementById('purchase_cost_container');
            const receiptContainer = document.getElementById('purchase_receipt_container');
            const costInput = document.getElementById('purchase_cost');
            const receiptInput = document.getElementById('purchase_receipt');
        
            // Add null checks to prevent errors
            if (costContainer && receiptContainer && costInput && receiptInput) {
                if (didBuy) {
                    costContainer.classList.remove('hidden');
                    receiptContainer.classList.remove('hidden');
                    costInput.setAttribute('required', 'required');
                    receiptInput.setAttribute('required', 'required');
                } else {
                    costContainer.classList.add('hidden');
                    receiptContainer.classList.add('hidden');
                    costInput.removeAttribute('required');
                    receiptInput.removeAttribute('required');
                }
            }
        }

        // -----------------------------------------
        // Confirmation Modal
        // -----------------------------------------
        function showConfirmation(action) {
            const modal = document.getElementById('confirmationModal');
            const confirmButton = document.getElementById('confirmButton');
            const message = document.getElementById('confirmationMessage');

            if (action === 'in') {
                message.textContent = '{{ __('messages.confirm_clock_in') }}';
                confirmButton.onclick = () => document.getElementById('clockInForm').submit();
            } else {
                message.textContent = '{{ __('messages.confirm_clock_out') }}';
                confirmButton.onclick = () => document.getElementById('clockOutForm').submit();
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

                if (!milesIn || !imageIn) {
                    clockInErrorBox.innerText = '{{ __('messages.car_usage_validation') }}';
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

            // Clear any previous error
            clockOutErrorBox.innerText = '';
            clockOutErrorBox.classList.add('hidden');

            // If using car => check miles_out + image_out
            if (usingCar) {
                const milesOut = document.getElementById('miles_out').value.trim();
                const imageOut = document.getElementById('image_out').value; // file path

                if (!milesOut || !imageOut) {
                    clockOutErrorBox.innerText = '{{ __('messages.car_clock_out_validation') }}';
                    clockOutErrorBox.classList.remove('hidden');
                    return;
                }
            }

            // Also validate the "did you buy anything?" question
            const didBuyYes = document.getElementById('didBuyYes');
            if (didBuyYes && didBuyYes.checked) {
                const costVal = document.getElementById('purchase_cost').value.trim();
                const receiptVal = document.getElementById('purchase_receipt').value;

                if (!costVal || !receiptVal) {
                    clockOutErrorBox.innerText = '{{ __('messages.purchase_validation') }}';
                    clockOutErrorBox.classList.remove('hidden');
                    return;
                }
            }

            // If all good
            showConfirmation('out');
        }

        // -----------------------------------------
        // On page load, attach event listeners
        // -----------------------------------------
        document.addEventListener('DOMContentLoaded', function() {
            const usingCarYes = document.getElementById('using_car_yes');
            const usingCarNo = document.getElementById('using_car_no');

            // Clock-in toggles
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

            // Clock-out toggles for "did you buy anything"
            const didBuyYes = document.getElementById('didBuyYes');
            const didBuyNo = document.getElementById('didBuyNo');

            if (didBuyYes) {
                didBuyYes.addEventListener('change', () => togglePurchaseFields(true));
            }
            if (didBuyNo) {
                didBuyNo.addEventListener('change', () => togglePurchaseFields(false));
            }

            // Set initial state for purchase fields
            if (didBuyYes && didBuyYes.checked) {
                togglePurchaseFields(true);
            } else {
                togglePurchaseFields(false);
            }
        });
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
@endsection
