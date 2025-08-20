@extends('layouts.app')

@section('title', 'Lease Tracker Dashboard')

@section('content')
    <div class="min-h-screen bg-gray-50 py-8">
        <div class="max-w-full mx-auto px-4 sm:px-6 lg:px-8">
            <!-- Simple Header -->
            <div class="text-center mb-8">
                <h1 class="text-3xl font-bold text-gray-900 mb-2">Lease Tracker Dashboard</h1>
                <p class="text-gray-600">Generated on {{ now()->format('F j, Y \a\t g:i A') }}</p>
            </div>

            <!-- Excel-like Table -->
            <div class="bg-white shadow-lg rounded-lg overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="min-w-full border-collapse" id="leaseTrackerTable">
                        <thead>
                        <tr class="bg-[#ff671b] text-white">
                            <th class="border border-gray-300 px-3 py-3 text-center text-sm font-semibold cursor-pointer hover:bg-[#e55b17] transition-colors select-none"
                                onclick="sortLeaseTrackerTable(0, 'text')" id="leasetracker-header-0">
                                <div class="flex items-center justify-center">
                                    Store #
                                    <span class="ml-2 text-xs opacity-75" id="leasetracker-sort-indicator-0">A↓</span>
                                </div>
                            </th>
                            <th class="border border-gray-300 px-3 py-3 text-right text-sm font-semibold cursor-pointer hover:bg-[#e55b17] transition-colors select-none"
                                onclick="sortLeaseTrackerTable(1, 'number')" id="leasetracker-header-1">
                                <div class="flex items-center justify-end">
                                    AWS
                                    <span class="ml-2 text-xs opacity-75" id="leasetracker-sort-indicator-1">↑</span>
                                </div>
                            </th>
                            <th class="border border-gray-300 px-3 py-3 text-right text-sm font-semibold cursor-pointer hover:bg-[#e55b17] transition-colors select-none"
                                onclick="sortLeaseTrackerTable(2, 'number')" id="leasetracker-header-2">
                                <div class="flex items-center justify-end">
                                    Total Rent
                                    <span class="ml-2 text-xs opacity-75" id="leasetracker-sort-indicator-2">↑</span>
                                </div>
                            </th>
                            <th class="border border-gray-300 px-3 py-3 text-right text-sm font-semibold cursor-pointer hover:bg-[#e55b17] transition-colors select-none"
                                onclick="sortLeaseTrackerTable(3, 'number')" id="leasetracker-header-3">
                                <div class="flex items-center justify-end">
                                    L2S Ratio
                                    <span class="ml-2 text-xs opacity-75" id="leasetracker-sort-indicator-3">↑</span>
                                </div>
                            </th>
                            <th class="border border-gray-300 px-3 py-3 text-center text-sm font-semibold cursor-pointer hover:bg-[#e55b17] transition-colors select-none"
                                onclick="sortLeaseTrackerTable(4, 'text')" id="leasetracker-header-4">
                                <div class="flex items-center justify-center">
                                    Time Left on Current Term
                                    <span class="ml-2 text-xs opacity-75" id="leasetracker-sort-indicator-4">A↓</span>
                                </div>
                            </th>
                            <th class="border border-gray-300 px-3 py-3 text-center text-sm font-semibold cursor-pointer hover:bg-[#e55b17] transition-colors select-none"
                                onclick="sortLeaseTrackerTable(5, 'text')" id="leasetracker-header-5">
                                <div class="flex items-center justify-center">
                                    Total Lease Life
                                    <span class="ml-2 text-xs opacity-75" id="leasetracker-sort-indicator-5">A↓</span>
                                </div>
                            </th>
                            <th class="border border-gray-300 px-3 py-3 text-center text-sm font-semibold cursor-pointer hover:bg-[#e55b17] transition-colors select-none"
                                onclick="sortLeaseTrackerTable(6, 'text')" id="leasetracker-header-6">
                                <div class="flex items-center justify-center">
                                    Term 1 Exp
                                    <span class="ml-2 text-xs opacity-75" id="leasetracker-sort-indicator-6">A↓</span>
                                </div>
                            </th>
                            <th class="border border-gray-300 px-3 py-3 text-center text-sm font-semibold cursor-pointer hover:bg-[#e55b17] transition-colors select-none"
                                onclick="sortLeaseTrackerTable(7, 'text')" id="leasetracker-header-7">
                                <div class="flex items-center justify-center">
                                    Term 2 Exp
                                    <span class="ml-2 text-xs opacity-75" id="leasetracker-sort-indicator-7">A↓</span>
                                </div>
                            </th>
                            <th class="border border-gray-300 px-3 py-3 text-center text-sm font-semibold cursor-pointer hover:bg-[#e55b17] transition-colors select-none"
                                onclick="sortLeaseTrackerTable(8, 'text')" id="leasetracker-header-8">
                                <div class="flex items-center justify-center">
                                    Term 3 Exp
                                    <span class="ml-2 text-xs opacity-75" id="leasetracker-sort-indicator-8">A↓</span>
                                </div>
                            </th>
                            <th class="border border-gray-300 px-3 py-3 text-center text-sm font-semibold cursor-pointer hover:bg-[#e55b17] transition-colors select-none"
                                onclick="sortLeaseTrackerTable(9, 'text')" id="leasetracker-header-9">
                                <div class="flex items-center justify-center">
                                    Term 4 Exp
                                    <span class="ml-2 text-xs opacity-75" id="leasetracker-sort-indicator-9">A↓</span>
                                </div>
                            </th>
                            <th class="border border-gray-300 px-3 py-3 text-center text-sm font-semibold cursor-pointer hover:bg-[#e55b17] transition-colors select-none"
                                onclick="sortLeaseTrackerTable(10, 'text')" id="leasetracker-header-10">
                                <div class="flex items-center justify-center">
                                    Term 5 Exp
                                    <span class="ml-2 text-xs opacity-75" id="leasetracker-sort-indicator-10">A↓</span>
                                </div>
                            </th>
                            <th class="border border-gray-300 px-3 py-3 text-center text-sm font-semibold cursor-pointer hover:bg-[#e55b17] transition-colors select-none"
                                onclick="sortLeaseTrackerTable(11, 'text')" id="leasetracker-header-11">
                                <div class="flex items-center justify-center">
                                    Franchise Exp
                                    <span class="ml-2 text-xs opacity-75" id="leasetracker-sort-indicator-11">A↓</span>
                                </div>
                            </th>
                            <th class="border border-gray-300 px-3 py-3 text-center text-sm font-semibold cursor-pointer hover:bg-[#e55b17] transition-colors select-none"
                                onclick="sortLeaseTrackerTable(12, 'text')" id="leasetracker-header-12">
                                <div class="flex items-center justify-center">
                                    Status
                                    <span class="ml-2 text-xs opacity-75" id="leasetracker-sort-indicator-12">A↓</span>
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

                                // Get term expiration dates
                                $termExpirations = ['N/A', 'N/A', 'N/A', 'N/A', 'N/A'];
                                if ($termDates && $termDates->count() > 1) {
                                    for($i = 1; $i <= 5; $i++) {
                                        $term = $termDates->skip($i)->first();
                                        if ($term) {
                                            $termExpirations[$i-1] = $term['expiration_date']->format('m/d/Y');
                                        }
                                    }
                                }
                            @endphp

                            <tr class="{{ $rowClass }} hover:bg-[#fff4ed]">
                                <td class="border border-gray-300 px-3 py-3 text-sm text-center" data-sort="{{ $lease->store_number ?: 'N/A' }}">{{ $lease->store_number ?: 'N/A' }}</td>
                                <td class="border border-gray-300 px-3 py-3 text-sm text-right" data-sort="{{ $lease->aws ?: 0 }}">${{ number_format($lease->aws ?: 0, 0) }}</td>
                                <td class="border border-gray-300 px-3 py-3 text-sm text-right" data-sort="{{ $lease->total_rent ?: 0 }}">${{ number_format($lease->total_rent ?: 0, 0) }}</td>
                                <td class="border border-gray-300 px-3 py-3 text-sm text-right" data-sort="{{ $lease->lease_to_sales_ratio ?: 0 }}">{{ $lease->lease_to_sales_ratio ? number_format($lease->lease_to_sales_ratio * 100, 2) . '%' : 'N/A' }}</td>
                                <td class="border border-gray-300 px-3 py-3 text-sm text-center" data-sort="{{ $currentTerm ? ($currentTerm['time_left']['expired'] ? 'Expired' : $currentTerm['time_left']['formatted']) : 'N/A' }}">{{ $currentTerm ? ($currentTerm['time_left']['expired'] ? 'Expired' : $currentTerm['time_left']['formatted']) : 'N/A' }}</td>
                                <td class="border border-gray-300 px-3 py-3 text-sm text-center" data-sort="{{ $lease->time_until_last_term_ends ? $lease->time_until_last_term_ends['formatted'] : 'N/A' }}">{{ $lease->time_until_last_term_ends ? $lease->time_until_last_term_ends['formatted'] : 'N/A' }}</td>
                                <td class="border border-gray-300 px-3 py-3 text-sm text-center" data-sort="{{ $termExpirations[0] }}">{{ $termExpirations[0] }}</td>
                                <td class="border border-gray-300 px-3 py-3 text-sm text-center" data-sort="{{ $termExpirations[1] }}">{{ $termExpirations[1] }}</td>
                                <td class="border border-gray-300 px-3 py-3 text-sm text-center" data-sort="{{ $termExpirations[2] }}">{{ $termExpirations[2] }}</td>
                                <td class="border border-gray-300 px-3 py-3 text-sm text-center" data-sort="{{ $termExpirations[3] }}">{{ $termExpirations[3] }}</td>
                                <td class="border border-gray-300 px-3 py-3 text-sm text-center" data-sort="{{ $termExpirations[4] }}">{{ $termExpirations[4] }}</td>
                                <td class="border border-gray-300 px-3 py-3 text-sm text-center" data-sort="{{ $timeUntilFranchiseExpires ? ($timeUntilFranchiseExpires['expired'] ? 'Expired' : $timeUntilFranchiseExpires['formatted']) : 'N/A' }}">{{ $timeUntilFranchiseExpires ? ($timeUntilFranchiseExpires['expired'] ? 'Expired' : $timeUntilFranchiseExpires['formatted']) : 'N/A' }}</td>
                                <td class="border border-gray-300 px-3 py-3 text-sm text-center font-semibold {{ $status == 'EXPIRED' ? 'text-red-600' : ($status == 'WARNING' ? 'text-yellow-600' : 'text-green-600') }}" data-sort="{{ $status }}">{{ $status }}</td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Close Button -->
            <div class="mt-6 text-center">
                <button onclick="closeModal('leaseTrackerModal')"
                        class="inline-flex items-center px-4 py-2 bg-[#ff671b] text-white text-sm font-medium rounded hover:bg-[#e55b17]">
                    Close
                </button>
            </div>
        </div>
    </div>

    <script>

    </script>
@endsection
