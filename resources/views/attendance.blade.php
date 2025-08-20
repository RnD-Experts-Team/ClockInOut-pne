@extends('layouts.app')

@section('title', 'سجل الحضور')

@section('content')
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8" dir="rtl">
        <div class="sm:flex sm:items-center sm:justify-between">
            <div>
                <h1 class="text-2xl font-bold text-black-900 sm:text-3xl">سجل الحضور</h1>
                <p class="mt-2 text-sm text-black-700">عرض سجلات الحضور والانصراف الخاصة بك</p>
            </div>
        </div>

        <!-- Date Filter Section -->
        <div class="mt-8 bg-orange-50 ring-1 ring-orange-900/5 rounded-xl p-8 transition-all duration-300 hover:shadow-xl border border-orange-100">
            <form method="GET" action="{{ route('attendance.index') }}" id="filter-form" class="space-y-8">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 md:gap-8">
                    <!-- Start Date -->
                    <div class="space-y-3">
                        <label for="start_date" class="block text-sm font-semibold text-black-800">تاريخ البداية</label>
                        <div class="relative">
                            <input type="date" name="start_date" id="start_date"
                                   value="{{ request('start_date', Carbon\Carbon::now()->startOfWeek()->format('Y-m-d')) }}"
                                   class="block w-full rounded-xl border-orange-200 ring-1 ring-orange-900/5 focus:border-orange-500 focus:ring-2 focus:ring-orange-500/20 sm:text-sm transition-all duration-200 hover:border-orange-400 py-3 px-4">
                        </div>
                    </div>
                    <!-- End Date -->
                    <div class="space-y-3">
                        <label for="end_date" class="block text-sm font-semibold text-black-800">تاريخ النهاية</label>
                        <div class="relative">
                            <input type="date" name="end_date" id="end_date"
                                   value="{{ request('end_date', Carbon\Carbon::now()->endOfWeek()->format('Y-m-d')) }}"
                                   class="block w-full rounded-xl border-orange-200 ring-1 ring-orange-900/5 focus:border-orange-500 focus:ring-2 focus:ring-orange-500/20 sm:text-sm transition-all duration-200 hover:border-orange-400 py-3 px-4">
                        </div>
                    </div>
                </div>
                <!-- Filter Buttons -->
                <div class="flex justify-end mt-8 space-x-4 rtl:space-x-reverse">
                    <a href="{{ route('attendance.index') }}"
                       class="inline-flex items-center px-5 py-3 border border-orange-200 text-sm font-medium rounded-xl text-black-700 bg-orange-50 hover:bg-orange-100 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-orange-500 active:bg-orange-100 transition-all duration-200 transform hover:scale-[1.02] hover:shadow-md">
                        <svg class="-ml-1 mr-2 rtl:ml-2 rtl:-mr-1 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                        </svg>
                        إعادة تعيين
                    </a>
                    <button type="submit"
                            class="inline-flex items-center px-5 py-3 border border-transparent text-sm font-medium rounded-xl text-white bg-orange-600 hover:bg-orange-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-orange-500 active:bg-orange-800 transition-all duration-200 transform hover:scale-[1.02] hover:shadow-md">
                        <svg class="-ml-1 mr-2 rtl:ml-2 rtl:-mr-1 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z" />
                        </svg>
                        تطبيق الفلتر
                    </button>
                </div>
            </form>
        </div>

        <!-- Records Section -->
        <div class="mt-8">
            @if($clockings->count() > 0)
                <!-- Desktop Table View -->
                <div class="hidden sm:block">
                    <div class="overflow-hidden bg-orange-50 ring-1 ring-orange-900/5 rounded-lg">
                        <table class="min-w-full divide-y divide-orange-200">
                            <thead class="bg-orange-50">
                            <tr>
                                <th scope="col" class="py-3.5 pr-4 pl-3 text-right text-sm font-semibold text-black-900">التاريخ</th>
                                <th scope="col" class="px-3 py-3.5 text-right text-sm font-semibold text-black-900">أميال الحضور</th>
                                <th scope="col" class="px-3 py-3.5 text-right text-sm font-semibold text-black-900">أميال الانصراف</th>
                                <th scope="col" class="px-3 py-3.5 text-right text-sm font-semibold text-black-900">إجمالي الأميال</th>
                                <th scope="col" class="px-3 py-3.5 text-right text-sm font-semibold text-black-900">مدفوعات البنزين</th>
                                <th scope="col" class="px-3 py-3.5 text-right text-sm font-semibold text-black-900">تكلفة المشتريات</th>
                                <th scope="col" class="px-3 py-3.5 text-right text-sm font-semibold text-black-900">مدة الدوام</th>
                                <th scope="col" class="px-3 py-3.5 text-right text-sm font-semibold text-black-900">إجمالي الراتب</th>
                                <th scope="col" class="px-3 py-3.5 text-right text-sm font-semibold text-black-900">الصور</th>
                            </tr>
                            </thead>
                            <tbody class="divide-y divide-orange-200 bg-orange-50">
                            @foreach($clockings as $clocking)
                                <tr>
                                    <td class="whitespace-nowrap py-4 pr-4 pl-3 text-sm text-black-900">
                                        {{ \Carbon\Carbon::parse($clocking->clock_in)->format('Y-m-d') }}
                                    </td>
                                    <td class="whitespace-nowrap px-3 py-4 text-sm text-black-500">
                                        {{ $clocking->miles_in ?? '-' }}
                                    </td>
                                    <td class="whitespace-nowrap px-3 py-4 text-sm text-black-500">
                                        {{ $clocking->miles_out ?? '-' }}
                                    </td>
                                    <td class="whitespace-nowrap px-3 py-4 text-sm text-black-500">
                                        {{ $clocking->total_miles ?? '-' }}
                                    </td>
                                    <td class="whitespace-nowrap px-3 py-4 text-sm text-black-500">
                                        ${{ number_format($clocking->gas_payment ?? 0, 2) }}
                                    </td>
                                    <td class="whitespace-nowrap px-3 py-4 text-sm text-black-500">
                                        ${{ number_format($clocking->purchase_cost ?? 0, 2) }}
                                    </td>
                                    <td class="whitespace-nowrap px-3 py-4 text-sm text-black-500">
                                        {{ $clocking->total_hours }}
                                    </td>
                                    <td class="whitespace-nowrap px-3 py-4 text-sm text-black-500">
                                        ${{ number_format($clocking->total_salary ?? 0, 2) }}
                                    </td>
                                    <td class="whitespace-nowrap px-3 py-4 text-sm text-black-500">
                                        <div class="flex flex-row space-x-2">
                                            @if ($clocking->image_in)
                                                <a href="{{ asset('storage/' . $clocking->image_in) }}" target="_blank" class="group">
                                                    <img src="{{ asset('storage/' . $clocking->image_in) }}" alt="Clock In Image" class="h-10 w-10 rounded-lg object-cover ring-1 ring-orange-200 hover:ring-orange-500">
                                                </a>
                                            @endif
                                            @if ($clocking->image_out)
                                                <a href="{{ asset('storage/' . $clocking->image_out) }}" target="_blank" class="group">
                                                    <img src="{{ asset('storage/' . $clocking->image_out) }}" alt="Clock Out Image" class="h-10 w-10 rounded-lg object-cover ring-1 ring-orange-200 hover:ring-orange-500">
                                                </a>
                                            @endif
                                            @if ($clocking->purchase_receipt)
                                                <a href="{{ asset('storage/' . $clocking->purchase_receipt) }}" target="_blank" class="group">
                                                    <img src="{{ asset('storage/' . $clocking->purchase_receipt) }}" alt="Receipt" class="h-10 w-10 rounded-lg object-cover ring-1 ring-orange-200 hover:ring-orange-500">
                                                </a>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Mobile Card View -->
                <div class="sm:hidden space-y-4">
                    @foreach($clockings as $clocking)
                        <div class="bg-orange-50 ring-1 ring-orange-900/5 rounded-lg p-4 border border-orange-200">
                            <div class="flex justify-between items-start">
                                <div class="text-sm font-medium text-black-900">
                                    {{ \Carbon\Carbon::parse($clocking->clock_in)->format('Y-m-d') }}
                                </div>
                                <div class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-orange-100 text-black-800">
                                    {{ $clocking->total_hours }}
                                </div>
                            </div>
                            <dl class="mt-3 grid grid-cols-2 gap-4">
                                <div>
                                    <dt class="text-xs font-medium text-black-500">أميال الحضور</dt>
                                    <dd class="mt-1 text-sm text-black-900">{{ $clocking->miles_in ?? '-' }}</dd>
                                </div>
                                <div>
                                    <dt class="text-xs font-medium text-black-500">أميال الانصراف</dt>
                                    <dd class="mt-1 text-sm text-black-900">{{ $clocking->miles_out ?? '-' }}</dd>
                                </div>
                                <div>
                                    <dt class="text-xs font-medium text-black-500">إجمالي الأميال</dt>
                                    <dd class="mt-1 text-sm text-black-900">{{ $clocking->total_miles ?? '-' }}</dd>
                                </div>
                                <div>
                                    <dt class="text-xs font-medium text-black-500">مدفوعات البنزين</dt>
                                    <dd class="mt-1 text-sm text-black-900">${{ number_format($clocking->gas_payment ?? 0, 2) }}</dd>
                                </div>
                                <div>
                                    <dt class="text-xs font-medium text-black-500">تكلفة المشتريات</dt>
                                    <dd class="mt-1 text-sm text-black-900">${{ number_format($clocking->purchase_cost ?? 0, 2) }}</dd>
                                </div>
                                <div>
                                    <dt class="text-xs font-medium text-black-500">إجمالي الراتب</dt>
                                    <dd class="mt-1 text-sm text-black-900">${{ number_format($clocking->total_salary ?? 0, 2) }}</dd>
                                </div>
                            </dl>
                            @if($clocking->image_in || $clocking->image_out || $clocking->purchase_receipt)
                                <div class="mt-4">
                                    <dt class="text-xs font-medium text-black-500 mb-2">الصور</dt>
                                    <div class="flex flex-row space-x-2">
                                        @if ($clocking->image_in)
                                            <a href="{{ asset('storage/' . $clocking->image_in) }}" target="_blank" class="group">
                                                <img src="{{ asset('storage/' . $clocking->image_in) }}" alt="Clock In Image" class="h-10 w-10 rounded-lg object-cover ring-1 ring-orange-200 hover:ring-orange-500">
                                            </a>
                                        @endif
                                        @if ($clocking->image_out)
                                            <a href="{{ asset('storage/' . $clocking->image_out) }}" target="_blank" class="group">
                                                <img src="{{ asset('storage/' . $clocking->image_out) }}" alt="Clock Out Image" class="h-10 w-10 rounded-lg object-cover ring-1 ring-orange-200 hover:ring-orange-500">
                                            </a>
                                        @endif
                                        @if ($clocking->purchase_receipt)
                                            <a href="{{ asset('storage/' . $clocking->purchase_receipt) }}" target="_blank" class="group">
                                                <img src="{{ asset('storage/' . $clocking->purchase_receipt) }}" alt="Receipt" class="h-10 w-10 rounded-lg object-cover ring-1 ring-orange-200 hover:ring-orange-500">
                                            </a>
                                        @endif
                                    </div>
                                </div>
                            @endif
                        </div>
                    @endforeach
                </div>

                <!-- Pagination -->
                <div class="mt-8">
                    {{ $clockings->links('vendor.pagination.custom') }}
                </div>
            @else
                <!-- Empty State -->
                <div class="text-center bg-orange-50 rounded-lg ring-1 ring-orange-900/5 p-12 border border-orange-100">
                    <svg class="mx-auto h-12 w-12 text-black-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <h3 class="mt-2 text-sm font-semibold text-black-900">لا توجد سجلات حضور</h3>
                    <p class="mt-1 text-sm text-black-500">لم يتم تسجيل أي حضور أو انصراف بعد</p>
                </div>
            @endif
        </div>
    </div>

    <!-- Add this script at the bottom of the file -->
    <script>
        // Auto-submit form when dates change
        document.getElementById('start_date').addEventListener('change', submitForm);
        document.getElementById('end_date').addEventListener('change', submitForm);

        function submitForm() {
            document.getElementById('filter-form').submit();
        }
    </script>
    <style>
        /* RTL Support for Tailwind Classes */
        [dir="rtl"] .sm\:text-right {
            text-align: right;
        }

        [dir="rtl"] .sm\:pr-4 {
            padding-right: 1rem;
        }

        [dir="rtl"] .sm\:pl-3 {
            padding-left: 0.75rem;
        }
    </style>
@endsection
