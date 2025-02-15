@extends('layouts.app')

@section('title', 'سجل الحضور')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8" dir="rtl">
    <div class="sm:flex sm:items-center sm:justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-900 sm:text-3xl">سجل الحضور</h1>
            <p class="mt-2 text-sm text-gray-700">عرض سجلات الحضور والانصراف الخاصة بك</p>
        </div>
        
        {{-- <!-- Total Hours Summary -->
        <div class="mt-4 sm:mt-0 bg-white rounded-lg shadow-sm p-4 border border-gray-200">
            <div class="text-sm font-medium text-gray-500">إجمالي ساعات العمل</div>
            <div class="mt-1 text-2xl font-semibold text-primary-600">{{ $totalHours ?? '0' }} ساعة</div>
        </div> --}}
    </div>

    <!-- Records Section -->
    <div class="mt-8">
        @if($clockings->count() > 0)
            <!-- Desktop Table View -->
            <div class="hidden sm:block">
                <div class="overflow-hidden bg-white shadow-sm ring-1 ring-black ring-opacity-5 rounded-lg">
                    <table class="min-w-full divide-y divide-gray-300">
                        <thead class="bg-gray-50">
                            <tr>
                                <th scope="col" class="py-3.5 pr-4 pl-3 text-right text-sm font-semibold text-gray-900">التاريخ</th>
                                <th scope="col" class="px-3 py-3.5 text-right text-sm font-semibold text-gray-900">أميال الحضور</th>
                                <th scope="col" class="px-3 py-3.5 text-right text-sm font-semibold text-gray-900">أميال الانصراف</th>
                                <th scope="col" class="px-3 py-3.5 text-right text-sm font-semibold text-gray-900">مدة الدوام</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 bg-white">
                            @foreach($clockings as $clocking)
                                <tr>
                                    <td class="whitespace-nowrap py-4 pr-4 pl-3 text-sm text-gray-900">
                                        {{ \Carbon\Carbon::parse($clocking->clock_in)->format('Y-m-d') }}
                                    </td>
                                    <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-500">
                                        {{ $clocking->miles_in ?? '-' }}
                                    </td>
                                    <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-500">
                                        {{ $clocking->miles_out ?? '-' }}
                                    </td>
                                    <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-500">
                                        {{ $clocking->total_hours }}
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
                    <div class="bg-white shadow-sm rounded-lg p-4 border border-gray-200">
                        <div class="flex justify-between items-start">
                            <div class="text-sm font-medium text-gray-900">
                                {{ \Carbon\Carbon::parse($clocking->clock_in)->format('Y-m-d') }}
                            </div>
                            <div class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-primary-100 text-primary-800">
                                {{ $clocking->total_hours }}
                            </div>
                        </div>
                        <dl class="mt-3 grid grid-cols-2 gap-4">
                            <div>
                                <dt class="text-xs font-medium text-gray-500">أميال الحضور</dt>
                                <dd class="mt-1 text-sm text-gray-900">{{ $clocking->miles_in ?? '-' }}</dd>
                            </div>
                            <div>
                                <dt class="text-xs font-medium text-gray-500">أميال الانصراف</dt>
                                <dd class="mt-1 text-sm text-gray-900">{{ $clocking->miles_out ?? '-' }}</dd>
                            </div>
                        </dl>
                    </div>
                @endforeach
            </div>

            <!-- Pagination -->
            <div class="mt-8">
                {{ $clockings->links('vendor.pagination.custom') }}
            </div>
        @else
            <!-- Empty State -->
            <div class="text-center bg-white rounded-lg shadow-sm p-12 border border-gray-200">
                <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <h3 class="mt-2 text-sm font-semibold text-gray-900">لا توجد سجلات حضور</h3>
                <p class="mt-1 text-sm text-gray-500">لم يتم تسجيل أي حضور أو انصراف بعد</p>
            </div>
        @endif
    </div>
</div>

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