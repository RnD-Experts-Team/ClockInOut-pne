{{-- resources/views/payments/monthly-report.blade.php --}}
@extends('layouts.app')

@section('title', 'Monthly Report')

@section('content')
    <div class="min-h-screen bg-gray-50 py-8">
        <div class="max-w-full mx-auto px-4 sm:px-6 lg:px-8">
            <!-- Simple Header -->
            <div class="text-center mb-8">
                <h1 class="text-3xl font-bold text-white bg-[#ff671b] py-4 px-8 rounded-lg inline-block mb-4">Monthly Report</h1>
                <p class="text-gray-600">Generated on {{ now()->format('F j, Y \\a\\t g:i A') }}</p>

                <!-- Year Selector -->
                @if(count($availableYears) > 1)
                    <form method="GET" class="mt-4">
                        <label for="year" class="text-sm text-gray-600 mr-2">Select Year:</label>
                        <select name="year" id="year" onchange="this.form.submit()"
                                class="rounded border-gray-300 text-sm">
                            @foreach($availableYears as $year)
                                <option value="{{ $year }}" {{ $targetYear == $year ? 'selected' : '' }}>
                                    {{ $year }}
                                </option>
                            @endforeach
                        </select>
                    </form>
                @endif
            </div>

            <!-- Filter Display (if filters are applied) -->
            @if(request()->hasAny(['date_from', 'date_to', 'company_id', 'search', 'time_filter']))
                <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 mb-6">
                    <div class="text-center">
                        <h3 class="text-sm font-medium text-blue-900">Applied Filters</h3>
                        <div class="flex flex-wrap justify-center gap-2 mt-2">
                            @if(request('date_from') && request('date_to'))
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                    Date Range: {{ request('date_from') }} to {{ request('date_to') }}
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

            <!-- Excel-like Table -->
            <div class="bg-white shadow-lg rounded-lg overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="min-w-full border-collapse" id="monthlyReportTable">
                        <thead>
                        <tr class="bg-[#ff671b] text-white">
                            <th class="border border-gray-300 px-6 py-4 text-center text-sm font-semibold cursor-pointer hover:bg-[#e55b17] transition-colors select-none"
                                onclick="sortMonthlyTable(0, 'number')" id="monthly-header-0">
                                <div class="flex items-center justify-center">
                                    Month
                                    <span class="ml-2 text-xs opacity-75" id="monthly-sort-indicator-0">↑</span>
                                </div>
                            </th>
                            <th class="border border-gray-300 px-6 py-4 text-center text-sm font-semibold cursor-pointer hover:bg-[#e55b17] transition-colors select-none"
                                onclick="sortMonthlyTable(1, 'number')" id="monthly-header-1">
                                <div class="flex items-center justify-center">
                                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                                    </svg>
                                    Paid
                                    <span class="ml-2 text-xs opacity-75" id="monthly-sort-indicator-1">↑</span>
                                </div>
                            </th>
                            <th class="border border-gray-300 px-6 py-4 text-center text-sm font-semibold cursor-pointer hover:bg-[#e55b17] transition-colors select-none"
                                onclick="sortMonthlyTable(2, 'number')" id="monthly-header-2">
                                <div class="flex items-center justify-center">
                                    Average % Paid
                                    <span class="ml-2 text-xs opacity-75" id="monthly-sort-indicator-2">↑</span>
                                </div>
                            </th>
                        </tr>
                        </thead>
                        <tbody id="monthlyReportBody">
                        <!-- Year Indicator -->
                        @if($targetYear != $currentYear)
                            <tr class="bg-yellow-50" data-row-type="indicator">
                                <td colspan="3" class="border border-gray-300 px-6 py-2 text-center text-sm text-yellow-700">
                                    <strong>Showing data for {{ $targetYear }}</strong> (No data available for {{ $currentYear }})
                                </td>
                            </tr>
                        @else
                            <tr class="bg-[#fff4ed]" data-row-type="indicator">
                                <td colspan="3" class="border border-gray-300 px-6 py-2 text-center text-sm text-[#ff671b]">
                                    <strong>{{ $currentYear }} Payment Report (without labor)</strong>
                                </td>
                            </tr>
                        @endif

                        @foreach($monthlyData as $index => $data)
                            <tr class="{{ $index % 2 == 0 ? 'bg-white' : 'bg-gray-50' }} hover:bg-[#fff4ed]" data-row-index="{{ $index }}">
                                <td class="border border-gray-300 px-6 py-3 text-sm text-center font-medium" data-sort="{{ $data['month'] }}">
                                    <div class="flex items-center justify-center">
                                            <span class="bg-[#fff4ed] text-[#ff671b] px-2 py-1 rounded mr-2"
                                                  style="background-color: #fff4ed !important; color: #ff671b !important; font-weight: 500;">
                                                {{ $data['month'] }}
                                            </span>
                                        <span class="text-gray-600">{{ substr($data['month_name'], 0, 3) }}</span>
                                    </div>
                                    @if($data['payment_count'] > 0)
                                        <div class="text-xs text-gray-500 mt-1">{{ $data['payment_count'] }} payments</div>
                                    @endif
                                </td>
                                <td class="border border-gray-300 px-6 py-3 text-sm text-center" data-sort="{{ $data['paid_amount'] }}">
                                    @if($data['paid_amount'] > 0)
                                        <div class="font-semibold text-green-600" style="color: #059669 !important;">
                                            ${{ number_format($data['paid_amount'], 2) }}
                                        </div>
                                        @if($data['unpaid_amount'] > 0)
                                            <div class="text-xs text-red-500 mt-1" style="color: #dc2626 !important;">
                                                Unpaid: ${{ number_format($data['unpaid_amount'], 2) }}
                                            </div>
                                        @endif
                                    @else
                                        <span class="text-gray-400" style="color: #9ca3af !important;">$0.00</span>
                                    @endif
                                </td>
                                <td class="border border-gray-300 px-6 py-3 text-sm text-center" data-sort="{{ $data['percentage'] }}">
                                    @if($data['total_amount'] > 0)
                                        @if($data['percentage'] == 100)
                                            <span class="text-green-600 font-semibold" style="color: #059669 !important;">100%</span>
                                        @elseif($data['percentage'] >= 75)
                                            <span class="text-[#ff671b] font-semibold" style="color: #ff671b !important;">{{ number_format($data['percentage'], 0) }}%</span>
                                        @elseif($data['percentage'] >= 50)
                                            <span class="text-yellow-600 font-semibold" style="color: #ca8a04 !important;">{{ number_format($data['percentage'], 0) }}%</span>
                                        @elseif($data['percentage'] > 0)
                                            <span class="text-orange-600 font-semibold" style="color: #f59e42 !important;">{{ number_format($data['percentage'], 0) }}%</span>
                                        @else
                                            <span class="text-red-600 font-semibold" style="color: #dc2626 !important;">0%</span>
                                        @endif
                                    @else
                                        <span class="text-gray-400" style="color: #9ca3af !important;">N/A</span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                        </tbody>
                        <!-- Grand Total Row -->
                        <tfoot>
                        <tr class="bg-[#e55b17] text-white font-semibold">
                            <td class="border border-gray-300 px-6 py-3 text-sm text-center">
                                Grand Total
                                <div class="text-xs font-normal mt-1">
                                    {{ collect($monthlyData)->sum('payment_count') }} total payments
                                </div>
                            </td>
                            <td class="border border-gray-300 px-6 py-3 text-sm text-center">
                                <div>${{ number_format($grandTotal, 2) }}</div>
                                <div class="text-xs font-normal mt-1">
                                    Total: ${{ number_format($grandTotalAll, 2) }}
                                </div>
                            </td>
                            <td class="border border-gray-300 px-6 py-3 text-sm text-center">
                                {{ number_format($avgPercentage, 0) }}%
                            </td>
                        </tr>
                        </tfoot>
                    </table>
                </div>
            </div>

            <!-- Summary Cards -->
            @if(collect($monthlyData)->sum('payment_count') > 0)
                <div class="mt-6 grid grid-cols-1 md:grid-cols-4 gap-4">
                    <div class="bg-white p-4 rounded-lg shadow">
                        <div class="text-sm text-gray-600">Total Paid</div>
                        <div class="text-lg font-bold text-green-600">${{ number_format($grandTotal, 2) }}</div>
                    </div>
                    <div class="bg-white p-4 rounded-lg shadow">
                        <div class="text-sm text-gray-600">Total Outstanding</div>
                        <div class="text-lg font-bold text-red-600">${{ number_format($grandTotalAll - $grandTotal, 2) }}</div>
                    </div>
                    <div class="bg-white p-4 rounded-lg shadow">
                        <div class="text-sm text-gray-600">Payment Rate</div>
                        <div class="text-lg font-bold text-[#ff671b]">{{ number_format($avgPercentage, 1) }}%</div>
                    </div>
                    <div class="bg-white p-4 rounded-lg shadow">
                        <div class="text-sm text-gray-600">Best Month</div>
                        <div class="text-lg font-bold text-[#ff671b]">
                            @php
                                $bestMonth = collect($monthlyData)->where('percentage', '>', 0)->sortByDesc('percentage')->first();
                            @endphp
                            {{ $bestMonth ? $bestMonth['month_name'] : 'N/A' }}
                        </div>
                    </div>
                </div>
            @else
                <div class="mt-6 bg-yellow-50 border border-yellow-200 rounded-lg p-6">
                    <div class="text-center">
                        <svg class="mx-auto h-12 w-12 text-yellow-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01M6.938 18h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.35 16.5c-.77.833.192 2.5 1.732 2.5z" />
                        </svg>
                        <h3 class="mt-2 text-sm font-medium text-gray-900">No Payment Data Found</h3>
                        <p class="mt-1 text-sm text-gray-500">No payments have been recorded for any month in {{ $targetYear }}.</p>
                    </div>
                </div>
            @endif

            <!-- Close Button -->
            <div class="mt-6 text-center">
                <button onclick="closeModal('monthlyReportModal')"
                        class="inline-flex items-center px-4 py-2 bg-[#ff671b] text-white text-sm font-medium rounded hover:bg-[#e55b17]">
                    Close
                </button>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            let monthlySortDirection = {};

            window.sortMonthlyTable = function(columnIndex, type) {
                const table = document.getElementById('monthlyReportTable');
                const tbody = document.getElementById('monthlyReportBody');
                if (!table || !tbody) return;

                // Separate indicator row and data rows
                const indicatorRow = tbody.querySelector('tr[data-row-type="indicator"]');
                const dataRows = Array.from(tbody.querySelectorAll('tr[data-row-index]'));

                if (dataRows.length === 0) return;

                // Toggle sort direction
                const currentDirection = monthlySortDirection[columnIndex] || 'asc';
                const newDirection = currentDirection === 'asc' ? 'desc' : 'asc';
                monthlySortDirection[columnIndex] = newDirection;

                // Clear all sort indicators
                for (let i = 0; i < 3; i++) {
                    const indicator = document.getElementById(`monthly-sort-indicator-${i}`);
                    if (indicator) {
                        indicator.textContent = '↑';
                        indicator.style.opacity = '0.5';
                    }
                }

                // Set active sort indicator
                const activeIndicator = document.getElementById(`monthly-sort-indicator-${columnIndex}`);
                if (activeIndicator) {
                    activeIndicator.textContent = newDirection === 'asc' ? '↑' : '↓';
                    activeIndicator.style.opacity = '1';
                }

                // Sort data rows
                dataRows.sort((a, b) => {
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

                // Clear tbody and re-append indicator row first (if exists)
                while (tbody.firstChild) {
                    tbody.removeChild(tbody.firstChild);
                }
                if (indicatorRow) {
                    tbody.appendChild(indicatorRow);
                }

                // Re-append sorted data rows with alternating colors
                dataRows.forEach((row, idx) => {
                    row.className = (idx % 2 === 0 ? 'bg-white' : 'bg-gray-50') + ' hover:bg-[#fff4ed]';
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
                    // If no modal, go back to previous page or close window
                    window.history.back();
                }
            };
        });
    </script>
@endsection
