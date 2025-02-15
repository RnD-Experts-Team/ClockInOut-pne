@extends('layouts.app')

@section('title', 'الحضور')

@include('components.toast-notification')

@section('content')
<div class="max-w-xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <div class="bg-white rounded-2xl shadow-lg p-6 sm:p-8 transition-all duration-300 hover:shadow-xl">
        <!-- Status Header -->
        <div class="text-center mb-8 animate-fade-in">
            <div class="inline-flex items-center justify-center w-20 h-20 rounded-full mb-4 {{ $clocking ? 'bg-red-100' : 'bg-green-100' }} transition-colors duration-300">
                <svg class="w-10 h-10 {{ $clocking ? 'text-red-500' : 'text-green-500' }} transition-colors duration-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
            </div>
            <h2 class="text-3xl sm:text-4xl font-bold text-gray-900 mb-2 transition-all duration-300">
                {{ $clocking ? 'تسجيل الانصراف' : 'تسجيل الحضور' }}
            </h2>
            <p class="text-gray-500 text-lg transition-all duration-300">
                {{ $clocking ? 'سجل تفاصيل نهاية دوامك' : 'ابدأ تسجيل دوامك' }}
            </p>
        </div>

        @if ($clocking)
        <!-- Clock Out Form -->
        <form id="clockOutForm" action="{{ route('clocking.clockOut') }}" method="POST" enctype="multipart/form-data" class="space-y-6 animate-fade-in">
            @csrf
            <div class="space-y-4">
                <div class="transition-all duration-300 hover:shadow-md rounded-lg">
                    <label for="miles_out" class="block text-sm font-medium text-gray-700 mb-1">أميال الانصراف</label>
                    <div class="relative rounded-md shadow-sm">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path>
                            </svg>
                        </div>
                        <input type="number" id="miles_out" name="miles_out" class="block w-full pl-10 pr-3 py-3 border border-gray-300 rounded-lg focus:ring-red-500 focus:border-red-500 transition-all duration-300" placeholder="أدخل عدد الأميال">
                    </div>
                </div>

                <div class="transition-all duration-300 hover:shadow-md rounded-lg">
                    <label for="image_out" class="block text-sm font-medium text-gray-700 mb-1">رفع صورة</label>
                    <div class="mt-1">
                        <div class="flex justify-center px-6 pt-5 pb-6 border-2 border-gray-300 border-dashed rounded-lg hover:border-red-500 transition-all duration-300">
                            <div class="space-y-1 text-center">
                                <svg class="mx-auto h-12 w-12 text-gray-400" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                </svg>
                                <div class="flex text-sm text-gray-600">
                                    <label for="image_out" class="relative cursor-pointer rounded-md font-medium text-red-600 hover:text-red-500 focus-within:outline-none focus-within:ring-2 focus-within:ring-offset-2 focus-within:ring-red-500 transition-colors duration-300">
                                        <span>رفع ملف</span>
                                        <input id="image_out" name="image_out" type="file" accept="image/*" class="sr-only">
                                    </label>
                                    <p class="pl-1">أو اسحب وأفلت</p>
                                </div>
                                <p class="text-xs text-gray-500">PNG, JPG, GIF حتى 10 ميجابايت</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <button type="button" onclick="showConfirmation('out')" class="w-full flex items-center justify-center px-4 py-3 border border-transparent text-base font-medium rounded-lg text-white bg-red-600 hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 transition-all duration-300 transform hover:scale-105">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path>
                </svg>
                تسجيل الانصراف
            </button>
        </form>
        @else
        <!-- Clock In Form -->
        <form id="clockInForm" action="{{ route('clocking.clockIn') }}" method="POST" enctype="multipart/form-data" class="space-y-6 animate-fade-in">
            @csrf
            <div class="space-y-4">
                <div class="transition-all duration-300 hover:shadow-md rounded-lg">
                    <label for="miles_in" class="block text-sm font-medium text-gray-700 mb-1">أميال الحضور</label>
                    <div class="relative rounded-md shadow-sm">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path>
                            </svg>
                        </div>
                        <input type="number" id="miles_in" name="miles_in" class="block w-full pl-10 pr-3 py-3 border border-gray-300 rounded-lg focus:ring-green-500 focus:border-green-500 transition-all duration-300" placeholder="أدخل عدد الأميال">
                    </div>
                </div>

                <div class="transition-all duration-300 hover:shadow-md rounded-lg">
                    <label for="image_in" class="block text-sm font-medium text-gray-700 mb-1">رفع صورة</label>
                    <div class="mt-1">
                        <div class="flex justify-center px-6 pt-5 pb-6 border-2 border-gray-300 border-dashed rounded-lg hover:border-green-500 transition-all duration-300">
                            <div class="space-y-1 text-center">
                                <svg class="mx-auto h-12 w-12 text-gray-400" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                </svg>
                                <div class="flex text-sm text-gray-600">
                                    <label for="image_in" class="relative cursor-pointer rounded-md font-medium text-green-600 hover:text-green-500 focus-within:outline-none focus-within:ring-2 focus-within:ring-offset-2 focus-within:ring-green-500 transition-colors duration-300">
                                        <span>رفع ملف</span>
                                        <input id="image_in" name="image_in" type="file" accept="image/*" class="sr-only">
                                    </label>
                                    <p class="pl-1">أو اسحب وأفلت</p>
                                </div>
                                <p class="text-xs text-gray-500">PNG, JPG, GIF حتى 10 ميجابايت</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <button type="button" onclick="showConfirmation('in')" class="w-full flex items-center justify-center px-4 py-3 border border-transparent text-base font-medium rounded-lg text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 transition-all duration-300 transform hover:scale-105">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1"></path>
                </svg>
                تسجيل الحضور
            </button>
        </form>
        @endif
    </div>
</div>

<!-- Confirmation Modal -->
<div id="confirmationModal" class="fixed inset-0 flex items-center justify-center z-50 hidden animate-fade-in" aria-labelledby="modal-title" role="dialog" aria-modal="true">
    <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity"></div>
    <div class="bg-white rounded-lg shadow-xl transform transition-all sm:max-w-lg sm:w-full m-4">
        <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
            <div class="sm:flex sm:items-start">
                <div class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-blue-100 sm:mx-0 sm:h-10 sm:w-10">
                    <svg class="h-6 w-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
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
            <button type="button" id="confirmButton" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-blue-600 text-base font-medium text-white hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 sm:ml-3 sm:w-auto sm:text-sm transition-colors duration-300">
                تأكيد
            </button>
            <button type="button" onclick="hideConfirmation()" class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm transition-colors duration-300">
                إلغاء
            </button>
        </div>
    </div>
</div>

<script>
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

    let counterInterval;

    function startCounter() {
        const counterContainer = document.getElementById('clockCounterContainer');
        counterContainer.classList.remove('hidden');

        if (counterInterval) clearInterval(counterInterval);

        counterInterval = setInterval(updateCounter, 1000);
    }

    function updateCounter() {
        const clockCounter = document.getElementById('clockCounter');
        const clockInTime = localStorage.getItem('clockInTime');
        if (!clockInTime) {
            clockCounter.textContent = "00:00:00";
            return;
        }
        const startTime = new Date(clockInTime);
        const now = new Date();
        const diff = now - startTime;

        const hours = Math.floor(diff / (1000 * 60 * 60));
        const minutes = Math.floor((diff % (1000 * 60 * 60)) / (1000 * 60));
        const seconds = Math.floor((diff % (1000 * 60)) / 1000);

        clockCounter.textContent =
            String(hours).padStart(2, '0') + ":" +
            String(minutes).padStart(2, '0') + ":" +
            String(seconds).padStart(2, '0');
    }

    function clockIn() {
        if (!localStorage.getItem('clockInTime')) {
            localStorage.setItem('clockInTime', new Date().toISOString());
        }
        startCounter();
        showConfirmation('in');
    }

    function clockOut() {
        localStorage.removeItem('clockInTime');
        if (counterInterval) clearInterval(counterInterval);
        document.getElementById('clockCounterContainer').classList.add('hidden');
        showConfirmation('out');
    }

    document.addEventListener('DOMContentLoaded', function() {
        if (localStorage.getItem('clockInTime')) {
            startCounter();
        }
    });
</script>

<style>
    @keyframes fadeIn {
        from { opacity: 0; }
        to { opacity: 1; }
    }

    .animate-fade-in {
        animation: fadeIn 0.3s ease-out;
    }
</style>

@endsection

