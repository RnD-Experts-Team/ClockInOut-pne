@extends('layouts.app')

@section('title', 'الحضور')

@include('components.toast-notification')

@section('content')
    <div class="max-w-xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <div class="bg-white rounded-2xl shadow-lg p-6 sm:p-8 transition-all duration-300 hover:shadow-xl">

            <!-- Status Header -->
            <div class="text-center mb-8 animate-fade-in">
                <div
                    class="inline-flex items-center justify-center w-20 h-20 rounded-full mb-4 {{ $clocking ? 'bg-red-100' : 'bg-green-100' }} transition-colors duration-300">
                    <svg class="w-10 h-10 {{ $clocking ? 'text-red-500' : 'text-green-500' }} transition-colors duration-300"
                        fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
                <h2 class="text-3xl sm:text-4xl font-bold text-gray-900 mb-2 transition-all duration-300">
                    {{ $clocking ? 'تسجيل الانصراف' : 'تسجيل الحضور' }}
                </h2>
                <p class="text-gray-500 text-lg transition-all duration-300">
                    {{ $clocking ? 'سجل تفاصيل نهاية دوامك' : 'ابدأ تسجيل دوامك' }}
                </p>
            </div>

            {{-- Clock-Out Form --}}
            @if ($clocking)
                <form id="clockOutForm" action="{{ route('clocking.clockOut') }}" method="POST"
                    enctype="multipart/form-data" class="space-y-6 animate-fade-in">
                    @csrf

                    <!-- Clock-Out Error Message (initially hidden) -->
                    <div id="clockOutError"
                         class="hidden bg-red-100 text-red-800 p-3 rounded-lg mb-2">
                        <!-- Will be populated by JS if there's an error -->
                    </div>

                    @if ($using_car)
                        <div class="transition-all duration-300 hover:shadow-md rounded-lg" id="miles_out_container">
                            <label for="miles_out" class="block text-sm font-medium text-gray-700 mb-1">
                                أميال الانصراف
                            </label>
                            <div class="relative rounded-md shadow-sm">
                                <input type="number" id="miles_out" name="miles_out"
                                    class="block w-full pl-10 pr-3 py-3 border border-gray-300 rounded-lg focus:ring-red-500 focus:border-red-500 transition-all duration-300"
                                    placeholder="أدخل عدد الأميال" required>
                            </div>
                        </div>

                        <div class="transition-all duration-300 hover:shadow-md rounded-lg" id="image_out_container">
                            <label for="image_out" class="block text-sm font-medium text-gray-700 mb-1">
                                رفع صورة
                            </label>
                            <div class="mt-1">
                                <div
                                    class="flex justify-center px-6 pt-5 pb-6 border-2 border-gray-300 border-dashed rounded-lg hover:border-red-500 transition-all duration-300">
                                    <div class="text-center">
                                        <label for="image_out"
                                            class="relative cursor-pointer rounded-md font-medium text-red-600 hover:text-red-500 focus-within:outline-none focus-within:ring-2 focus-within:ring-offset-2 focus-within:ring-red-500 transition-colors duration-300">
                                            <span>رفع ملف</span>
                                            <input id="image_out" name="image_out" type="file" accept="image/*" capture="environment"
                                                class="sr-only" required>
                                        </label>
                                        <p class="pl-1 mt-1">أو اسحب وأفلت</p>
                                        <p class="text-xs text-gray-500 mt-2">يمكنك التقاط صورة مباشرة من الكاميرا أو اختيار صورة من المعرض</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endif

                    <!-- NEW: Radio Buttons => Did you buy anything for the company? -->
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            هل اشتريت أي شيء لصالح الشركة؟
                        </label>
                        <div class="flex items-center space-x-4">
                            <div class="flex items-center">
                                <input type="radio" id="didBuyYes" name="bought_something" value="1"
                                    class="focus:ring-indigo-500 h-4 w-4 text-indigo-600 border-gray-300">
                                <label for="didBuyYes" class="mr-2">نعم</label>
                            </div>
                            <div class="flex items-center">
                                <input type="radio" id="didBuyNo" name="bought_something" value="0"
                                    class="focus:ring-indigo-500 h-4 w-4 text-indigo-600 border-gray-300" checked>
                                <label for="didBuyNo" class="mr-2">لا</label>
                            </div>
                        </div>
                    </div>

                    <!-- NEW: Purchase cost container -->
                    <div class="transition-all duration-300 hover:shadow-md rounded-lg hidden" id="purchase_cost_container">
                        <label for="purchase_cost" class="block text-sm font-medium text-gray-700 mb-1">
                            تكلفة المشتريات
                        </label>
                        <div class="relative rounded-md shadow-sm">
                            <input type="number" step="0.01" id="purchase_cost" name="purchase_cost"
                                class="block w-full pl-10 pr-3 py-3 border border-gray-300 rounded-lg focus:ring-red-500 focus:border-red-500 transition-all duration-300"
                                placeholder="مثال: 150.00" >
                        </div>
                    </div>

                    <!-- NEW: Purchase receipt container -->
                    <div class="transition-all duration-300 hover:shadow-md rounded-lg hidden" id="purchase_receipt_container">
                        <label for="purchase_receipt" class="block text-sm font-medium text-gray-700 mb-1">
                            صورة الفاتورة / الإيصال
                        </label>
                        <div class="mt-1">
                            <div
                                class="flex justify-center px-6 pt-5 pb-6 border-2 border-gray-300 border-dashed rounded-lg hover:border-red-500 transition-all duration-300">
                                <div class="text-center">
                                    <label for="purchase_receipt"
                                        class="relative cursor-pointer rounded-md font-medium text-red-600 hover:text-red-500 focus-within:outline-none focus-within:ring-2 focus-within:ring-offset-2 focus-within:ring-red-500 transition-colors duration-300">
                                        <span>رفع ملف</span>
                                        <input id="purchase_receipt" name="purchase_receipt" type="file" accept="image/*" capture="environment"
                                            class="sr-only">
                                    </label>
                                    <p class="pl-1 mt-1">أو اسحب وأفلت</p>
                                    <p class="text-xs text-gray-500 mt-2">يمكنك التقاط صورة مباشرة من الكاميرا أو اختيار صورة من المعرض</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <button type="button" onclick="handleClockOutClicked()"
                        class="w-full flex items-center justify-center px-4 py-3 border border-transparent text-base font-medium rounded-lg text-white bg-red-600 hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 transition-all duration-300 transform hover:scale-105">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1">
                            </path>
                        </svg>
                        تسجيل الانصراف
                    </button>
                </form>

            {{-- Clock-In Form --}}
            @else
                <form id="clockInForm" action="{{ route('clocking.clockIn') }}" method="POST"
                    enctype="multipart/form-data" class="space-y-6 animate-fade-in">
                    @csrf

                    <!-- Clock-In Error Message (initially hidden) -->
                    <div id="clockInError"
                         class="hidden bg-red-100 text-red-800 p-3 rounded-lg mb-2">
                        <!-- Will be populated by JS if there's an error -->
                    </div>

                    <div class="space-y-4">
                        <!-- Radio button to check if car is used -->
                        <div class="mb-4">
                            <label class="block text-sm font-medium text-gray-700 mb-2">هل تستخدم سيارتك؟</label>
                            <div class="flex items-center space-x-4">
                                <div class="flex items-center">
                                    <input type="radio" id="using_car_yes" name="using_car" value="1"
                                        class="focus:ring-indigo-500 h-4 w-4 text-indigo-600 border-gray-300">
                                    <label for="using_car_yes" class="mr-2">نعم</label>
                                </div>
                                <div class="flex items-center">
                                    <input type="radio" id="using_car_no" name="using_car" value="0"
                                        class="focus:ring-indigo-500 h-4 w-4 text-indigo-600 border-gray-300" checked>
                                    <label for="using_car_no" class="mr-2">لا</label>
                                </div>
                            </div>
                        </div>

                        <!-- Clock In Miles -->
                        <div class="transition-all duration-300 hover:shadow-md rounded-lg" id="miles_in_container"
                            style="display: none;">
                            <label for="miles_in" class="block text-sm font-medium text-gray-700 mb-1">
                                أميال الحضور
                            </label>
                            <div class="relative rounded-md shadow-sm">
                                <input type="number" id="miles_in" name="miles_in"
                                    class="block w-full pl-10 pr-3 py-3 border border-gray-300 rounded-lg focus:ring-green-500 focus:border-green-500 transition-all duration-300"
                                    placeholder="أدخل عدد الأميال">
                            </div>
                        </div>

                        <!-- Clock In Image -->
                        <div class="transition-all duration-300 hover:shadow-md rounded-lg" id="image_in_container"
                            style="display: none;">
                            <label for="image_in" class="block text-sm font-medium text-gray-700 mb-1">رفع صورة</label>
                            <div class="mt-1">
                                <div
                                    class="flex justify-center px-6 pt-5 pb-6 border-2 border-gray-300 border-dashed rounded-lg hover:border-green-500 transition-all duration-300">
                                    <div class="text-center">
                                        <label for="image_in"
                                            class="relative cursor-pointer rounded-md font-medium text-green-600 hover:text-green-500 focus-within:outline-none focus-within:ring-2 focus-within:ring-offset-2 focus-within:ring-green-500 transition-colors duration-300">
                                            <span>رفع ملف</span>
                                            <input id="image_in" name="image_in" type="file" accept="image/*" capture="environment"
                                                class="sr-only">
                                        </label>
                                        <p class="pl-1 mt-1">أو اسحب وأفلت</p>
                                        <p class="text-xs text-gray-500 mt-2">يمكنك التقاط صورة مباشرة من الكاميرا أو اختيار صورة من المعرض</p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <button type="button" onclick="handleClockInClicked()"
                            class="w-full flex items-center justify-center px-4 py-3 border border-transparent text-base font-medium rounded-lg text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 transition-all duration-300 transform hover:scale-105">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1"></path>
                            </svg>
                            تسجيل الحضور
                        </button>
                    </div>
                </form>
            @endif
        </div>
    </div>

    <!-- Confirmation Modal -->
    <div id="confirmationModal" class="fixed inset-0 flex items-center justify-center z-50 hidden animate-fade-in"
        aria-labelledby="modal-title" role="dialog" aria-modal="true">
        <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity"></div>
        <div class="bg-white rounded-lg shadow-xl transform transition-all sm:max-w-lg sm:w-full m-4">
            <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                <div class="sm:flex sm:items-start">
                    <div
                        class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-blue-100 sm:mx-0 sm:h-10 sm:w-10">
                        <svg class="h-6 w-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                        </svg>
                    </div>
                    <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left">
                        <h3 class="text-lg leading-6 font-medium text-gray-900" id="modal-title">
                            تأكيد الإجراء
                        </h3>
                        <div class="mt-2">
                            <p class="text-sm text-gray-500" id="confirmationMessage">
                                هل أنت متأكد أنك تريد المتابعة؟
                            </p>
                        </div>
                    </div>
                </div>
            </div>
            <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                <button type="button" id="confirmButton"
                    class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-blue-600 text-base font-medium text-white hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 sm:ml-3 sm:w-auto sm:text-sm transition-colors duration-300">
                    تأكيد
                </button>
                <button type="button" onclick="hideConfirmation()"
                    class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm transition-colors duration-300">
                    إلغاء
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

        // -----------------------------------------
        // Confirmation Modal
        // -----------------------------------------
        function showConfirmation(action) {
            const modal = document.getElementById('confirmationModal');
            const confirmButton = document.getElementById('confirmButton');
            const message = document.getElementById('confirmationMessage');

            if (action === 'in') {
                message.textContent = 'هل أنت متأكد أنك تريد تسجيل الحضور؟';
                confirmButton.onclick = () => document.getElementById('clockInForm').submit();
            } else {
                message.textContent = 'هل أنت متأكد أنك تريد تسجيل الانصراف؟';
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
                    clockInErrorBox.innerText =
                        'عند اختيار "نعم" لاستخدام السيارة، يجب إدخال الأميال ورفع الصورة.';
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
                    clockOutErrorBox.innerText =
                        'عند الانصراف باستخدام السيارة، يجب إدخال الأميال ورفع الصورة.';
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
                    clockOutErrorBox.innerText =
                        'عند اختيار "نعم" للشراء، يجب إدخال تكلفة المشتريات ورفع صورة الفاتورة.';
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
