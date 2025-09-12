@extends('layouts.app')

@section('title', 'Lease Tracker Dashboard')

@section('content')
    <div class="min-h-screen bg-gray-50 py-8">
        <div class="max-w-full mx-auto px-2 sm:px-4 lg:px-6">
            <!-- Simple Header -->
            <div class="text-center mb-6">
                <h1 class="text-2xl font-bold text-gray-900 mb-2">Lease Tracker Dashboard</h1>
                <p class="text-gray-600 text-sm">Generated on {{ now()->format('F j, Y \\a\\t g:i A') }}</p>
            </div>

            <!-- Compact Excel-like Table -->
            <div class="bg-white shadow-lg rounded-lg overflow-hidden">
                <div class="w-full">
                    <table class="w-full table-fixed border-collapse text-xs" id="leaseTrackerTable">
                        <colgroup>
                            <col style="width: 5%">   <!-- Store # -->
                            <col style="width: 7%">   <!-- AWS -->
                            <col style="width: 7%">   <!-- Total Rent -->
                            <col style="width: 6%">   <!-- L2S Ratio -->
                            <col style="width: 9%">   <!-- Time Left -->
                            <col style="width: 6%">   <!-- Current Term NEW -->
                            <col style="width: 9%">   <!-- Total Lease Life -->
                            <col style="width: 8%">   <!-- Initial Lease Exp -->
                            <col style="width: 7%">   <!-- Term 1 -->
                            <col style="width: 7%">   <!-- Term 2 -->
                            <col style="width: 7%">   <!-- Term 3 -->
                            <col style="width: 7%">   <!-- Term 4 -->
                            <col style="width: 7%">   <!-- Term 5 -->
                            <col style="width: 7%">   <!-- Franchise Exp -->
                            <col style="width: 6%">   <!-- Status -->
                        </colgroup>
                        <thead>
                        <tr class="bg-[#ff671b] text-white">
                            <th class="border border-gray-300 px-1 py-2 text-center text-xs font-semibold cursor-pointer hover:bg-[#e55b17] transition-colors select-none"
                                onclick="sortLeaseTrackerTable(0, 'number')" id="leasetracker-header-0">
                                <div class="flex flex-col items-center">
                                    <span class="leading-tight">Store</span>
                                    <span class="leading-tight">#</span>
                                    <span class="text-xs opacity-75" id="leasetracker-sort-indicator-0">↑</span>
                                </div>
                            </th>
                            <th class="border border-gray-300 px-1 py-2 text-center text-xs font-semibold cursor-pointer hover:bg-[#e55b17] transition-colors select-none"
                                onclick="sortLeaseTrackerTable(1, 'number')" id="leasetracker-header-1">
                                <div class="flex flex-col items-center">
                                    <span class="leading-tight">AWS</span>
                                    <span class="text-xs opacity-75" id="leasetracker-sort-indicator-1">↑</span>
                                </div>
                            </th>
                            <th class="border border-gray-300 px-1 py-2 text-center text-xs font-semibold cursor-pointer hover:bg-[#e55b17] transition-colors select-none"
                                onclick="sortLeaseTrackerTable(2, 'number')" id="leasetracker-header-2">
                                <div class="flex flex-col items-center">
                                    <span class="leading-tight">Total</span>
                                    <span class="leading-tight">Rent</span>
                                    <span class="text-xs opacity-75" id="leasetracker-sort-indicator-2">↑</span>
                                </div>
                            </th>
                            <th class="border border-gray-300 px-1 py-2 text-center text-xs font-semibold cursor-pointer hover:bg-[#e55b17] transition-colors select-none"
                                onclick="sortLeaseTrackerTable(3, 'number')" id="leasetracker-header-3">
                                <div class="flex flex-col items-center">
                                    <span class="leading-tight">L2S</span>
                                    <span class="leading-tight">Ratio</span>
                                    <span class="text-xs opacity-75" id="leasetracker-sort-indicator-3">↑</span>
                                </div>
                            </th>
                            <th class="border border-gray-300 px-1 py-2 text-center text-xs font-semibold cursor-pointer hover:bg-[#e55b17] transition-colors select-none"
                                onclick="sortLeaseTrackerTable(4, 'text')" id="leasetracker-header-4">
                                <div class="flex flex-col items-center">
                                    <span class="leading-tight">Time Left on</span>
                                    <span class="leading-tight">Current Term</span>
                                    <span class="text-xs opacity-75" id="leasetracker-sort-indicator-4">A↓</span>
                                </div>
                            </th>
                            <!-- NEW: Current Term Column -->
                            <th class="border border-gray-300 px-1 py-2 text-center text-xs font-semibold cursor-pointer hover:bg-[#e55b17] transition-colors select-none"
                                onclick="sortLeaseTrackerTable(5, 'number')" id="leasetracker-header-5">
                                <div class="flex flex-col items-center">
                                    <span class="leading-tight">Current</span>
                                    <span class="leading-tight">Term</span>
                                    <span class="text-xs opacity-75" id="leasetracker-sort-indicator-5">↑</span>
                                </div>
                            </th>
                            <th class="border border-gray-300 px-1 py-2 text-center text-xs font-semibold cursor-pointer hover:bg-[#e55b17] transition-colors select-none"
                                onclick="sortLeaseTrackerTable(6, 'text')" id="leasetracker-header-6">
                                <div class="flex flex-col items-center">
                                    <span class="leading-tight">Total</span>
                                    <span class="leading-tight">Lease Life</span>
                                    <span class="text-xs opacity-75" id="leasetracker-sort-indicator-6">A↓</span>
                                </div>
                            </th>
                            <!-- Initial Lease Expiration Column -->
                            <th class="border border-gray-300 px-1 py-2 text-center text-xs font-semibold cursor-pointer hover:bg-[#e55b17] transition-colors select-none"
                                onclick="sortLeaseTrackerTable(7, 'text')" id="leasetracker-header-7">
                                <div class="flex flex-col items-center">
                                    <span class="leading-tight">Initial Lease</span>
                                    <span class="leading-tight">Exp</span>
                                    <span class="text-xs opacity-75" id="leasetracker-sort-indicator-7">A↓</span>
                                </div>
                            </th>
                            <th class="border border-gray-300 px-1 py-2 text-center text-xs font-semibold cursor-pointer hover:bg-[#e55b17] transition-colors select-none"
                                onclick="sortLeaseTrackerTable(8, 'text')" id="leasetracker-header-8">
                                <div class="flex flex-col items-center">
                                    <span class="leading-tight">Term 1</span>
                                    <span class="leading-tight">Exp</span>
                                    <span class="text-xs opacity-75" id="leasetracker-sort-indicator-8">A↓</span>
                                </div>
                            </th>
                            <th class="border border-gray-300 px-1 py-2 text-center text-xs font-semibold cursor-pointer hover:bg-[#e55b17] transition-colors select-none"
                                onclick="sortLeaseTrackerTable(9, 'text')" id="leasetracker-header-9">
                                <div class="flex flex-col items-center">
                                    <span class="leading-tight">Term 2</span>
                                    <span class="leading-tight">Exp</span>
                                    <span class="text-xs opacity-75" id="leasetracker-sort-indicator-9">A↓</span>
                                </div>
                            </th>
                            <th class="border border-gray-300 px-1 py-2 text-center text-xs font-semibold cursor-pointer hover:bg-[#e55b17] transition-colors select-none"
                                onclick="sortLeaseTrackerTable(10, 'text')" id="leasetracker-header-10">
                                <div class="flex flex-col items-center">
                                    <span class="leading-tight">Term 3</span>
                                    <span class="leading-tight">Exp</span>
                                    <span class="text-xs opacity-75" id="leasetracker-sort-indicator-10">A↓</span>
                                </div>
                            </th>
                            <th class="border border-gray-300 px-1 py-2 text-center text-xs font-semibold cursor-pointer hover:bg-[#e55b17] transition-colors select-none"
                                onclick="sortLeaseTrackerTable(11, 'text')" id="leasetracker-header-11">
                                <div class="flex flex-col items-center">
                                    <span class="leading-tight">Term 4</span>
                                    <span class="leading-tight">Exp</span>
                                    <span class="text-xs opacity-75" id="leasetracker-sort-indicator-11">A↓</span>
                                </div>
                            </th>
                            <th class="border border-gray-300 px-1 py-2 text-center text-xs font-semibold cursor-pointer hover:bg-[#e55b17] transition-colors select-none"
                                onclick="sortLeaseTrackerTable(12, 'text')" id="leasetracker-header-12">
                                <div class="flex flex-col items-center">
                                    <span class="leading-tight">Term 5</span>
                                    <span class="leading-tight">Exp</span>
                                    <span class="text-xs opacity-75" id="leasetracker-sort-indicator-12">A↓</span>
                                </div>
                            </th>
                            <th class="border border-gray-300 px-1 py-2 text-center text-xs font-semibold cursor-pointer hover:bg-[#e55b17] transition-colors select-none"
                                onclick="sortLeaseTrackerTable(13, 'text')" id="leasetracker-header-13">
                                <div class="flex flex-col items-center">
                                    <span class="leading-tight">Franchise</span>
                                    <span class="leading-tight">Exp</span>
                                    <span class="text-xs opacity-75" id="leasetracker-sort-indicator-13">A↓</span>
                                </div>
                            </th>
                            <th class="border border-gray-300 px-1 py-2 text-center text-xs font-semibold cursor-pointer hover:bg-[#e55b17] transition-colors select-none"
                                onclick="sortLeaseTrackerTable(14, 'text')" id="leasetracker-header-14">
                                <div class="flex flex-col items-center">
                                    <span class="leading-tight">Status</span>
                                    <span class="text-xs opacity-75" id="leasetracker-sort-indicator-14">A↓</span>
                                </div>
                            </th>
                        </tr>
                        </thead>
                        <tbody id="leaseTrackerBody">
                        @foreach($leases as $index => $lease)
                            @php
                                $currentTerm = $lease->current_term_info;
                                $termDates = $lease->term_expiration_dates;
                                $timeUntilFranchiseExpires = $lease->time_until_franchise_expires;

                                $isExpired = $currentTerm && $currentTerm['time_left']['expired'];
                                $isWarning = $currentTerm && !$currentTerm['time_left']['expired'] &&
                                    ($currentTerm['time_left']['years'] == 0 && $currentTerm['time_left']['months'] <= 6);

                                $rowClass = '';
                                $status = 'ACTIVE';
                                if ($isExpired) {
                                    $rowClass = 'bg-red-100';
                                    $status = 'EXPIRED';
                                } elseif ($isWarning) {
                                    $rowClass = 'bg-yellow-100';
                                    $status = 'WARNING';
                                } else {
                                    $rowClass = $index % 2 == 0 ? 'bg-white' : 'bg-gray-50';
                                }

                                // Get term expiration dates (starting from renewal terms, not initial)
                                $termExpirations = ['N/A', 'N/A', 'N/A', 'N/A', 'N/A'];
                                if ($termDates && $termDates->count() > 1) {
                                    for($i = 1; $i <= 5; $i++) {
                                        $term = $termDates->skip($i)->first();
                                        if ($term) {
                                            $termExpirations[$i-1] = $term['expiration_date']->format('m/d/Y');
                                        }
                                    }
                                }

                                // Color coding for Time Left on Current Term
                                $timeLeftColorClass = 'bg-gray-100 text-gray-700';
                                if ($currentTerm && !$currentTerm['time_left']['expired']) {
                                    $totalDays = ($currentTerm['time_left']['years'] * 365) + ($currentTerm['time_left']['months'] * 30) + $currentTerm['time_left']['days'];
                                    if ($totalDays <= 30) {
                                        $timeLeftColorClass = 'bg-red-600 text-white';
                                    } elseif ($totalDays <= 90) {
                                        $timeLeftColorClass = 'bg-red-200 text-red-800';
                                    } else {
                                        $timeLeftColorClass = 'bg-gray-100 text-gray-700';
                                    }
                                } elseif ($isExpired) {
                                    $timeLeftColorClass = 'bg-red-600 text-white';
                                }
                            @endphp

                            <tr class="{{ $rowClass }} hover:bg-[#fff4ed]">
                                <td class="border border-gray-300 px-1 py-2 text-xs text-center break-words" data-sort="{{ $lease->store_number ?: 'N/A' }}">{{ $lease->store_number ?: 'N/A' }}</td>
                                <td class="border border-gray-300 px-1 py-2 text-xs text-right break-words" data-sort="{{ $lease->aws ?: 0 }}">${{ number_format($lease->aws ?: 0, 2) }}</td>
                                <td class="border border-gray-300 px-1 py-2 text-xs text-right break-words" data-sort="{{ $lease->total_rent ?: 0 }}">${{ number_format($lease->total_rent ?: 0, 2) }}</td>
                                <td class="border border-gray-300 px-1 py-2 text-xs text-right break-words" data-sort="{{ $lease->lease_to_sales_ratio ?: 0 }}">{{ $lease->lease_to_sales_ratio ? number_format($lease->lease_to_sales_ratio * 100, 2) . '%' : 'N/A' }}</td>

                                <!-- Color-coded Time Left on Current Term -->
                                <td class="border border-gray-300 px-1 py-2 text-xs text-center break-words leading-tight" data-sort="{{ $currentTerm ? ($currentTerm['time_left']['expired'] ? 'Expired' : $currentTerm['time_left']['formatted']) : 'N/A' }}">
                                    @if($currentTerm)
                                        <span class="inline-flex items-center px-1 py-1 rounded text-xs font-medium {{ $timeLeftColorClass }}">
                                            {{ $currentTerm['time_left']['expired'] ? 'Expired' : $currentTerm['time_left']['formatted'] }}
                                        </span>
                                    @else
                                        <span class="text-gray-400 text-xs">N/A</span>
                                    @endif
                                </td>

                                <!-- NEW: Current Term Column -->
                                <td class="border border-gray-300 px-1 py-2 text-xs text-center break-words font-semibold" data-sort="{{ $lease->current_term ?: 0 }}">
                                    @if($lease->current_term)
                                        <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                            {{ $lease->current_term }}
                                        </span>
                                    @else
                                        <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-gray-100 text-gray-600">
                                            Auto
                                        </span>
                                    @endif
                                </td>

                                <td class="border border-gray-300 px-1 py-2 text-xs text-center break-words leading-tight" data-sort="{{ $lease->time_until_last_term_ends ? $lease->time_until_last_term_ends['formatted'] : 'N/A' }}">{{ $lease->time_until_last_term_ends ? $lease->time_until_last_term_ends['formatted'] : 'N/A' }}</td>
                                <!-- Initial Lease Expiration Date Column -->
                                <td class="border border-gray-300 px-1 py-2 text-xs text-center break-words" data-sort="{{ $lease->initial_lease_expiration_date ? $lease->initial_lease_expiration_date->format('m/d/Y') : 'N/A' }}">{{ $lease->initial_lease_expiration_date ? $lease->initial_lease_expiration_date->format('m/d/Y') : 'N/A' }}</td>
                                <td class="border border-gray-300 px-1 py-2 text-xs text-center break-words" data-sort="{{ $termExpirations[0] }}">{{ $termExpirations[0] }}</td>
                                <td class="border border-gray-300 px-1 py-2 text-xs text-center break-words" data-sort="{{ $termExpirations[1] }}">{{ $termExpirations[1] }}</td>
                                <td class="border border-gray-300 px-1 py-2 text-xs text-center break-words" data-sort="{{ $termExpirations[2] }}">{{ $termExpirations[2] }}</td>
                                <td class="border border-gray-300 px-1 py-2 text-xs text-center break-words" data-sort="{{ $termExpirations[3] }}">{{ $termExpirations[3] }}</td>
                                <td class="border border-gray-300 px-1 py-2 text-xs text-center break-words" data-sort="{{ $termExpirations[4] }}">{{ $termExpirations[4] }}</td>
                                <td class="border border-gray-300 px-1 py-2 text-xs text-center break-words leading-tight" data-sort="{{ $timeUntilFranchiseExpires ? ($timeUntilFranchiseExpires['expired'] ? 'Expired' : $timeUntilFranchiseExpires['formatted']) : 'N/A' }}">{{ $timeUntilFranchiseExpires ? ($timeUntilFranchiseExpires['expired'] ? 'Expired' : $timeUntilFranchiseExpires['formatted']) : 'N/A' }}</td>
                                <td class="border border-gray-300 px-1 py-2 text-xs text-center font-semibold break-words {{ $status == 'EXPIRED' ? 'text-red-600' : ($status == 'WARNING' ? 'text-yellow-600' : 'text-green-600') }}" data-sort="{{ $status }}">{{ $status }}</td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Sorting functionality for the lease tracker table
        let leaseTrackerSortColumn = 0;
        let leaseTrackerSortDirection = 'asc';

        function sortLeaseTrackerTable(column, type) {
            const tbody = document.getElementById('leaseTrackerBody');
            const rows = Array.from(tbody.querySelectorAll('tr'));

            // Toggle sort direction if clicking the same column
            if (leaseTrackerSortColumn === column) {
                leaseTrackerSortDirection = leaseTrackerSortDirection === 'asc' ? 'desc' : 'asc';
            } else {
                leaseTrackerSortDirection = 'asc';
                leaseTrackerSortColumn = column;
            }

            // Clear all sort indicators (updated for 15 columns now)
            for (let i = 0; i <= 14; i++) {
                const indicator = document.getElementById(`leasetracker-sort-indicator-${i}`);
                if (indicator) {
                    if (type === 'number') {
                        indicator.textContent = '↑';
                    } else {
                        indicator.textContent = 'A↓';
                    }
                }
            }

            // Set current sort indicator
            const currentIndicator = document.getElementById(`leasetracker-sort-indicator-${column}`);
            if (currentIndicator) {
                if (type === 'number') {
                    currentIndicator.textContent = leaseTrackerSortDirection === 'asc' ? '↑' : '↓';
                } else {
                    currentIndicator.textContent = leaseTrackerSortDirection === 'asc' ? 'A↓' : 'Z↑';
                }
            }

            // Sort rows
            rows.sort((a, b) => {
                const aValue = a.cells[column].getAttribute('data-sort') || '';
                const bValue = b.cells[column].getAttribute('data-sort') || '';

                let comparison = 0;
                if (type === 'number') {
                    const aNum = parseFloat(aValue) || 0;
                    const bNum = parseFloat(bValue) || 0;
                    comparison = aNum - bNum;
                } else {
                    comparison = aValue.localeCompare(bValue);
                }

                return leaseTrackerSortDirection === 'asc' ? comparison : -comparison;
            });

            // Reattach sorted rows and update alternating colors
            rows.forEach((row, index) => {
                tbody.appendChild(row);
                // Update row colors while preserving status-based colors
                if (!row.classList.contains('bg-red-100') && !row.classList.contains('bg-yellow-100')) {
                    row.className = row.className.replace(/bg-(white|gray-50)/, '');
                    row.classList.add(index % 2 === 0 ? 'bg-white' : 'bg-gray-50');
                    row.classList.add('hover:bg-[#fff4ed]');
                }
            });
        }

        function closeModal(modalId) {
            // Add your modal closing logic here
            window.close(); // or whatever method you use to close the modal
        }
    </script>
@endsection
