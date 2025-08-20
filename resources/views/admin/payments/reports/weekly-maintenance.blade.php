@extends('layouts.app')

@section('title', 'Weekly Maintenance Report')

@section('content')
    <div class="min-h-screen bg-gray-50 py-8">
        <div class="max-w-full mx-auto px-4 sm:px-6 lg:px-8">
            <!-- Simple Header -->
            <div class="text-center mb-8">
                <div class="bg-[#ff671b] text-white py-4 px-8 rounded-lg inline-block mb-4">
                    <div class="flex items-center justify-center space-x-4">
                        <span class="text-xl">Week:</span>
                        <span class="text-3xl font-bold">{{ now()->weekOfYear }}</span>
                        <span class="text-3xl font-bold">{{ now()->year }}</span>
                        <span class="text-xl ml-8">Weekly Maintenance Report</span>
                    </div>
                </div>
                <p class="text-gray-600">Generated on {{ now()->format('F j, Y \a\t g:i A') }}</p>
            </div>

            @php
                // Get all stores that have payments using proper store relationships
                $stores = \App\Models\Store::whereHas('payments')
                    ->orderBy('store_number')
                    ->pluck('store_number');

                $currentWeek = now()->weekOfYear;
                $currentYear = now()->year;

                // Calculate totals for summary
                $totalEquipmentCost = 0;
                $totalServiceCost = 0;
                $totalWeeklyCost = 0;
                $totalMonthlyCost = 0;
                $totalFourWeeksCost = 0;
                $totalNinetyDaysCost = 0;
            @endphp

                <!-- Summary Statistics -->
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-8">
                <div class="bg-white rounded-lg shadow p-4 text-center">
                    <div class="text-2xl font-bold text-[#ff671b]">{{ $stores->count() }}</div>
                    <div class="text-sm text-gray-600">Active Stores</div>
                </div>
                <div class="bg-white rounded-lg shadow p-4 text-center">
                    <div class="text-2xl font-bold text-[#ff671b]">Week {{ $currentWeek }}</div>
                    <div class="text-sm text-gray-600">{{ $currentYear }}</div>
                </div>
                <div class="bg-white rounded-lg shadow p-4 text-center">
                    <div class="text-2xl font-bold text-[#ff671b]" id="total-week-cost">${{ number_format($totalWeeklyCost, 2) }}</div>
                    <div class="text-sm text-gray-600">This Week Total</div>
                </div>
                <div class="bg-white rounded-lg shadow p-4 text-center">
                    <div class="text-2xl font-bold text-[#ff671b]" id="total-month-cost">${{ number_format($totalMonthlyCost, 2) }}</div>
                    <div class="text-sm text-gray-600">This Month Total</div>
                </div>
            </div>

            <!-- Excel-like Table -->
            <div class="bg-white shadow-lg rounded-lg overflow-hidden">
                @if($stores->count() > 0)
                    <div class="overflow-x-auto">
                        <table class="min-w-full border-collapse" id="weeklyMaintenanceTable">
                            <thead>
                            <tr class="bg-[#ff671b] text-white">
                                <th class="border border-gray-300 px-4 py-3 text-center text-sm font-semibold cursor-pointer hover:bg-[#e55b17] transition-colors select-none"
                                    onclick="sortWeeklyTable(0, 'text')" id="weekly-header-0">
                                    <div class="flex items-center justify-center">
                                        Store#
                                        <span class="ml-2 text-xs opacity-75" id="weekly-sort-indicator-0">A↓</span>
                                    </div>
                                </th>
                                <th class="border border-gray-300 px-4 py-3 text-center text-sm font-semibold cursor-pointer hover:bg-[#e55b17] transition-colors select-none"
                                    onclick="sortWeeklyTable(1, 'number')" id="weekly-header-1">
                                    <div class="flex items-center justify-center">
                                        Equipment/Parts
                                        <span class="ml-2 text-xs opacity-75" id="weekly-sort-indicator-1">↑</span>
                                    </div>
                                </th>
                                <th class="border border-gray-300 px-4 py-3 text-center text-sm font-semibold cursor-pointer hover:bg-[#e55b17] transition-colors select-none"
                                    onclick="sortWeeklyTable(2, 'number')" id="weekly-header-2">
                                    <div class="flex items-center justify-center">
                                        Service
                                        <span class="ml-2 text-xs opacity-75" id="weekly-sort-indicator-2">↑</span>
                                    </div>
                                </th>
                                <th class="border border-gray-300 px-4 py-3 text-center text-sm font-semibold cursor-pointer hover:bg-[#e55b17] transition-colors select-none"
                                    onclick="sortWeeklyTable(3, 'number')" id="weekly-header-3">
                                    <div class="flex items-center justify-center">
                                        This Week
                                        <span class="ml-2 text-xs opacity-75" id="weekly-sort-indicator-3">↑</span>
                                    </div>
                                </th>
                                <th class="border border-gray-300 px-4 py-3 text-center text-sm font-semibold cursor-pointer hover:bg-[#e55b17] transition-colors select-none"
                                    onclick="sortWeeklyTable(4, 'number')" id="weekly-header-4">
                                    <div class="flex items-center justify-center">
                                        This Month
                                        <span class="ml-2 text-xs opacity-75" id="weekly-sort-indicator-4">↑</span>
                                    </div>
                                </th>
                                <th class="border border-gray-300 px-4 py-3 text-center text-sm font-semibold cursor-pointer hover:bg-[#e55b17] transition-colors select-none"
                                    onclick="sortWeeklyTable(5, 'number')" id="weekly-header-5">
                                    <div class="flex items-center justify-center">
                                        4 Weeks
                                        <span class="ml-2 text-xs opacity-75" id="weekly-sort-indicator-5">↑</span>
                                    </div>
                                </th>
                                <th class="border border-gray-300 px-4 py-3 text-center text-sm font-semibold cursor-pointer hover:bg-[#e55b17] transition-colors select-none"
                                    onclick="sortWeeklyTable(6, 'number')" id="weekly-header-6">
                                    <div class="flex items-center justify-center">
                                        90 Days
                                        <span class="ml-2 text-xs opacity-75" id="weekly-sort-indicator-6">↑</span>
                                    </div>
                                </th>
                            </tr>
                            </thead>
                            <tbody id="weeklyMaintenanceBody">
                            @foreach($stores as $index => $store)
                                @php
                                    // Get payments for different time periods - use store relationships properly
                                    $thisWeekPayments = \App\Models\Payment::whereHas('store', function($query) use ($store) {
                                            $query->where('store_number', $store);
                                        })
                                        ->orWhere('store', $store)
                                        ->whereYear('date', $currentYear)
                                        ->where(DB::raw('WEEK(date)'), $currentWeek)
                                        ->get();

                                    $thisMonthPayments = \App\Models\Payment::whereHas('store', function($query) use ($store) {
                                            $query->where('store_number', $store);
                                        })
                                        ->orWhere('store', $store)
                                        ->thisMonth()
                                        ->get();

                                    $fourWeeksPayments = \App\Models\Payment::whereHas('store', function($query) use ($store) {
                                            $query->where('store_number', $store);
                                        })
                                        ->orWhere('store', $store)
                                        ->within4Weeks()
                                        ->get();

                                    $ninetyDaysPayments = \App\Models\Payment::whereHas('store', function($query) use ($store) {
                                            $query->where('store_number', $store);
                                        })
                                        ->orWhere('store', $store)
                                        ->within90Days()
                                        ->get();

                                    $equipmentCost = $thisWeekPayments->where('maintenance_type', 'Equipment/Parts')->sum('cost');
                                    $serviceCost = $thisWeekPayments->where('maintenance_type', 'Service')->sum('cost');
                                    $totalCost = $thisWeekPayments->sum('cost');
                                    $thisMonthCost = $thisMonthPayments->sum('cost');
                                    $fourWeeksCost = $fourWeeksPayments->sum('cost');
                                    $ninetyDaysCost = $ninetyDaysPayments->sum('cost');

                                    // Add to totals
                                    $totalEquipmentCost += $equipmentCost;
                                    $totalServiceCost += $serviceCost;
                                    $totalWeeklyCost += $totalCost;
                                    $totalMonthlyCost += $thisMonthCost;
                                    $totalFourWeeksCost += $fourWeeksCost;
                                    $totalNinetyDaysCost += $ninetyDaysCost;
                                @endphp
                                <tr class="{{ $index % 2 == 0 ? 'bg-gray-50' : 'bg-white' }} hover:bg-[#fff4ed]">
                                    <td class="border border-gray-300 px-4 py-3 text-sm text-center font-medium" data-sort="{{ $store }}">
                                        <div class="flex items-center justify-center">
                                            <div class="w-3 h-3 bg-[#ff671b] rounded-full mr-2"></div>
                                            {{ $store }}
                                        </div>
                                    </td>

                                    <!-- Equipment Cost -->
                                    <td class="border border-gray-300 px-4 py-3 text-sm text-center" data-sort="{{ $equipmentCost }}">
                                        @if($equipmentCost > 0)
                                            <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-[#fff4ed] text-[#ff671b] border border-[#ff671b]">
                                        ${{ number_format($equipmentCost, 2) }}
                                    </span>
                                        @else
                                            <span class="text-gray-400">$0.00</span>
                                        @endif
                                    </td>

                                    <!-- Service Cost -->
                                    <td class="border border-gray-300 px-4 py-3 text-sm text-center" data-sort="{{ $serviceCost }}">
                                        @if($serviceCost > 0)
                                            <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-[#fff4ed] text-[#ff671b] border border-[#ff671b]">
                                        ${{ number_format($serviceCost, 2) }}
                                    </span>
                                        @else
                                            <span class="text-gray-400">$0.00</span>
                                        @endif
                                    </td>

                                    <!-- This Week Total -->
                                    <td class="border border-gray-300 px-4 py-3 text-sm text-center" data-sort="{{ $totalCost }}">
                                        @if($totalCost > 0)
                                            <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-semibold
                                        @if($totalCost >= 1000) bg-red-100 text-red-800 border-red-200
                                        @elseif($totalCost >= 500) bg-orange-100 text-orange-800 border-orange-200
                                        @else bg-[#fff4ed] text-[#ff671b] border-[#ff671b] @endif border">
                                        ${{ number_format($totalCost, 2) }}
                                    </span>
                                        @else
                                            <span class="text-gray-400">$0.00</span>
                                        @endif
                                    </td>

                                    <!-- This Month Cost -->
                                    <td class="border border-gray-300 px-4 py-3 text-sm text-center" data-sort="{{ $thisMonthCost }}">
                                        @if($thisMonthCost > 0)
                                            <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-[#fff4ed] text-[#ff671b] border border-[#ff671b]">
                                        ${{ number_format($thisMonthCost, 2) }}
                                    </span>
                                        @else
                                            <span class="text-gray-400">$0.00</span>
                                        @endif
                                    </td>

                                    <!-- Four Weeks Cost -->
                                    <td class="border border-gray-300 px-4 py-3 text-sm text-center" data-sort="{{ $fourWeeksCost }}">
                                        @if($fourWeeksCost > 0)
                                            <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium
                                        @if($fourWeeksCost > 1000) bg-orange-100 text-orange-800 border-orange-200
                                        @else bg-[#fff4ed] text-[#ff671b] border-[#ff671b] @endif border">
                                        ${{ number_format($fourWeeksCost, 2) }}
                                    </span>
                                        @else
                                            <span class="text-gray-400">$0.00</span>
                                        @endif
                                    </td>

                                    <!-- Ninety Days Cost -->
                                    <td class="border border-gray-300 px-4 py-3 text-sm text-center" data-sort="{{ $ninetyDaysCost }}">
                                        @if($ninetyDaysCost > 0)
                                            <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium
                                        @if($ninetyDaysCost > 3000) bg-red-100 text-red-800 border-red-200
                                        @elseif($ninetyDaysCost > 1500) bg-orange-100 text-orange-800 border-orange-200
                                        @else bg-[#fff4ed] text-[#ff671b] border-[#ff671b] @endif border">
                                        ${{ number_format($ninetyDaysCost, 2) }}
                                    </span>
                                        @else
                                            <span class="text-gray-400">$0.00</span>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                            </tbody>

                            <!-- Totals Footer -->
                            <tfoot>
                            <tr class="bg-[#e55b17] text-white font-bold">
                                <td class="border border-gray-300 px-4 py-4 text-sm text-center">
                                    TOTALS ({{ $stores->count() }} stores)
                                </td>
                                <td class="border border-gray-300 px-4 py-4 text-sm text-center">
                                    ${{ number_format($totalEquipmentCost, 2) }}
                                </td>
                                <td class="border border-gray-300 px-4 py-4 text-sm text-center">
                                    ${{ number_format($totalServiceCost, 2) }}
                                </td>
                                <td class="border border-gray-300 px-4 py-4 text-sm text-center text-lg">
                                    ${{ number_format($totalWeeklyCost, 2) }}
                                </td>
                                <td class="border border-gray-300 px-4 py-4 text-sm text-center">
                                    ${{ number_format($totalMonthlyCost, 2) }}
                                </td>
                                <td class="border border-gray-300 px-4 py-4 text-sm text-center">
                                    ${{ number_format($totalFourWeeksCost, 2) }}
                                </td>
                                <td class="border border-gray-300 px-4 py-4 text-sm text-center">
                                    ${{ number_format($totalNinetyDaysCost, 2) }}
                                </td>
                            </tr>
                            </tfoot>
                        </table>
                    </div>
                @else
                    <div class="text-center py-12">
                        <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                        <h3 class="mt-2 text-sm font-medium text-gray-900">No maintenance data</h3>
                        <p class="mt-1 text-sm text-gray-500">No stores have recorded maintenance payments yet.</p>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Update summary totals
            document.getElementById('total-week-cost').textContent = '${{ number_format($totalWeeklyCost, 2) }}';
            document.getElementById('total-month-cost').textContent = '${{ number_format($totalMonthlyCost, 2) }}';

            let weeklySortDirection = {};
            window.sortWeeklyTable = function(columnIndex, type) {
                const table = document.getElementById('weeklyMaintenanceTable');
                const tbody = document.getElementById('weeklyMaintenanceBody');
                if (!table || !tbody) return;
                const rows = Array.from(tbody.querySelectorAll('tr'));
                if (rows.length === 0) return;

                // Toggle sort direction
                const currentDirection = weeklySortDirection[columnIndex] || 'asc';
                const newDirection = currentDirection === 'asc' ? 'desc' : 'asc';
                weeklySortDirection[columnIndex] = newDirection;

                // Clear all sort indicators
                for (let i = 0; i < 7; i++) {
                    const indicator = document.getElementById(`weekly-sort-indicator-${i}`);
                    if (indicator) {
                        indicator.textContent = i === 0 ? 'A↓' : '↑';
                        indicator.style.opacity = '0.5';
                    }
                }

                const activeIndicator = document.getElementById(`weekly-sort-indicator-${columnIndex}`);
                if (activeIndicator) {
                    if (type === 'number') {
                        activeIndicator.textContent = newDirection === 'asc' ? '↑' : '↓';
                    } else {
                        activeIndicator.textContent = newDirection === 'asc' ? 'A↓' : 'Z↑';
                    }
                    activeIndicator.style.opacity = '1';
                }

                // Sort rows
                rows.sort((a, b) => {
                    let aValue, bValue;
                    if (type === 'number') {
                        aValue = parseFloat(a.cells[columnIndex].getAttribute('data-sort')) || 0;
                        bValue = parseFloat(b.cells[columnIndex].getAttribute('data-sort')) || 0;
                    } else {
                        aValue = (a.cells[columnIndex].getAttribute('data-sort') || '').toLowerCase();
                        bValue = (b.cells[columnIndex].getAttribute('data-sort') || '').toLowerCase();
                    }
                    if (newDirection === 'asc') return aValue > bValue ? 1 : -1;
                    else return aValue < bValue ? 1 : -1;
                });

                // Re-append sorted rows with alternating colors
                rows.forEach((row, idx) => {
                    row.className = (idx % 2 == 0 ? 'bg-gray-50' : 'bg-white') + ' hover:bg-[#fff4ed]';
                    tbody.appendChild(row);
                });
            };
        });
    </script>
@endsection
