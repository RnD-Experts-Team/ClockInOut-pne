@extends('layouts.app')

@section('title', 'Cost per Store Within 1 Year')

@section('content')
    <div class="min-h-screen bg-gray-50 py-8">
        <div class="max-w-full mx-auto px-4 sm:px-6 lg:px-8">
            <!-- Header -->
            <div class="text-center mb-8">
                <h1 class="text-3xl font-bold text-white bg-[#ff671b] py-4 px-8 rounded-lg inline-block mb-4">
                    Cost per Store - {{ $targetYear ?? 'Current Year' }}
                </h1>
                <p class="text-gray-600">Generated on {{ now()->format('F j, Y \\a\\t g:i A') }}</p>

                <!-- Year Selector -->
                @if(count($availableYears) > 1)
                    <form method="GET" class="mt-4">
                        <label for="year" class="text-sm text-gray-600 mr-2">Select Year:</label>
                        <select name="year" id="year" onchange="this.form.submit()"
                                class="rounded border-gray-300 text-sm">
                            @foreach($availableYears as $year)
                                <option value="{{ $year }}" {{ ($targetYear ?? now()->year) == $year ? 'selected' : '' }}>
                                    {{ $year }}
                                </option>
                            @endforeach
                        </select>
                    </form>
                @endif
            </div>

            <!-- Filter Display -->
            @if(request()->hasAny(['date_from', 'date_to', 'company_id', 'search']))
                <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 mb-6">
                    <div class="text-center">
                        <h3 class="text-sm font-medium text-blue-900">Applied Filters</h3>
                        <div class="flex flex-wrap justify-center gap-2 mt-2">
                            @if($dateFrom && $dateTo)
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                    Date Range: {{ $dateFrom }} to {{ $dateTo }}
                                </span>
                            @endif
                            @if(request('company_id') && request('company_id') !== 'all')
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                    Company Filter Applied
                                </span>
                            @endif
                            @if(request('search'))
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                    Search: {{ request('search') }}
                                </span>
                            @endif
                        </div>
                    </div>
                </div>
            @endif

            <!-- Summary Statistics -->
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-8">
                <div class="bg-white rounded-lg shadow p-4 text-center">
                    <div class="text-2xl font-bold text-[#ff671b]">{{ $storeYearlyCosts->count() }}</div>
                    <div class="text-sm text-gray-600">Active Stores</div>
                </div>
                <div class="bg-white rounded-lg shadow p-4 text-center">
                    <div class="text-2xl font-bold text-[#ff671b]">${{ number_format($grandTotal, 2) }}</div>
                    <div class="text-sm text-gray-600">Total Cost</div>
                </div>
                <div class="bg-white rounded-lg shadow p-4 text-center">
                    <div class="text-2xl font-bold text-[#ff671b]">${{ number_format($avgCostPerStore, 2) }}</div>
                    <div class="text-sm text-gray-600">Average per Store</div>
                </div>
                <div class="bg-white rounded-lg shadow p-4 text-center">
                    <div class="text-2xl font-bold text-[#ff671b]">{{ $storeYearlyCosts->sum('payment_count') }}</div>
                    <div class="text-sm text-gray-600">Total Payments</div>
                </div>
            </div>

            <!-- Excel-like Table -->
            <div class="bg-white shadow-lg rounded-lg overflow-hidden">
                @if($storeYearlyCosts->count() > 0)
                    <div class="overflow-x-auto">
                        <table class="min-w-full border-collapse" id="costPerStoreTable">
                            <thead>
                            <tr class="bg-[#ff671b] text-white">
                                <th class="border border-gray-300 px-6 py-4 text-left text-sm font-semibold cursor-pointer hover:bg-[#e55b17] select-none"
                                    onclick="sortCostPerStoreTable(0, 'text')" id="costperstore-header-0">
                                    <div class="flex items-center">
                                        Store
                                        <span class="ml-2 text-xs opacity-75" id="costperstore-sort-indicator-0">A↓</span>
                                    </div>
                                </th>
                                <th class="border border-gray-300 px-6 py-4 text-center text-sm font-semibold cursor-pointer hover:bg-[#e55b17] select-none"
                                    onclick="sortCostPerStoreTable(1, 'number')" id="costperstore-header-1">
                                    <div class="flex items-center justify-center">
                                        Payment Count
                                        <span class="ml-2 text-xs opacity-75" id="costperstore-sort-indicator-1">↑</span>
                                    </div>
                                </th>
                                <th class="border border-gray-300 px-6 py-4 text-right text-sm font-semibold cursor-pointer hover:bg-[#e55b17] select-none"
                                    onclick="sortCostPerStoreTable(2, 'number')" id="costperstore-header-2">
                                    <div class="flex items-center justify-end">
                                        Total Cost
                                        <span class="ml-2 text-xs opacity-75" id="costperstore-sort-indicator-2">↑</span>
                                    </div>
                                </th>
                                <th class="border border-gray-300 px-6 py-4 text-center text-sm font-semibold">
                                    % of Total
                                </th>
                            </tr>
                            </thead>
                            <tbody id="costPerStoreBody">
                            @foreach($storeYearlyCosts as $index => $storeData)
                                @php
                                    $percentage = $grandTotal > 0 ? ($storeData->total_cost / $grandTotal) * 100 : 0;
                                @endphp
                                <tr class="{{ $index % 2 == 0 ? 'bg-blue-50' : 'bg-white' }} hover:bg-[#fff4ed]">
                                    <td class="border border-gray-300 px-6 py-3 text-sm font-medium text-gray-900" data-sort="{{ $storeData->store_display }}">
                                        <div class="flex items-center">
                                            <div class="w-3 h-3 bg-[#ff671b] rounded-full mr-3"></div>
                                            {{ $storeData->store_display }}
                                        </div>
                                    </td>
                                    <td class="border border-gray-300 px-6 py-3 text-sm text-center text-gray-600" data-sort="{{ $storeData->payment_count }}">
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-[#fff4ed] text-[#ff671b] border border-[#ff671b]">
                                            {{ $storeData->payment_count }}
                                        </span>
                                    </td>
                                    <td class="border border-gray-300 px-6 py-3 text-sm text-right" data-sort="{{ $storeData->total_cost }}">
                                        <div class="flex items-center justify-end">
                                            <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-semibold
                                                @if($storeData->total_cost >= 1000) bg-red-100 text-red-800
                                                @elseif($storeData->total_cost >= 500) bg-orange-100 text-orange-800
                                                @else bg-[#fff4ed] text-[#ff671b] @endif">
                                                ${{ number_format($storeData->total_cost, 2) }}
                                            </span>
                                        </div>
                                    </td>
                                    <td class="border border-gray-300 px-6 py-3 text-sm text-center text-gray-600" data-sort="{{ $percentage }}">
                                        <div class="flex flex-col items-center">
                                            <span class="font-medium">{{ number_format($percentage, 1) }}%</span>
                                            <div class="w-16 bg-gray-200 rounded-full h-2 mt-1">
                                                <div class="bg-[#ff671b] h-2 rounded-full" style="width: {{ min($percentage, 100) }}%"></div>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                            </tbody>

                            <!-- Total Row -->
                            <tfoot>
                            <tr class="bg-[#e55b17] text-white font-bold">
                                <td class="border border-gray-300 px-6 py-4 text-sm text-left">
                                    TOTAL ({{ $storeYearlyCosts->count() }} stores)
                                </td>
                                <td class="border border-gray-300 px-6 py-4 text-sm text-center">
                                    {{ $storeYearlyCosts->sum('payment_count') }}
                                </td>
                                <td class="border border-gray-300 px-6 py-4 text-sm text-right text-xl">
                                    ${{ number_format($grandTotal, 2) }}
                                </td>
                                <td class="border border-gray-300 px-6 py-4 text-sm text-center">
                                    100.0%
                                </td>
                            </tr>
                            </tfoot>
                        </table>
                    </div>

                    <!-- Top Performers Section -->
                    @if($storeYearlyCosts->count() > 3)
                        <div class="p-6 border-t bg-gray-50">
                            <h3 class="text-lg font-semibold text-gray-800 mb-4">Top Cost Centers</h3>
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                @foreach($storeYearlyCosts->take(3) as $index => $store)
                                    <div class="bg-white rounded-lg p-4 shadow-sm border-l-4
                                        @if($index === 0) border-red-500
                                        @elseif($index === 1) border-orange-500
                                        @else border-[#ff671b] @endif">
                                        <div class="flex justify-between items-start">
                                            <div>
                                                <div class="text-sm font-medium text-gray-600">
                                                    @if($index === 0) 🥇 Highest Cost
                                                    @elseif($index === 1) 🥈 Second Highest
                                                    @else 🥉 Third Highest @endif
                                                </div>
                                                <div class="text-lg font-bold text-gray-900">{{ $store->store_display }}</div>
                                                <div class="text-sm text-gray-500">{{ $store->payment_count }} payments</div>
                                            </div>
                                            <div class="text-right">
                                                <div class="text-xl font-bold
                                                    @if($index === 0) text-red-600
                                                    @elseif($index === 1) text-orange-600
                                                    @else text-[#ff671b] @endif">
                                                    ${{ number_format($store->total_cost, 2) }}
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif
                @else
                    <div class="text-center py-12">
                        <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                        <h3 class="mt-2 text-sm font-medium text-gray-900">No payment data found</h3>
                        <p class="mt-1 text-sm text-gray-500">
                            No store payments found for {{ $dateFrom && $dateTo ? 'the selected date range' : 'the selected year' }}.
                        </p>
                    </div>
                @endif
            </div>


        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            let costPerStoreSortDirection = {};

            window.sortCostPerStoreTable = function(columnIndex, type) {
                const table = document.getElementById('costPerStoreTable');
                const tbody = document.getElementById('costPerStoreBody');
                if (!table || !tbody) return;

                const rows = Array.from(tbody.querySelectorAll('tr'));
                if (rows.length === 0) return;

                // Toggle sort direction
                const currentDirection = costPerStoreSortDirection[columnIndex] || 'asc';
                const newDirection = currentDirection === 'asc' ? 'desc' : 'asc';
                costPerStoreSortDirection[columnIndex] = newDirection;

                // Clear all sort indicators
                for (let i = 0; i < 3; i++) {
                    const indicator = document.getElementById(`costperstore-sort-indicator-${i}`);
                    if (indicator) {
                        indicator.textContent = i === 0 ? 'A↓' : '↑';
                        indicator.style.opacity = '0.5';
                    }
                }

                const activeIndicator = document.getElementById(`costperstore-sort-indicator-${columnIndex}`);
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

                rows.forEach((row, idx) => {
                    row.className = (idx % 2 == 0 ? 'bg-blue-50' : 'bg-white') + ' hover:bg-[#fff4ed]';
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
