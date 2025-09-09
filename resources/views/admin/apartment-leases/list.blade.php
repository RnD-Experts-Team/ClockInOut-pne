@extends('layouts.app')

@section('title', 'Apartment Lease List')

@section('content')
    <div class="min-h-screen bg-gray-50 py-8">
        <div class="max-w-full mx-auto ">
            <!-- Simple Header -->
            <div class="text-center mb-8">
                <h1 class="text-3xl font-bold text-white bg-[#ff671b] py-4 px-8 rounded-lg inline-block mb-4">Apartment Lease List</h1>
                <p class="text-gray-600">Generated on {{ now()->format('F j, Y \a\t g:i A') }}</p>
            </div>

            <!-- Excel-like Table -->
            <div class="bg-white shadow-lg rounded-lg ">
                <div class="overflow-x-auto">
                    <table class="min-w-full " id="leaseTable">
                        <thead>
                        <tr class="bg-[#ff671b] text-white">
                            <th class="border border-gray-300 px-3 py-3 text-left text-sm font-semibold cursor-pointer hover:bg-[#e55b17] transition-colors select-none"
                                onclick="sortLeaseTable(0, 'text')" id="lease-header-0">
                                <div class="flex items-center justify-center">
                                    Store #
                                    <span class="ml-2 text-xs opacity-75" id="lease-sort-indicator-0">A↓</span>
                                </div>
                            </th>
                            <th class="border border-gray-300 px-3 py-3 text-left text-sm font-semibold cursor-pointer hover:bg-[#e55b17] transition-colors select-none"
                                onclick="sortLeaseTable(1, 'text')" id="lease-header-1">
                                <div class="flex items-center justify-between">
                                    Address
                                    <span class="ml-2 text-xs opacity-75" id="lease-sort-indicator-1">A↓</span>
                                </div>
                            </th>
                            <th class="border border-gray-300 px-3 py-3 text-right text-sm font-semibold cursor-pointer hover:bg-[#e55b17] transition-colors select-none"
                                onclick="sortLeaseTable(2, 'number')" id="lease-header-2">
                                <div class="flex items-center justify-end">
                                    Total Rent
                                    <span class="ml-2 text-xs opacity-75" id="lease-sort-indicator-2">↑</span>
                                </div>
                            </th>
                            <th class="border border-gray-300 px-3 py-3 text-right text-sm font-semibold cursor-pointer hover:bg-[#e55b17] transition-colors select-none"
                                onclick="sortLeaseTable(3, 'number')" id="lease-header-3">
                                <div class="flex items-center justify-end">
                                    AT Count
                                    <span class="ml-2 text-xs opacity-75" id="lease-sort-indicator-3">↑</span>
                                </div>
                            </th>
                            <th class="border border-gray-300 px-3 py-3 text-left text-sm font-semibold cursor-pointer hover:bg-[#e55b17] transition-colors select-none"
                                onclick="sortLeaseTable(4, 'text')" id="lease-header-4">
                                <div class="flex items-center justify-between">
                                    Lease Holder
                                    <span class="ml-2 text-xs opacity-75" id="lease-sort-indicator-4">A↓</span>
                                </div>
                            </th>
                            <th class="border border-gray-300 px-3 py-3 text-center text-sm font-semibold cursor-pointer hover:bg-[#e55b17] transition-colors select-none"
                                onclick="sortLeaseTable(5, 'text')" id="lease-header-5">
                                <div class="flex items-center justify-center">
                                    Expires
                                    <span class="ml-2 text-xs opacity-75" id="lease-sort-indicator-5">A↓</span>
                                </div>
                            </th>
                            <th class="border border-gray-300 px-3 py-3 text-center text-sm font-semibold cursor-pointer hover:bg-[#e55b17] transition-colors select-none"
                                onclick="sortLeaseTable(6, 'number')" id="lease-header-6">
                                <div class="flex items-center justify-center">
                                    Family
                                    <span class="ml-2 text-xs opacity-75" id="lease-sort-indicator-6">↑</span>
                                </div>
                            </th>
                            <th class="border border-gray-300 px-3 py-3 text-center text-sm font-semibold cursor-pointer hover:bg-[#e55b17] transition-colors select-none"
                                onclick="sortLeaseTable(7, 'number')" id="lease-header-7">
                                <div class="flex items-center justify-center">
                                    Cars
                                    <span class="ml-2 text-xs opacity-75" id="lease-sort-indicator-7">↑</span>
                                </div>
                            </th>
                        </tr>
                        </thead>
                        <tbody id="leaseTableBody">
                        @forelse($leases as $index => $lease)
                            <tr class="{{ $index % 2 == 0 ? 'bg-white' : 'bg-gray-50' }} hover:bg-[#fff4ed]">
                                <td class="border border-gray-300 px-3 py-3 text-sm text-center"
                                    data-sort="{{ $lease->store_number ?: 'N/A' }}">
                                    {{ $lease->store_number ?: 'N/A' }}
                                </td>
                                <td class="border border-gray-300 px-3 py-3 text-sm"
                                    data-sort="{{ $lease->apartment_address ?: 'N/A' }}">
                                    {{ $lease->apartment_address ?: 'N/A' }}
                                </td>
                                <td class="border border-gray-300 px-3 py-3 text-sm text-right"
                                    data-sort="{{ $lease->total_rent ?: 0 }}">
                                    ${{ number_format($lease->total_rent ?: 0, 0) }}
                                </td>
                                <td class="border border-gray-300 px-3 py-3 text-sm text-right"
                                    data-sort="{{ $lease->number_of_AT ?: 0 }}">
                                    {{ $lease->number_of_AT ?: 'N/A' }}
                                </td>
                                <td class="border border-gray-300 px-3 py-3 text-sm"
                                    data-sort="{{ $lease->lease_holder ?: 'N/A' }}">
                                    {{ $lease->lease_holder ?: 'N/A' }}
                                </td>

                                <!-- Color-coded Expires column -->
                                <td class="border border-gray-300 px-3 py-3 text-sm text-center"
                                    data-sort="{{ $lease->expiration_date ? \Carbon\Carbon::parse($lease->expiration_date)->format('Y-m-d') : 'N/A' }}">
                                    @if($lease->expiration_date)
                                        @php
                                            $expirationDate = \Carbon\Carbon::parse($lease->expiration_date);
                                            $now = \Carbon\Carbon::now();
                                            $daysUntilExpiration = $now->diffInDays($expirationDate, false);
                                            $isExpired = $expirationDate->isPast();

                                            if ($isExpired) {
                                                // Already expired - bright red background
                                                $bgColor = 'bg-red-600 text-white';
                                            } elseif ($daysUntilExpiration <= 30) {
                                                // Expiring within 1 month - bright red background
                                                $bgColor = 'bg-red-600 text-white';
                                            } elseif ($daysUntilExpiration <= 90) {
                                                // Expiring within 3 months - light red background
                                                $bgColor = 'bg-red-200 text-red-800';
                                            } else {
                                                // More than 3 months - light color
                                                $bgColor = 'bg-gray-100 text-gray-700';
                                            }
                                        @endphp
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $bgColor }}">
                                        {{ $expirationDate->format('M j, Y') }}
                                    </span>
                                    @else
                                        N/A
                                    @endif
                                </td>

                                <td class="border border-gray-300 px-3 py-3 text-sm text-center"
                                    data-sort="{{ $lease->is_family === 'Yes' || $lease->is_family === 'yes' ? 1 : 0 }}">
                                    {{ $lease->is_family === 'Yes' || $lease->is_family === 'yes' ? 'Yes' : 'No' }}
                                </td>
                                <td class="border border-gray-300 px-3 py-3 text-sm text-center"
                                    data-sort="{{ $lease->has_car ?: 0 }}">
                                    {{ $lease->has_car > 0 ? $lease->has_car : '0' }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="border border-gray-300 px-3 py-8 text-center text-gray-500">
                                    <div class="flex flex-col items-center">
                                        <svg class="w-12 h-12 text-gray-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                        </svg>
                                        <h3 class="text-lg font-medium text-gray-900 mb-1">No Leases Found</h3>
                                        <p class="text-sm text-gray-500">No apartment lease data available.</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                        </tbody>

                        <!-- Totals Row -->
                        <tfoot>
                        <tr class="bg-[#e55b17] text-white font-semibold">
                            <td class="border border-gray-300 px-3 py-3 text-sm text-center">TOTAL</td>
                            <td class="border border-gray-300 px-3 py-3 text-sm">{{ $leases->count() }} Apartments</td>
                            <td class="border border-gray-300 px-3 py-3 text-sm text-right">${{ number_format($leases->sum(fn($lease) => $lease->total_rent), 0) }}</td>
                            <td class="border border-gray-300 px-3 py-3 text-sm text-right">{{ $leases->sum('number_of_AT') }}</td>
                            <td class="border border-gray-300 px-3 py-3 text-sm"></td>
                            <td class="border border-gray-300 px-3 py-3 text-sm text-center">
                                @php
                                    $expiringWithin30Days = $leases->filter(function($lease) {
                                        if (!$lease->expiration_date) return false;
                                        $expirationDate = \Carbon\Carbon::parse($lease->expiration_date);
                                        $daysUntil = \Carbon\Carbon::now()->diffInDays($expirationDate, false);
                                        return $daysUntil <= 30;
                                    })->count();
                                @endphp
                                {{ $expiringWithin30Days }} Urgent
                            </td>
                            <td class="border border-gray-300 px-3 py-3 text-sm text-center">{{ $leases->where('is_family', 'Yes')->count() + $leases->where('is_family', 'yes')->count() }} Yes</td>
                            <td class="border border-gray-300 px-3 py-3 text-sm text-center">{{ $leases->sum('has_car') }}</td>
                        </tr>
                        </tfoot>
                    </table>
                </div>
            </div>

            <!-- Close Button -->
            <div class="mt-6 text-center">
                <button onclick="closeModal('apartmentLeaseListModal')"
                        class="inline-flex items-center px-4 py-2 bg-[#ff671b] text-white text-sm font-medium rounded hover:bg-[#e55b17]">
                    Close
                </button>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            let sortDirection = {};

            window.sortLeaseTable = function(columnIndex, type) {
                const table = document.getElementById('leaseTable');
                const tbody = document.getElementById('leaseTableBody');
                if (!table || !tbody) return;
                const rows = Array.from(tbody.querySelectorAll('tr:not(:has(td[colspan]))')); // Exclude empty state row

                if (rows.length === 0) return;

                // Toggle sort direction
                const currentDirection = sortDirection[columnIndex] || 'asc';
                const newDirection = currentDirection === 'asc' ? 'desc' : 'asc';
                sortDirection[columnIndex] = newDirection;

                // Clear all sort indicators
                for (let i = 0; i < 8; i++) {
                    const indicator = document.getElementById(`lease-sort-indicator-${i}`);
                    if (indicator) {
                        indicator.textContent = i === 0 || i === 1 || i === 4 || i === 5 ? 'A↓' : '↑';
                        indicator.style.opacity = '0.5';
                    }
                }

                // Set active sort indicator
                const activeIndicator = document.getElementById(`lease-sort-indicator-${columnIndex}`);
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
                    let aValue = a.cells[columnIndex].getAttribute('data-sort') || '';
                    let bValue = b.cells[columnIndex].getAttribute('data-sort') || '';

                    if (type === 'number') {
                        aValue = parseFloat(aValue) || 0;
                        bValue = parseFloat(bValue) || 0;
                    } else {
                        aValue = aValue.toLowerCase();
                        bValue = bValue.toLowerCase();
                    }

                    if (aValue === 'N/A' && bValue === 'N/A') return 0;
                    if (aValue === 'N/A') return 1;
                    if (bValue === 'N/A') return -1;

                    if (newDirection === 'asc') {
                        return aValue > bValue ? 1 : -1;
                    } else {
                        return aValue < bValue ? 1 : -1;
                    }
                });

                // Clear tbody and re-append sorted rows with alternating colors
                while (tbody.firstChild) {
                    tbody.removeChild(tbody.firstChild);
                }
                rows.forEach((row, index) => {
                    row.className = (index % 2 === 0 ? 'bg-white' : 'bg-gray-50') + ' hover:bg-[#fff4ed]';
                    tbody.appendChild(row);
                });

                // Re-append empty state row if present
                const emptyRow = document.querySelector('tr:has(td[colspan])');
                if (emptyRow) {
                    tbody.appendChild(emptyRow);
                }
            };
        });
    </script>
@endsection
