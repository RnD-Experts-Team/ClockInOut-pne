@extends('layouts.app')

@section('title', 'Cost By Company')

@section('content')
    <div class="min-h-screen bg-gray-50 py-8">
        <div class="max-w-full mx-auto px-4 sm:px-6 lg:px-8">
            <!-- Simple Header -->
            <div class="text-center mb-8">
                <h1 class="text-3xl font-bold text-white bg-[#ff671b] py-4 px-8 rounded-lg inline-block mb-4">Cost By Company</h1>
                <p class="text-gray-600">Generated on {{ now()->format('F j, Y \a\t g:i A') }}</p>
            </div>

            <!-- Excel-like Table -->
            <div class="bg-white shadow-lg rounded-lg overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="min-w-full border-collapse" id="costByCompanyTable">
                        <thead>
                        <tr class="bg-[#ff671b] text-white">
                            <th class="border border-gray-300 px-6 py-4 text-left text-sm font-semibold cursor-pointer hover:bg-[#e55b17] transition-colors select-none"
                                onclick="sortCompanyTable(0, 'text')" id="company-header-0">
                                <div class="flex items-center justify-between">
                                    Company Name
                                    <span class="ml-2 text-xs opacity-75" id="company-sort-indicator-0">A↓</span>
                                </div>
                            </th>
                            <th class="border border-gray-300 px-6 py-4 text-center text-sm font-semibold cursor-pointer hover:bg-[#e55b17] transition-colors select-none"
                                onclick="sortCompanyTable(1, 'number')" id="company-header-1">
                                <div class="flex items-center justify-center">
                                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                                    </svg>
                                    This Month Cost
                                    <span class="ml-2 text-xs opacity-75" id="company-sort-indicator-1">↑</span>
                                </div>
                            </th>
                            <th class="border border-gray-300 px-6 py-4 text-center text-sm font-semibold cursor-pointer hover:bg-[#e55b17] transition-colors select-none"
                                onclick="sortCompanyTable(2, 'number')" id="company-header-2">
                                <div class="flex items-center justify-center">
                                    90 Days Cost
                                    <span class="ml-2 text-xs opacity-75" id="company-sort-indicator-2">↑</span>
                                </div>
                            </th>
                        </tr>
                        </thead>
                        <tbody id="companyTableBody">
                        @php
                            $companies = \App\Models\Company::with(['payments' => function($query) {
                                $query->thisMonth();
                            }])->get();

                            $companiesWithNinetyDays = \App\Models\Company::with(['payments' => function($query) {
                                $query->within90Days();
                            }])->get();
                        @endphp

                        @foreach($companies as $index => $company)
                            @php
                                $thisMonthCost = $company->payments->sum('cost');
                                $ninetyDaysCost = $companiesWithNinetyDays->find($company->id)->payments->sum('cost');
                            @endphp
                            <tr class="{{ $index % 2 == 0 ? 'bg-gray-50' : 'bg-white' }} hover:bg-[#fff4ed]" data-row-index="{{ $index }}">
                                <td class="border border-gray-300 px-6 py-3 text-sm font-medium text-gray-900" data-sort="{{ $company->name }}">
                                    {{ $company->name }}
                                </td>
                                <td class="border border-gray-300 px-6 py-3 text-sm text-center" data-sort="{{ $thisMonthCost }}">
                                    <div class="flex items-center justify-center">
                                        @if($thisMonthCost > 0)
                                            <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium"
                                                  style="background-color: #eff6ff !important; color: #1e40af !important; border: 1px solid #bfdbfe; border-radius: 9999px; padding: 0.25rem 0.75rem; font-size: 0.75rem; font-weight: 500; display: inline-flex; align-items: center;">
                                            ${{ number_format($thisMonthCost, 2) }}
                                        </span>
                                        @else
                                            <span class="text-gray-400" style="color: #9ca3af !important;">$0.00</span>
                                        @endif
                                    </div>
                                </td>
                                <td class="border border-gray-300 px-6 py-3 text-sm text-center" data-sort="{{ $ninetyDaysCost }}">
                                    <div class="flex items-center justify-center">
                                        @if($ninetyDaysCost > 0)
                                            @if($ninetyDaysCost > 15000)
                                                <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium"
                                                      style="background-color: #fef2f2 !important; color: #991b1b !important; border: 1px solid #fecaca; border-radius: 9999px; padding: 0.25rem 0.75rem; font-size: 0.75rem; font-weight: 500; display: inline-flex; align-items: center;">
                                                ${{ number_format($ninetyDaysCost, 2) }}
                                            </span>
                                            @else
                                                <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium"
                                                      style="background-color: #f0fdf4 !important; color: #166534 !important; border: 1px solid #bbf7d0; border-radius: 9999px; padding: 0.25rem 0.75rem; font-size: 0.75rem; font-weight: 500; display: inline-flex; align-items: center;">
                                                ${{ number_format($ninetyDaysCost, 2) }}
                                            </span>
                                            @endif
                                        @else
                                            <span class="text-gray-400" style="color: #9ca3af !important;">$0.00</span>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Close Button -->
            <div class="mt-6 text-center">
                <button onclick="closeModal('costByCompanyModal')"
                        class="inline-flex items-center px-4 py-2 bg-[#ff671b] text-white text-sm font-medium rounded hover:bg-[#e55b17]">
                    Close
                </button>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            let companySortDirection = {};

            // Make sortCompanyTable function globally available
            window.sortCompanyTable = function(columnIndex, type) {
                const table = document.getElementById('costByCompanyTable');
                const tbody = document.getElementById('companyTableBody');

                if (!table || !tbody) return; // Elements not found

                const rows = Array.from(tbody.querySelectorAll('tr'));

                if (rows.length === 0) return; // No data to sort

                // Toggle sort direction
                const currentDirection = companySortDirection[columnIndex] || 'asc';
                const newDirection = currentDirection === 'asc' ? 'desc' : 'asc';
                companySortDirection[columnIndex] = newDirection;

                // Clear all sort indicators
                for (let i = 0; i < 3; i++) {
                    const indicator = document.getElementById(`company-sort-indicator-${i}`);
                    if (indicator) {
                        if (i === 0) { // Company name column (text)
                            indicator.textContent = 'A↓';
                        } else { // Cost columns (numbers)
                            indicator.textContent = '↑';
                        }
                        indicator.style.opacity = '0.5';
                    }
                }

                // Set active sort indicator
                const activeIndicator = document.getElementById(`company-sort-indicator-${columnIndex}`);
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
                        bValue = (a.cells[columnIndex].getAttribute('data-sort') || '').toLowerCase();
                    }

                    if (newDirection === 'asc') {
                        return aValue > bValue ? 1 : -1;
                    } else {
                        return aValue < bValue ? 1 : -1;
                    }
                });

                // Re-append sorted rows with alternating colors
                rows.forEach((row, index) => {
                    row.className = (index % 2 === 0 ? 'bg-gray-50' : 'bg-white') + ' hover:bg-[#fff4ed]';
                    tbody.appendChild(row);
                });
            };
        });
    </script>
@endsection
