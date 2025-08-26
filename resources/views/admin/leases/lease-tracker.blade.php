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
                            <col style="width: 6%">   <!-- Store # -->
                            <col style="width: 8%">   <!-- AWS -->
                            <col style="width: 8%">   <!-- Total Rent -->
                            <col style="width: 7%">   <!-- L2S Ratio -->
                            <col style="width: 12%">  <!-- Time Left -->
                            <col style="width: 12%">  <!-- Total Lease Life -->
                            <col style="width: 8%">   <!-- Term 1 -->
                            <col style="width: 8%">   <!-- Term 2 -->
                            <col style="width: 8%">   <!-- Term 3 -->
                            <col style="width: 8%">   <!-- Term 4 -->
                            <col style="width: 8%">   <!-- Term 5 -->
                            <col style="width: 9%">   <!-- Franchise Exp -->
                            <col style="width: 8%">   <!-- Status -->
                        </colgroup>
                        <thead>
                        <tr class="bg-[#ff671b] text-white">
                            <th class="border border-gray-300 px-1 py-2 text-center text-xs font-semibold cursor-pointer hover:bg-[#e55b17] transition-colors select-none"
                                onclick="sortLeaseTrackerTable(0, 'text')" id="leasetracker-header-0">
                                <div class="flex flex-col items-center">
                                    <span class="leading-tight">Store</span>
                                    <span class="leading-tight">#</span>
                                    <span class="text-xs opacity-75" id="leasetracker-sort-indicator-0">A↓</span>
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
                            <th class="border border-gray-300 px-1 py-2 text-center text-xs font-semibold cursor-pointer hover:bg-[#e55b17] transition-colors select-none"
                                onclick="sortLeaseTrackerTable(5, 'text')" id="leasetracker-header-5">
                                <div class="flex flex-col items-center">
                                    <span class="leading-tight">Total</span>
                                    <span class="leading-tight">Lease Life</span>
                                    <span class="text-xs opacity-75" id="leasetracker-sort-indicator-5">A↓</span>
                                </div>
                            </th>
                            <th class="border border-gray-300 px-1 py-2 text-center text-xs font-semibold cursor-pointer hover:bg-[#e55b17] transition-colors select-none"
                                onclick="sortLeaseTrackerTable(6, 'text')" id="leasetracker-header-6">
                                <div class="flex flex-col items-center">
                                    <span class="leading-tight">Term 1</span>
                                    <span class="leading-tight">Exp</span>
                                    <span class="text-xs opacity-75" id="leasetracker-sort-indicator-6">A↓</span>
                                </div>
                            </th>
                            <th class="border border-gray-300 px-1 py-2 text-center text-xs font-semibold cursor-pointer hover:bg-[#e55b17] transition-colors select-none"
                                onclick="sortLeaseTrackerTable(7, 'text')" id="leasetracker-header-7">
                                <div class="flex flex-col items-center">
                                    <span class="leading-tight">Term 2</span>
                                    <span class="leading-tight">Exp</span>
                                    <span class="text-xs opacity-75" id="leasetracker-sort-indicator-7">A↓</span>
                                </div>
                            </th>
                            <th class="border border-gray-300 px-1 py-2 text-center text-xs font-semibold cursor-pointer hover:bg-[#e55b17] transition-colors select-none"
                                onclick="sortLeaseTrackerTable(8, 'text')" id="leasetracker-header-8">
                                <div class="flex flex-col items-center">
                                    <span class="leading-tight">Term 3</span>
                                    <span class="leading-tight">Exp</span>
                                    <span class="text-xs opacity-75" id="leasetracker-sort-indicator-8">A↓</span>
                                </div>
                            </th>
                            <th class="border border-gray-300 px-1 py-2 text-center text-xs font-semibold cursor-pointer hover:bg-[#e55b17] transition-colors select-none"
                                onclick="sortLeaseTrackerTable(9, 'text')" id="leasetracker-header-9">
                                <div class="flex flex-col items-center">
                                    <span class="leading-tight">Term 4</span>
                                    <span class="leading-tight">Exp</span>
                                    <span class="text-xs opacity-75" id="leasetracker-sort-indicator-9">A↓</span>
                                </div>
                            </th>
                            <th class="border border-gray-300 px-1 py-2 text-center text-xs font-semibold cursor-pointer hover:bg-[#e55b17] transition-colors select-none"
                                onclick="sortLeaseTrackerTable(10, 'text')" id="leasetracker-header-10">
                                <div class="flex flex-col items-center">
                                    <span class="leading-tight">Term 5</span>
                                    <span class="leading-tight">Exp</span>
                                    <span class="text-xs opacity-75" id="leasetracker-sort-indicator-10">A↓</span>
                                </div>
                            </th>
                            <th class="border border-gray-300 px-1 py-2 text-center text-xs font-semibold cursor-pointer hover:bg-[#e55b17] transition-colors select-none"
                                onclick="sortLeaseTrackerTable(11, 'text')" id="leasetracker-header-11">
                                <div class="flex flex-col items-center">
                                    <span class="leading-tight">Franchise</span>
                                    <span class="leading-tight">Exp</span>
                                    <span class="text-xs opacity-75" id="leasetracker-sort-indicator-11">A↓</span>
                                </div>
                            </th>
                            <th class="border border-gray-300 px-1 py-2 text-center text-xs font-semibold cursor-pointer hover:bg-[#e55b17] transition-colors select-none"
                                onclick="sortLeaseTrackerTable(12, 'text')" id="leasetracker-header-12">
                                <div class="flex flex-col items-center">
                                    <span class="leading-tight">Status</span>
                                    <span class="text-xs opacity-75" id="leasetracker-sort-indicator-12">A↓</span>
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
                                <td class="border border-gray-300 px-1 py-2 text-xs text-center break-words" data-sort="{{ $lease->store_number ?: 'N/A' }}">{{ $lease->store_number ?: 'N/A' }}</td>
                                <td class="border border-gray-300 px-1 py-2 text-xs text-right break-words" data-sort="{{ $lease->aws ?: 0 }}">${{ number_format($lease->aws ?: 0, 2) }}</td>
                                <td class="border border-gray-300 px-1 py-2 text-xs text-right break-words" data-sort="{{ $lease->total_rent ?: 0 }}">${{ number_format($lease->total_rent ?: 0, 2) }}</td>
                                <td class="border border-gray-300 px-1 py-2 text-xs text-right break-words" data-sort="{{ $lease->lease_to_sales_ratio ?: 0 }}">{{ $lease->lease_to_sales_ratio ? number_format($lease->lease_to_sales_ratio * 100, 2) . '%' : 'N/A' }}</td>
                                <td class="border border-gray-300 px-1 py-2 text-xs text-center break-words leading-tight" data-sort="{{ $currentTerm ? ($currentTerm['time_left']['expired'] ? 'Expired' : $currentTerm['time_left']['formatted']) : 'N/A' }}">{{ $currentTerm ? ($currentTerm['time_left']['expired'] ? 'Expired' : $currentTerm['time_left']['formatted']) : 'N/A' }}</td>
                                <td class="border border-gray-300 px-1 py-2 text-xs text-center break-words leading-tight" data-sort="{{ $lease->time_until_last_term_ends ? $lease->time_until_last_term_ends['formatted'] : 'N/A' }}">{{ $lease->time_until_last_term_ends ? $lease->time_until_last_term_ends['formatted'] : 'N/A' }}</td>
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

            <!-- Close Button -->
            <div class="mt-4 text-center">
                <button onclick="closeModal('leaseTrackerModal')"
                        class="inline-flex items-center px-4 py-2 bg-[#ff671b] text-white text-sm font-medium rounded hover:bg-[#e55b17]">
                    Close
                </button>
            </div>
        </div>
    </div>
@endsection
