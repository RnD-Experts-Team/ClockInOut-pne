{{-- resources/views/leases/index.blade.php --}}
@extends('layouts.app')

@section('title', 'Lease Management')

@section('content')
<div class="max-w-full mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <!-- Header Section -->
    <div class="sm:flex sm:items-center sm:justify-between mb-6">
        <div>
            <h1 class="text-3xl font-bold text-gray-900">Lease Management</h1>
            <p class="mt-2 text-sm text-gray-700">Manage store leases, terms, and financial analytics</p>
        </div>
        <div class="mt-4 sm:mt-0 flex space-x-3">
            <button type="button" id="openPortfolioModal"
                    class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-lg text-white bg-purple-600 hover:bg-purple-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-purple-500 transition-all duration-300 hover:shadow-lg">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                </svg>
                Portfolio Analytics
            </button>
            <a href="{{ route('leases.create') }}" 
               class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-lg text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-all duration-300 hover:shadow-lg">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                </svg>
                Add Lease
            </a>
            <a href="{{ route('leases.export', request()->query()) }}" 
               class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-lg text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 transition-all duration-300 hover:shadow-lg">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                </svg>
                Export CSV
            </a>
            <a href="{{ route('leases.import.form') }}" 
   class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-lg text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition-all duration-300 hover:shadow-lg">
    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12" />
    </svg>
    Import Leases
</a>
        </div>
    </div>

    <!-- Quick Stats Overview -->
    <div class="grid grid-cols-1 md:grid-cols-5 gap-4 mb-8">
        <div class="bg-white rounded-lg shadow-sm p-6 border border-gray-200 hover:shadow-md transition-shadow">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <svg class="w-8 h-8 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-2m-14 0h2m0 0V9a2 2 0 012-2h2a2 2 0 012 2v10" />
                    </svg>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-500">Total Leases</p>
                    <p class="text-2xl font-bold text-blue-600">{{ $stats['total'] }}</p>
                </div>
            </div>
        </div>
        <div class="bg-white rounded-lg shadow-sm p-6 border border-gray-200 hover:shadow-md transition-shadow">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <svg class="w-8 h-8 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3.055 11H5a2 2 0 012 2v1a2 2 0 002 2 2 2 0 012 2v2.945M8 3.935V5.5A2.5 2.5 0 0010.5 8h.5a2 2 0 012 2 2 2 0 104 0 2 2 0 012-2h1.064M15 20.488V18a2 2 0 012-2h3.064" />
                    </svg>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-500">With HVAC</p>
                    <p class="text-2xl font-bold text-green-600">{{ $stats['with_hvac'] }}</p>
                </div>
            </div>
        </div>
        <div class="bg-white rounded-lg shadow-sm p-6 border border-gray-200 hover:shadow-md transition-shadow">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <svg class="w-8 h-8 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-500">Franchise Expiring</p>
                    <p class="text-2xl font-bold text-red-600">{{ $stats['franchise_expiring_soon'] }}</p>
                </div>
            </div>
        </div>
        <div class="bg-white rounded-lg shadow-sm p-6 border border-gray-200 hover:shadow-md transition-shadow">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <svg class="w-8 h-8 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-500">Lease Expiring</p>
                    <p class="text-2xl font-bold text-orange-600">{{ $stats['lease_expiring_soon'] }}</p>
                </div>
            </div>
        </div>
        <div class="bg-white rounded-lg shadow-sm p-6 border border-gray-200 hover:shadow-md transition-shadow">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <svg class="w-8 h-8 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 5a1 1 0 011-1h14a1 1 0 011 1v2a1 1 0 01-1 1H5a1 1 0 01-1-1V5zM4 13a1 1 0 011-1h6a1 1 0 011 1v6a1 1 0 01-1 1H5a1 1 0 01-1-1v-6zM16 13a1 1 0 011-1h2a1 1 0 011 1v6a1 1 0 01-1 1h-2a1 1 0 01-1-1v-6z" />
                    </svg>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-500">Total SQF</p>
                    <p class="text-2xl font-bold text-purple-600">{{ number_format($stats['total_sqf']) }}</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters Section -->
    <div class="bg-white shadow-lg rounded-xl p-6 mb-8 transition-all duration-300 hover:shadow-xl border border-gray-100">
        <div class="flex justify-between items-center mb-4">
            <h2 class="text-lg font-semibold text-gray-900">Filter Leases</h2>
            <button type="button" id="toggleFilters" class="text-sm text-blue-600 hover:text-blue-800">
                <span id="toggleText">Hide Filters</span>
                <svg id="toggleIcon" class="w-4 h-4 inline ml-1 transform transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                </svg>
            </button>
        </div>
        <form method="GET" action="{{ route('leases.index') }}" id="filterForm" class="space-y-6">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
                <div>
                    <label for="search" class="block text-sm font-semibold text-gray-800 mb-2">Search</label>
                    <input type="text" name="search" id="search" 
                           class="block w-full rounded-xl border-gray-200 shadow-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 sm:text-sm transition-all duration-200 hover:border-blue-400 py-3 px-4" 
                           placeholder="Store number, name, address..." 
                           value="{{ request('search') }}">
                </div>
                <div>
                    <label for="hvac" class="block text-sm font-semibold text-gray-800 mb-2">HVAC</label>
                    <select name="hvac" id="hvac" class="block w-full rounded-xl border-gray-200 shadow-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 sm:text-sm transition-all duration-200 hover:border-blue-400 py-3 px-4">
                        <option value="all" {{ request('hvac') === 'all' || !request('hvac') ? 'selected' : '' }}>All</option>
                        <option value="1" {{ request('hvac') === '1' ? 'selected' : '' }}>With HVAC</option>
                        <option value="0" {{ request('hvac') === '0' ? 'selected' : '' }}>Without HVAC</option>
                    </select>
                </div>
                <div>
                    <label for="expiring" class="block text-sm font-semibold text-gray-800 mb-2">Expiring Soon</label>
                    <select name="expiring" id="expiring" class="block w-full rounded-xl border-gray-200 shadow-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 sm:text-sm transition-all duration-200 hover:border-blue-400 py-3 px-4">
                        <option value="all" {{ request('expiring') === 'all' || !request('expiring') ? 'selected' : '' }}>All</option>
                        <option value="franchise" {{ request('expiring') === 'franchise' ? 'selected' : '' }}>Franchise Expiring</option>
                        <option value="lease" {{ request('expiring') === 'lease' ? 'selected' : '' }}>Lease Expiring</option>
                    </select>
                </div>
                <div class="flex items-end">
                    <div class="w-full space-y-2">
                        <button type="submit" 
                                class="w-full inline-flex justify-center items-center px-4 py-3 border border-transparent text-sm font-medium rounded-xl text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-all duration-200">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                            </svg>
                            Apply Filter
                        </button>
                        <a href="{{ route('leases.index') }}" 
                           class="w-full inline-flex justify-center items-center px-4 py-2 border border-gray-200 text-sm font-medium rounded-xl text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500 transition-all duration-200">
                            Reset
                        </a>
                    </div>
                </div>
            </div>
        </form>
    </div>

    <!-- Leases Table -->
    <div class="bg-white shadow-sm ring-1 ring-black ring-opacity-5 rounded-lg overflow-hidden">
        @if($leases->count() > 0)
            <!-- Desktop Table View -->
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-300">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-3 py-3.5 text-left text-xs font-medium text-gray-500 uppercase tracking-wide">Store Info</th>
                            <th class="px-3 py-3.5 text-left text-xs font-medium text-gray-500 uppercase tracking-wide">Financial</th>
                            <th class="px-3 py-3.5 text-left text-xs font-medium text-gray-500 uppercase tracking-wide">Current Term</th>
                            <th class="px-3 py-3.5 text-left text-xs font-medium text-gray-500 uppercase tracking-wide">Portfolio Metrics</th>
                            <th class="px-3 py-3.5 text-left text-xs font-medium text-gray-500 uppercase tracking-wide">Expirations</th>
                            <th class="px-3 py-3.5 text-left text-xs font-medium text-gray-500 uppercase tracking-wide">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 bg-white">
                        @foreach($leases as $lease)
                            @php
                                $currentTerm = $lease->current_term_info;
                                $timeUntilLastTerm = $lease->time_until_last_term_ends;
                                $leaseToSalesRatio = $lease->lease_to_sales_ratio;
                                $timeUntilFranchiseExpires = $lease->time_until_franchise_expires;
                                $termDates = $lease->term_expiration_dates;
                            @endphp
                            <tr class="hover:bg-gray-50 transition-colors duration-150">
                                <!-- Store Info -->
                                <td class="px-3 py-4 text-sm">
                                    <div class="space-y-1">
                                        <div class="font-medium text-gray-900">{{ $lease->store_number }}</div>
                                        <div class="text-gray-600 text-xs">{{ $lease->name }}</div>
                                        @if($lease->sqf)
                                            <div class="text-xs text-blue-600">{{ number_format($lease->sqf) }} SQF</div>
                                        @endif
                                        @if($lease->hvac)
                                            <span class="inline-flex items-center px-1.5 py-0.5 rounded text-xs font-medium bg-green-100 text-green-800">
                                                HVAC
                                            </span>
                                        @endif
                                    </div>
                                </td>

                                <!-- Financial -->
                                <td class="px-3 py-4 text-sm">
                                    <div class="space-y-1">
                                        <div class="font-semibold text-green-600">${{ number_format($lease->total_rent, 0) }}/mo</div>
                                        @if($lease->aws)
                                            <div class="text-xs text-blue-600">AWS: ${{ number_format($lease->aws, 0) }}</div>
                                        @endif
                                        @if($lease->base_rent)
                                            <div class="text-xs text-gray-500">Base: ${{ number_format($lease->base_rent, 0) }}</div>
                                        @endif
                                    </div>
                                </td>

                                <!-- Current Term -->
                                <td class="px-3 py-4 text-sm">
                                    @if($currentTerm)
                                        <div class="space-y-1">
                                            <div class="font-medium text-gray-900 text-xs">{{ $currentTerm['term_name'] }}</div>
                                            <div class="text-xs {{ $currentTerm['time_left']['expired'] ? 'text-red-600' : 'text-gray-600' }}">
                                                {{ $currentTerm['time_left']['expired'] ? 'Expired' : $currentTerm['time_left']['formatted'] }}
                                            </div>
                                            <div class="text-xs text-gray-500">
                                                {{ $currentTerm['expiration_date']->format('M d, Y') }}
                                            </div>
                                        </div>
                                    @else
                                        <span class="text-gray-400 text-xs">No term data</span>
                                    @endif
                                </td>

                                <!-- Portfolio Metrics -->
                                <td class="px-3 py-4 text-sm">
                                    <div class="space-y-2">
                                        @if($leaseToSalesRatio)
                                            <div class="text-center">
                                                <div class="font-medium text-xs {{ $leaseToSalesRatio > 0.15 ? 'text-red-600' : ($leaseToSalesRatio > 0.10 ? 'text-orange-600' : 'text-green-600') }}">
                                                    {{ number_format($leaseToSalesRatio * 100, 1) }}%
                                                </div>
                                                <div class="text-xs text-gray-500">L2S Ratio</div>
                                            </div>
                                        @endif
                                        @if($termDates->count() > 0)
                                            <div class="text-center">
                                                <div class="font-medium text-xs text-purple-600">{{ $termDates->count() }}</div>
                                                <div class="text-xs text-gray-500">Terms</div>
                                            </div>
                                        @endif
                                    </div>
                                </td>

                                <!-- Expirations -->
                                <td class="px-3 py-4 text-sm">
                                    <div class="space-y-1">
                                        @if($timeUntilLastTerm)
                                            <div class="text-xs">
                                                <span class="text-gray-500">Last Term:</span>
                                                <span class="{{ $timeUntilLastTerm['expired'] ? 'text-red-600' : 'text-gray-600' }}">
                                                    {{ $timeUntilLastTerm['expired'] ? 'Expired' : $timeUntilLastTerm['formatted'] }}
                                                </span>
                                            </div>
                                        @endif
                                        @if($timeUntilFranchiseExpires)
                                            <div class="text-xs">
                                                <span class="text-gray-500">Franchise:</span>
                                                <span class="{{ $timeUntilFranchiseExpires['expired'] ? 'text-red-600' : 'text-gray-600' }}">
                                                    {{ $timeUntilFranchiseExpires['expired'] ? 'Expired' : $timeUntilFranchiseExpires['formatted'] }}
                                                </span>
                                            </div>
                                        @endif
                                    </div>
                                </td>

                                <!-- Actions -->
                                <td class="px-3 py-4 text-sm">
                                    <div class="flex space-x-2">
                                        <a href="{{ route('leases.show', $lease) }}" 
                                           class="inline-flex items-center text-blue-600 hover:text-blue-900 font-medium transition-colors duration-150">
                                            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                            </svg>
                                            View
                                        </a>
                                        <a href="{{ route('leases.edit', $lease) }}" 
                                           class="inline-flex items-center text-orange-600 hover:text-orange-900 font-medium transition-colors duration-150">
                                            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                            </svg>
                                            Edit
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <!-- Mobile Card View -->
            <div class="sm:hidden space-y-4 p-4">
                @foreach($leases as $lease)
                    @php
                        $currentTerm = $lease->current_term_info;
                        $leaseToSalesRatio = $lease->lease_to_sales_ratio;
                    @endphp
                    <div class="bg-white border border-gray-200 rounded-lg p-4 shadow-sm">
                        <div class="flex justify-between items-start mb-3">
                            <div>
                                <p class="text-sm font-medium text-gray-900">{{ $lease->store_number }}</p>
                                <p class="text-xs text-gray-500">{{ $lease->name }}</p>
                            </div>
                            <div class="text-right">
                                <p class="text-sm font-semibold text-green-600">${{ number_format($lease->total_rent, 0) }}/mo</p>
                                @if($leaseToSalesRatio)
                                    <p class="text-xs {{ $leaseToSalesRatio > 0.15 ? 'text-red-600' : ($leaseToSalesRatio > 0.10 ? 'text-orange-600' : 'text-green-600') }}">
                                        {{ number_format($leaseToSalesRatio * 100, 1) }}% L2S
                                    </p>
                                @endif
                            </div>
                        </div>
                        
                        @if($currentTerm)
                            <div class="mb-3 p-2 bg-blue-50 rounded">
                                <p class="text-xs font-medium text-blue-900">{{ $currentTerm['term_name'] }}</p>
                                <p class="text-xs text-blue-700">
                                    {{ $currentTerm['time_left']['expired'] ? 'Expired' : $currentTerm['time_left']['formatted'] . ' remaining' }}
                                </p>
                            </div>
                        @endif
                        
                        <div class="flex justify-between items-center">
                            <div class="flex space-x-4 text-xs text-gray-500">
                                @if($lease->aws)
                                    <span>AWS: ${{ number_format($lease->aws, 0) }}</span>
                                @endif
                                @if($lease->sqf)
                                    <span>{{ number_format($lease->sqf) }} SQF</span>
                                @endif
                            </div>
                            <div class="flex space-x-3">
                                <a href="{{ route('leases.show', $lease) }}" class="text-blue-600 hover:text-blue-900 font-medium text-sm">View</a>
                                <a href="{{ route('leases.edit', $lease) }}" class="text-orange-600 hover:text-orange-900 font-medium text-sm">Edit</a>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

            <!-- Pagination -->
            <div class="px-4 py-3 border-t border-gray-200 sm:px-6">
                <div class="flex justify-between items-center">
                    <div class="text-sm text-gray-700">
                        Showing {{ $leases->firstItem() ?? 0 }} to {{ $leases->lastItem() ?? 0 }} 
                        of {{ $leases->total() }} results
                    </div>
                    <div class="flex space-x-1">
                        {{ $leases->links() }}
                    </div>
                </div>
            </div>
        @else
            <div class="text-center py-12">
                <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-2m-14 0h2m0 0V9a2 2 0 012-2h2a2 2 0 012 2v10" />
                </svg>
                <h3 class="mt-2 text-sm font-medium text-gray-900">No leases found</h3>
                <p class="mt-1 text-sm text-gray-500">Get started by creating a new lease.</p>
                <div class="mt-6">
                    <a href="{{ route('leases.create') }}" 
                       class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-lg text-white bg-blue-600 hover:bg-blue-700">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                        </svg>
                        Add Lease
                    </a>
                </div>
            </div>
        @endif
    </div>
</div>

<!-- Portfolio Analytics Modal -->
<div id="portfolioModal" class="fixed inset-0 z-50 overflow-y-auto hidden" aria-labelledby="modal-title" role="dialog" aria-modal="true">
    <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
        <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" aria-hidden="true" id="modalBackdrop"></div>
        
        <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
        
        <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-6xl sm:w-full">
            <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                <div class="flex justify-between items-center mb-6">
                    <h3 class="text-2xl font-bold text-gray-900" id="modal-title">Portfolio Analytics</h3>
                    <button type="button" id="closePortfolioModal" class="text-gray-400 hover:text-gray-600">
                        <span class="sr-only">Close</span>
                        <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                <!-- Store Selection -->
                <div class="mb-6 bg-gray-50 rounded-lg p-4">
                    <h4 class="text-lg font-semibold text-gray-900 mb-4">Select Stores for Analysis</h4>
                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                        <div>
                            <label for="modal_portfolio_stores" class="block text-sm font-medium text-gray-700 mb-2">
                                Choose Stores
                            </label>
                            <select id="modal_portfolio_stores" multiple 
                                    class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm"
                                    style="min-height: 150px;">
                                @foreach($availableStores as $storeNumber)
                                    <option value="{{ $storeNumber }}">{{ $storeNumber }}</option>
                                @endforeach
                            </select>
                            <p class="mt-1 text-xs text-gray-500">Hold Ctrl/Cmd to select multiple stores</p>
                        </div>
                        
                        <div class="flex flex-col justify-center space-y-3">
                            <button type="button" id="modalSelectAllStores"
                                    class="inline-flex justify-center items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-lg text-gray-700 bg-white hover:bg-gray-50">
                                Select All Stores
                            </button>
                            <button type="button" id="modalClearStoreSelection"
                                    class="inline-flex justify-center items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-lg text-gray-700 bg-white hover:bg-gray-50">
                                Clear Selection
                            </button>
                            <button type="button" id="updatePortfolioStats"
                                    class="inline-flex justify-center items-center px-4 py-2 border border-transparent text-sm font-medium rounded-lg text-white bg-blue-600 hover:bg-blue-700">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                                </svg>
                                Update Analytics
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Portfolio Statistics Display -->
                <div id="portfolioStatsContainer">
                    <!-- Selected Stores Info -->
                    <div id="selectedStoresInfo" class="mb-4 hidden">
                        <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                            <h4 class="text-sm font-semibold text-blue-900 mb-2">Analysis Scope:</h4>
                            <div id="selectedStoresList" class="flex flex-wrap gap-2"></div>
                        </div>
                    </div>

                    <!-- Portfolio Totals -->
                    <div class="mb-6">
                        <h4 class="text-lg font-semibold text-gray-900 mb-4 text-center">Portfolio Totals</h4>
                        <div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-6 gap-4" id="portfolioTotals">
                            <!-- Stats will be populated via JavaScript -->
                        </div>
                    </div>

                    <!-- Portfolio Averages -->
                    <div class="mb-6">
                        <h4 class="text-lg font-semibold text-gray-900 mb-4 text-center">Portfolio Averages</h4>
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-6" id="portfolioAverages">
                            <!-- Averages will be populated via JavaScript -->
                        </div>
                    </div>
                </div>

                <!-- Loading Indicator -->
                <div id="portfolioLoading" class="hidden text-center py-8">
                    <svg class="animate-spin -ml-1 mr-3 h-8 w-8 text-blue-600 mx-auto" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    <p class="text-gray-600 mt-2">Calculating portfolio statistics...</p>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Modal controls
    const portfolioModal = document.getElementById('portfolioModal');
    const openModalBtn = document.getElementById('openPortfolioModal');
    const closeModalBtn = document.getElementById('closePortfolioModal');
    const modalBackdrop = document.getElementById('modalBackdrop');
    
    // Filter toggle
    const toggleFilters = document.getElementById('toggleFilters');
    const filterForm = document.getElementById('filterForm');
    const toggleText = document.getElementById('toggleText');
    const toggleIcon = document.getElementById('toggleIcon');
    
    // Portfolio controls
    const modalStoreSelect = document.getElementById('modal_portfolio_stores');
    const modalSelectAllBtn = document.getElementById('modalSelectAllStores');
    const modalClearBtn = document.getElementById('modalClearStoreSelection');
    const updateStatsBtn = document.getElementById('updatePortfolioStats');
    const portfolioLoading = document.getElementById('portfolioLoading');
    const portfolioStatsContainer = document.getElementById('portfolioStatsContainer');

    // Functions to control body scroll
    function disableBodyScroll() {
        // Save current scroll position
        const scrollY = window.scrollY;
        document.body.style.position = 'fixed';
        document.body.style.top = `-${scrollY}px`;
        document.body.style.width = '100%';
        document.body.setAttribute('data-scroll-position', scrollY);
    }

    function enableBodyScroll() {
        // Restore scroll position
        const scrollY = document.body.getAttribute('data-scroll-position');
        document.body.style.position = '';
        document.body.style.top = '';
        document.body.style.width = '';
        document.body.removeAttribute('data-scroll-position');
        window.scrollTo(0, parseInt(scrollY || '0'));
    }

    // Open modal
    openModalBtn.addEventListener('click', function() {
        portfolioModal.classList.remove('hidden');
        disableBodyScroll(); // Prevent background scrolling
        loadInitialPortfolioStats();
    });

    // Close modal function
    function closeModal() {
        portfolioModal.classList.add('hidden');
        enableBodyScroll(); // Restore background scrolling
    }
    
    closeModalBtn.addEventListener('click', closeModal);
    modalBackdrop.addEventListener('click', closeModal);

    // Close modal on Escape key
    document.addEventListener('keydown', function(event) {
        if (event.key === 'Escape' && !portfolioModal.classList.contains('hidden')) {
            closeModal();
        }
    });

    // Prevent modal content clicks from closing modal
    document.querySelector('#portfolioModal .inline-block').addEventListener('click', function(event) {
        event.stopPropagation();
    });

    // Filter toggle
    toggleFilters.addEventListener('click', function() {
        const isHidden = filterForm.style.display === 'none';
        filterForm.style.display = isHidden ? 'block' : 'none';
        toggleText.textContent = isHidden ? 'Hide Filters' : 'Show Filters';
        toggleIcon.style.transform = isHidden ? 'rotate(0deg)' : 'rotate(-90deg)';
    });

    // Portfolio store selection
    modalSelectAllBtn.addEventListener('click', function() {
        for (let i = 0; i < modalStoreSelect.options.length; i++) {
            modalStoreSelect.options[i].selected = true;
        }
    });

    modalClearBtn.addEventListener('click', function() {
        for (let i = 0; i < modalStoreSelect.options.length; i++) {
            modalStoreSelect.options[i].selected = false;
        }
    });

    // Update portfolio statistics
    updateStatsBtn.addEventListener('click', function() {
        const selectedStores = Array.from(modalStoreSelect.selectedOptions).map(option => option.value);
        updatePortfolioStatistics(selectedStores);
    });

    function loadInitialPortfolioStats() {
        updatePortfolioStatistics([]);
    }

    function updatePortfolioStatistics(stores) {
        portfolioLoading.classList.remove('hidden');
        portfolioStatsContainer.classList.add('hidden');

        fetch('{{ route("leases.portfolio-stats") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({ stores: stores })
        })
        .then(response => response.json())
        .then(data => {
            updatePortfolioDisplay(data);
            portfolioLoading.classList.add('hidden');
            portfolioStatsContainer.classList.remove('hidden');
        })
        .catch(error => {
            console.error('Error:', error);
            portfolioLoading.classList.add('hidden');
            portfolioStatsContainer.classList.remove('hidden');
        });
    }

    function updatePortfolioDisplay(data) {
        // Update selected stores info
        const selectedStoresInfo = document.getElementById('selectedStoresInfo');
        const selectedStoresList = document.getElementById('selectedStoresList');
        
        if (data.selected_stores && data.selected_stores.length > 0) {
            selectedStoresInfo.classList.remove('hidden');
            selectedStoresList.innerHTML = data.selected_stores.map(store => 
                `<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">${store}</span>`
            ).join('');
        } else {
            selectedStoresInfo.classList.add('hidden');
        }

        // Update totals
        const portfolioTotals = document.getElementById('portfolioTotals');
        portfolioTotals.innerHTML = `
            <div class="text-center p-4 bg-blue-50 rounded-lg">
                <p class="text-sm font-medium text-gray-600">Total AWS</p>
                <p class="text-xl font-bold text-blue-600">$${Number(data.totals.aws).toLocaleString('en-US', {minimumFractionDigits: 2})}</p>
            </div>
            <div class="text-center p-4 bg-green-50 rounded-lg">
                <p class="text-sm font-medium text-gray-600">Total Rent</p>
                <p class="text-xl font-bold text-green-600">$${Number(data.totals.total_rent).toLocaleString('en-US', {minimumFractionDigits: 2})}</p>
            </div>
            <div class="text-center p-4 bg-purple-50 rounded-lg">
                <p class="text-sm font-medium text-gray-600">Lease to Sales Ratio</p>
                <p class="text-xl font-bold text-purple-600">${(data.totals.lease_to_sales_ratio * 100).toFixed(2)}%</p>
            </div>
            <div class="text-center p-4 bg-indigo-50 rounded-lg">
                <p class="text-sm font-medium text-gray-600">Total Base Rent</p>
                <p class="text-xl font-bold text-indigo-600">$${Number(data.totals.base_rent).toLocaleString('en-US', {minimumFractionDigits: 2})}</p>
            </div>
            <div class="text-center p-4 bg-yellow-50 rounded-lg">
                <p class="text-sm font-medium text-gray-600">Total Insurance</p>
                <p class="text-xl font-bold text-yellow-600">$${Number(data.totals.insurance).toLocaleString('en-US', {minimumFractionDigits: 2})}</p>
            </div>
            <div class="text-center p-4 bg-red-50 rounded-lg">
                <p class="text-sm font-medium text-gray-600">Total CAM</p>
                <p class="text-xl font-bold text-red-600">$${Number(data.totals.cam).toLocaleString('en-US', {minimumFractionDigits: 2})}</p>
            </div>
        `;

        // Update averages
        const portfolioAverages = document.getElementById('portfolioAverages');
        portfolioAverages.innerHTML = `
            <div class="text-center p-6 bg-blue-50 rounded-lg">
                <p class="text-sm font-medium text-gray-600">Average AWS</p>
                <p class="text-2xl font-bold text-blue-600">$${Number(data.averages.aws).toLocaleString('en-US', {minimumFractionDigits: 2})}</p>
            </div>
            <div class="text-center p-6 bg-green-50 rounded-lg">
                <p class="text-sm font-medium text-gray-600">Average Total Rent</p>
                <p class="text-2xl font-bold text-green-600">$${Number(data.averages.total_rent).toLocaleString('en-US', {minimumFractionDigits: 2})}</p>
            </div>
            <div class="text-center p-6 bg-purple-50 rounded-lg">
                <p class="text-sm font-medium text-gray-600">Average Lease to Sales Ratio</p>
                <p class="text-2xl font-bold text-purple-600">${(data.averages.lease_to_sales_ratio * 100).toFixed(2)}%</p>
            </div>
        `;
    }
});
</script>

@endsection
