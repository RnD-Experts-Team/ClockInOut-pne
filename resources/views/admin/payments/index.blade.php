{{-- resources/views/payments/index.blade.php --}}
@extends('layouts.app')

@section('title', 'Payment Tracker')

@section('content')
    <div class="max-w-full mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <!-- Header Section -->
        <div class="sm:flex sm:items-center sm:justify-between mb-6">
            <div>
                <h1 class="text-3xl font-bold text-gray-900">Payment Tracker</h1>
                <p class="mt-2 text-sm text-gray-700">Manage maintenance payments and financial analytics</p>
            </div>
            <div class="mt-4 sm:mt-0 flex space-x-3">
                <button type="button" id="openPortfolioModal"
                        class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-lg text-white bg-purple-600 hover:bg-purple-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-purple-500 transition-all duration-300 hover:shadow-lg">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                    </svg>
                    Analytics
                </button>
                <a href="{{ route('payments.create') }}"
                   class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-lg text-white bg-[#3B82F6] hover:bg-[#2563EB] focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-[#3B82F6] transition-all duration-300 hover:shadow-lg">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                    </svg>
                    Add Payment
                </a>
                <a href="{{ route('payments.export', request()->query()) }}"
                   class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-lg text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 transition-all duration-300 hover:shadow-lg">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                    Export CSV
                </a>
                <a href="{{ route('payments.dashboard') }}"
                   class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-lg text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition-all duration-300 hover:shadow-lg">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a4 4 0 01-4-4V5a4 4 0 014-4h10a4 4 0 014 4v10a4 4 0 01-4 4z" />
                    </svg>
                    Dashboard
                </a>
            </div>
        </div>

        <!-- Quick Stats Overview -->
        <div class="grid grid-cols-1 md:grid-cols-5 gap-4 mb-8">
            <div class="bg-white rounded-lg shadow-sm p-6 border border-gray-200 hover:shadow-md transition-shadow">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <svg class="w-8 h-8 text-[#3B82F6]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-500">Total Payments</p>
                        <p class="text-2xl font-bold text-[#3B82F6]">{{ $stats['total'] }}</p>
                    </div>
                </div>
            </div>
            <div class="bg-white rounded-lg shadow-sm p-6 border border-gray-200 hover:shadow-md transition-shadow">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <svg class="w-8 h-8 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-500">Total Cost</p>
                        <p class="text-2xl font-bold text-green-600">${{ number_format($stats['total_cost'], 2) }}</p>
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
                        <p class="text-sm font-medium text-gray-500">Unpaid</p>
                        <p class="text-2xl font-bold text-red-600">${{ number_format($stats['unpaid_amount'], 2) }}</p>
                    </div>
                </div>
            </div>
            <div class="bg-white rounded-lg shadow-sm p-6 border border-gray-200 hover:shadow-md transition-shadow">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <svg class="w-8 h-8 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3a4 4 0 118 0v4m-4 0l-8 4v12l8-4m0 0l8 4M12 11v8" />
                        </svg>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-500">This Month</p>
                        <p class="text-2xl font-bold text-orange-600">{{ $stats['this_month'] }}</p>
                    </div>
                </div>
            </div>
            <div class="bg-white rounded-lg shadow-sm p-6 border border-gray-200 hover:shadow-md transition-shadow">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <svg class="w-8 h-8 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 8h6m-5 0a3 3 0 110 6H9l3 3m-3-6h6m6 1a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-500">This Month Cost</p>
                        <p class="text-2xl font-bold text-purple-600">${{ number_format($stats['this_month_cost'], 2) }}</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Filters Section -->
        <div class="bg-white shadow-lg rounded-xl p-6 mb-8 transition-all duration-300 hover:shadow-xl border border-gray-100">
            <div class="flex justify-between items-center mb-4">
                <h2 class="text-lg font-semibold text-gray-900">Filter Payments</h2>
                <button type="button" id="toggleFilters" class="text-sm text-[#3B82F6] hover:text-[#2563EB]">
                    <span id="toggleText">Hide Filters</span>
                    <svg id="toggleIcon" class="w-4 h-4 inline ml-1 transform transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                    </svg>
                </button>
            </div>
            <form method="GET" action="{{ route('payments.index') }}" id="filterForm" class="space-y-6">
                <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
                    <div>
                        <label for="search" class="block text-sm font-semibold text-gray-800 mb-2">Search</label>
                        <input type="text" name="search" id="search"
                               class="block w-full rounded-xl border-gray-200 shadow-sm focus:border-[#3B82F6] focus:ring-2 focus:ring-[#3B82F6]/20 sm:text-sm transition-all duration-200 hover:border-[#3B82F6] py-3 px-4"
                               placeholder="Store, company, notes..."
                               value="{{ request('search') }}">
                    </div>
                    <div>
                        <label for="company_id" class="block text-sm font-semibold text-gray-800 mb-2">Company</label>
                        <select name="company_id" id="company_id" class="block w-full rounded-xl border-gray-200 shadow-sm focus:border-[#3B82F6] focus:ring-2 focus:ring-[#3B82F6]/20 sm:text-sm transition-all duration-200 hover:border-[#3B82F6] py-3 px-4">
                            <option value="all" {{ request('company_id') === 'all' || !request('company_id') ? 'selected' : '' }}>All Companies</option>
                            @foreach($companies as $company)
                                <option value="{{ $company->id }}" {{ request('company_id') == $company->id ? 'selected' : '' }}>{{ $company->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label for="paid" class="block text-sm font-semibold text-gray-800 mb-2">Payment Status</label>
                        <select name="paid" id="paid" class="block w-full rounded-xl border-gray-200 shadow-sm focus:border-[#3B82F6] focus:ring-2 focus:ring-[#3B82F6]/20 sm:text-sm transition-all duration-200 hover:border-[#3B82F6] py-3 px-4">
                            <option value="all" {{ request('paid') === 'all' || !request('paid') ? 'selected' : '' }}>All</option>
                            <option value="1" {{ request('paid') === '1' ? 'selected' : '' }}>Paid</option>
                            <option value="0" {{ request('paid') === '0' ? 'selected' : '' }}>Unpaid</option>
                        </select>
                    </div>
                    <div>
                        <label for="time_filter" class="block text-sm font-semibold text-gray-800 mb-2">Time Filter</label>
                        <select name="time_filter" id="time_filter" class="block w-full rounded-xl border-gray-200 shadow-sm focus:border-[#3B82F6] focus:ring-2 focus:ring-[#3B82F6]/20 sm:text-sm transition-all duration-200 hover:border-[#3B82F6] py-3 px-4">
                            <option value="" {{ !request('time_filter') ? 'selected' : '' }}>All Time</option>
                            <option value="this_week" {{ request('time_filter') === 'this_week' ? 'selected' : '' }}>This Week</option>
                            <option value="this_month" {{ request('time_filter') === 'this_month' ? 'selected' : '' }}>This Month</option>
                            <option value="within_4_weeks" {{ request('time_filter') === 'within_4_weeks' ? 'selected' : '' }}>Within 4 Weeks</option>
                            <option value="within_90_days" {{ request('time_filter') === 'within_90_days' ? 'selected' : '' }}>Within 90 Days</option>
                            <option value="within_1_year" {{ request('time_filter') === 'within_1_year' ? 'selected' : '' }}>Within 1 Year</option>
                        </select>
                    </div>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
                    <div>
                        <label for="maintenance_type" class="block text-sm font-semibold text-gray-800 mb-2">Maintenance Type</label>
                        <select name="maintenance_type" id="maintenance_type" class="block w-full rounded-xl border-gray-200 shadow-sm focus:border-[#3B82F6] focus:ring-2 focus:ring-[#3B82F6]/20 sm:text-sm transition-all duration-200 hover:border-[#3B82F6] py-3 px-4">
                            <option value="all" {{ request('maintenance_type') === 'all' || !request('maintenance_type') ? 'selected' : '' }}>All Types</option>
                            @foreach($maintenanceTypes as $type)
                                <option value="{{ $type }}" {{ request('maintenance_type') === $type ? 'selected' : '' }}>{{ $type }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label for="date_from" class="block text-sm font-semibold text-gray-800 mb-2">From Date</label>
                        <input type="date" name="date_from" id="date_from"
                               class="block w-full rounded-xl border-gray-200 shadow-sm focus:border-[#3B82F6] focus:ring-2 focus:ring-[#3B82F6]/20 sm:text-sm transition-all duration-200 hover:border-[#3B82F6] py-3 px-4"
                               value="{{ request('date_from') }}">
                    </div>
                    <div>
                        <label for="date_to" class="block text-sm font-semibold text-gray-800 mb-2">To Date</label>
                        <input type="date" name="date_to" id="date_to"
                               class="block w-full rounded-xl border-gray-200 shadow-sm focus:border-[#3B82F6] focus:ring-2 focus:ring-[#3B82F6]/20 sm:text-sm transition-all duration-200 hover:border-[#3B82F6] py-3 px-4"
                               value="{{ request('date_to') }}">
                    </div>
                    <div class="flex items-end">
                        <div class="w-full space-y-2">
                            <button type="submit"
                                    class="w-full inline-flex justify-center items-center px-4 py-3 border border-transparent text-sm font-medium rounded-xl text-white bg-[#3B82F6] hover:bg-[#2563EB] focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-[#3B82F6] transition-all duration-200">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                                </svg>
                                Apply Filter
                            </button>
                            <a href="{{ route('payments.index') }}"
                               class="w-full inline-flex justify-center items-center px-4 py-2 border border-gray-200 text-sm font-medium rounded-xl text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500 transition-all duration-200">
                                Reset
                            </a>
                        </div>
                    </div>
                </div>
            </form>
        </div>

        <!-- Payments Table -->
        <div class="bg-white shadow-sm ring-1 ring-black ring-opacity-5 rounded-lg overflow-hidden">
            @if($payments->count() > 0)
                <!-- Desktop Table View -->
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-300">
                        <thead class="bg-gray-50">
                        <tr>
                            <th class="px-3 py-3.5 text-left text-xs font-medium text-gray-500 uppercase tracking-wide">Store Info</th>
                            <th class="px-3 py-3.5 text-left text-xs font-medium text-gray-500 uppercase tracking-wide">Service Details</th>
                            <th class="px-3 py-3.5 text-left text-xs font-medium text-gray-500 uppercase tracking-wide">Financial</th>
                            <th class="px-3 py-3.5 text-left text-xs font-medium text-gray-500 uppercase tracking-wide">Time Metrics</th>
                            <th class="px-3 py-3.5 text-left text-xs font-medium text-gray-500 uppercase tracking-wide">Status</th>
                            <th class="px-3 py-3.5 text-left text-xs font-medium text-gray-500 uppercase tracking-wide">Actions</th>
                        </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 bg-white">
                        @foreach($payments as $payment)
                            <tr class="hover:bg-gray-50 transition-colors duration-150">
                                <!-- Store Info -->
                                <td class="px-3 py-4 text-sm">
                                    <div class="space-y-1">
                                        <div class="font-medium text-gray-900">{{ $payment->store }}</div>
                                        <div class="text-gray-600 text-xs">{{ $payment->date->format('M d, Y') }}</div>
                                        <div class="text-xs text-gray-500">Week {{ $payment->week }}</div>
                                    </div>
                                </td>

                                <!-- Service Details -->
                                <td class="px-3 py-4 text-sm">
                                    <div class="space-y-1">
                                        <div class="font-medium text-gray-900 text-xs">{{ $payment->company->name }}</div>
                                        @if($payment->what_got_fixed)
                                            <div class="text-xs text-gray-600">{{ $payment->what_got_fixed }}</div>
                                        @endif
                                        @if($payment->maintenance_type)
                                            <span class="inline-flex items-center px-1.5 py-0.5 rounded text-xs font-medium bg-blue-100 text-blue-800">
                                                {{ $payment->maintenance_type }}
                                            </span>
                                        @endif
                                    </div>
                                </td>

                                <!-- Financial -->
                                <td class="px-3 py-4 text-sm">
                                    <div class="space-y-1">
                                        <div class="font-semibold text-green-600">${{ number_format($payment->cost, 2) }}</div>
                                        @if($payment->payment_method)
                                            <div class="text-xs text-gray-500">{{ $payment->payment_method }}</div>
                                        @endif
                                    </div>
                                </td>

                                <!-- Time Metrics -->
                                <td class="px-3 py-4 text-sm">
                                    <div class="space-y-1">
                                        <div class="text-xs">
                                            <span class="text-gray-500">Month:</span>
                                            <span class="{{ $payment->this_month === 'This Month' ? 'text-green-600' : 'text-gray-600' }}">
                                                {{ $payment->this_month }}
                                            </span>
                                        </div>
                                        <div class="text-xs">
                                            <span class="text-gray-500">90 Days:</span>
                                            <span class="{{ $payment->within_90_days === 'Within 90 days' ? 'text-green-600' : 'text-gray-600' }}">
                                                {{ $payment->within_90_days }}
                                            </span>
                                        </div>
                                        <div class="text-xs">
                                            <span class="text-gray-500">1 Year:</span>
                                            <span class="{{ $payment->within_1_year === 'Within 1 Year' ? 'text-green-600' : 'text-gray-600' }}">
                                                {{ $payment->within_1_year }}
                                            </span>
                                        </div>
                                    </div>
                                </td>

                                <!-- Status -->
                                <td class="px-3 py-4 text-sm">
                                    <div class="space-y-1">
                                        @if($payment->paid)
                                            <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                                <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                                                </svg>
                                                Paid
                                            </span>
                                        @else
                                            <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                                <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                                    <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd" />
                                                </svg>
                                                Unpaid
                                            </span>
                                        @endif
                                    </div>
                                </td>

                                <!-- Actions -->
                                <td class="px-3 py-4 text-sm">
                                    <div class="flex space-x-2">
                                        <a href="{{ route('payments.show', $payment) }}"
                                           class="inline-flex items-center text-[#3B82F6] hover:text-[#2563EB] font-medium transition-colors duration-150">
                                            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                            </svg>
                                            View
                                        </a>
                                        <a href="{{ route('payments.edit', $payment) }}"
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

                <!-- Pagination -->
                <div class="px-4 py-3 border-t border-gray-200 sm:px-6">
                    <div class="flex justify-between items-center">
                        <div class="text-sm text-gray-700">
                            Showing {{ $payments->firstItem() ?? 0 }} to {{ $payments->lastItem() ?? 0 }}
                            of {{ $payments->total() }} results
                        </div>
                        <div class="flex space-x-1">
                            {{ $payments->links() }}
                        </div>
                    </div>
                </div>
            @else
                <div class="text-center py-12">
                    <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                    <h3 class="mt-2 text-sm font-medium text-gray-900">No payments found</h3>
                    <p class="mt-1 text-sm text-gray-500">Get started by recording a new payment.</p>
                    <div class="mt-6">
                        <a href="{{ route('payments.create') }}"
                           class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-lg text-white bg-[#3B82F6] hover:bg-[#2563EB]">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                            </svg>
                            Add Payment
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
                        <h3 class="text-2xl font-bold text-gray-900" id="modal-title">Payment Analytics</h3>
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
                                        class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-[#3B82F6] focus:ring-[#3B82F6] sm:text-sm"
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
                                        class="inline-flex justify-center items-center px-4 py-2 border border-transparent text-sm font-medium rounded-lg text-white bg-[#3B82F6] hover:bg-[#2563EB]">
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
                            <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-5 gap-4" id="portfolioTotals">
                                <!-- Stats will be populated via JavaScript -->
                            </div>
                        </div>

                        <!-- Portfolio Averages -->
                        <div class="mb-6">
                            <h4 class="text-lg font-semibold text-gray-900 mb-4 text-center">Portfolio Averages</h4>
                            <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-5 gap-4" id="portfolioAverages">
                                <!-- Averages will be populated via JavaScript -->
                            </div>
                        </div>
                    </div>

                    <!-- Loading Indicator -->
                    <div id="portfolioLoading" class="hidden text-center py-8">
                        <svg class="animate-spin -ml-1 mr-3 h-8 w-8 text-[#3B82F6] mx-auto" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        <p class="text-gray-600 mt-2">Calculating payment statistics...</p>
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

                fetch('{{ route("payments.portfolio-stats") }}', {
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
                        <p class="text-sm font-medium text-gray-600">Total Cost</p>
                        <p class="text-xl font-bold text-[#3B82F6]">$${Number(data.totals.total_cost || 0).toLocaleString('en-US', {minimumFractionDigits: 2})}</p>
                    </div>
                    <div class="text-center p-4 bg-green-50 rounded-lg">
                        <p class="text-sm font-medium text-gray-600">Paid Amount</p>
                        <p class="text-xl font-bold text-green-600">$${Number(data.totals.paid_amount || 0).toLocaleString('en-US', {minimumFractionDigits: 2})}</p>
                    </div>
                    <div class="text-center p-4 bg-red-50 rounded-lg">
                        <p class="text-sm font-medium text-gray-600">Unpaid Amount</p>
                        <p class="text-xl font-bold text-red-600">$${Number(data.totals.unpaid_amount || 0).toLocaleString('en-US', {minimumFractionDigits: 2})}</p>
                    </div>
                    <div class="text-center p-4 bg-purple-50 rounded-lg">
                        <p class="text-sm font-medium text-gray-600">Total Records</p>
                        <p class="text-xl font-bold text-purple-600">${data.totals.count || 0}</p>
                    </div>
                    <div class="text-center p-4 bg-gray-50 rounded-lg">
                        <p class="text-sm font-medium text-gray-600">Stores Count</p>
                        <p class="text-xl font-bold text-gray-600">${data.totals.stores_count || 0}</p>
                    </div>
                `;
                }

                // Update averages
                const portfolioAverages = document.getElementById('portfolioAverages');
                if (portfolioAverages && data.averages) {
                    portfolioAverages.innerHTML = `
                    <div class="text-center p-4 bg-blue-50 rounded-lg">
                        <p class="text-sm font-medium text-gray-600">Average Cost</p>
                        <p class="text-lg font-bold text-[#3B82F6]">$${Number(data.averages.avg_cost || 0).toLocaleString('en-US', {minimumFractionDigits: 2})}</p>
                    </div>
                    <div class="text-center p-4 bg-green-50 rounded-lg">
                        <p class="text-sm font-medium text-gray-600">Average Per Store</p>
                        <p class="text-lg font-bold text-green-600">$${Number(data.averages.avg_per_store || 0).toLocaleString('en-US', {minimumFractionDigits: 2})}</p>
                    </div>
                `;
                }
            }
        });
    </script>

@endsection
