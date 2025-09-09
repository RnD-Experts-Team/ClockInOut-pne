@extends('layouts.app')

@section('title', 'Cost Breakdown Analysis')

@section('content')
    <div class="min-h-screen bg-gray-50 py-8">
        <div class="max-w-full mx-auto ">
            <!-- Simple Header -->
            <div class="text-center mb-8">
                <h1 class="text-3xl font-bold text-gray-900 mb-2">Cost Breakdown Analysis</h1>
                <p class="text-gray-600">Generated on {{ now()->format('F j, Y \\a\\t g:i A') }}</p>
            </div>

            <!-- Excel-like Table -->
            <div class="bg-white shadow-lg rounded-lg overflow-hidden">
                <div class="overflow-x-auto no-scrollbar">
                    <table class="min-w-full border-collapse" id="costBreakdownTable">
                        <thead>
                        <tr class="bg-[#ff671b] text-white">
                            <!-- FIXED: Store # should be sorted as number, not text -->
                            <th class="border border-gray-300 px-3 py-3 text-center text-sm font-semibold cursor-pointer hover:bg-[#e55b17] transition-colors select-none"
                                onclick="sortCostBreakdownTable(0, 'number')" id="costbreakdown-header-0">
                                <div class="flex items-center justify-center">
                                    Store #
                                    <span class="ml-2 text-xs opacity-75" id="costbreakdown-sort-indicator-0">↑</span>
                                </div>
                            </th>
                            <th class="border border-gray-300 px-3 py-3 text-left text-sm font-semibold cursor-pointer hover:bg-[#e55b17] transition-colors select-none"
                                onclick="sortCostBreakdownTable(1, 'text')" id="costbreakdown-header-1">
                                <div class="flex items-center justify-between">
                                    Store Name
                                    <span class="ml-2 text-xs opacity-75" id="costbreakdown-sort-indicator-1">A↓</span>
                                </div>
                            </th>
                            <th class="border border-gray-300 px-3 py-3 text-right text-sm font-semibold cursor-pointer hover:bg-[#e55b17] transition-colors select-none"
                                onclick="sortCostBreakdownTable(2, 'number')" id="costbreakdown-header-2">
                                <div class="flex items-center justify-end">
                                    AWS
                                    <span class="ml-2 text-xs opacity-75" id="costbreakdown-sort-indicator-2">↑</span>
                                </div>
                            </th>
                            <th class="border border-gray-300 px-3 py-3 text-right text-sm font-semibold cursor-pointer hover:bg-[#e55b17] transition-colors select-none"
                                onclick="sortCostBreakdownTable(3, 'number')" id="costbreakdown-header-3">
                                <div class="flex items-center justify-end">
                                    Total Rent
                                    <span class="ml-2 text-xs opacity-75" id="costbreakdown-sort-indicator-3">↑</span>
                                </div>
                            </th>
                            <th class="border border-gray-300 px-3 py-3 text-right text-sm font-semibold cursor-pointer hover:bg-[#e55b17] transition-colors select-none"
                                onclick="sortCostBreakdownTable(4, 'number')" id="costbreakdown-header-4">
                                <div class="flex items-center justify-end">
                                    L2S Ratio
                                    <span class="ml-2 text-xs opacity-75" id="costbreakdown-sort-indicator-4">↑</span>
                                </div>
                            </th>
                            <th class="border border-gray-300 px-3 py-3 text-right text-sm font-semibold cursor-pointer hover:bg-[#e55b17] transition-colors select-none"
                                onclick="sortCostBreakdownTable(5, 'number')" id="costbreakdown-header-5">
                                <div class="flex items-center justify-end">
                                    Base Rent
                                    <span class="ml-2 text-xs opacity-75" id="costbreakdown-sort-indicator-5">↑</span>
                                </div>
                            </th>
                            <th class="border border-gray-300 px-3 py-3 text-right text-sm font-semibold cursor-pointer hover:bg-[#e55b17] transition-colors select-none"
                                onclick="sortCostBreakdownTable(6, 'number')" id="costbreakdown-header-6">
                                <div class="flex items-center justify-end">
                                    % Increase
                                    <span class="ml-2 text-xs opacity-75" id="costbreakdown-sort-indicator-6">↑</span>
                                </div>
                            </th>
                            <th class="border border-gray-300 px-3 py-3 text-right text-sm font-semibold cursor-pointer hover:bg-[#e55b17] transition-colors select-none"
                                onclick="sortCostBreakdownTable(7, 'number')" id="costbreakdown-header-7">
                                <div class="flex items-center justify-end">
                                    Insurance
                                    <span class="ml-2 text-xs opacity-75" id="costbreakdown-sort-indicator-7">↑</span>
                                </div>
                            </th>
                            <th class="border border-gray-300 px-3 py-3 text-right text-sm font-semibold cursor-pointer hover:bg-[#e55b17] transition-colors select-none"
                                onclick="sortCostBreakdownTable(8, 'number')" id="costbreakdown-header-8">
                                <div class="flex items-center justify-end">
                                    CAM
                                    <span class="ml-2 text-xs opacity-75" id="costbreakdown-sort-indicator-8">↑</span>
                                </div>
                            </th>
                            <th class="border border-gray-300 px-3 py-3 text-right text-sm font-semibold cursor-pointer hover:bg-[#e55b17] transition-colors select-none"
                                onclick="sortCostBreakdownTable(9, 'number')" id="costbreakdown-header-9">
                                <div class="flex items-center justify-end">
                                    RE Taxes
                                    <span class="ml-2 text-xs opacity-75" id="costbreakdown-sort-indicator-9">↑</span>
                                </div>
                            </th>
                            <th class="border border-gray-300 px-3 py-3 text-right text-sm font-semibold cursor-pointer hover:bg-[#e55b17] transition-colors select-none"
                                onclick="sortCostBreakdownTable(10, 'number')" id="costbreakdown-header-10">
                                <div class="flex items-center justify-end">
                                    Others
                                    <span class="ml-2 text-xs opacity-75" id="costbreakdown-sort-indicator-10">↑</span>
                                </div>
                            </th>
                            <th class="border border-gray-300 px-3 py-3 text-right text-sm font-semibold cursor-pointer hover:bg-[#e55b17] transition-colors select-none"
                                onclick="sortCostBreakdownTable(11, 'number')" id="costbreakdown-header-11">
                                <div class="flex items-center justify-end">
                                    Security Deposit
                                    <span class="ml-2 text-xs opacity-75" id="costbreakdown-sort-indicator-11">↑</span>
                                </div>
                            </th>
                        </tr>
                        </thead>
                        <tbody id="costBreakdownBody">
                        @foreach($leases as $index => $lease)
                            <tr class="{{ $index % 2 == 0 ? 'bg-white' : 'bg-gray-50' }} hover:bg-[#fff4ed]">
                                <!-- FIXED: Removed escape characters -->
                                <td class="border border-gray-300 px-3 py-3 text-sm text-center" data-sort="{{ $lease->store_number ?: 'N/A' }}">{{ $lease->store_number ?: 'N/A' }}</td>
                                <td class="border border-gray-300 px-3 py-3 text-sm" data-sort="{{ $lease->name ?: 'N/A' }}">{{ $lease->name ?: 'N/A' }}</td>
                                <td class="border border-gray-300 px-3 py-3 text-sm text-right" data-sort="{{ $lease->aws ?: 0 }}">${{ number_format($lease->aws ?: 0, 2) }}</td>
                                <td class="border border-gray-300 px-3 py-3 text-sm text-right" data-sort="{{ $lease->total_rent ?: 0 }}">${{ number_format($lease->total_rent ?: 0, 2) }}</td>
                                <td class="border border-gray-300 px-3 py-3 text-sm text-right" data-sort="{{ $lease->lease_to_sales_ratio ?: 0 }}">{{ $lease->lease_to_sales_ratio ? number_format($lease->lease_to_sales_ratio * 100, 2) . '%' : 'N/A' }}</td>
                                <td class="border border-gray-300 px-3 py-3 text-sm text-right" data-sort="{{ $lease->base_rent ?: 0 }}">${{ number_format($lease->base_rent ?: 0, 2) }}</td>
                                <td class="border border-gray-300 px-3 py-3 text-sm text-right" data-sort="{{ $lease->percent_increase_per_year ?: 0 }}">{{ $lease->percent_increase_per_year ? number_format($lease->percent_increase_per_year, 2) . '%' : 'N/A' }}</td>
                                <td class="border border-gray-300 px-3 py-3 text-sm text-right" data-sort="{{ $lease->insurance ?: 0 }}">${{ number_format($lease->insurance ?: 0, 2) }}</td>
                                <td class="border border-gray-300 px-3 py-3 text-sm text-right" data-sort="{{ $lease->cam ?: 0 }}">${{ number_format($lease->cam ?: 0, 2) }}</td>
                                <td class="border border-gray-300 px-3 py-3 text-sm text-right" data-sort="{{ $lease->re_taxes ?: 0 }}">${{ number_format($lease->re_taxes ?: 0, 2) }}</td>
                                <td class="border border-gray-300 px-3 py-3 text-sm text-right" data-sort="{{ $lease->others ?: 0 }}">${{ number_format($lease->others ?: 0, 2) }}</td>
                                <td class="border border-gray-300 px-3 py-3 text-sm text-right" data-sort="{{ $lease->security_deposit ?: 0 }}">${{ number_format($lease->security_deposit ?: 0, 2) }}</td>
                            </tr>
                        @endforeach
                        </tbody>
                        <tfoot>
                        <tr class="bg-[#e55b17] text-white font-semibold">
                            <td class="border border-gray-300 px-3 py-3 text-sm text-center">TOTAL</td>
                            <td class="border border-gray-300 px-3 py-3 text-sm">{{ $leases->count() }} Stores</td>
                            <td class="border border-gray-300 px-3 py-3 text-sm text-right">${{ number_format($leases->sum('aws'), 2) }}</td>
                            <td class="border border-gray-300 px-3 py-3 text-sm text-right">${{ number_format($leases->sum(fn($lease) => $lease->total_rent), 2) }}</td>
                            <td class="border border-gray-300 px-3 py-3 text-sm text-right">
                                @php
                                    $totalAws = $leases->sum('aws');
                                    $totalRent = $leases->sum(fn($lease) => $lease->total_rent);
                                    $totalRatio = $totalAws > 0 ? ($totalRent * 12) / ($totalAws * 4) : 0;
                                @endphp
                                {{ number_format($totalRatio * 100, 2) }}%
                            </td>
                            <td class="border border-gray-300 px-3 py-3 text-sm text-right">${{ number_format($leases->sum('base_rent'), 2) }}</td>
                            <td class="border border-gray-300 px-3 py-3 text-sm text-right">
                                @php
                                    $leasesWithIncrease = $leases->filter(fn($lease) => $lease->percent_increase_per_year > 0);
                                    $avgIncrease = $leasesWithIncrease->count() > 0 ? $leasesWithIncrease->avg('percent_increase_per_year') : 0;
                                @endphp
                                {{ number_format($avgIncrease, 2) }}%
                            </td>
                            <td class="border border-gray-300 px-3 py-3 text-sm text-right">${{ number_format($leases->sum('insurance'), 2) }}</td>
                            <td class="border border-gray-300 px-3 py-3 text-sm text-right">${{ number_format($leases->sum('cam'), 2) }}</td>
                            <td class="border border-gray-300 px-3 py-3 text-sm text-right">${{ number_format($leases->sum('re_taxes'), 2) }}</td>
                            <td class="border border-gray-300 px-3 py-3 text-sm text-right">${{ number_format($leases->sum('others'), 2) }}</td>
                            <td class="border border-gray-300 px-3 py-3 text-sm text-right">${{ number_format($leases->sum('security_deposit'), 2) }}</td>
                        </tr>
                        </tfoot>
                    </table>
                </div>
            </div>

            <!-- Close Button -->
            <div class="mt-6 text-center">
                <button onclick="closeModal('costBreakdownModal')"
                        class="inline-flex items-center px-4 py-2 bg-[#ff671b] text-white text-sm font-medium rounded hover:bg-[#e55b17]">
                    Close
                </button>
            </div>
        </div>
    </div>

    <script>
        // Fixed Cost Breakdown Table Sorting
        document.addEventListener('DOMContentLoaded', function() {
            let costBreakdownSortDirection = {};

            window.sortCostBreakdownTable = function(columnIndex, type) {
                const tbody = document.getElementById('costBreakdownBody');
                if (!tbody) return;

                const rows = Array.from(tbody.querySelectorAll('tr'));
                if (rows.length === 0) return;

                const currentDirection = costBreakdownSortDirection[columnIndex] || 'asc';
                const newDirection = currentDirection === 'asc' ? 'desc' : 'asc';
                costBreakdownSortDirection[columnIndex] = newDirection;

                // Clear all sort indicators
                for (let i = 0; i <= 11; i++) {
                    const indicator = document.getElementById(`costbreakdown-sort-indicator-${i}`);
                    if (indicator) {
                        // Store # (0) and Store Name (1) are text/number, rest are numbers
                        if (i === 0) {
                            indicator.textContent = '↑'; // Store # is number
                        } else if (i === 1) {
                            indicator.textContent = 'A↓'; // Store Name is text
                        } else {
                            indicator.textContent = '↑'; // All other columns are numbers
                        }
                        indicator.style.opacity = '0.5';
                    }
                }

                // Set active sort indicator
                const activeIndicator = document.getElementById(`costbreakdown-sort-indicator-${columnIndex}`);
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
                        // FIXED: Store numbers need integer parsing
                        if (columnIndex === 0) {
                            aValue = parseInt(a.cells[columnIndex].getAttribute('data-sort')) || 0;
                            bValue = parseInt(b.cells[columnIndex].getAttribute('data-sort')) || 0;
                        } else {
                            aValue = parseFloat(a.cells[columnIndex].getAttribute('data-sort')) || 0;
                            bValue = parseFloat(b.cells[columnIndex].getAttribute('data-sort')) || 0;
                        }
                    } else {
                        aValue = (a.cells[columnIndex].getAttribute('data-sort') || '').toLowerCase();
                        bValue = (b.cells[columnIndex].getAttribute('data-sort') || '').toLowerCase();

                        // Handle N/A values
                        if (aValue === 'n/a') aValue = 'zzzz';
                        if (bValue === 'n/a') bValue = 'zzzz';
                    }

                    if (newDirection === 'asc') {
                        return aValue > bValue ? 1 : (aValue < bValue ? -1 : 0);
                    } else {
                        return aValue < bValue ? 1 : (aValue > bValue ? -1 : 0);
                    }
                });

                // Re-append sorted rows with alternating colors
                rows.forEach((row, index) => {
                    row.className = (index % 2 === 0 ? 'bg-white' : 'bg-gray-50') + ' hover:bg-[#fff4ed]';
                    tbody.appendChild(row);
                });
            };
        });

        function closeModal(modalId) {
            // Add your modal closing logic here
            window.close(); // or whatever method you use to close the modal
        }
    </script>
@endsection
