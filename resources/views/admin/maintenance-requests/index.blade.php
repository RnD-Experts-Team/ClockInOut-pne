@extends('layouts.app')

@section('title', 'Maintenance Requests')

@section('content')
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <!-- Header Section -->
        <div class="sm:flex sm:items-center sm:justify-between mb-6">
            <div>
                <h1 class="text-3xl font-bold text-gray-900">Maintenance Requests</h1>
                <p class="mt-2 text-sm text-gray-600">Manage and track all maintenance requests</p>
            </div>
            <div class="mt-4 sm:mt-0 flex space-x-3">

                <!-- Ticket Report Button -->
                <button type="button" onclick="openModal('ticketReportModal')"
                        class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-lg text-white bg-gradient-to-r from-orange-500 to-orange-600 hover:from-orange-600 hover:to-orange-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-orange-500 transition-all duration-300 hover:shadow-lg transform hover:scale-105">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a4 4 0 01-4-4V5a4 4 0 014-4h10a4 4 0 014 4v10a4 4 0 01-4 4z"></path>
                    </svg>
                    Ticket Report
                </button>

                <a href="{{ route('maintenance-requests.export', request()->query()) }}"
                   class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-lg text-white bg-gradient-to-r from-blue-500 to-blue-600 hover:from-blue-600 hover:to-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-all duration-300 hover:shadow-lg transform hover:scale-105">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                    Export CSV
                </a>
            </div>
        </div>

        <!-- Enhanced Status Counts with Icons and Better Colors -->
        <div class="grid grid-cols-1 md:grid-cols-6 gap-6 mb-6 items-stretch">
            <!-- Total -->
            <div class="bg-gradient-to-br from-gray-50 to-gray-100 rounded-xl shadow-sm border border-gray-200 p-6 hover:shadow-md transition-shadow h-full flex flex-col">
                <div class="flex items-center flex-1">
                    <div class="flex-shrink-0">
                        <div class="w-12 h-12 bg-gray-600 rounded-lg flex items-center justify-center">
                            <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                            </svg>
                        </div>
                    </div>
                    <div class="ml-4 flex-1">
                        <p class="text-sm font-medium text-gray-600">Total</p>
                        <p class="text-3xl font-bold text-gray-900">{{ $statusCounts['all'] ?? 0 }}</p>
                    </div>
                </div>
            </div>

            <!-- On Hold -->
            <div class="bg-gradient-to-br from-yellow-50 to-yellow-100 rounded-xl shadow-sm border border-yellow-200 p-6 hover:shadow-md transition-shadow h-full flex flex-col">
                <div class="flex items-center flex-1">
                    <div class="flex-shrink-0">
                        <div class="w-12 h-12 bg-yellow-500 rounded-lg flex items-center justify-center">
                            <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 9v6m4-6v6m7-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                    </div>
                    <div class="ml-4 flex-1">
                        <p class="text-sm font-medium text-yellow-700">On Hold</p>
                        <p class="text-3xl font-bold text-yellow-900">{{ $statusCounts['on_hold'] ?? 0 }}</p>
                    </div>
                </div>
            </div>

            <!-- Received -->
            <div class="bg-gradient-to-br from-purple-50 to-purple-100 rounded-xl shadow-sm border border-purple-200 p-6 hover:shadow-md transition-shadow h-full flex flex-col">
                <div class="flex items-center flex-1">
                    <div class="flex-shrink-0">
                        <div class="w-12 h-12 bg-purple-500 rounded-lg flex items-center justify-center">
                            <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                            </svg>
                        </div>
                    </div>
                    <div class="ml-4 flex-1">
                        <p class="text-sm font-medium text-purple-700">Received</p>
                        <p class="text-3xl font-bold text-purple-900">{{ $statusCounts['received'] ?? 0 }}</p>
                    </div>
                </div>
            </div>

            <!-- In Progress -->
            <div class="bg-gradient-to-br from-blue-50 to-blue-100 rounded-xl shadow-sm border border-blue-200 p-6 hover:shadow-md transition-shadow h-full flex flex-col">
                <div class="flex items-center flex-1">
                    <div class="flex-shrink-0">
                        <div class="w-12 h-12 bg-blue-500 rounded-lg flex items-center justify-center">
                            <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                            </svg>
                        </div>
                    </div>
                    <div class="ml-4 flex-1">
                        <p class="text-sm font-medium text-blue-700">In Progress</p>
                        <p class="text-3xl font-bold text-blue-900">{{ $statusCounts['in_progress'] ?? 0 }}</p>
                    </div>
                </div>
            </div>

            <!-- Done -->
            <div class="bg-gradient-to-br from-green-50 to-green-100 rounded-xl shadow-sm border border-green-200 p-6 hover:shadow-md transition-shadow h-full flex flex-col">
                <div class="flex items-center flex-1">
                    <div class="flex-shrink-0">
                        <div class="w-12 h-12 bg-green-500 rounded-lg flex items-center justify-center">
                            <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                    </div>
                    <div class="ml-4 flex-1">
                        <p class="text-sm font-medium text-green-700">Done</p>
                        <p class="text-3xl font-bold text-green-900">{{ $statusCounts['done'] ?? 0 }}</p>
                    </div>
                </div>
            </div>

            <!-- Canceled -->
            <div class="bg-gradient-to-br from-red-50 to-red-100 rounded-xl shadow-sm border border-red-200 p-6 hover:shadow-md transition-shadow h-full flex flex-col">
                <div class="flex items-center flex-1">
                    <div class="flex-shrink-0">
                        <div class="w-12 h-12 bg-red-500 rounded-lg flex items-center justify-center">
                            <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                    </div>
                    <div class="ml-4 flex-1">
                        <p class="text-sm font-medium text-red-700">Canceled</p>
                        <p class="text-3xl font-bold text-red-900">{{ $statusCounts['canceled'] ?? 0 }}</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Enhanced Filters Section -->
        <div class="bg-white shadow-lg rounded-xl p-6 mb-8 border border-gray-200">
            <form method="GET" action="{{ route('maintenance-requests.index') }}" class="space-y-6" id="filterForm">
                <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
                    <!-- Status Filter -->
                    <div>
                        <label for="status" class="block text-sm font-semibold text-gray-800 mb-2">Status</label>
                        <select name="status" id="status" class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-orange-500 focus:ring-2 focus:ring-orange-500/20 sm:text-sm transition-all duration-200 hover:border-gray-400 py-3 px-4">
                            <option value="all" {{ request('status') === 'all' || !request('status') ? 'selected' : '' }}>All Statuses</option>
                            <option value="on_hold" {{ request('status') === 'on_hold' ? 'selected' : '' }}>On Hold</option>
                            <option value="received" {{ request('status') === 'received' ? 'selected' : '' }}>Received</option> <!-- UPDATED -->
                            <option value="in_progress" {{ request('status') === 'in_progress' ? 'selected' : '' }}>In Progress</option>
                            <option value="done" {{ request('status') === 'done' ? 'selected' : '' }}>Done</option>
                            <option value="canceled" {{ request('status') === 'canceled' ? 'selected' : '' }}>Canceled</option>
                        </select>
                    </div>

                    <!-- Urgency Filter -->
                    <div>
                        <label for="urgency" class="block text-sm font-semibold text-gray-800 mb-2">Urgency</label>
                        <select name="urgency" id="urgency" class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-orange-500 focus:ring-2 focus:ring-orange-500/20 sm:text-sm transition-all duration-200 hover:border-gray-400 py-3 px-4">
                            <option value="all" {{ request('urgency') === 'all' || !request('urgency') ? 'selected' : '' }}>All Urgencies</option>
                            @if(isset($urgencyLevels))
                                @foreach($urgencyLevels as $urgency)
                                    <option value="{{ $urgency->id }}" {{ request('urgency') == $urgency->id ? 'selected' : '' }}>
                                        {{ $urgency->name }}
                                    </option>
                                @endforeach
                            @endif
                        </select>
                    </div>

                    <!-- Store Filter -->
                    <div>
                        <label for="store" class="block text-sm font-semibold text-gray-800 mb-2">Store</label>
                        <select name="store" class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-orange-500 focus:ring-2 focus:ring-orange-500/20 sm:text-sm transition-all duration-200 hover:border-gray-400 py-3 px-4">>
                            <option value="all">All Stores</option>
                            @foreach($stores as $store)
                                <option value="{{ $store->id }}">{{ $store->store_number }} - {{ $store->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Date Range Filter -->
                    <div>
                        <label for="date_range" class="block text-sm font-semibold text-gray-800 mb-2">Date Range</label>
                        <select name="date_range" id="date_range" class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-orange-500 focus:ring-2 focus:ring-orange-500/20 sm:text-sm transition-all duration-200 hover:border-gray-400 py-3 px-4">
                            <option value="all" {{ request('date_range') === 'all' || !request('date_range') ? 'selected' : '' }}>All Dates</option>
                            <option value="this_week" {{ request('date_range') === 'this_week' ? 'selected' : '' }}>This Week</option>
                            <option value="this_month" {{ request('date_range') === 'this_month' ? 'selected' : '' }}>This Month</option>
                            <option value="this_year" {{ request('date_range') === 'this_year' ? 'selected' : '' }}>This Year</option>
                            <option value="custom" {{ request('date_range') === 'custom' ? 'selected' : '' }}>Custom</option>
                        </select>
                    </div>
                </div>

                <!-- Custom Date Fields -->
                <div id="customDateFields" style="display: {{ request('date_range') === 'custom' ? 'block' : 'none' }};" class="grid grid-cols-1 md:grid-cols-2 gap-6 mt-6">
                    <div>
                        <label for="start_date" class="block text-sm font-semibold text-gray-800 mb-2">Start Date</label>
                        <input type="date" name="start_date" id="start_date" class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-orange-500 focus:ring-2 focus:ring-orange-500/20 sm:text-sm transition-all duration-200 hover:border-gray-400 py-3 px-4" value="{{ request('start_date') }}">
                    </div>
                    <div>
                        <label for="end_date" class="block text-sm font-semibold text-gray-800 mb-2">End Date</label>
                        <input type="date" name="end_date" id="end_date" class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-orange-500 focus:ring-2 focus:ring-orange-500/20 sm:text-sm transition-all duration-200 hover:border-gray-400 py-3 px-4" value="{{ request('end_date') }}">
                    </div>
                </div>

                <!-- Search Field -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mt-6">
                    <div>
                        <label for="search" class="block text-sm font-semibold text-gray-800 mb-2">Search</label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                                </svg>
                            </div>
                            <input type="text" name="search" id="search"
                                   class="block w-full pl-10 rounded-lg border-gray-300 shadow-sm focus:border-orange-500 focus:ring-2 focus:ring-orange-500/20 sm:text-sm transition-all duration-200 hover:border-gray-400 py-3 px-4"
                                   placeholder="Search store, equipment, description..."
                                   value="{{ request('search') }}">
                        </div>
                    </div>
                </div>

                <!-- Filter Actions -->
                <div class="flex justify-end space-x-4">
                    <a href="{{ route('maintenance-requests.index') }}"
                       class="inline-flex items-center px-5 py-3 border border-gray-300 text-sm font-medium rounded-lg text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-orange-500 transition-all duration-200 transform hover:scale-[1.02] hover:shadow-md">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                        </svg>
                        Reset
                    </a>
                    <button type="submit"
                            class="inline-flex items-center px-5 py-3 border border-transparent text-sm font-medium rounded-lg text-white bg-gradient-to-r from-orange-500 to-orange-600 hover:from-orange-600 hover:to-orange-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-orange-500 transition-all duration-200 transform hover:scale-[1.02] hover:shadow-md">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                        </svg>
                        Apply Filter
                    </button>
                </div>
            </form>
        </div>

        <!-- Enhanced Bulk Actions -->

        <!-- Enhanced Requests Table -->
        <div class="bg-white shadow-xl rounded-xl overflow-hidden border border-gray-200">
            @if(isset($maintenanceRequests) && $maintenanceRequests->count() > 0)
                <!-- Desktop Table View -->
                <div class="hidden sm:block overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gradient-to-r from-gray-50 to-gray-100 sticky top-0 z-10">
                        <tr>
                            <th class="py-4 pl-6 pr-3 text-left">
                                <input type="checkbox" id="selectAll" form="bulkForm" class="rounded border-gray-300 text-orange-600 focus:ring-orange-500 w-4 h-4">
                            </th>
                            <th class="px-4 py-4 text-left text-xs font-semibold text-gray-900 uppercase tracking-wider">Entry #</th>
                            <th class="px-4 py-4 text-left text-xs font-semibold text-gray-900 uppercase tracking-wider">Store</th>
                            <th class="px-4 py-4 text-left text-xs font-semibold text-gray-900 uppercase tracking-wider">Requester</th>
                            <th class="px-4 py-4 text-left text-xs font-semibold text-gray-900 uppercase tracking-wider">Equipment</th>
                            <th class="px-4 py-4 text-left text-xs font-semibold text-gray-900 uppercase tracking-wider">Urgency</th>
                            <th class="px-4 py-4 text-left text-xs font-semibold text-gray-900 uppercase tracking-wider">Status</th>
                            <th class="px-4 py-4 text-left text-xs font-semibold text-gray-900 uppercase tracking-wider">Assigned To</th>
                            <th class="px-4 py-4 text-left text-xs font-semibold text-gray-900 uppercase tracking-wider">Due Date</th>
                            <th class="px-4 py-4 text-left text-xs font-semibold text-gray-900 uppercase tracking-wider">Date</th>
                            <th class="px-4 py-4 text-left text-xs font-semibold text-gray-900 uppercase tracking-wider">Cognito Status</th>
                            <th class="px-4 py-4 text-left text-xs font-semibold text-gray-900 uppercase tracking-wider">Actions</th>
                        </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                        @foreach($maintenanceRequests as $index => $request)
                            <tr class="{{ $index % 2 == 0 ? 'bg-white' : 'bg-gray-50' }} hover:bg-orange-50 transition-colors duration-150 cursor-pointer">
                                <td class="py-4 pl-6 pr-3">
                                    <input type="checkbox" form="bulkForm" name="request_ids[]" value="{{ $request->id }}" class="request-checkbox rounded border-gray-300 text-orange-600 focus:ring-orange-500 w-4 h-4">
                                </td>
                                <td class="px-4 py-4 text-sm font-bold text-gray-900">
                                    <span class="bg-gray-100 px-2 py-1 rounded-lg">{{ $request->entry_number ?? $request->id }}</span>
                                </td>
                                <td class="px-4 py-4 text-sm text-gray-600">
                                    @if($request->store && is_object($request->store))
                                        <div class="font-medium text-gray-900">{{ $request->store->store_number }}</div>
                                        <div class="text-xs text-gray-500">{{ $request->store->name ?: 'No Name' }}</div>
                                    @else
                                        <span class="text-gray-400">{{ $request->store ?: 'No Store' }}</span>
                                    @endif
                                </td>
                                <td class="px-4 py-4 text-sm text-gray-600">
                                    <div class="flex items-center">
                                        <div class="w-8 h-8 bg-gray-300 rounded-full flex items-center justify-center mr-3">
                                            <span class="text-xs font-medium text-gray-700">
                                                {{ substr($request->requester->first_name ?? 'N', 0, 1) }}{{ substr($request->requester->last_name ?? 'A', 0, 1) }}
                                            </span>
                                        </div>
                                        <div>
                                            <div class="font-medium text-gray-900">{{ $request->requester->first_name ?? 'N/A' }} {{ $request->requester->last_name ?? '' }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-4 py-4 text-sm text-gray-600 max-w-xs">
                                    <div class="truncate" title="{{ $request->equipment_with_issue ?? 'No Equipment' }}">
                                        {{ Str::limit($request->equipment_with_issue ?? 'No Equipment', 30) }}
                                    </div>
                                </td>
                                <td class="px-4 py-4 text-sm">
                                    @if($request->urgencyLevel)
                                        @switch($request->urgencyLevel->name)
                                            @case('Critical')
                                            @case('Impacts Sales')
                                                <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold bg-red-100 text-red-800 border border-red-200">
                                                    <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                                        <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path>
                                                    </svg>
                                                    {{ $request->urgencyLevel->name }}
                                                </span>
                                                @break
                                            @case('High')
                                                <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold bg-orange-100 text-orange-800 border border-orange-200">
                                                    <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                                        <path fill-rule="evenodd" d="M5 2a1 1 0 011 1v1h1a1 1 0 010 2H6v1a1 1 0 01-2 0V6H3a1 1 0 010-2h1V3a1 1 0 011-1zm0 10a1 1 0 011 1v1h1a1 1 0 110 2H6v1a1 1 0 11-2 0v-1H3a1 1 0 110-2h1v-1a1 1 0 011-1zM12 2a1 1 0 01.967.744L14.146 7.2 17.5 9.134a1 1 0 010 1.732L14.146 12.8l-1.179 4.456a1 1 0 01-1.934 0L9.854 12.8 6.5 10.866a1 1 0 010-1.732L9.854 7.2l1.179-4.456A1 1 0 0112 2z" clip-rule="evenodd"></path>
                                                    </svg>
                                                    {{ $request->urgencyLevel->name }}
                                                </span>
                                                @break
                                            @case('Medium')
                                                <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold bg-yellow-100 text-yellow-800 border border-yellow-200">
                                                    <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"></path>
                                                    </svg>
                                                    {{ $request->urgencyLevel->name }}
                                                </span>
                                                @break
                                            @case('Low')
                                                <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold bg-green-100 text-green-800 border border-green-200">
                                                    <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                                                    </svg>
                                                    {{ $request->urgencyLevel->name }}
                                                </span>
                                                @break
                                        @endswitch
                                    @else
                                        <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                                            Unknown
                                        </span>
                                    @endif
                                </td>
                                <td class="px-4 py-4 text-sm">
                                    @switch($request->status)
                                        @case('on_hold')
                                            <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold bg-yellow-100 text-yellow-800 border border-yellow-200">
            <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zM7 8a1 1 0 012 0v4a1 1 0 11-2 0V8zm5-1a1 1 0 00-1 1v4a1 1 0 102 0V8a1 1 0 00-1-1z" clip-rule="evenodd"></path>
            </svg>
            On Hold
        </span>
                                            @break
                                        @case('received') {{-- CHANGED from reserved --}}
                                        <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold bg-purple-100 text-purple-800 border border-purple-200">
    <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
        <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
    </svg>
    Received
</span>
                                        @break

                                        @case('in_progress')
                                            <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold bg-blue-100 text-blue-800 border border-blue-200">
                                                <svg class="w-3 h-3 mr-1 animate-spin" fill="currentColor" viewBox="0 0 20 20">
                                                    <path fill-rule="evenodd" d="M4 2a1 1 0 011 1v2.101a7.002 7.002 0 0111.601 2.566 1 1 0 11-1.885.666A5.002 5.002 0 005.999 7H9a1 1 0 010 2H4a1 1 0 01-1-1V3a1 1 0 011-1zm.008 9.057a1 1 0 011.276.61A5.002 5.002 0 0014.001 13H11a1 1 0 110-2h5a1 1 0 011 1v5a1 1 0 11-2 0v-2.101a7.002 7.002 0 01-11.601-2.566 1 1 0 01.61-1.276z" clip-rule="evenodd"></path>
                                                </svg>
                                                In Progress
                                            </span>
                                            @break
                                        @case('done')
                                            <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold bg-green-100 text-green-800 border border-green-200">
                                                <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                                                </svg>
                                                Done
                                            </span>
                                            @break
                                        @case('canceled')
                                            <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold bg-red-100 text-red-800 border border-red-200">
                                                <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"></path>
                                                </svg>
                                                Canceled
                                            </span>
                                            @break
                                    @endswitch
                                </td>
                                <td class="px-4 py-4 text-sm text-gray-600">
                                    @if($request->effective_assigned_user)
                                        <div class="flex items-center">
                                            <div class="w-6 h-6 bg-blue-500 rounded-full flex items-center justify-center mr-2">
                <span class="text-xs font-medium text-white">
                    {{ substr($request->effective_assigned_user->name, 0, 1) }}
                </span>
                                            </div>
                                            <div>
                                                <div class="font-medium text-gray-900">{{ $request->effective_assigned_user->name }}</div>
                                                <div class="text-xs text-gray-500">
                                                    {{ $request->assignment_source === 'task_assignment' ? 'Task Assignment' : 'Direct Assignment' }}
                                                </div>
                                            </div>
                                        </div>
                                    @else
                                        <span class="text-gray-400 italic">Not Assigned</span>
                                    @endif
                                </td>

                                <!-- ✅ Display due date from latest TaskAssignment -->
                                <td class="px-4 py-4 text-sm text-gray-600">
                                    @if($request->latestTaskAssignment && $request->latestTaskAssignment->due_date)
                                        <div class="flex items-center">
                                            <svg class="w-4 h-4 mr-1 text-orange-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                            </svg>
                                            <div>
                                                <div class="font-medium text-gray-900">{{ $request->latestTaskAssignment->due_date->format('M d, Y') }}</div>
                                                <div class="text-xs text-gray-500">{{ $request->latestTaskAssignment->due_date->format('g:i A') }}</div>
                                            </div>
                                        </div>
                                    @else
                                        <span class="text-gray-400">No Due Date Set</span>
                                    @endif
                                </td>
                                <td class="px-4 py-4 text-sm text-gray-600">
                                    @if($request->created_at)
                                        <div class="flex items-center">
                                            <svg class="w-4 h-4 mr-1 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                            </svg>
                                            <span>{{ $request->created_at->format('M d, Y') }}</span>
                                        </div>
                                    @else
                                        <span class="text-gray-400">N/A</span>
                                    @endif
                                </td>
                                <td class="px-4 py-4 text-sm">
                                    @if($request->not_in_cognito)
                                        <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium bg-red-100 text-red-800 border border-red-200">
                                            <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M13.477 14.89A6 6 0 015.11 6.524l8.367 8.368zm1.414-1.414L6.524 5.11a6 6 0 018.367 8.367zM18 10a8 8 0 11-16 0 8 8 0 0116 0z" clip-rule="evenodd"></path>
                                            </svg>
                                            Local Only
                                        </span>
                                    @else
                                        <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800 border border-green-200">
                                            <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                                            </svg>
                                            Synced
                                        </span>
                                    @endif
                                </td>
                                <td class="px-4 py-4 text-sm">
                                    <div class="flex space-x-2">
                                        <a href="{{ route('maintenance-requests.show', $request) }}"
                                           class="text-blue-600 hover:text-blue-900 font-medium hover:underline transition-colors">View</a>
                                        <form action="{{ route('maintenance-requests.destroy', $request) }}" method="POST" style="display: inline;" onsubmit="return confirm('Are you sure you want to delete this request?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="text-red-600 hover:text-red-900 font-medium hover:underline transition-colors">Delete</button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>

                <!-- Enhanced Pagination -->
                <div class="px-6 py-4 border-t border-gray-200 bg-gray-50">
                    <div class="flex justify-between items-center">
                        <div class="text-sm text-gray-700">
                            Showing <span class="font-medium">{{ $maintenanceRequests->firstItem() ?? 0 }}</span> to
                            <span class="font-medium">{{ $maintenanceRequests->lastItem() ?? 0 }}</span> of
                            <span class="font-medium">{{ $maintenanceRequests->total() ?? 0 }}</span> results
                        </div>
                        <div class="flex space-x-1">
                            {{ $maintenanceRequests->links() }}
                        </div>
                    </div>
                </div>
            @else
                <div class="text-center py-16">
                    <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01" />
                    </svg>
                    <h3 class="mt-4 text-lg font-medium text-gray-900">No maintenance requests found</h3>
                    <p class="mt-2 text-sm text-gray-500">No requests match your current filters. Try adjusting your search criteria.</p>
                </div>
            @endif
        </div>

        <!-- Enhanced Ticket Report Modal -->
        <div id="ticketReportModal" class="hidden fixed inset-0 bg-opacity-50 overflow-y-auto h-full w-full z-50">
            <div class="relative top-4 mx-auto p-5 border w-11/12 max-w-7xl shadow-2xl rounded-xl bg-white">
                <div class="mt-3">
                    <div class="flex justify-between items-center mb-6">
                        <h3 class="text-xl font-bold text-gray-900 flex items-center">
                            <svg class="w-6 h-6 mr-2 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a4 4 0 01-4-4V5a4 4 0 014-4h10a4 4 0 014 4v10a4 4 0 01-4 4z"></path>
                            </svg>
                            Ticket Report
                        </h3>
                        <div class="flex space-x-3">
                            <button onclick="generateScreenshot('ticketReportModal', 'ticket-report')"
                                    class="inline-flex items-center px-4 py-2 bg-gradient-to-r from-green-500 to-green-600 text-white text-sm font-medium rounded-lg hover:from-green-600 hover:to-green-700 transition-all duration-200 transform hover:scale-105 shadow-lg">
                                📸 Screenshot
                            </button>
                            <button onclick="closeModal('ticketReportModal')"
                                    class="text-gray-400 hover:text-gray-600 transition-colors p-2">
                                <span class="sr-only">Close</span>
                                <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                </svg>
                            </button>
                        </div>
                    </div>
                    <div id="ticketReportContent" class="modal-content bg-white rounded-lg shadow-sm">
                        <!-- Content will be loaded here -->
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Select all functionality
            const selectAll = document.getElementById('selectAll');
            const checkboxes = document.querySelectorAll('.request-checkbox');
            const bulkBtn = document.getElementById('bulkBtn');

            if (selectAll) {
                selectAll.addEventListener('change', function() {
                    checkboxes.forEach(checkbox => checkbox.checked = this.checked);
                    toggleBulkButton();
                });
            }

            checkboxes.forEach(checkbox => {
                checkbox.addEventListener('change', toggleBulkButton);
            });

            function toggleBulkButton() {
                const checked = document.querySelectorAll('.request-checkbox:checked').length;
                if (bulkBtn) bulkBtn.disabled = checked === 0;
            }

            // Bulk status select functionality
            const bulkStatusSelect = document.querySelector('.bulk-status-select');
            const bulkAssignedToField = document.querySelector('.bulk-assigned-to-field');
            const bulkDueDateField = document.querySelector('.bulk-due-date-field');
            const bulkCostsField = document.querySelector('.bulk-costs-field');

            if (bulkStatusSelect) {
                bulkStatusSelect.addEventListener('change', function() {
                    [bulkAssignedToField, bulkDueDateField, bulkCostsField].forEach(field => {
                        if (field) field.style.display = 'none';
                    });

                    if (this.value === 'in_progress') {
                        if (bulkAssignedToField) bulkAssignedToField.style.display = 'block';
                        if (bulkDueDateField) bulkDueDateField.style.display = 'block';
                    } else if (this.value === 'done') {
                        if (bulkCostsField) bulkCostsField.style.display = 'block';
                    }
                });
            }

            // Date range filter
            const dateRangeSelect = document.getElementById('date_range');
            const customDateFields = document.getElementById('customDateFields');

            if (dateRangeSelect && customDateFields) {
                dateRangeSelect.addEventListener('change', function() {
                    customDateFields.style.display = this.value === 'custom' ? 'block' : 'none';
                });
            }

            // Enhanced Modal functionality
            window.openModal = function(modalId) {
                const modal = document.getElementById(modalId);
                if (modal) {
                    modal.classList.remove('hidden');
                    if (modalId === 'ticketReportModal') {
                        loadTicketReport();
                    }
                    document.body.style.overflow = 'hidden';
                }
            }

            window.closeModal = function(modalId) {
                const modal = document.getElementById(modalId);
                if (modal) {
                    modal.classList.add('hidden');
                    document.body.style.overflow = 'auto';
                }
            }

            // Enhanced loadTicketReport function
            function loadTicketReport() {
                const params = new URLSearchParams();
                const form = document.getElementById('filterForm');

                if (form) {
                    const formData = new FormData(form);
                    for (let [key, value] of formData.entries()) {
                        if (value && value !== 'all') params.append(key, value);
                    }
                }

                const contentDiv = document.getElementById('ticketReportContent');
                contentDiv.innerHTML = `
                <div class="text-center py-12">
                    <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-orange-600 mx-auto"></div>
                    <p class="mt-4 text-gray-600 font-medium">Loading report...</p>
                </div>
            `;

                fetch(`{{ route('maintenance-requests.ticket-report') }}?${params.toString()}`)
                    .then(response => {
                        if (!response.ok) {
                            throw new Error(`HTTP error! status: ${response.status}`);
                        }
                        return response.text();
                    })
                    .then(html => {
                        contentDiv.innerHTML = html;

                        // Force all content to be fully rendered and visible
                        setTimeout(() => {
                            const allElements = contentDiv.querySelectorAll('*');
                            allElements.forEach(el => {
                                if (el.textContent && el.textContent.trim()) {
                                    el.style.visibility = 'visible';
                                    el.style.opacity = '1';
                                    el.style.color = el.style.color || 'inherit';
                                }
                            });
                        }, 300);
                    })
                    .catch(error => {
                        console.error('Error loading report:', error);
                        contentDiv.innerHTML = `
                        <div class="text-center py-12 text-red-600">
                            <svg class="w-12 h-12 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.732 16.5c-.77.833.192 2.5 1.732 2.5z"></path>
                            </svg>
                            <p class="font-medium">Error loading report</p>
                            <p class="text-sm mt-1">${error.message}</p>
                        </div>
                    `;
                    });
            }

            // Close modal when clicking outside
            window.onclick = function(event) {
                if (event.target.classList.contains('fixed') && event.target.classList.contains('inset-0')) {
                    const modalId = event.target.id;
                    if (modalId) closeModal(modalId);
                }
            }

            // Ticket Report Sorting Functions
            let ticketReportSortDirection = {};

            window.sortTicketReportTable = function(columnIndex, type) {
                const table = document.getElementById('ticketReportTable');
                const tbody = document.getElementById('ticketReportBody');

                if (!table || !tbody) return;

                const rows = Array.from(tbody.querySelectorAll('tr'));
                if (rows.length === 0) return;

                const currentDirection = ticketReportSortDirection[columnIndex] || 'asc';
                const newDirection = currentDirection === 'asc' ? 'desc' : 'asc';
                ticketReportSortDirection[columnIndex] = newDirection;

                // Clear all sort indicators
                for (let i = 0; i < 9; i++) {
                    const indicator = document.getElementById(`ticketreport-sort-indicator-${i}`);
                    if (indicator) {
                        if (i === 5 || i === 6) { // Date columns
                            indicator.textContent = '↑';
                        } else if (i === 8) { // Costs (number)
                            indicator.textContent = '↑';
                        } else { // Text columns
                            indicator.textContent = 'A↓';
                        }
                        indicator.style.opacity = '0.5';
                    }
                }

                // Set active sort indicator
                const activeIndicator = document.getElementById(`ticketreport-sort-indicator-${columnIndex}`);
                if (activeIndicator) {
                    if (type === 'number' || type === 'date') {
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
                    } else if (type === 'date') {
                        aValue = a.cells[columnIndex].getAttribute('data-sort') || '9999-12-31';
                        bValue = b.cells[columnIndex].getAttribute('data-sort') || '9999-12-31';
                    } else {
                        aValue = (a.cells[columnIndex].getAttribute('data-sort') || '').toLowerCase();
                        bValue = (b.cells[columnIndex].getAttribute('data-sort') || '').toLowerCase();
                    }

                    if (newDirection === 'asc') {
                        return aValue > bValue ? 1 : -1;
                    } else {
                        return aValue < bValue ? 1 : -1;
                    }
                });

                // Re-append sorted rows with alternating colors
                rows.forEach((row, index) => {
                    row.className = (index % 2 === 0 ? 'bg-white' : 'bg-gray-50') + ' hover:bg-orange-50 transition-colors';
                    tbody.appendChild(row);
                });
            };
        });

        // Load html2canvas library dynamically
        function loadHtml2Canvas() {
            return new Promise((resolve, reject) => {
                if (window.html2canvas) {
                    resolve(window.html2canvas);
                    return;
                }
                const script = document.createElement('script');
                // Use the correct html2canvas-pro CDN URL
                script.src = 'https://cdn.jsdelivr.net/npm/html2canvas-pro@latest/dist/html2canvas-pro.min.js';
                script.onload = () => resolve(window.html2canvas);
                script.onerror = reject;
                document.head.appendChild(script);
            });
        }

        // Enhanced Screenshot functionality with Arabic language selection
        function generateScreenshot(modalId, type) {
            const contentDiv = document.getElementById('ticketReportContent');

            if (!contentDiv || !contentDiv.innerHTML.trim() || contentDiv.innerHTML.includes('Loading report')) {
                alert('Please wait for the report to load completely before taking a screenshot.');
                return;
            }

            // Check if there's actual data in the table
            const tableRows = contentDiv.querySelectorAll('tbody tr');
            if (tableRows.length === 0) {
                alert('No data available to screenshot. Please ensure the report has loaded properly.');
                return;
            }

            // Show language selection modal
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

            // Apply language-specific styling before screenshot
            applyLanguageStyles(language);

            showScreenshotLoading(modalId);

            // Scroll to top to avoid layout issues
            window.scrollTo(0, 0);

            loadHtml2Canvas().then(html2canvas => {
                // Wait longer for content to be fully rendered
                setTimeout(() => {
                    const contentDiv = document.getElementById('ticketReportContent');
                    const options = {
                        backgroundColor: '#f9fafb',
                        scale: 1.5,
                        useCORS: true,
                        allowTaint: true,
                        logging: false,
                        scrollX: 0,
                        scrollY: 0,
                        width: contentDiv.scrollWidth,
                        height: contentDiv.scrollHeight,
                        onclone: function(clonedDoc) {
                            const clonedContent = clonedDoc.querySelector('#ticketReportContent');
                            if (clonedContent) {
                                // Remove classes that cause 'nested' look
                                clonedContent.classList.remove('rounded-lg', 'shadow-sm');
                                clonedContent.style.transform = 'none';
                                clonedContent.style.position = 'static';
                                clonedContent.style.overflow = 'visible';
                                clonedContent.style.backgroundColor = 'transparent';
                                clonedContent.style.padding = '0';
                                clonedContent.style.margin = '0';

                                // Hide the bottom close button
                                const closeDiv = clonedContent.querySelector('.mt-6.text-center');
                                if (closeDiv) {
                                    closeDiv.style.display = 'none';
                                }

                                // Apply language direction if Arabic
                                if (language === 'ar') {
                                    clonedContent.style.direction = 'rtl';
                                    clonedContent.style.textAlign = 'right';
                                }

                                // Ensure all text is visible
                                const allElements = clonedContent.querySelectorAll('*');
                                allElements.forEach(el => {
                                    if (el.textContent && el.textContent.trim()) {
                                        el.style.visibility = 'visible';
                                        el.style.opacity = '1';
                                        el.style.color = el.style.color || 'inherit';
                                        el.style.fontSize = el.style.fontSize || 'inherit';
                                        el.style.fontFamily = el.style.fontFamily || 'inherit';
                                    }
                                });

                                // Fix table specifically
                                const table = clonedContent.querySelector('table');
                                if (table) {
                                    table.style.tableLayout = 'auto';
                                    table.style.width = 'auto';
                                    table.style.borderCollapse = 'collapse';
                                    table.style.display = 'table';
                                    table.style.margin = '0';

                                    if (language === 'ar') {
                                        table.style.direction = 'rtl';
                                    }

                                    // Fix all table cells
                                    const cells = table.querySelectorAll('td, th');
                                    cells.forEach(cell => {
                                        cell.style.whiteSpace = 'normal';
                                        cell.style.overflow = 'visible';
                                        cell.style.textOverflow = 'clip';
                                        cell.style.padding = '12px 8px';
                                        cell.style.border = '2px solid #d1d5db';
                                        if (cell.textContent && cell.textContent.trim()) {
                                            cell.style.color = '#374151';
                                            cell.style.fontSize = '14px';
                                        }
                                    });
                                }
                            }
                        }
                    };

                    html2canvas(contentDiv, options).then(canvas => {
                        hideScreenshotLoading(modalId);

                        if (canvas.width === 0 || canvas.height === 0) {
                            showScreenshotError(modalId, 'Screenshot generated but appears to be empty');
                            return;
                        }

                        canvas.toBlob(blob => {
                            if (!blob) {
                                showScreenshotError(modalId, 'Failed to generate screenshot blob');
                                return;
                            }

                            const url = URL.createObjectURL(blob);
                            const now = new Date();
                            const dateStr = now.toISOString().split('T')[0];
                            const timeStr = now.toTimeString().split(' ')[0].replace(/:/g, '-');
                            const languageSuffix = language === 'ar' ? '-arabic' : '-english';
                            const filename = `${type}${languageSuffix}-${dateStr}-${timeStr}.png`;

                            // Auto download
                            const link = document.createElement('a');
                            link.href = url;
                            link.download = filename;
                            document.body.appendChild(link);
                            link.click();
                            document.body.removeChild(link);

                            showScreenshotReady(modalId, url, url, filename);

                            // Reset language styles after screenshot
                            resetLanguageStyles();

                            setTimeout(() => {
                                URL.revokeObjectURL(url);
                            }, 10000);
                        }, 'image/png', 0.98);
                    }).catch(error => {
                        console.error('html2canvas error:', error);
                        hideScreenshotLoading(modalId);
                        showScreenshotError(modalId, 'Failed to capture screenshot: ' + error.message);
                        resetLanguageStyles();
                    });
                }, 1000);
            }).catch(error => {
                console.error('Failed to load html2canvas:', error);
                hideScreenshotLoading(modalId);
                showScreenshotError(modalId, 'Failed to load screenshot library');
                resetLanguageStyles();
            });
        }

        function applyLanguageStyles(language) {
            const contentElement = document.querySelector('#ticketReportContent');
            if (!contentElement) return;

            if (language === 'ar') {
                // Apply Arabic styling
                contentElement.style.direction = 'rtl';
                contentElement.style.textAlign = 'right';

                // Fix table layout for RTL
                const table = contentElement.querySelector('table');
                if (table) {
                    table.style.direction = 'rtl';
                    table.style.textAlign = 'right';

                    // Fix table cells alignment
                    const allCells = table.querySelectorAll('th, td');
                    allCells.forEach(cell => {
                        cell.style.textAlign = 'right';
                        cell.style.direction = 'rtl';
                        cell.style.verticalAlign = 'middle';
                        cell.style.whiteSpace = 'nowrap';
                        cell.style.overflow = 'hidden';
                        cell.style.textOverflow = 'ellipsis';
                    });

                    // Reverse the order of header columns for proper RTL display
                    const headerRow = table.querySelector('thead tr');
                    if (headerRow) {
                        const headers = Array.from(headerRow.children);
                        headers.reverse().forEach(header => {
                            headerRow.appendChild(header);
                        });
                    }

                    // Reverse the order of data cells in each row
                    const dataRows = table.querySelectorAll('tbody tr');
                    dataRows.forEach(row => {
                        const cells = Array.from(row.children);
                        cells.reverse().forEach(cell => {
                            row.appendChild(cell);
                        });
                    });
                }

                // Update header text to Arabic
                const header = contentElement.querySelector('h1');
                if (header) {
                    header.setAttribute('data-original-text', header.textContent);
                    header.textContent = 'تقرير طلبات الصيانة';
                    header.style.textAlign = 'right';
                }

                // Rest of your translation code...
                const elementsToTranslate = {
                    'Ticket Report': 'تقرير التذاكر',
                    'Entry #': 'رقم الإدخال',
                    'Store': 'المتجر',
                    'Requester': 'مقدم الطلب',
                    'Equipment': 'المعدات',
                    'Issue Description': 'وصف المشكلة',
                    'Urgency': 'الأولوية',
                    'Status': 'الحالة',
                    'Assigned To': 'تم التعيين لـ',
                    'Due Date': 'تاريخ التسليم',
                    'Created Date': 'تاريخ الإنشاء',
                    'Costs': 'التكاليف',
                    'On Hold': 'قيد الانتظار',
                    'In Progress': 'قيد التنفيذ',
                    'Done': 'تم',
                    'High': 'عالي',
                    'Medium': 'متوسط',
                    'Low': 'منخفض',
                    'Not Assigned': 'غير معين'
                };

                // Apply translations
                Object.keys(elementsToTranslate).forEach(englishText => {
                    const elements = contentElement.querySelectorAll('th, td, p, span, h1, h2, h3');
                    elements.forEach(el => {
                        if (el.textContent && el.textContent.trim() === englishText) {
                            el.setAttribute('data-original-text', el.textContent);
                            el.textContent = elementsToTranslate[englishText];
                        }
                    });
                });

            } else {
                // English is default, reset any previous Arabic styling
                resetLanguageStyles();
            }
        }


        function applyLanguageStyles(language) {
            const contentElement = document.querySelector('#ticketReportContent');
            if (!contentElement) return;

            if (language === 'ar') {
                // Apply Arabic styling
                contentElement.style.direction = 'rtl';
                contentElement.style.textAlign = 'right';

                // Fix table layout for RTL
                const table = contentElement.querySelector('table');
                if (table) {
                    table.style.direction = 'rtl';
                    table.style.textAlign = 'right';

                    // Fix table cells alignment
                    const allCells = table.querySelectorAll('th, td');
                    allCells.forEach(cell => {
                        cell.style.textAlign = 'right';
                        cell.style.direction = 'rtl';
                    });
                }

                // Enhanced translation mapping with more comprehensive coverage
                const elementsToTranslate = {
                    'Ticket Report': 'تقرير طلبات الصيانة',
                    'Entry #': 'رقم الإدخال',
                    'Store': 'المتجر',
                    'Requester': 'مقدم الطلب',
                    'Equipment': 'المعدات',
                    'Issue Description': 'وصف المشكلة',
                    'Description': 'الوصف',
                    'Urgency': 'الأولوية',
                    'Status': 'الحالة',
                    'Assigned to': 'مُسند إلى',           // Header translation
                    'Due Date': 'تاريخ الاستحقاق',
                    'Created Date': 'تاريخ الإنشاء',
                    'Date Created': 'تاريخ الإنشاء',
                    'Costs': 'التكاليف',
                    'Total Cost': 'إجمالي التكلفة',
                    'Actions': 'الإجراءات',
                    'On Hold': 'قيد الانتظار',
                    'In Progress': 'قيد التنفيذ',
                    'Done': 'مكتمل',
                    'Completed': 'مكتمل',
                    'Canceled': 'ملغي',
                    'Cancelled': 'ملغي',
                    'received': 'تم تلقيه',
                    'High': 'عالي',
                    'Medium': 'متوسط',
                    'Low': 'منخفض',
                    'Critical': 'حرج',
                    'Impacts Sales': 'يؤثر على المبيعات',
                    'Not Assigned': 'غير مُعيَّن',
                    'No Due Date': 'لا يوجد تاريخ استحقاق',
                    'N/A': 'غير متاح',
                    'Total': 'الإجمالي',
                    'Summary': 'ملخص',
                    // Add header variations that might exist
                    'Maintenance Requests Report': 'تقرير طلبات الصيانة',
                    'Report': 'تقرير'
                };

                // Apply translations with more thorough searching
                function translateElement(element, englishText, arabicText) {
                    // Check exact match
                    if (element.textContent && element.textContent.trim() === englishText) {
                        element.setAttribute('data-original-text', element.textContent);
                        element.textContent = arabicText;
                        return true;
                    }

                    // Check if element contains the text (for cases where there might be extra spaces)
                    if (element.textContent && element.textContent.trim().includes(englishText)) {
                        element.setAttribute('data-original-text', element.textContent);
                        element.textContent = element.textContent.replace(englishText, arabicText);
                        return true;
                    }

                    return false;
                }

                // Apply translations to all possible elements
                Object.keys(elementsToTranslate).forEach(englishText => {
                    const arabicText = elementsToTranslate[englishText];

                    // Search in headers first
                    const headers = contentElement.querySelectorAll('h1, h2, h3, h4, h5, h6');
                    headers.forEach(header => translateElement(header, englishText, arabicText));

                    // Search in table headers
                    const tableHeaders = contentElement.querySelectorAll('th');
                    tableHeaders.forEach(th => translateElement(th, englishText, arabicText));

                    // Search in table cells
                    const tableCells = contentElement.querySelectorAll('td');
                    tableCells.forEach(td => translateElement(td, englishText, arabicText));

                    // Search in other elements
                    const otherElements = contentElement.querySelectorAll('p, span, div, label');
                    otherElements.forEach(el => translateElement(el, englishText, arabicText));
                });

                // Force update for main header if it exists
                const mainHeader = contentElement.querySelector('h1');
                if (mainHeader && !mainHeader.getAttribute('data-original-text')) {
                    mainHeader.setAttribute('data-original-text', mainHeader.textContent);
                    mainHeader.textContent = 'تقرير طلبات الصيانة';
                    mainHeader.style.textAlign = 'right';
                }

            } else {
                // English is default, reset any previous Arabic styling
                resetLanguageStyles();
            }
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
            // Simple success notification
            const notification = document.createElement('div');
            notification.className = 'fixed top-4 right-4 bg-green-600 text-white px-6 py-4 rounded-lg shadow-xl z-[100] max-w-sm transform translate-x-0 transition-transform duration-300';
            notification.innerHTML = `
            <div class="flex items-center">
                <svg class="w-6 h-6 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                </svg>
                <div>
                    <p class="font-semibold">Screenshot Generated!</p>
                    <p class="text-sm opacity-90">File downloaded successfully</p>
                </div>
            </div>
        `;

            document.body.appendChild(notification);

            setTimeout(() => {
                notification.style.transform = 'translateX(100%)';
                setTimeout(() => {
                    if (document.body.contains(notification)) {
                        document.body.removeChild(notification);
                    }
                }, 300);
            }, 3000);
        }

        function showScreenshotError(modalId, message = 'Screenshot generation failed') {
            // Simple error notification
            const notification = document.createElement('div');
            notification.className = 'fixed top-4 right-4 bg-red-600 text-white px-6 py-4 rounded-lg shadow-xl z-[100] max-w-sm transform translate-x-0 transition-transform duration-300';
            notification.innerHTML = `
            <div class="flex items-center">
                <svg class="w-6 h-6 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
                <div>
                    <p class="font-semibold">Screenshot Error</p>
                    <p class="text-sm opacity-90">${message}</p>
                </div>
            </div>
        `;

            document.body.appendChild(notification);

            setTimeout(() => {
                notification.style.transform = 'translateX(100%)';
                setTimeout(() => {
                    if (document.body.contains(notification)) {
                        document.body.removeChild(notification);
                    }
                }, 300);
            }, 5000);
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

    </script>
@endsection
