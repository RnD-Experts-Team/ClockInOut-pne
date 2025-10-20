@extends('layouts.app')

@section('title', 'Lease Management')

@section('content')
        <div class="max-w-full mx-auto px-4 sm:px-6 lg:px-8 py-8">
            <!-- Header Section -->
            <div class="sm:flex sm:items-center sm:justify-between mb-6">
                <div>
                    <h1 class="text-3xl font-bold text-black-900">Lease Management</h1>
                    <p class="mt-2 text-sm text-black-600">Manage store leases, terms, financial analytics, and renewals</p>
                </div>
                <div class="mt-4 sm:mt-0 flex space-x-3">
                    <button type="button" id="openPortfolioModal"
                            class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-lg text-white bg-orange-600 hover:bg-orange-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-orange-500 transition-all duration-300 hover:shadow-lg">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                        </svg>
                        Portfolio Analytics
                    </button>
                    <a href="{{ route('leases.create') }}"
                       class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-lg text-white bg-orange-600 hover:bg-orange-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-orange-500 transition-all duration-300 hover:shadow-lg">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                        </svg>
                        Add Lease
                    </a>
                    <a href="{{ route('leases.export', request()->query()) }}"
                       class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-lg text-white bg-orange-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 transition-all duration-300 hover:shadow-lg">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                        Export CSV
                    </a>

                    <!-- Report Views Dropdown -->
                    <div class="relative inline-block text-left">
                        <button type="button" id="reportsDropdown"
                                class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-lg text-white bg-orange-600 hover:bg-orange-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-orange-500 transition-all duration-300 hover:shadow-lg">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a4 4 0 01-4-4V5a4 4 0 014-4h10a4 4 0 014 4v10a4 4 0 01-4 4z"></path>
                            </svg>
                            View Reports
                            <svg class="w-4 h-4 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                            </svg>
                        </button>
                        <div id="reportsMenu" class="hidden origin-top-right absolute right-0 mt-2 w-64 rounded-md shadow-lg bg-white ring-1 ring-black ring-opacity-5 z-50">
                            <div class="py-1" role="menu">
                                <button onclick="openModal('landlordContactModal')"
                                        class="group flex items-center px-4 py-2 text-sm text-black-700 hover:bg-orange-100 hover:text-black-600 w-full text-left"
                                        role="menuitem">
                                    <svg class="w-4 h-4 mr-3 text-black-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                                    </svg>
                                    Landlord Contact Directory
                                </button>
                                <button onclick="openModal('costBreakdownModal')"
                                        class="group flex items-center px-4 py-2 text-sm text-black-700 hover:bg-orange-100 hover:text-black-600 w-full text-left"
                                        role="menuitem">
                                    <svg class="w-4 h-4 mr-3 text-black-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z"></path>
                                    </svg>
                                    Cost Breakdown Analysis
                                </button>
                                <button onclick="openModal('leaseTrackerModal')"
                                        class="group flex items-center px-4 py-2 text-sm text-black-700 hover:bg-orange-100 hover:text-black-600 w-full text-left"
                                        role="menuitem">
                                    <svg class="w-4 h-4 mr-3 text-black-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                                    </svg>
                                    Lease Tracker Dashboard
                                </button>
                            </div>
                        </div>
                    </div>

                    <a href="{{ route('leases.import.form') }}"
                       class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-lg text-white bg-orange-600 hover:bg-orange-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-orange-500 transition-all duration-300 hover:shadow-lg">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12" />
                        </svg>
                        Import Leases
                    </a>
                </div>
            </div>

            <!-- NEW: Renewal Alerts Banner -->
            @if(isset($stats['renewals_due_soon']) && $stats['renewals_due_soon'] > 0)
                <div class="mb-6 bg-blue-50 border-l-4 border-blue-400 p-4 rounded-lg shadow-sm">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <svg class="h-5 w-5 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                        <div class="ml-3 flex-1">
                            <p class="text-sm text-blue-700">
                                <span class="font-medium">🔔 Lease Renewals Alert:</span>
                                {{ $stats['renewals_due_soon'] }} lease{{ $stats['renewals_due_soon'] > 1 ? 's' : '' }}
                                {{ $stats['renewals_due_soon'] > 1 ? 'are' : 'is' }} due for renewal within 30 days.
                                @if(isset($stats['overdue_renewals']) && $stats['overdue_renewals'] > 0)
                                    <span class="font-semibold text-red-600">{{ $stats['overdue_renewals'] }} renewal{{ $stats['overdue_renewals'] > 1 ? 's are' : ' is' }} overdue!</span>
                                @endif
                            </p>
                        </div>
                        <div class="ml-3">
                            <button onclick="filterByRenewals()" class="inline-flex items-center px-3 py-1 border border-blue-300 text-xs font-medium rounded text-blue-700 bg-blue-100 hover:bg-blue-200">
                                View Renewals
                            </button>
                        </div>
                    </div>
                </div>
            @endif

            <!-- Enhanced Quick Stats Overview with Renewal Stats -->
            <div class="grid grid-cols-1 md:grid-cols-6 gap-4 mb-8">
                <div class="bg-orange-50 rounded-lg shadow-sm p-6 border border-orange-200 hover:shadow-md transition-shadow">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <svg class="w-8 h-8 text-black-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-2m-14 0h2m0 0V9a2 2 0 012-2h2a2 2 0 012 2v10" />
                            </svg>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-black-700">Total Leases</p>
                            <p class="text-2xl font-bold text-black-600">{{ $stats['total'] }}</p>
                        </div>
                    </div>
                </div>

                <div class="bg-orange-50 rounded-lg shadow-sm p-6 border border-orange-200 hover:shadow-md transition-shadow">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <svg class="w-8 h-8 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3.055 11H5a2 2 0 012 2v1a2 2 0 002 2 2 2 0 012 2v2.945M8 3.935V5.5A2.5 2.5 0 0010.5 8h.5a2 2 0 012 2 2 2 0 104 0 2 2 0 012-2h1.064M15 20.488V18a2 2 0 012-2h3.064" />
                            </svg>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-black-700">With HVAC</p>
                            <p class="text-2xl font-bold text-green-600">{{ $stats['with_hvac'] }}</p>
                        </div>
                    </div>
                </div>

                <div class="bg-orange-50 rounded-lg shadow-sm p-6 border border-orange-200 hover:shadow-md transition-shadow">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <svg class="w-8 h-8 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-black-700">Franchise Expiring</p>
                            <p class="text-2xl font-bold text-red-600">{{ $stats['franchise_expiring_soon'] }}</p>
                        </div>
                    </div>
                </div>

                <div class="bg-orange-50 rounded-lg shadow-sm p-6 border border-orange-200 hover:shadow-md transition-shadow">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <svg class="w-8 h-8 text-black-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-black-700">Lease Expiring</p>
                            <p class="text-2xl font-bold text-black-600">{{ $stats['lease_expiring_soon'] }}</p>
                        </div>
                    </div>
                </div>

                <!-- NEW: Renewals Due Soon -->
                <div class="bg-blue-50 rounded-lg shadow-sm p-6 border border-blue-200 hover:shadow-md transition-shadow">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <svg class="w-8 h-8 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-blue-700">Renewals Due</p>
                            <p class="text-2xl font-bold text-blue-600">{{ $stats['renewals_due_soon'] ?? 0 }}</p>
                        </div>
                    </div>
                </div>

                <!-- NEW: Overdue Renewals -->
                <div class="bg-red-50 rounded-lg shadow-sm p-6 border border-red-200 hover:shadow-md transition-shadow">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <svg class="w-8 h-8 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                            </svg>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-red-700">Overdue Renewals</p>
                            <p class="text-2xl font-bold text-red-600">{{ $stats['overdue_renewals'] ?? 0 }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Enhanced Filters Section with Renewal Filters -->
            <div class="bg-orange-50 shadow-lg rounded-xl p-6 mb-8 transition-all duration-300 hover:shadow-xl border border-orange-200">
                <div class="flex justify-between items-center mb-4">
                    <h2 class="text-lg font-semibold text-black-900">Filter Leases</h2>
                    <button type="button" id="toggleFilters" class="text-sm text-black-600 hover:text-black-700">
                        <span id="toggleText">Hide Filters</span>
                        <svg id="toggleIcon" class="w-4 h-4 inline ml-1 transform transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                        </svg>
                    </button>
                </div>
                <form method="GET" action="{{ route('leases.index') }}" id="filterForm" class="space-y-6">
                    <div class="grid grid-cols-1 md:grid-cols-5 gap-6">
                        <div>
                            <label for="search" class="block text-sm font-semibold text-black-700 mb-2">Search</label>
                            <input type="text" name="search" id="search"
                                   class="block w-full rounded-xl border-orange-300 shadow-sm focus:border-orange-500 focus:ring-2 focus:ring-orange-500/20 sm:text-sm transition-all duration-200 hover:border-orange-500 py-3 px-4"
                                   placeholder="Store number, name, address..."
                                   value="{{ request('search') }}">
                        </div>

                        <div>
                            <label for="hvac" class="block text-sm font-semibold text-black-700 mb-2">HVAC</label>
                            <select name="hvac" id="hvac" class="block w-full rounded-xl border-orange-300 shadow-sm focus:border-orange-500 focus:ring-2 focus:ring-orange-500/20 sm:text-sm transition-all duration-200 hover:border-orange-500 py-3 px-4">
                                <option value="all" {{ request('hvac') === 'all' || !request('hvac') ? 'selected' : '' }}>All</option>
                                <option value="1" {{ request('hvac') === '1' ? 'selected' : '' }}>With HVAC</option>
                                <option value="0" {{ request('hvac') === '0' ? 'selected' : '' }}>Without HVAC</option>
                            </select>
                        </div>

                        <div>
                            <label for="expiring" class="block text-sm font-semibold text-black-700 mb-2">Expiring Soon</label>
                            <select name="expiring" id="expiring" class="block w-full rounded-xl border-orange-300 shadow-sm focus:border-orange-500 focus:ring-2 focus:ring-orange-500/20 sm:text-sm transition-all duration-200 hover:border-orange-500 py-3 px-4">
                                <option value="all" {{ request('expiring') === 'all' || !request('expiring') ? 'selected' : '' }}>All</option>
                                <option value="franchise" {{ request('expiring') === 'franchise' ? 'selected' : '' }}>Franchise Expiring</option>
                                <option value="lease" {{ request('expiring') === 'lease' ? 'selected' : '' }}>Lease Expiring</option>
                                <option value="renewal" {{ request('expiring') === 'renewal' ? 'selected' : '' }}>🔔 Renewals Due</option>
                            </select>
                        </div>

                        <!-- NEW: Lease Status Filter with Renewal Options -->
                        <div>
                            <label for="lease_status" class="block text-sm font-semibold text-black-700 mb-2">Status</label>
                            <select name="lease_status" id="lease_status" class="block w-full rounded-xl border-orange-300 shadow-sm focus:border-orange-500 focus:ring-2 focus:ring-orange-500/20 sm:text-sm transition-all duration-200 hover:border-orange-500 py-3 px-4">
                                <option value="all" {{ request('lease_status') === 'all' || !request('lease_status') ? 'selected' : '' }}>All Status</option>
                                <option value="active" {{ request('lease_status') === 'active' ? 'selected' : '' }}>Active Leases</option>
                                <option value="expiring_soon" {{ request('lease_status') === 'expiring_soon' ? 'selected' : '' }}>Expiring Soon</option>
                                <option value="expired" {{ request('lease_status') === 'expired' ? 'selected' : '' }}>Expired</option>
                                <option value="renewal_pending" {{ request('lease_status') === 'renewal_pending' ? 'selected' : '' }}>🔔 Renewal Pending</option>
                                <option value="renewal_overdue" {{ request('lease_status') === 'renewal_overdue' ? 'selected' : '' }}>⚠️ Renewal Overdue</option>
                            </select>
                        </div>

                        <div class="flex items-end">
                            <div class="w-full space-y-2">
                                <button type="submit"
                                        class="w-full inline-flex justify-center items-center px-4 py-3 border border-transparent text-sm font-medium rounded-xl text-white bg-orange-600 hover:bg-orange-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-orange-500 transition-all duration-200">
                                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                                    </svg>
                                    Apply Filter
                                </button>
                                <a href="{{ route('leases.index') }}"
                                   class="w-full inline-flex justify-center items-center px-4 py-2 border border-orange-300 text-sm font-medium rounded-xl text-black-700 bg-white hover:bg-orange-100 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-orange-500 transition-all duration-200">
                                    Reset
                                </a>
                            </div>
                        </div>
                    </div>
                </form>
            </div>

            <!-- Enhanced Leases Table with Renewal Information -->
            <div class="bg-orange-50 shadow-sm ring-1 ring-black ring-opacity-5 rounded-lg overflow-hidden">
                @if($leases->count() > 0)
                    <!-- Desktop Table View -->
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-orange-200">
                            <thead class="bg-orange-100">
                            <tr>
                                <th class="px-3 py-3.5 text-left text-xs font-medium text-black-700 uppercase tracking-wide">Store Info</th>
                                <th class="px-3 py-3.5 text-left text-xs font-medium text-black-700 uppercase tracking-wide">Financial</th>
                                <th class="px-3 py-3.5 text-left text-xs font-medium text-black-700 uppercase tracking-wide">Current Term</th>
                                <th class="px-3 py-3.5 text-left text-xs font-medium text-black-700 uppercase tracking-wide">📋 Renewal Status</th>
                                <th class="px-3 py-3.5 text-left text-xs font-medium text-black-700 uppercase tracking-wide">Expirations</th>
                                <th class="px-3 py-3.5 text-left text-xs font-medium text-black-700 uppercase tracking-wide">Actions</th>
                            </tr>
                            </thead>
                            <tbody class="divide-y divide-orange-200 bg-orange-50">
                            @foreach($leases as $lease)
                                @php
                                    $currentTerm = $lease->current_term_info;
                                    $timeUntilLastTerm = $lease->time_until_last_term_ends;
                                    $leaseToSalesRatio = $lease->lease_to_sales_ratio;
                                    $timeUntilFranchiseExpires = $lease->time_until_franchise_expires;
                                    $termDates = $lease->term_expiration_dates;
                                    $renewalStatusInfo = $lease->renewal_status_info;
                                @endphp
                                <tr class="hover:bg-orange-100 transition-colors duration-150 {{ $lease->is_renewal_overdue ? 'bg-red-50 border-l-4 border-red-400' : '' }}">
                                    <!-- Store Info -->
                                    <td class="px-3 py-4 text-sm">
                                        <div class="space-y-1">
                                            <div class="font-medium text-black-900">Store #{{ $lease->store_number }}</div>
                                            @if($lease->store)
                                                <div class="text-black-700 text-xs font-semibold">{{ $lease->store->name }}</div>
                                            @else
                                                <div class="text-red-500 text-xs">No Store Linked</div>
                                            @endif
                                            @if($lease->name && $lease->name !== ($lease->store->name ?? ''))
                                                <div class="text-black-600 text-xs italic">{{ $lease->name }}</div>
                                            @endif
                                            @if($lease->sqf)
                                                <div class="text-xs text-black-600">{{ number_format($lease->sqf) }} SQF</div>
                                            @endif
                                            @if($lease->hvac)
                                                <span class="inline-flex items-center px-1.5 py-0.5 rounded text-xs font-medium bg-green-100 text-green-800">HVAC</span>
                                            @endif
                                        </div>
                                    </td>

                                    <!-- Financial -->
                                    <td class="px-3 py-4 text-sm">
                                        <div class="space-y-1">
                                            <div class="font-semibold text-green-600">${{ number_format($lease->total_rent, 2) }}/mo</div>
                                            @if($lease->aws)
                                                <div class="text-xs text-black-600">AWS: ${{ number_format($lease->aws, 2) }}</div>
                                            @endif
                                            @if($lease->base_rent)
                                                <div class="text-xs text-black-700">Base: ${{ number_format($lease->base_rent, 2) }}</div>
                                            @endif
                                        </div>
                                    </td>

                                    <!-- Current Term -->
                                    <td class="px-3 py-4 text-sm">
                                        @if($currentTerm)
                                            <div class="space-y-1">
                                                <div class="font-medium text-black-900 text-xs">{{ $currentTerm['term_name'] }}</div>
                                                <div class="text-xs {{ $currentTerm['time_left']['expired'] ? 'text-red-600' : 'text-black-700' }}">
                                                    {{ $currentTerm['time_left']['expired'] ? 'Expired' : $currentTerm['time_left']['formatted'] }}
                                                </div>
                                                <div class="text-xs text-black-700">
                                                    {{ $currentTerm['expiration_date']->format('M d, Y') }}
                                                </div>
                                            </div>
                                        @else
                                            <span class="text-black-700 text-xs">No term data</span>
                                        @endif
                                    </td>

                                    <!-- NEW: Renewal Status Column -->
                                    <td class="px-3 py-4 text-sm">
                                        <div class="space-y-2">
                                            @if($lease->renewal_date)
                                                <div class="space-y-1">
                                                    <!-- Renewal Status Badge -->
                                                    <div class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium {{ $renewalStatusInfo['class'] }}">
                                                        {{ $renewalStatusInfo['message'] }}
                                                    </div>
                                                    <!-- Renewal Date -->
                                                    <div class="text-xs text-gray-600">
                                                        Due: {{ $lease->formatted_renewal_date }}
                                                    </div>
                                                    <!-- Created By -->
                                                    @if($lease->renewalCreatedBy)
                                                        <div class="text-xs text-gray-500">
                                                            By: {{ $lease->renewalCreatedBy->name }}
                                                        </div>
                                                    @endif
                                                </div>
                                                <!-- Renewal Action Buttons -->
                                                <div class="flex space-x-1">
                                                    @if($lease->renewal_status === 'pending')
                                                        <button onclick="completeRenewal({{ $lease->id }})"
                                                                class="inline-flex items-center px-2 py-1 text-xs font-medium rounded text-green-700 bg-green-100 hover:bg-green-200">
                                                            ✅ Complete
                                                        </button>
                                                        <button onclick="sendRenewalReminder({{ $lease->id }})"
                                                                class="inline-flex items-center px-2 py-1 text-xs font-medium rounded text-blue-700 bg-blue-100 hover:bg-blue-200">
                                                            🔔 Remind
                                                        </button>
                                                    @elseif($lease->renewal_status === 'completed')
                                                        <span class="inline-flex items-center px-2 py-1 text-xs font-medium rounded text-green-700 bg-green-100">
                                                    ✅ Done
                                                </span>
                                                    @endif
                                                </div>
                                            @else
                                                <div class="text-xs text-gray-500">
                                                    No renewal date set
                                                </div>
                                                <button onclick="setRenewalDate({{ $lease->id }})"
                                                        class="inline-flex items-center px-2 py-1 text-xs font-medium rounded text-blue-700 bg-blue-100 hover:bg-blue-200">
                                                    📅 Set Date
                                                </button>
                                            @endif
                                        </div>
                                    </td>

                                    <!-- Expirations -->
                                    <td class="px-3 py-4 text-sm">
                                        <div class="space-y-1">
                                            @if($timeUntilLastTerm)
                                                <div class="text-xs">
                                                    <span class="text-black-700">Last Term:</span>
                                                    <span class="{{ $timeUntilLastTerm['expired'] ? 'text-red-600' : 'text-black-700' }}">
                                                {{ $timeUntilLastTerm['expired'] ? 'Expired' : $timeUntilLastTerm['formatted'] }}
                                            </span>
                                                </div>
                                            @endif
                                            @if($timeUntilFranchiseExpires)
                                                <div class="text-xs">
                                                    <span class="text-black-700">Franchise:</span>
                                                    <span class="{{ $timeUntilFranchiseExpires['expired'] ? 'text-red-600' : 'text-black-700' }}">
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
                                               class="inline-flex items-center text-black-600 hover:text-black-700 font-medium transition-colors duration-150">
                                                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                                </svg>
                                                View
                                            </a>
                                            <a href="{{ route('leases.edit', $lease) }}"
                                               class="inline-flex items-center text-black-600 hover:text-black-700 font-medium transition-colors duration-150">
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

                    <!-- Pagination -->
                    <div class="px-4 py-3 border-t border-orange-200 sm:px-6">
                        <div class="flex justify-between items-center">
                            <div class="text-sm text-black-700">
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
                        <svg class="mx-auto h-12 w-12 text-black-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-2m-14 0h2m0 0V9a2 2 0 012-2h2a2 2 0 012 2v10" />
                        </svg>
                        <h3 class="mt-2 text-sm font-medium text-black-900">No leases found</h3>
                        <p class="mt-1 text-sm text-black-700">Get started by creating a new lease.</p>
                        <div class="mt-6">
                            <a href="{{ route('leases.create') }}"
                               class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-lg text-white bg-orange-600 hover:bg-orange-700">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                                </svg>
                                Add Lease
                            </a>
                        </div>
                    </div>
                @endif
            </div>

            <!-- Existing Modals (keeping them as they are) -->
            <!-- ... keep all your existing modal code ... -->
            <!-- Modal Containers -->
            <!-- Landlord Contact Modal -->
            <div id="landlordContactModal" class="hidden fixed inset-0  bg-opacity-50 overflow-y-auto h-full w-full z-50">
                <div class="relative top-4 mx-auto p-5 border w-11/12 max-w-7xl shadow-lg rounded-md bg-orange-50">
                    <div class="mt-3">
                        <div class="flex justify-between items-center mb-4">
                            <div class="flex space-x-2">
                                <button onclick="generateScreenshot('landlordContactModal', 'landlord-contact')"
                                        class="inline-flex items-center px-3 py-2 bg-green-600 text-white text-sm font-medium rounded hover:bg-green-700 transition-colors">
                                    📸 Screenshot
                                </button>
                                <button onclick="closeModal('landlordContactModal')"
                                        class="text-black-700 hover:text-black-600 text-xl font-bold">&times;</button>
                            </div>
                        </div>
                        <div id="landlordContactContent" class="modal-content">
                            <!-- Content will be loaded here -->
                        </div>
                    </div>
                </div>
            </div>

            <!-- Cost Breakdown Modal -->
            <div id="costBreakdownModal" class="hidden fixed inset-0  bg-opacity-50 overflow-y-auto h-full w-full z-50">
                <div class="relative top-4 mx-auto p-5 border w-11/12 max-w-7xl shadow-lg rounded-md bg-orange-50">
                    <div class="mt-3">
                        <div class="flex justify-between items-center mb-4">
                            <div class="flex space-x-2">
                                <button onclick="generateScreenshot('costBreakdownModal', 'cost-breakdown')"
                                        class="inline-flex items-center px-3 py-2 bg-green-600 text-white text-sm font-medium rounded hover:bg-green-700 transition-colors">
                                    📸 Screenshot
                                </button>
                                <button onclick="closeModal('costBreakdownModal')"
                                        class="text-black-700 hover:text-black-600 text-xl font-bold">&times;</button>
                            </div>
                        </div>
                        <div id="costBreakdownContent" class="modal-content">
                            <!-- Content will be loaded here -->
                        </div>
                    </div>
                </div>
            </div>

            <!-- Lease Tracker Modal -->
            <div id="leaseTrackerModal" class="hidden fixed inset-0 bg-opacity-50 overflow-y-auto h-full w-full z-50">
                <div class="relative top-4 mx-auto p-5 border w-11/12 max-w-7xl shadow-lg rounded-md bg-orange-50">
                    <div class="mt-3">
                        <div class="flex justify-between items-center mb-4">
                            <div class="flex space-x-2">
                                <button onclick="generateScreenshot('leaseTrackerModal', 'lease-tracker')"
                                        class="inline-flex items-center px-3 py-2 bg-green-600 text-white text-sm font-medium rounded hover:bg-green-700 transition-colors">
                                    📸 Screenshot
                                </button>
                                <button onclick="closeModal('leaseTrackerModal')"
                                        class="text-black-700 hover:text-black-600 text-xl font-bold">&times;</button>
                            </div>
                        </div>
                        <div id="leaseTrackerContent" class="modal-content no-scrollbar">
                            <!-- Content will be loaded here -->
                        </div>
                    </div>
                </div>
            </div>

            <!-- Portfolio Analytics Modal -->
            <div id="portfolioModal" class="fixed inset-0 z-50 overflow-y-auto hidden" aria-labelledby="modal-title" role="dialog" aria-modal="true">
                <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                    <div class="fixed  transition-opacity" aria-hidden="true" id="modalBackdrop"></div>

                    <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

                    <div class="inline-block align-bottom bg-orange-50 rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-6xl sm:w-full">
                        <div class="bg-orange-50 px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                            <div class="flex justify-between items-center mb-6">
                                <h3 class="text-2xl font-bold text-black-900" id="modal-title">Portfolio Analytics</h3>
                                <button type="button" id="closePortfolioModal" class="text-black-700 hover:text-black-600">
                                    <span class="sr-only">Close</span>
                                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                    </svg>
                                </button>
                            </div>

                            <!-- Store Selection -->
                            <div class="mb-6 bg-orange-100 rounded-lg p-4">
                                <h4 class="text-lg font-semibold text-black-900 mb-4">Select Stores for Analysis</h4>
                                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                                    <div>
                                        <label for="modal_portfolio_stores" class="block text-sm font-medium text-black-700 mb-2">
                                            Choose Stores
                                        </label>
                                        <select id="modal_portfolio_stores" multiple
                                                class="block w-full rounded-lg border-orange-300 shadow-sm focus:border-orange-500 focus:ring-orange-500 sm:text-sm"
                                                style="min-height: 150px;">
                                            @foreach($availableStores as $store)
                                                <option value="{{ $store->id }}">{{ $store->store_number }}</option>
                                            @endforeach
                                        </select>
                                        <p class="mt-1 text-xs text-black-700">Hold Ctrl/Cmd to select multiple stores</p>
                                    </div>

                                    <div class="flex flex-col justify-center space-y-3">
                                        <button type="button" id="modalSelectAllStores"
                                                class="inline-flex justify-center items-center px-4 py-2 border border-orange-300 text-sm font-medium rounded-lg text-black-700 bg-white hover:bg-orange-100">
                                            Select All Stores
                                        </button>
                                        <button type="button" id="modalClearStoreSelection"
                                                class="inline-flex justify-center items-center px-4 py-2 border border-orange-300 text-sm font-medium rounded-lg text-black-700 bg-white hover:bg-orange-100">
                                            Clear Selection
                                        </button>
                                        <button type="button" id="updatePortfolioStats"
                                                class="inline-flex justify-center items-center px-4 py-2 border border-transparent text-sm font-medium rounded-lg text-white bg-orange-600 hover:bg-orange-700">
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
                                    <div class="bg-orange-100 border border-orange-200 rounded-lg p-4">
                                        <h4 class="text-sm font-semibold text-black-600 mb-2">Analysis Scope:</h4>
                                        <div id="selectedStoresList" class="flex flex-wrap gap-2"></div>
                                    </div>
                                </div>

                                <!-- Portfolio Totals -->
                                <div class="mb-6">
                                    <h4 class="text-lg font-semibold text-black-900 mb-4 text-center">Portfolio Totals</h4>
                                    <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-5 gap-4" id="portfolioTotals">
                                        <!-- Stats will be populated via JavaScript -->
                                    </div>
                                </div>

                                <!-- Portfolio Averages -->
                                <div class="mb-6">
                                    <h4 class="text-lg font-semibold text-black-900 mb-4 text-center">Portfolio Averages</h4>
                                    <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-5 gap-4" id="portfolioAverages">
                                        <!-- Averages will be populated via JavaScript -->
                                    </div>
                                </div>
                            </div>

                            <!-- Loading Indicator -->
                            <div id="portfolioLoading" class="hidden text-center py-8">
                                <svg class="animate-spin -ml-1 mr-3 h-8 w-8 text-black-600 mx-auto" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                                <p class="text-black-700 mt-2">Calculating portfolio statistics...</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
            <!-- NEW: Renewal Action Modals -->
            <!-- Set Renewal Date Modal -->
            <div id="setRenewalModal" class="hidden fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
                <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
                    <div class="mt-3">
                        <h3 class="text-lg font-medium text-gray-900 mb-4">Set Renewal Date</h3>
                        <form id="setRenewalForm">
                            <input type="hidden" id="renewal_lease_id" />
                            <div class="mb-4">
                                <label class="block text-sm font-medium text-gray-700 mb-2">Renewal Date</label>
                                <input type="date" id="renewal_date_input"
                                       class="block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                       min="{{ date('Y-m-d') }}">
                            </div>
                            <div class="mb-4">
                                <label class="block text-sm font-medium text-gray-700 mb-2">Notes</label>
                                <textarea id="renewal_notes_input" rows="3"
                                          class="block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                          placeholder="Add notes about the renewal..."></textarea>
                            </div>
                            <div class="flex justify-end space-x-3">
                                <button type="button" onclick="closeRenewalModal()"
                                        class="px-4 py-2 border border-gray-300 rounded-md text-gray-700 hover:bg-gray-50">
                                    Cancel
                                </button>
                                <button type="submit"
                                        class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700">
                                    Set Renewal Date
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Complete Renewal Modal -->
            <div id="completeRenewalModal" class="hidden fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
                <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
                    <div class="mt-3">
                        <h3 class="text-lg font-medium text-gray-900 mb-4">Complete Renewal</h3>
                        <form id="completeRenewalForm">
                            <input type="hidden" id="complete_lease_id" />
                            <div class="mb-4">
                                <label class="block text-sm font-medium text-gray-700 mb-2">Completion Notes</label>
                                <textarea id="completion_notes" rows="3"
                                          class="block w-full rounded-md border-gray-300 shadow-sm focus:border-green-500 focus:ring-green-500"
                                          placeholder="Add notes about the completed renewal..."></textarea>
                            </div>
                            <div class="flex justify-end space-x-3">
                                <button type="button" onclick="closeCompleteModal()"
                                        class="px-4 py-2 border border-gray-300 rounded-md text-gray-700 hover:bg-gray-50">
                                    Cancel
                                </button>
                                <button type="submit"
                                        class="px-4 py-2 bg-green-600 text-white rounded-md hover:bg-green-700">
                                    Mark as Completed
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>

    <style>
        /* Hide last column ONLY in landlord contact modal for screenshots */
        #landlordContactModal table.hide-last-column th:last-child,
        #landlordContactModal table.hide-last-column td:last-child {
            display: none !important;
        }

        /* Also hide when printing landlord contact modal */
        @media print {
            #landlordContactModal table th:last-child,
            #landlordContactModal table td:last-child {
                display: none !important;
            }
        }
    </style>
    <script>
        // SIMPLIFIED AND FIXED Read More/Read Less Implementation + All Original Code
        document.addEventListener('DOMContentLoaded', function() {
            let TRUNCATE_LENGTH = 200; // Start with 200, but can be changed

// Function to change truncate length dynamically
            window.changeTruncateLength = function(newLength) {
                TRUNCATE_LENGTH = newLength;

                // Reset all processed elements
                document.querySelectorAll('.truncatable').forEach(el => {
                    el.removeAttribute('data-truncated');
                    // Restore original text if it was already truncated
                    const originalText = el.getAttribute('data-original-text');
                    if (originalText) {
                        el.textContent = originalText;
                        el.removeAttribute('data-original-text');
                    }
                });

                // Re-run truncation with new length
                initTruncation();

                console.log(`Truncate length changed to: ${newLength}`);
            };
            // Use event delegation for Read More/Read Less buttons
            document.body.addEventListener('click', function(event) {
                if (event.target.classList.contains('read-more-btn')) {
                    const parent = event.target.closest('.truncatable');
                    if (parent) {
                        parent.querySelector('.truncated-content').style.display = 'none';
                        parent.querySelector('.full-content').style.display = 'inline';
                    }
                } else if (event.target.classList.contains('read-less-btn')) {
                    const parent = event.target.closest('.truncatable');
                    if (parent) {
                        parent.querySelector('.truncated-content').style.display = 'inline';
                        parent.querySelector('.full-content').style.display = 'none';
                    }
                }
            });

            // Function to initialize truncation
            function initTruncation() {
                document.querySelectorAll('.truncatable').forEach(function(element) {
                    const originalText = element.textContent.trim();

                    // Skip if already processed or text is short
                    if (originalText.length <= TRUNCATE_LENGTH || element.hasAttribute('data-truncated')) {
                        return;
                    }

                    // Mark as processed
                    element.setAttribute('data-truncated', 'true');

                    // Create truncated version
                    const truncatedText = originalText.substring(0, TRUNCATE_LENGTH);

                    // Replace content
                    element.innerHTML = `
                <span class="truncated-content">
                    ${truncatedText}...
                    <button type="button" class="read-more-btn" style="color:#ff671b; background:none; border:none; text-decoration:underline; cursor:pointer; font-size:inherit; padding:0; margin-left:5px;">
                        Read More
                    </button>
                </span>
                <span class="full-content" style="display:none;">
                    ${originalText}
                    <button type="button" class="read-less-btn" style="color:#ff671b; background:none; border:none; text-decoration:underline; cursor:pointer; font-size:inherit; padding:0; margin-left:5px;">
                        Read Less
                    </button>
                </span>
            `;
                });
            }

            // Initialize truncation on page load
            initTruncation();

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

            // Report Views Dropdown
            const reportsDropdown = document.getElementById('reportsDropdown');
            const reportsMenu = document.getElementById('reportsMenu');

            // Functions to control body scroll
            function disableBodyScroll() {
                const scrollY = window.scrollY;
                document.body.style.position = 'fixed';
                document.body.style.top = `-${scrollY}px`;
                document.body.style.width = '100%';
                document.body.setAttribute('data-scroll-position', scrollY);
            }

            function enableBodyScroll() {
                const scrollY = document.body.getAttribute('data-scroll-position');
                document.body.style.position = '';
                document.body.style.top = '';
                document.body.style.width = '';
                document.body.removeAttribute('data-scroll-position');
                window.scrollTo(0, parseInt(scrollY || '0'));
            }

            // Open portfolio modal
            if (openModalBtn) {
                openModalBtn.addEventListener('click', function() {
                    portfolioModal.classList.remove('hidden');
                    disableBodyScroll();
                    loadInitialPortfolioStats();
                });
            }

            // Close modal function
            function closeModal() {
                if (portfolioModal) {
                    portfolioModal.classList.add('hidden');
                    enableBodyScroll();
                }
            }

            if (closeModalBtn) {
                closeModalBtn.addEventListener('click', closeModal);
            }
            if (modalBackdrop) {
                modalBackdrop.addEventListener('click', closeModal);
            }

            // Close modal on Escape key
            document.addEventListener('keydown', function(event) {
                if (event.key === 'Escape' && portfolioModal && !portfolioModal.classList.contains('hidden')) {
                    closeModal();
                }
            });

            // Prevent modal content clicks from closing modal
            const modalContent = document.querySelector('#portfolioModal .inline-block');
            if (modalContent) {
                modalContent.addEventListener('click', function(event) {
                    event.stopPropagation();
                });
            }

            // Reports Dropdown functionality
            if (reportsDropdown && reportsMenu) {
                reportsDropdown.addEventListener('click', function(e) {
                    e.preventDefault();
                    reportsMenu.classList.toggle('hidden');
                });

                // Close dropdown when clicking outside
                document.addEventListener('click', function(e) {
                    if (!reportsDropdown.contains(e.target) && !reportsMenu.contains(e.target)) {
                        reportsMenu.classList.add('hidden');
                    }
                });
            }

            // Filter toggle
            if (toggleFilters && filterForm && toggleText && toggleIcon) {
                toggleFilters.addEventListener('click', function() {
                    const isHidden = filterForm.style.display === 'none';
                    filterForm.style.display = isHidden ? 'block' : 'none';
                    toggleText.textContent = isHidden ? 'Hide Filters' : 'Show Filters';
                    toggleIcon.style.transform = isHidden ? 'rotate(0deg)' : 'rotate(-90deg)';
                });
            }

            // Portfolio store selection
            if (modalSelectAllBtn && modalStoreSelect) {
                modalSelectAllBtn.addEventListener('click', function() {
                    for (let i = 0; i < modalStoreSelect.options.length; i++) {
                        modalStoreSelect.options[i].selected = true;
                    }
                });
            }

            if (modalClearBtn && modalStoreSelect) {
                modalClearBtn.addEventListener('click', function() {
                    for (let i = 0; i < modalStoreSelect.options.length; i++) {
                        modalStoreSelect.options[i].selected = false;
                    }
                });
            }

            // Update portfolio statistics
            if (updateStatsBtn && modalStoreSelect) {
                updateStatsBtn.addEventListener('click', function() {
                    const selectedStores = Array.from(modalStoreSelect.selectedOptions).map(option => option.value);
                    updatePortfolioStatistics(selectedStores);
                });
            }

            function loadInitialPortfolioStats() {
                updatePortfolioStatistics([]);
            }

            function updatePortfolioStatistics(stores) {
                if (portfolioLoading) portfolioLoading.classList.remove('hidden');
                if (portfolioStatsContainer) portfolioStatsContainer.classList.add('hidden');

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
                        if (portfolioLoading) portfolioLoading.classList.add('hidden');
                        if (portfolioStatsContainer) portfolioStatsContainer.classList.remove('hidden');
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        if (portfolioLoading) portfolioLoading.classList.add('hidden');
                        if (portfolioStatsContainer) portfolioStatsContainer.classList.remove('hidden');
                    });
            }

            function updatePortfolioDisplay(data) {
                // Update selected stores info
                const selectedStoresInfo = document.getElementById('selectedStoresInfo');
                const selectedStoresList = document.getElementById('selectedStoresList');

                if (data.selected_stores && data.selected_stores.length > 0) {
                    if (selectedStoresInfo) selectedStoresInfo.classList.remove('hidden');
                    if (selectedStoresList) {
                        selectedStoresList.innerHTML = data.selected_stores.map(store =>
                            `<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">${store}</span>`
                        ).join('');
                    }
                } else {
                    if (selectedStoresInfo) selectedStoresInfo.classList.add('hidden');
                }

                // Update totals
                const portfolioTotals = document.getElementById('portfolioTotals');
                if (portfolioTotals && data.totals) {
                    portfolioTotals.innerHTML = `
            <div class="text-center p-4 bg-blue-50 rounded-lg">
                <p class="text-sm font-medium text-gray-600">Total AWS</p>
                <p class="text-xl font-bold text-[#3B82F6]">$${Number(data.totals.aws || 0).toLocaleString('en-US', {minimumFractionDigits: 2})}</p>
            </div>
            <div class="text-center p-4 bg-green-50 rounded-lg">
                <p class="text-sm font-medium text-gray-600">Total Rent</p>
                <p class="text-xl font-bold text-green-600">$${Number(data.totals.total_rent || 0).toLocaleString('en-US', {minimumFractionDigits: 2})}</p>
            </div>
            <div class="text-center p-4 bg-purple-50 rounded-lg">
                <p class="text-sm font-medium text-gray-600">Lease to Sales Ratio</p>
                <p class="text-xl font-bold text-purple-600">${((data.totals.lease_to_sales_ratio || 0) * 100).toFixed(2)}%</p>
            </div>
            <div class="text-center p-4 bg-indigo-50 rounded-lg">
                <p class="text-sm font-medium text-gray-600">Total Base Rent</p>
                <p class="text-xl font-bold text-indigo-600">$${Number(data.totals.base_rent || 0).toLocaleString('en-US', {minimumFractionDigits: 2})}</p>
            </div>
            <div class="text-center p-4 bg-gray-50 rounded-lg">
                <p class="text-sm font-medium text-gray-600">% Increase Per Year</p>
                <p class="text-xl font-bold text-gray-600">${Number(data.totals.percent_increase_per_year || 0).toFixed(2)}%</p>
            </div>
        `;
                }

                // Update averages
                const portfolioAverages = document.getElementById('portfolioAverages');
                if (portfolioAverages && data.averages) {
                    portfolioAverages.innerHTML = `
            <div class="text-center p-4 bg-blue-50 rounded-lg">
                <p class="text-sm font-medium text-gray-600">Average AWS</p>
                <p class="text-lg font-bold text-[#3B82F6]">$${Number(data.averages.aws || 0).toLocaleString('en-US', {minimumFractionDigits: 2})}</p>
            </div>
            <div class="text-center p-4 bg-green-50 rounded-lg">
                <p class="text-sm font-medium text-gray-600">Average Total Rent</p>
                <p class="text-lg font-bold text-green-600">$${Number(data.averages.total_rent || 0).toLocaleString('en-US', {minimumFractionDigits: 2})}</p>
            </div>
            <div class="text-center p-4 bg-purple-50 rounded-lg">
                <p class="text-sm font-medium text-gray-600">Average Lease to Sales Ratio</p>
                <p class="text-lg font-bold text-purple-600">${((data.averages.lease_to_sales_ratio || 0) * 100).toFixed(2)}%</p>
            </div>
            <div class="text-center p-4 bg-indigo-50 rounded-lg">
                <p class="text-sm font-medium text-gray-600">Average Base Rent</p>
                <p class="text-lg font-bold text-indigo-600">$${Number(data.averages.base_rent || 0).toLocaleString('en-US', {minimumFractionDigits: 2})}</p>
            </div>
            <div class="text-center p-4 bg-gray-50 rounded-lg">
                <p class="text-sm font-medium text-gray-600">Average % Increase Per Year</p>
                <p class="text-lg font-bold text-gray-600">${Number(data.averages.percent_increase_per_year || 0).toFixed(2)}%</p>
            </div>
        `;
                }
            }

            // Landlord Table Sorting functionality
            let landlordSortDirection = {};

            window.sortLandlordTable = function(columnIndex, type) {
                const tbody = document.getElementById('landlordTableBody');
                if (!tbody) return;

                const rows = Array.from(tbody.querySelectorAll('tr'));
                if (rows.length === 0) return;

                const currentDirection = landlordSortDirection[columnIndex] || 'asc';
                const newDirection = currentDirection === 'asc' ? 'desc' : 'asc';
                landlordSortDirection[columnIndex] = newDirection;

                // Clear all sort indicators
                for (let i = 0; i <= 8; i++) {
                    const indicator = document.getElementById(`landlord-sort-indicator-${i}`);
                    if (indicator) {
                        if (i === 0 || i === 6 || i === 7) {
                            indicator.textContent = '↑';
                        } else {
                            indicator.textContent = 'A↓';
                        }
                        indicator.style.opacity = '0.5';
                    }
                }

                // Set active sort indicator
                const activeIndicator = document.getElementById(`landlord-sort-indicator-${columnIndex}`);
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

                // IMPORTANT: Re-initialize truncation after sorting
                // Reset truncation flags first
                document.querySelectorAll('.truncatable').forEach(el => {
                    el.removeAttribute('data-truncated');
                });
                initTruncation();
            };

            // Cost Breakdown Table Sorting
            let costBreakdownSortDirection = {};
            window.sortCostBreakdownTable = function(columnIndex, type) {
                const tbody = document.getElementById('costBreakdownBody');
                if (!tbody) return;

                const rows = Array.from(tbody.querySelectorAll('tr'));
                if (rows.length === 0) return;

                const currentDirection = costBreakdownSortDirection[columnIndex] || 'asc';
                const newDirection = currentDirection === 'asc' ? 'desc' : 'asc';
                costBreakdownSortDirection[columnIndex] = newDirection;

                rows.sort((a, b) => {
                    let aValue, bValue;
                    if (type === 'number') {
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
                    }
                    return newDirection === 'asc' ? (aValue > bValue ? 1 : -1) : (aValue < bValue ? 1 : -1);
                });

                rows.forEach((row, index) => {
                    row.className = (index % 2 === 0 ? 'bg-white' : 'bg-gray-50') + ' hover:bg-[#fff4ed]';
                    tbody.appendChild(row);
                });

                // Re-initialize truncation after sorting
                document.querySelectorAll('.truncatable').forEach(el => {
                    el.removeAttribute('data-truncated');
                });
                initTruncation();
            };

            // Lease Tracker Table Sorting
            let leaseTrackerSortDirection = {};

            window.sortLeaseTrackerTable = function(columnIndex, type) {
                const table = document.getElementById('leaseTrackerTable');
                const tbody = document.getElementById('leaseTrackerBody');

                if (!table || !tbody) return;

                const rows = Array.from(tbody.querySelectorAll('tr'));
                if (rows.length === 0) return;

                const currentDirection = leaseTrackerSortDirection[columnIndex] || 'asc';
                const newDirection = currentDirection === 'asc' ? 'desc' : 'asc';
                leaseTrackerSortDirection[columnIndex] = newDirection;

                // Clear all sort indicators
                for (let i = 0; i <= 13; i++) {
                    const indicator = document.getElementById(`leasetracker-sort-indicator-${i}`);
                    if (indicator) {
                        if (i === 0 || i === 1 || i === 2 || i === 3) {
                            indicator.textContent = '↑';
                        } else {
                            indicator.textContent = 'A↓';
                        }
                        indicator.style.opacity = '0.5';
                    }
                }

                // Set active sort indicator
                const activeIndicator = document.getElementById(`leasetracker-sort-indicator-${columnIndex}`);
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
                        const aRaw = a.cells[columnIndex].getAttribute('data-sort');
                        const bRaw = b.cells[columnIndex].getAttribute('data-sort');

                        if (columnIndex === 0) {
                            aValue = parseInt(aRaw) || 0;
                            bValue = parseInt(bRaw) || 0;
                        } else {
                            aValue = parseFloat(aRaw) || 0;
                            bValue = parseFloat(bRaw) || 0;
                        }
                    } else {
                        aValue = (a.cells[columnIndex].getAttribute('data-sort') || '').toLowerCase();
                        bValue = (b.cells[columnIndex].getAttribute('data-sort') || '').toLowerCase();

                        if (aValue === 'n/a') aValue = 'zzzz';
                        if (bValue === 'n/a') bValue = 'zzzz';
                    }

                    if (newDirection === 'asc') {
                        return aValue > bValue ? 1 : (aValue < bValue ? -1 : 0);
                    } else {
                        return aValue < bValue ? 1 : (aValue > bValue ? -1 : 0);
                    }
                });

                // Re-append sorted rows with updated row colors
                rows.forEach((row, index) => {
                    const statusCell = row.cells[13].getAttribute('data-sort');
                    let rowClass = '';
                    if (statusCell === 'EXPIRED') {
                        rowClass = 'bg-red-100';
                    } else if (statusCell === 'WARNING') {
                        rowClass = 'bg-yellow-100';
                    } else {
                        rowClass = index % 2 === 0 ? 'bg-white' : 'bg-gray-50';
                    }
                    row.className = `${rowClass} hover:bg-[#fff4ed]`;
                    tbody.appendChild(row);
                });

                // Re-initialize truncation after sorting
                document.querySelectorAll('.truncatable').forEach(el => {
                    el.removeAttribute('data-truncated');
                });
                initTruncation();
            };
        });

        // Modal functionality for reports
        function openModal(modalId) {
            // Close dropdown
            const reportsMenu = document.getElementById('reportsMenu');
            if (reportsMenu) {
                reportsMenu.classList.add('hidden');
            }

            // Show modal
            document.getElementById(modalId).classList.remove('hidden');

            // Load content based on modal type
            if (modalId === 'landlordContactModal') {
                loadLandlordContact();
            } else if (modalId === 'costBreakdownModal') {
                loadCostBreakdown();
            } else if (modalId === 'leaseTrackerModal') {
                loadLeaseTracker();
            }

            // Prevent body scrolling
            document.body.style.overflow = 'hidden';
        }

        function closeModal(modalId) {
            document.getElementById(modalId).classList.add('hidden');
            // Restore body scrolling
            document.body.style.overflow = 'auto';
        }

        // Load content functions
        function loadLandlordContact() {
            fetch('{{ route("leases.landlord-contact") }}')
                .then(response => response.text())
                .then(html => {
                    const parser = new DOMParser();
                    const doc = parser.parseFromString(html, 'text/html');
                    let content = doc.querySelector('main') || doc.querySelector('.container') || doc.body;

                    // Remove navigation elements
                    if (content === doc.body) {
                        content = content.cloneNode(true);
                        const navElements = content.querySelectorAll('[role="menu"], .dropdown, #payments-dropdown');
                        navElements.forEach(nav => nav.remove());
                    }

                    document.getElementById('landlordContactContent').innerHTML = content ? content.outerHTML : '<p>Error loading content</p>';

                    // Re-initialize truncation for modal content
                    setTimeout(() => {
                        if (window.reinitTextTruncation) {
                            window.reinitTextTruncation();
                        }
                    }, 100);
                })
                .catch(error => {
                    console.error('Error:', error);
                    document.getElementById('landlordContactContent').innerHTML = '<p>Error loading content</p>';
                });
        }

        function loadCostBreakdown() {
            fetch('{{ route("leases.cost-breakdown") }}')
                .then(response => response.text())
                .then(html => {
                    const parser = new DOMParser();
                    const doc = parser.parseFromString(html, 'text/html');
                    let content = doc.querySelector('main') || doc.querySelector('.container') || doc.body;

                    if (content === doc.body) {
                        content = content.cloneNode(true);
                        const navElements = content.querySelectorAll('[role="menu"], .dropdown, #payments-dropdown');
                        navElements.forEach(nav => nav.remove());
                    }

                    document.getElementById('costBreakdownContent').innerHTML = content ? content.outerHTML : '<p>Error loading content</p>';

                    setTimeout(() => {
                        if (window.reinitTextTruncation) {
                            window.reinitTextTruncation();
                        }
                    }, 100);
                })
                .catch(error => {
                    console.error('Error:', error);
                    document.getElementById('costBreakdownContent').innerHTML = '<p>Error loading content</p>';
                });
        }

        function loadLeaseTracker() {
            fetch('{{ route("leases.lease-tracker") }}')
                .then(response => response.text())
                .then(html => {
                    const parser = new DOMParser();
                    const doc = parser.parseFromString(html, 'text/html');
                    let content = doc.querySelector('main') || doc.querySelector('.container') || doc.body;

                    if (content === doc.body) {
                        content = content.cloneNode(true);
                        const navElements = content.querySelectorAll('[role="menu"], .dropdown, #payments-dropdown');
                        navElements.forEach(nav => nav.remove());
                    }

                    document.getElementById('leaseTrackerContent').innerHTML = content ? content.outerHTML : '<p>Error loading content</p>';

                    setTimeout(() => {
                        if (window.reinitTextTruncation) {
                            window.reinitTextTruncation();
                        }
                    }, 100);
                })
                .catch(error => {
                    console.error('Error:', error);
                    document.getElementById('leaseTrackerContent').innerHTML = '<p>Error loading content</p>';
                });
        }

        // Load html2canvas library dynamically


        // Enhanced screenshot functionality with language selection
        function generateScreenshot(modalId, type) {
            showLanguageSelection(modalId, type);
        }

        function showLanguageSelection(modalId, type) {
            const languageModal = document.createElement('div');
            languageModal.id = 'languageSelectionModal';
            languageModal.className = 'fixed inset-0 bg-opacity-50 z-[110] flex items-center justify-center';
            languageModal.innerHTML = `
    <div class="bg-white rounded-lg shadow-xl p-6 max-w-md w-full mx-4">
        <div class="text-center mb-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-2">Select Language for Screenshot</h3>
            <p class="text-sm text-gray-600">Choose the language for your screenshot export</p>
        </div>

        <div class="space-y-3">
            <button onclick="proceedWithScreenshot('${modalId}', '${type}', 'en')"
                    class="w-full flex items-center justify-center px-4 py-3 border border-gray-300 rounded-lg text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-orange-500 focus:border-orange-500 transition-colors">
                <span class="text-2xl mr-3">🇺🇸</span>
                <span class="font-medium">English</span>
            </button>

            <button onclick="proceedWithScreenshot('${modalId}', '${type}', 'ar')"
                    class="w-full flex items-center justify-center px-4 py-3 border border-gray-300 rounded-lg text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-orange-500 focus:border-orange-500 transition-colors">
                <span class="text-2xl mr-3">🇸🇦</span>
                <span class="font-medium">العربية (Arabic)</span>
            </button>

            <button onclick="proceedWithScreenshot('${modalId}', '${type}', 'es')"
                    class="w-full flex items-center justify-center px-4 py-3 border border-gray-300 rounded-lg text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-orange-500 focus:border-orange-500 transition-colors">
                <span class="text-2xl mr-3">🇪🇸</span>
                <span class="font-medium">Español (Spanish)</span>
            </button>
        </div>

        <div class="mt-6 text-center">
            <button onclick="hideLanguageSelection()"
                    class="px-4 py-2 text-sm text-gray-500 hover:text-gray-700 focus:outline-none">
                Cancel
            </button>
        </div>
    </div>
`;

            document.body.appendChild(languageModal);
        }

        function hideLanguageSelection() {
            const languageModal = document.getElementById('languageSelectionModal');
            if (languageModal) {
                languageModal.remove();
            }
        }

        function proceedWithScreenshot(modalId, type, language) {
            hideLanguageSelection();
            applyLanguageStyles(language, modalId);
            showScreenshotLoading(modalId);

            // **NEW: Hide all scrollbars for screenshots**
            const scrollbarHideSheet = document.createElement('style');
            scrollbarHideSheet.id = 'screenshot-hide-scrollbars';
            scrollbarHideSheet.textContent = `
        /* Hide all scrollbars during screenshot */
        * {
            scrollbar-width: none !important; /* Firefox */
            -ms-overflow-style: none !important; /* IE and Edge */
        }

        /* Hide webkit scrollbars (Chrome, Safari) */
        *::-webkit-scrollbar {
            display: none !important;
            width: 0 !important;
            height: 0 !important;
        }

        /* Ensure content is still scrollable but no visible scrollbars */
        .modal-content {
            overflow: visible !important;
        }

        /* Hide specific scrollable areas */
        .overflow-x-auto,
        .overflow-y-auto,
        .overflow-auto {
            overflow: visible !important;
        }
    `;
            document.head.appendChild(scrollbarHideSheet);

            // Hide Responsibilities column for landlord contact modal
            if (modalId === 'landlordContactModal') {
                const modal = document.getElementById(modalId);
                const contentElement = modal.querySelector('.modal-content');
                const table = contentElement.querySelector('table');
                if (table) {
                    table.classList.add('hide-last-column');
                    table.offsetHeight; // Force reflow

                    setTimeout(() => {
                        takeActualScreenshot();
                    }, 150);
                    return;
                }
            }

            takeActualScreenshot();

            function takeActualScreenshot() {
                loadDomToImage().then(domtoimage => {
                    const modal = document.getElementById(modalId);
                    const contentElement = modal.querySelector('.modal-content');

                    if (!contentElement) {
                        showScreenshotError(modalId, 'Content not found for screenshot');
                        cleanupScreenshotStyles();
                        return;
                    }

                    const options = {
                        bgcolor: '#ffffff',
                        width: contentElement.scrollWidth,
                        height: contentElement.scrollHeight,
                        style: {
                            transform: 'scale(1)',
                            transformOrigin: 'top left'
                        }
                    };

                    domtoimage.toPng(contentElement, options)
                        .then(dataUrl => {
                            return fetch(dataUrl).then(res => res.blob());
                        })
                        .then(blob => {
                            const url = URL.createObjectURL(blob);
                            const now = new Date();
                            const dateStr = now.toISOString().split('T')[0];
                            const timeStr = now.toTimeString().split(' ')[0].replace(/:/g, '-');
                            const languageSuffix = language === 'ar' ? '-arabic' : '-english';
                            const filename = `${type}${languageSuffix}-${dateStr}-${timeStr}.png`;

                            hideScreenshotLoading(modalId);
                            showScreenshotReady(modalId, url, url, filename);
                            resetLanguageStyles(modalId);
                            cleanupScreenshotStyles();

                            // Show column again for landlord contact modal
                            if (modalId === 'landlordContactModal') {
                                const table = contentElement.querySelector('table');
                                if (table) {
                                    table.classList.remove('hide-last-column');
                                }
                            }
                        })
                        .catch(error => {
                            console.error('dom-to-image error:', error);
                            showScreenshotError(modalId, 'Failed to capture screenshot');
                            resetLanguageStyles(modalId);
                            cleanupScreenshotStyles();

                            if (modalId === 'landlordContactModal') {
                                const modal = document.getElementById(modalId);
                                const contentElement = modal.querySelector('.modal-content');
                                const table = contentElement.querySelector('table');
                                if (table) {
                                    table.classList.remove('hide-last-column');
                                }
                            }
                        });
                }).catch(error => {
                    console.error('Failed to load dom-to-image:', error);
                    showScreenshotError(modalId, 'Failed to load screenshot library');
                    resetLanguageStyles(modalId);
                    cleanupScreenshotStyles();

                    if (modalId === 'landlordContactModal') {
                        const modal = document.getElementById(modalId);
                        const contentElement = modal.querySelector('.modal-content');
                        const table = contentElement.querySelector('table');
                        if (table) {
                            table.classList.remove('hide-last-column');
                        }
                    }
                });
            }

            // **UPDATED: Cleanup function now removes scrollbar styles too**
            function cleanupScreenshotStyles() {
                const scrollbarStyles = document.getElementById('screenshot-hide-scrollbars');
                if (scrollbarStyles) {
                    scrollbarStyles.remove();
                }
            }
        }

        // Load dom-to-image library
        function loadDomToImage() {
            return new Promise((resolve, reject) => {
                if (window.domtoimage) {
                    resolve(window.domtoimage);
                    return;
                }
                const script = document.createElement('script');
                script.src = 'https://cdnjs.cloudflare.com/ajax/libs/dom-to-image/2.6.0/dom-to-image.min.js';
                script.onload = () => resolve(window.domtoimage);
                script.onerror = reject;
                document.head.appendChild(script);
            });
        }

        // **NEW: Load dom-to-image library instead of html2canvas**
        function loadDomToImage() {
            return new Promise((resolve, reject) => {
                if (window.domtoimage) {
                    resolve(window.domtoimage);
                    return;
                }
                const script = document.createElement('script');
                script.src = 'https://cdnjs.cloudflare.com/ajax/libs/dom-to-image/2.6.0/dom-to-image.min.js';
                script.onload = () => resolve(window.domtoimage);
                script.onerror = reject;
                document.head.appendChild(script);
            });
        }



        function loadHtml2Canvas() {
            return new Promise((resolve, reject) => {
                if (window.html2canvas) {
                    resolve(window.html2canvas);
                    return;
                }
                const script = document.createElement('script');
                // Use the correct html2canvas-pro CDN URL
                script.src = 'https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js';
                script.onload = () => resolve(window.html2canvas);
                script.onerror = reject;
                document.head.appendChild(script);
            });
        }
        function applyLanguageStyles(language, modalId) {
            const contentElement = document.querySelector(`#${modalId} .modal-content`);
            if (!contentElement) return;

            if (language === 'ar') {
                // Apply Arabic styling
                contentElement.style.direction = 'rtl';
                contentElement.style.textAlign = 'right';

                // Update header text to Arabic based on modal type
                const header = contentElement.querySelector('h1');
                if (header) {
                    header.setAttribute('data-original-text', header.textContent);

                    // Set Arabic title based on modal type
                    const arabicTitles = {
                        'landlordContactModal': 'دليل جهات الاتصال لأصحاب العقارات',
                        'costBreakdownModal': 'تحليل تفصيل التكلفة',
                        'leaseTrackerModal': 'لوحة متابعة الإيجارات'
                    };

                    if (arabicTitles[modalId]) {
                        header.textContent = arabicTitles[modalId];
                    }
                }

                // Apply Arabic translations to common table headers and elements
                const elementsToTranslate = {
                    // Basic table headers
                    'Store': 'المتجر',
                    'Store Number': 'رقم المتجر',
                    'Store Name': 'اسم المتجر',
                    'Landlord': 'المالك',
                    'Landlord Name': 'اسم المالك',
                    'Contact Info': 'معلومات الاتصال',
                    'Phone': 'الهاتف',
                    'Email': 'البريد الإلكتروني',
                    'Address': 'العنوان',
                    'AWS': 'متوسط المبيعات المرجح',
                    'Total Rent': 'إجمالي الإيجار',
                    'Base Rent': 'الإيجار الأساسي',
                    'L2S Ratio': 'نسبة الإيجار للمبيعات',
                    'SQF': 'القدم المربع',
                    'Term': 'المدة',
                    'Status': 'الحالة',
                    'Current Term': 'المدة الحالية',
                    'Expiration Date': 'تاريخ الانتهاء',
                    'Generated on': 'تم إنشاؤه في',
                    'TOTAL': 'المجموع',

                    // Additional headers for lease tracker
                    'Franchise Expiry': 'انتهاء الامتياز',
                    'Time Left': 'الوقت المتبقي',
                    'Lease Expiry': 'انتهاء الإيجار',
                    'Expired': 'منتهي الصلاحية',
                    'Active': 'نشط',
                    'Warning': 'تحذير',

                    // Cost breakdown headers
                    'Security Deposit': 'التأمين',
                    'RE Taxes': 'ضرائب العقارات',
                    'CAM': 'تكاليف المنطقة المشتركة',
                    'Insurance': 'التأمين',
                    'Others': 'أخرى',
                    'Increase %': 'نسبة الزيادة',
                    'Monthly Rate': 'المعدل الشهري',

                    // Action buttons and labels
                    'Actions': 'الإجراءات',
                    'Edit': 'تعديل',
                    'View': 'عرض',
                    'Delete': 'حذف',
                    'Close': 'إغلاق'
                };

                // Translate table headers and common elements
                Object.keys(elementsToTranslate).forEach(englishText => {
                    const elements = contentElement.querySelectorAll('th, td, p, span');
                    elements.forEach(el => {
                        if (el.textContent.includes(englishText)) {
                            el.setAttribute('data-original-text', el.textContent);
                            el.textContent = el.textContent.replace(englishText, elementsToTranslate[englishText]);
                        }
                    });
                });

            } else {
                // English is default, reset any previous Arabic styling
                resetLanguageStyles(modalId);
            }
        }

        function resetLanguageStyles(modalId) {
            const contentElement = document.querySelector(`#${modalId} .modal-content`);
            if (!contentElement) return;

            // Reset direction and text alignment
            contentElement.style.direction = '';
            contentElement.style.textAlign = '';

            // Restore original text content
            contentElement.querySelectorAll('[data-original-text]').forEach(element => {
                const originalText = element.getAttribute('data-original-text');
                if (originalText) {
                    element.textContent = originalText;
                    element.removeAttribute('data-original-text');
                }
            });
        }

        function showScreenshotLoading(modalId) {
            const modal = document.getElementById(modalId);
            const screenshotButton = modal.querySelector('button[onclick*="generateScreenshot"]');

            if (screenshotButton) {
                screenshotButton.innerHTML = `
    <svg class="animate-spin w-4 h-4 mr-2" fill="none" viewBox="0 0 24 24">
        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
    </svg>
    Generating...
`;
                screenshotButton.disabled = true;
            }
        }

        function hideScreenshotLoading(modalId) {
            const modal = document.getElementById(modalId);
            const screenshotButton = modal.querySelector('button[onclick*="generateScreenshot"]');

            if (screenshotButton) {
                screenshotButton.innerHTML = '📸 Screenshot';
                screenshotButton.disabled = false;
            }
        }

        function showScreenshotReady(modalId, screenshotUrl, downloadUrl, filename) {
            const instructionDiv = document.createElement('div');
            instructionDiv.id = 'screenshotInstructions';
            instructionDiv.className = 'fixed top-4 left-1/2 transform -translate-x-1/2 bg-green-600 text-white px-6 py-4 rounded-lg shadow-lg z-[100] max-w-md';
            instructionDiv.innerHTML = `
    <div class="text-center">
        <div class="flex items-center justify-center mb-3">
            <svg class="w-6 h-6 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
            </svg>
            <strong>Screenshot Generated Successfully!</strong>
        </div>
        <div class="space-y-3">
            <div>
                <img src="${screenshotUrl}" alt="Generated Screenshot" class="max-w-full mx-auto rounded border shadow" style="max-height: 200px;">
            </div>
            <div class="flex space-x-3 justify-center">
                <button onclick="downloadScreenshot('${downloadUrl}', '${filename}')"
                       class="inline-flex items-center px-4 py-2 bg-white text-green-600 rounded hover:bg-gray-100 font-medium">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                    </svg>
                    Download
                </button>
                <button onclick="hideScreenshotInstructions()"
                        class="inline-flex items-center px-4 py-2 bg-gray-600 text-white rounded hover:bg-gray-700">
                    Close
                </button>
            </div>
        </div>
    </div>
`;
            document.body.appendChild(instructionDiv);

            // Auto-hide after 15 seconds
            setTimeout(() => {
                hideScreenshotInstructions();
            }, 15000);
        }

        function downloadScreenshot(url, filename) {
            const link = document.createElement('a');
            link.href = url;
            link.download = filename;
            document.body.appendChild(link);
            link.click();
            document.body.removeChild(link);

            // Clean up the blob URL after download
            setTimeout(() => {
                URL.revokeObjectURL(url);
            }, 1000);
        }

        function showScreenshotError(modalId, message = 'Screenshot generation failed') {
            const instructionDiv = document.createElement('div');
            instructionDiv.id = 'screenshotInstructions';
            instructionDiv.className = 'fixed top-4 left-1/2 transform -translate-x-1/2 bg-red-600 text-white px-6 py-4 rounded-lg shadow-lg z-[100]';
            instructionDiv.innerHTML = `
    <div class="text-center">
        <div class="flex items-center justify-center mb-2">
            <svg class="w-6 h-6 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
            </svg>
            <strong>Screenshot Error</strong>
        </div>
        <p class="text-sm mb-3">${message}</p>
        <button onclick="hideScreenshotInstructions()"
                class="inline-flex items-center px-4 py-2 bg-white text-red-600 rounded hover:bg-gray-100">
            Close
        </button>
    </div>
`;
            document.body.appendChild(instructionDiv);
        }

        function hideScreenshotInstructions() {
            const instructions = document.getElementById('screenshotInstructions');
            if (instructions) {
                instructions.remove();
            }
        }

        // Close modal when clicking outside
        window.onclick = function(event) {
            const modals = ['landlordContactModal', 'costBreakdownModal', 'leaseTrackerModal'];
            modals.forEach(modalId => {
                const modal = document.getElementById(modalId);
                if (event.target === modal) {
                    closeModal(modalId);
                }
            });
        }

        // Make reinitTextTruncation available globally for modal reinitialization
        window.reinitTextTruncation = function() {
            document.querySelectorAll('.truncatable').forEach(el => {
                el.removeAttribute('data-truncated');
            });

            document.querySelectorAll('.truncatable').forEach(function(element) {
                const originalText = element.textContent.trim();

                if (originalText.length <= 200 || element.hasAttribute('data-truncated')) {
                    return;
                }

                element.setAttribute('data-truncated', 'true');
                const truncatedText = originalText.substring(0, 200);

                element.innerHTML = `
            <span class="truncated-content">
                ${truncatedText}...
                <button type="button" class="read-more-btn" style="color:#ff671b; background:none; border:none; text-decoration:underline; cursor:pointer; font-size:inherit; padding:0; margin-left:5px;">
                    Read More
                </button>
            </span>
            <span class="full-content" style="display:none;">
                ${originalText}
                <button type="button" class="read-less-btn" style="color:#ff671b; background:none; border:none; text-decoration:underline; cursor:pointer; font-size:inherit; padding:0; margin-left:5px;">
                    Read Less
                </button>
            </span>
        `;
            });
        };

        function filterByRenewals() {
            const form = document.getElementById('filterForm');
            const expiringSelect = document.getElementById('expiring');
            expiringSelect.value = 'renewal';
            form.submit();
        }

        function setRenewalDate(leaseId) {
            document.getElementById('renewal_lease_id').value = leaseId;
            document.getElementById('setRenewalModal').classList.remove('hidden');
        }

        function closeRenewalModal() {
            document.getElementById('setRenewalModal').classList.add('hidden');
            document.getElementById('setRenewalForm').reset();
        }

        function completeRenewal(leaseId) {
            document.getElementById('complete_lease_id').value = leaseId;
            document.getElementById('completeRenewalModal').classList.remove('hidden');
        }

        function closeCompleteModal() {
            document.getElementById('completeRenewalModal').classList.add('hidden');
            document.getElementById('completeRenewalForm').reset();
        }

        async function sendRenewalReminder(leaseId) {
            try {
                const response = await fetch(`/leases/${leaseId}/send-renewal-reminder`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    }
                });

                const data = await response.json();

                if (data.success) {
                    showNotification('✅ Renewal Reminder Sent', data.message, 'success');
                } else {
                    showNotification('❌ Error', data.error, 'error');
                }
            } catch (error) {
                showNotification('❌ Network Error', 'Failed to send reminder', 'error');
            }
        }

        // Handle Set Renewal Date Form
        document.getElementById('setRenewalForm').addEventListener('submit', async function(e) {
            e.preventDefault();

            const leaseId = document.getElementById('renewal_lease_id').value;
            const renewalDate = document.getElementById('renewal_date_input').value;
            const renewalNotes = document.getElementById('renewal_notes_input').value;

            try {
                const response = await fetch(`/leases/${leaseId}`, {
                    method: 'PATCH',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: JSON.stringify({
                        renewal_date: renewalDate,
                        renewal_notes: renewalNotes,
                        renewal_status: 'pending'
                    })
                });

                if (response.ok) {
                    showNotification('✅ Renewal Date Set', 'Automatic reminders have been created', 'success');
                    closeRenewalModal();
                    setTimeout(() => location.reload(), 2000);
                } else {
                    showNotification('❌ Error', 'Failed to set renewal date', 'error');
                }
            } catch (error) {
                showNotification('❌ Network Error', 'Failed to set renewal date', 'error');
            }
        });

        // Handle Complete Renewal Form
        document.getElementById('completeRenewalForm').addEventListener('submit', async function(e) {
            e.preventDefault();

            const leaseId = document.getElementById('complete_lease_id').value;
            const completionNotes = document.getElementById('completion_notes').value;

            try {
                const response = await fetch(`/leases/${leaseId}/complete-renewal`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: JSON.stringify({
                        completion_notes: completionNotes
                    })
                });

                if (response.ok) {
                    showNotification('✅ Renewal Completed', 'Lease renewal has been marked as completed', 'success');
                    closeCompleteModal();
                    setTimeout(() => location.reload(), 2000);
                } else {
                    showNotification('❌ Error', 'Failed to complete renewal', 'error');
                }
            } catch (error) {
                showNotification('❌ Network Error', 'Failed to complete renewal', 'error');
            }
        });

        // Notification Function
        function showNotification(title, message, type = 'info') {
            // Remove existing notifications
            const existing = document.querySelectorAll('.lease-notification');
            existing.forEach(n => n.remove());

            const colors = {
                success: 'bg-green-100 border-green-500 text-green-800',
                error: 'bg-red-100 border-red-500 text-red-800',
                warning: 'bg-yellow-100 border-yellow-500 text-yellow-800',
                info: 'bg-blue-100 border-blue-500 text-blue-800'
            };

            const notification = document.createElement('div');
            notification.className = `lease-notification fixed top-4 right-4 max-w-sm ${colors[type]} border-l-4 p-4 rounded-lg shadow-lg z-50`;
            notification.innerHTML = `
        <div class="flex justify-between items-start">
            <div>
                <h4 class="font-semibold text-sm">${title}</h4>
                <p class="text-sm mt-1">${message}</p>
            </div>
            <button onclick="this.parentElement.parentElement.remove()" class="text-lg font-bold ml-4">&times;</button>
        </div>
    `;

            document.body.appendChild(notification);

            // Auto remove after 5 seconds
            setTimeout(() => {
                if (notification.parentElement) {
                    notification.remove();
                }
            }, 5000);
        }

        // Initialize on page load
        document.addEventListener('DOMContentLoaded', function() {
            console.log('🔔 Lease renewal system initialized');
        });
    </script>


@endsection
