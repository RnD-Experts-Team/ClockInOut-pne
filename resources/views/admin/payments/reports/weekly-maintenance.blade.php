@extends('layouts.app')

@section('title', 'Weekly Maintenance Report')

@section('content')
    <div class="min-h-screen bg-gray-50 py-8">
        <div class="max-w-full mx-auto px-4 sm:px-6 lg:px-8">
            <!-- Simple Header -->

            <!-- Summary Statistics -->
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-8">
                <div class="bg-white rounded-lg shadow p-4 text-center">
                    <div class="text-2xl font-bold text-[#ff671b]">{{ $storeNames->count() }}</div>
                    <div class="text-sm text-gray-600">Active Stores</div>
                </div>
                <div class="bg-white rounded-lg shadow p-4 text-center">
                    <div class="text-2xl font-bold text-[#ff671b]">Week {{ $currentWeek }}</div>
                    <div class="text-sm text-gray-600">{{ $targetYear }}</div>
                </div>
                <div class="bg-white rounded-lg shadow p-4 text-center">
                    <div class="text-2xl font-bold text-[#ff671b]">${{ number_format($totalWeeklyCost, 2) }}</div>
                    <div class="text-sm text-gray-600">
                        @if(request('date_from') && request('date_to'))
                            Filtered Period Total
                        @else
                            This Week Total
                        @endif
                    </div>
                </div>
                <div class="bg-white rounded-lg shadow p-4 text-center">
                    <div class="text-2xl font-bold text-[#ff671b]">${{ number_format($totalMonthlyCost, 2) }}</div>
                    <div class="text-sm text-gray-600">This Month Total</div>
                </div>
            </div>

            <!-- Excel-like Table -->
            <div class="bg-white shadow-lg rounded-lg overflow-hidden">
                @if($storeNames->count() > 0)
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
                                        @if(request('date_from') && request('date_to'))
                                            Filtered Period
                                        @else
                                            This Week
                                        @endif
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
                            @foreach($storeData as $index => $data)
                                <tr class="{{ $index % 2 == 0 ? 'bg-gray-50' : 'bg-white' }} hover:bg-[#fff4ed]">

                                    <td class="border border-gray-300 px-4 py-3 text-sm text-center font-medium" data-sort="{{ $data['store'] }}">
                                        <div class="flex flex-col items-center justify-center">
                                            <div class="flex items-center">
                                                <div class="w-3 h-3 bg-[#ff671b] rounded-full mr-2"></div>
                                                <span class="font-semibold">{{ $data['store'] }}</span>
                                            </div>
                                            <span class="text-xs text-gray-500 mt-1">Store #{{ $data['store_number'] }}</span>
                                        </div>
                                    </td>

                                    <!-- Equipment Cost -->
                                    <td class="border border-gray-300 px-4 py-3 text-sm text-center" data-sort="{{ $data['equipment_cost'] }}">
                                        @if($data['equipment_cost'] > 0)
                                            <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-[#fff4ed] text-[#ff671b] border border-[#ff671b]">
                                                ${{ number_format($data['equipment_cost'], 2) }}
                                            </span>
                                        @else
                                            <span class="text-gray-400">$0.00</span>
                                        @endif
                                    </td>

                                    <!-- Service Cost -->
                                    <td class="border border-gray-300 px-4 py-3 text-sm text-center" data-sort="{{ $data['service_cost'] }}">
                                        @if($data['service_cost'] > 0)
                                            <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-[#fff4ed] text-[#ff671b] border border-[#ff671b]">
                                                ${{ number_format($data['service_cost'], 2) }}
                                            </span>
                                        @else
                                            <span class="text-gray-400">$0.00</span>
                                        @endif
                                    </td>

                                    <!-- This Week/Filtered Period Total -->
                                    <td class="border border-gray-300 px-4 py-3 text-sm text-center" data-sort="{{ $data['total_cost'] }}">
                                        @if($data['total_cost'] > 0)
                                            <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-semibold
                                                @if($data['total_cost'] >= 1000) bg-red-100 text-red-800 border-red-200
                                                @elseif($data['total_cost'] >= 500) bg-orange-100 text-orange-800 border-orange-200
                                                @else bg-[#fff4ed] text-[#ff671b] border-[#ff671b] @endif border">
                                                ${{ number_format($data['total_cost'], 2) }}
                                            </span>
                                        @else
                                            <span class="text-gray-400">$0.00</span>
                                        @endif
                                    </td>

                                    <!-- This Month Cost -->
                                    <td class="border border-gray-300 px-4 py-3 text-sm text-center" data-sort="{{ $data['this_month_cost'] }}">
                                        @if($data['this_month_cost'] > 0)
                                            <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-[#fff4ed] text-[#ff671b] border border-[#ff671b]">
                                                ${{ number_format($data['this_month_cost'], 2) }}
                                            </span>
                                        @else
                                            <span class="text-gray-400">$0.00</span>
                                        @endif
                                    </td>

                                    <!-- Four Weeks Cost -->
                                    <td class="border border-gray-300 px-4 py-3 text-sm text-center" data-sort="{{ $data['four_weeks_cost'] }}">
                                        @if($data['four_weeks_cost'] > 0)
                                            <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium
                                                @if($data['four_weeks_cost'] > 1000) bg-orange-100 text-orange-800 border-orange-200
                                                @else bg-[#fff4ed] text-[#ff671b] border-[#ff671b] @endif border">
                                                ${{ number_format($data['four_weeks_cost'], 2) }}
                                            </span>
                                        @else
                                            <span class="text-gray-400">$0.00</span>
                                        @endif
                                    </td>

                                    <!-- Ninety Days Cost -->
                                    <td class="border border-gray-300 px-4 py-3 text-sm text-center" data-sort="{{ $data['ninety_days_cost'] }}">
                                        @if($data['ninety_days_cost'] > 0)
                                            <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium
                                                @if($data['ninety_days_cost'] > 3000) bg-red-100 text-red-800 border-red-200
                                                @elseif($data['ninety_days_cost'] > 1500) bg-orange-100 text-orange-800 border-orange-200
                                                @else bg-[#fff4ed] text-[#ff671b] border-[#ff671b] @endif border">
                                                ${{ number_format($data['ninety_days_cost'], 2) }}
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
                                    TOTALS ({{ $storeNames->count() }} stores)
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
                        <h3 class="mt-2 text-sm font-medium text-gray-900">No payment data</h3>
                        <p class="mt-1 text-sm text-gray-500">No stores have recorded maintenance payments yet for {{ $targetYear }}.</p>
                    </div>
                @endif
            </div>


        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Table sorting functionality
            let weeklySortDirection = {};

            window.sortWeeklyTable = function(columnIndex, type) {
                const table = document.getElementById('weeklyMaintenanceTable');
                const tbody = document.getElementById('weeklyMaintenanceBody');
                if (!table || !tbody) return;
                const rows = Array.from(tbody.querySelectorAll('tr'));
                if (rows.length === 0) return;

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
                    return newDirection === 'asc' ? (aValue > bValue ? 1 : -1) : (aValue < bValue ? 1 : -1);
                });

                // Re-append sorted rows with alternating colors
                rows.forEach((row, idx) => {
                    row.className = (idx % 2 == 0 ? 'bg-gray-50' : 'bg-white') + ' hover:bg-[#fff4ed]';
                    tbody.appendChild(row);
                });
            };

            // Close modal function
            window.closeModal = function(modalId) {
                const modal = document.getElementById(modalId);
                if (modal) {
                    modal.classList.add('hidden');
                    document.body.style.overflow = 'auto';
                } else {
                    window.history.back();
                }
            };
        });
    </script>
@endsection
