@extends('layouts.app')

@section('title', 'Cost By Company')

@section('content')
    <div class="min-h-screen bg-gray-50 py-8">
        <div class="max-w-full mx-auto px-4 sm:px-6 lg:px-8">
            <!-- Header -->
            <div class="text-center mb-8">
                <h1 class="text-3xl font-bold text-white bg-[#ff671b] py-4 px-8 rounded-lg inline-block mb-4">Cost By Company</h1>
                <p class="text-gray-600">Generated on {{ now()->format('F j, Y \\a\\t g:i A') }}</p>
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
                        </div>
                    </div>
                </div>
            @endif

            <!-- Table -->
            <div class="bg-white shadow-lg rounded-lg overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="min-w-full border-collapse" id="costByCompanyTable">
                        <thead>
                        <tr class="bg-[#ff671b] text-white">
                            <th class="border border-gray-300 px-6 py-4 text-left text-sm font-semibold cursor-pointer hover:bg-[#e55b17] transition-colors"
                                onclick="sortCompanyTable(0, 'text')" id="company-header-0">
                                <div class="flex items-center">
                                    Company Name
                                    <span class="ml-2 text-xs opacity-75" id="company-sort-indicator-0">A↓</span>
                                </div>
                            </th>
                            <th class="border border-gray-300 px-6 py-4 text-center text-sm font-semibold cursor-pointer hover:bg-[#e55b17] transition-colors"
                                onclick="sortCompanyTable(1, 'number')" id="company-header-1">
                                <div class="flex items-center justify-center">
                                    This Month Cost
                                    <span class="ml-2 text-xs opacity-75" id="company-sort-indicator-1">↑</span>
                                </div>
                            </th>
                            <th class="border border-gray-300 px-6 py-4 text-center text-sm font-semibold cursor-pointer hover:bg-[#e55b17] transition-colors"
                                onclick="sortCompanyTable(2, 'number')" id="company-header-2">
                                <div class="flex items-center justify-center">
                                    90 Days Cost
                                    <span class="ml-2 text-xs opacity-75" id="company-sort-indicator-2">↑</span>
                                </div>
                            </th>
                        </tr>
                        </thead>
                        <tbody id="companyTableBody">
                        @foreach($companies as $index => $company)
                            <tr class="{{ $index % 2 == 0 ? 'bg-gray-50' : 'bg-white' }} hover:bg-gray-100">
                                <td class="border border-gray-300 px-6 py-3 text-sm font-medium" data-sort="{{ strtolower($company->name) }}">
                                    {{ $company->name }}
                                    @if($company->payment_count > 0)
                                        <div class="text-xs text-gray-500">{{ $company->payment_count }} payments</div>
                                    @endif
                                </td>
                                <td class="border border-gray-300 px-6 py-3 text-sm text-center" data-sort="{{ $company->total_cost }}">
                                    @if($company->total_cost > 0)
                                        <div class="font-semibold text-green-600">${{ number_format($company->total_cost, 2) }}</div>
                                        @if($company->unpaid_cost > 0)
                                            <div class="text-xs text-red-500 mt-1">
                                                Unpaid: ${{ number_format($company->unpaid_cost, 2) }}
                                            </div>
                                        @endif
                                    @else
                                        <span class="text-gray-400">$0.00</span>
                                    @endif
                                </td>
                                <td class="border border-gray-300 px-6 py-3 text-sm text-center" data-sort="{{ $companiesWithNinetyDays->where('id', $company->id)->first()->ninety_day_cost ?? 0 }}">
                                    @php
                                        $ninetyDayCost = $companiesWithNinetyDays->where('id', $company->id)->first()->ninety_day_cost ?? 0;
                                    @endphp
                                    @if($ninetyDayCost > 0)
                                        <span class="font-semibold text-[#ff671b]">${{ number_format($ninetyDayCost, 2) }}</span>
                                    @else
                                        <span class="text-gray-400">$0.00</span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                        </tbody>
                        <!-- Total Row -->
                        <tfoot>
                        <tr class="bg-[#e55b17] text-white font-semibold">
                            <td class="border border-gray-300 px-6 py-3 text-sm">
                                Total ({{ $companies->count() }} companies)
                            </td>
                            <td class="border border-gray-300 px-6 py-3 text-sm text-center">
                                ${{ number_format($companies->sum('total_cost'), 2) }}
                            </td>
                            <td class="border border-gray-300 px-6 py-3 text-sm text-center">
                                ${{ number_format($companiesWithNinetyDays->sum('ninety_day_cost'), 2) }}
                            </td>
                        </tr>
                        </tfoot>
                    </table>
                </div>
            </div>

            <!-- Summary Cards -->
            <div class="mt-6 grid grid-cols-1 md:grid-cols-3 gap-4">
                <div class="bg-white p-4 rounded-lg shadow">
                    <div class="text-sm text-gray-600">Top Company</div>
                    <div class="text-lg font-bold text-[#ff671b]">
                        {{ $companies->sortByDesc('total_cost')->first()->name ?? 'N/A' }}
                    </div>
                </div>
                <div class="bg-white p-4 rounded-lg shadow">
                    <div class="text-sm text-gray-600">Average per Company</div>
                    <div class="text-lg font-bold text-green-600">
                        ${{ $companies->count() > 0 ? number_format($companies->avg('total_cost'), 2) : '0.00' }}
                    </div>
                </div>
                <div class="bg-white p-4 rounded-lg shadow">
                    <div class="text-sm text-gray-600">Companies with Costs</div>
                    <div class="text-lg font-bold text-blue-600">
                        {{ $companies->where('total_cost', '>', 0)->count() }}/{{ $companies->count() }}
                    </div>
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
@endsection
