{{-- resources/views/maintenance-requests/index.blade.php --}}
@extends('layouts.app')

@section('title', 'Maintenance Requests')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <div class="sm:flex sm:items-center sm:justify-between mb-6">
        <div>
            <h1 class="text-3xl font-bold text-gray-900">Maintenance Requests</h1>
            <p class="mt-2 text-sm text-gray-700">Manage and track all maintenance requests</p>
        </div>
        <div class="mt-4 sm:mt-0">
            <a href="{{ route('maintenance-requests.export', request()->query()) }}" 
               class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-lg text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 transition-all duration-300 hover:shadow-lg">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                </svg>
                Export CSV
            </a>
        </div>
    </div>

    <!-- Status Counts -->
    <div class="grid grid-cols-1 md:grid-cols-5 gap-4 mb-8">
        <!-- Total -->
        <div class="bg-white rounded-lg shadow-sm p-6 border border-gray-200">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <svg class="w-8 h-8 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                    </svg>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-500">Total</p>
                    <p class="text-2xl font-bold text-blue-600">{{ $statusCounts['all'] }}</p>
                </div>
            </div>
        </div>
        <!-- On Hold -->
        <div class="bg-white rounded-lg shadow-sm p-6 border border-gray-200">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <svg class="w-8 h-8 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 9v6m4-6v6m7-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-500">On Hold</p>
                    <p class="text-2xl font-bold text-orange-600">{{ $statusCounts['on_hold'] }}</p>
                </div>
            </div>
        </div>
        <!-- In Progress -->
        <div class="bg-white rounded-lg shadow-sm p-6 border border-gray-200">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <svg class="w-8 h-8 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.828 14.828a4 4 0 01-5.656 0M9 10h1.5a2.5 2.5 0 110 5H9V10z" />
                    </svg>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-500">In Progress</p>
                    <p class="text-2xl font-bold text-blue-600">{{ $statusCounts['in_progress'] }}</p>
                </div>
            </div>
        </div>
        <!-- Done -->
        <div class="bg-white rounded-lg shadow-sm p-6 border border-gray-200">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <svg class="w-8 h-8 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-500">Done</p>
                    <p class="text-2xl font-bold text-green-600">{{ $statusCounts['done'] }}</p>
                </div>
            </div>
        </div>
        <!-- Canceled -->
        <div class="bg-white rounded-lg shadow-sm p-6 border border-gray-200">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <svg class="w-8 h-8 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-500">Canceled</p>
                    <p class="text-2xl font-bold text-red-600">{{ $statusCounts['canceled'] }}</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters -->
    <div class="bg-white shadow-lg rounded-xl p-6 mb-8 transition-all duration-300 hover:shadow-xl border border-gray-100">
        <form method="GET" action="{{ route('maintenance-requests.index') }}" class="space-y-6">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
                <!-- Status Filter -->
                <div>
                    <label for="status" class="block text-sm font-semibold text-gray-800 mb-2">Status</label>
                    <select name="status" id="status" class="block w-full rounded-xl border-gray-200 shadow-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 sm:text-sm transition-all duration-200 hover:border-blue-400 py-3 px-4">
                        <option value="all" {{ request('status') === 'all' || !request('status') ? 'selected' : '' }}>All Statuses</option>
                        <option value="on_hold" {{ request('status') === 'on_hold' ? 'selected' : '' }}>On Hold</option>
                        <option value="in_progress" {{ request('status') === 'in_progress' ? 'selected' : '' }}>In Progress</option>
                        <option value="done" {{ request('status') === 'done' ? 'selected' : '' }}>Done</option>
                        <option value="canceled" {{ request('status') === 'canceled' ? 'selected' : '' }}>Canceled</option>
                    </select>
                </div>
                <!-- Urgency Filter -->
                <div>
                    <label for="urgency" class="block text-sm font-semibold text-gray-800 mb-2">Urgency</label>
                    <select name="urgency" id="urgency" class="block w-full rounded-xl border-gray-200 shadow-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 sm:text-sm transition-all duration-200 hover:border-blue-400 py-3 px-4">
                        <option value="all" {{ request('urgency') === 'all' || !request('urgency') ? 'selected' : '' }}>All Urgencies</option>
                        @foreach($urgencyLevels as $urgency)
                            <option value="{{ $urgency->id }}" {{ request('urgency') == $urgency->id ? 'selected' : '' }}>
                                {{ $urgency->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <!-- Store Filter -->
                <div>
                    <label for="store" class="block text-sm font-semibold text-gray-800 mb-2">Store</label>
                    <select name="store" id="store" class="block w-full rounded-xl border-gray-200 shadow-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 sm:text-sm transition-all duration-200 hover:border-blue-400 py-3 px-4">
                        <option value="all" {{ request('store') === 'all' || !request('store') ? 'selected' : '' }}>All Stores</option>
                        @foreach($stores as $store)
                            <option value="{{ $store }}" {{ request('store') === $store ? 'selected' : '' }}>
                                {{ $store }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <!-- Search Field -->
                <div>
                    <label for="search" class="block text-sm font-semibold text-gray-800 mb-2">Search</label>
                    <input type="text" name="search" id="search" 
                           class="block w-full rounded-xl border-gray-200 shadow-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 sm:text-sm transition-all duration-200 hover:border-blue-400 py-3 px-4" 
                           placeholder="Search store, equipment, description..." 
                           value="{{ request('search') }}">
                </div>
            </div>
            <div class="flex justify-end space-x-4">
                <a href="{{ route('maintenance-requests.index') }}" 
                   class="inline-flex items-center px-5 py-3 border border-gray-200 text-sm font-medium rounded-xl text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500 transition-all duration-200 transform hover:scale-[1.02] hover:shadow-md">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                    </svg>
                    Reset
                </a>
                <button type="submit" 
                        class="inline-flex items-center px-5 py-3 border border-transparent text-sm font-medium rounded-xl text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-all duration-200 transform hover:scale-[1.02] hover:shadow-md">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                    </svg>
                    Apply Filter
                </button>
            </div>
        </form>
    </div>

    <!-- Bulk Actions -->
    <form id="bulkForm" method="POST" action="{{ route('maintenance-requests.bulk-update-status') }}">
        @csrf
        @method('PATCH')
        <div class="bg-white rounded-xl shadow-sm p-6 mb-6 border border-gray-200">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <!-- Bulk Status -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Bulk Status Update</label>
                    <select name="status" class="bulk-status-select block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm" required>
                        <option value="">Select Status</option>
                        <option value="on_hold">On Hold</option>
                        <option value="in_progress">In Progress</option>
                        <option value="done">Done</option>
                        <option value="canceled">Canceled</option>
                    </select>
                </div>
                <!-- Costs (when done) -->
                <div class="bulk-costs-field" style="display: none;">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Costs *</label>
                    <input type="number" name="costs" step="0.01" min="0" class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm" placeholder="0.00">
                </div>
                <!-- How We Fixed It (when done) -->
                <div class="bulk-fix-field" style="display: none;">
                    <label class="block text-sm font-medium text-gray-700 mb-2">How We Fixed It *</label>
                    <textarea name="how_we_fixed_it" class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm" placeholder="Describe how the issue was fixed"></textarea>
                </div>
                <!-- Update Button -->
                <div class="flex items-end">
                    <button type="submit" id="bulkBtn" disabled
                            class="w-full inline-flex justify-center items-center px-4 py-2 bg-orange-600 text-white rounded-lg hover:bg-orange-700 transition-colors duration-200 disabled:opacity-50">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                        </svg>
                        Update Selected
                    </button>
                </div>
            </div>
        </div>
    </form>

    <!-- Requests Table -->
    <div class="bg-white shadow-sm ring-1 ring-black ring-opacity-5 rounded-lg overflow-hidden">
        @if($maintenanceRequests->count() > 0)
            <!-- Desktop Table View -->
            <div class="hidden sm:block">
                <table class="min-w-full divide-y divide-gray-300">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="py-3.5 pl-4 pr-3 text-left text-sm font-semibold text-gray-900">
                                <input type="checkbox"
                                       id="selectAll"
                                       form="bulkForm"
                                       class="rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                            </th>
                            <th class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">Entry #</th>
                            <th class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">Store</th>
                            <th class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">Requester</th>
                            <th class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">Equipment</th>
                            <th class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">Urgency</th>
                            <th class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">Status</th>
                            <th class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">Date</th>
                            <th class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 bg-white">
                        @foreach($maintenanceRequests as $request)
                            <tr class="hover:bg-gray-50 transition-colors duration-150">
                                <td class="py-4 pl-4 pr-3">
                                    <input type="checkbox"
                                           form="bulkForm"
                                           name="request_ids[]"
                                           value="{{ $request->id }}"
                                           class="request-checkbox rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                                </td>
                                <td class="whitespace-nowrap px-3 py-4 text-sm font-medium text-gray-900">{{ $request->entry_number }}</td>
                                <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-500">{{ $request->store }}</td>
                                <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-500">{{ $request->requester->full_name }}</td>
                                <td class="px-3 py-4 text-sm text-gray-500">{{ Str::limit($request->equipment_with_issue, 30) }}</td>
                                <td class="whitespace-nowrap px-3 py-4 text-sm">
                                    @switch($request->urgencyLevel->name)
                                        @case('Impacts Sales')
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                                <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                                    <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                                                </svg>
                                                {{ $request->urgencyLevel->name }}
                                            </span>
                                            @break
                                        @case('High')
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-orange-100 text-orange-800">
                                                <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                                    <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                                                </svg>
                                                {{ $request->urgencyLevel->name }}
                                            </span>
                                            @break
                                        @case('Medium')
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                                <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd" />
                                                </svg>
                                                {{ $request->urgencyLevel->name }}
                                            </span>
                                            @break
                                        @case('Low')
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                                                <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd" />
                                                </svg>
                                                {{ $request->urgencyLevel->name }}
                                            </span>
                                            @break
                                    @endswitch
                                </td>
                                <td class="whitespace-nowrap px-3 py-4 text-sm">
                                    @switch($request->status)
                                        @case('on_hold')
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-orange-100 text-orange-800">
                                                <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8 7a1 1 0 00-1 1v4a1 1 0 001 1h4a1 1 0 001-1V8a1 1 0 00-1-1H8z" clip-rule="evenodd" />
                                                </svg>
                                                On Hold
                                            </span>
                                            @break
                                        @case('in_progress')
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                                <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM9.555 7.168A1 1 0 008 8v4a1 1 0 001.555.832l3-2a1 1 0 000-1.664l-3-2z" clip-rule="evenodd" />
                                                </svg>
                                                In Progress
                                            </span>
                                            @break
                                        @case('done')
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                                <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                                                </svg>
                                                Done
                                            </span>
                                            @break
                                        @case('canceled')
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                                <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                                                </svg>
                                                Canceled
                                            </span>
                                            @break
                                    @endswitch
                                </td>
                                <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-500">{{ $request->created_at->format('M d, Y') }}</td>
                                <td class="whitespace-nowrap px-3 py-4 text-sm">
                                    <div class="flex space-x-2">
                                        <a href="{{ route('maintenance-requests.show', $request) }}" 
                                           class="inline-flex items-center text-blue-600 hover:text-blue-900 font-medium transition-colors duration-150">
                                            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                            </svg>
                                            View
                                        </a>

                                        @if(!in_array($request->status, ['done', 'canceled']))
                                            <button type="button" 
                                                    class="inline-flex items-center text-orange-600 hover:text-orange-900 font-medium transition-colors duration-150" 
                                                    onclick="openModal('statusModal{{ $request->id }}')">
                                                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                                </svg>
                                                Edit
                                            </button>
                                        @endif

                                        <form action="{{ route('maintenance-requests.destroy', $request) }}" 
                                              method="POST" 
                                              style="display: inline;" 
                                              onsubmit="return confirm('Are you sure you want to delete this maintenance request? This action cannot be undone.')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" 
                                                    class="inline-flex items-center text-red-600 hover:text-red-900 font-medium transition-colors duration-150">
                                                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                                </svg>
                                                Delete
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>

                            <!-- Status Update Modal -->
                            @if(!in_array($request->status, ['done', 'canceled']))
                                <div class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden" id="statusModal{{ $request->id }}">
                                    <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
                                        <div class="mt-3">
                                            <div class="flex items-center justify-between mb-4">
                                                <h3 class="text-lg font-medium text-gray-900">Update Status - Entry #{{ $request->entry_number }}</h3>
                                                <button type="button" class="text-gray-400 hover:text-gray-600" onclick="closeModal('statusModal{{ $request->id }}')">
                                                    <span class="sr-only">Close</span>
                                                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                                    </svg>
                                                </button>
                                            </div>
                                            <form action="{{ route('maintenance-requests.update-status', $request) }}" method="POST">
                                                @csrf
                                                @method('PATCH')
                                                <div class="mb-4">
                                                    <label for="status{{ $request->id }}" class="block text-sm font-medium text-gray-700 mb-2">New Status</label>
                                                    <select name="status" id="status{{ $request->id }}" class="status-select block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm" required>
                                                        @if($request->status === 'on_hold')
                                                            <option value="in_progress">In Progress</option>
                                                            <option value="done">Done</option>
                                                            <option value="canceled">Canceled</option>
                                                        @elseif($request->status === 'in_progress')
                                                            <option value="on_hold">On Hold</option>
                                                            <option value="done">Done</option>
                                                            <option value="canceled">Canceled</option>
                                                        @endif
                                                    </select>
                                                </div>
                                                <div class="mb-4 costs-field" style="display: none;">
                                                    <label for="costs{{ $request->id }}" class="block text-sm font-medium text-gray-700 mb-2">Costs *</label>
                                                    <input type="number" name="costs" id="costs{{ $request->id }}" step="0.01" min="0" class="block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm" placeholder="0.00">
                                                </div>
                                                <div class="mb-4 fix-field" style="display: none;">
                                                    <label for="how_we_fixed_it{{ $request->id }}" class="block text-sm font-medium text-gray-700 mb-2">How We Fixed It *</label>
                                                    <textarea name="how_we_fixed_it" id="how_we_fixed_it{{ $request->id }}" class="block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm" rows="3" placeholder="Please describe how the issue was fixed..."></textarea>
                                                </div>
                                                <div class="flex justify-end space-x-3">
                                                    <button type="button" class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-100 hover:bg-gray-200 rounded-md transition-colors duration-150" onclick="closeModal('statusModal{{ $request->id }}')">Cancel</button>
                                                    <button type="submit" class="px-4 py-2 text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 rounded-md transition-colors duration-150">Update Status</button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            @endif

                        @endforeach
                    </tbody>
                </table>
            </div>

            <!-- Mobile Card View -->
            <div class="sm:hidden space-y-4 p-4">
                @foreach($maintenanceRequests as $request)
                    <div class="bg-white border border-gray-200 rounded-lg p-4 shadow-sm">
                        <div class="flex justify-between items-start mb-3">
                            <div class="flex items-center">
                                <input type="checkbox"
                                       form="bulkForm"
                                       name="request_ids[]"
                                       value="{{ $request->id }}"
                                       class="request-checkbox rounded border-gray-300 text-blue-600 focus:ring-blue-500 mr-3">
                                <div>
                                    <p class="text-sm font-medium text-gray-900">Entry #{{ $request->entry_number }}</p>
                                    <p class="text-xs text-gray-500">{{ $request->created_at->format('M d, Y') }}</p>
                                </div>
                            </div>
                            <div class="flex space-x-2">
                                @switch($request->status)
                                    @case('on_hold')
                                        <svg class="w-5 h-5 text-orange-600" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8 7a1 1 0 00-1 1v4a1 1 0 001 1h4a1 1 0 001-1V8a1 1 0 00-1-1H8z" clip-rule="evenodd" />
                                        </svg>
                                        @break
                                    @case('in_progress')
                                        <svg class="w-5 h-5 text-blue-600" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM9.555 7.168A1 1 0 008 8v4a1 1 0 001.555.832l3-2a1 1 0 000-1.664l-3-2z" clip-rule="evenodd" />
                                        </svg>
                                        @break
                                    @case('done')
                                        <svg class="w-5 h-5 text-green-600" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                                        </svg>
                                        @break
                                    @case('canceled')
                                        <svg class="w-5 h-5 text-red-600" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                                        </svg>
                                        @break
                                @endswitch
                            </div>
                        </div>
                        <dl class="grid grid-cols-2 gap-4 text-sm">
                            <div>
                                <dt class="font-medium text-gray-500">Store</dt>
                                <dd class="text-gray-900">{{ $request->store }}</dd>
                            </div>
                            <div>
                                <dt class="font-medium text-gray-500">Requester</dt>
                                <dd class="text-gray-900">{{ $request->requester->full_name }}</dd>
                            </div>
                            <div class="col-span-2">
                                <dt class="font-medium text-gray-500">Equipment</dt>
                                <dd class="text-gray-900">{{ $request->equipment_with_issue }}</dd>
                            </div>
                        </dl>
                        <div class="mt-4 flex justify-between items-center">
                            <div class="text-xs">
                                @switch($request->urgencyLevel->name)
                                    @case('Impacts Sales')
                                        <span class="inline-flex items-center px-2 py-1 font-semibold rounded-full bg-red-100 text-red-800">
                                            <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                                            </svg>
                                            Critical
                                        </span>
                                        @break
                                    @case('High')
                                        <span class="inline-flex items-center px-2 py-1 font-semibold rounded-full bg-orange-100 text-orange-800">
                                            <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                                            </svg>
                                            High
                                        </span>
                                        @break
                                    @case('Medium')
                                        <span class="inline-flex items-center px-2 py-1 font-semibold rounded-full bg-blue-100 text-blue-800">
                                            <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd" />
                                            </svg>
                                            Medium
                                        </span>
                                        @break
                                    @case('Low')
                                        <span class="inline-flex items-center px-2 py-1 font-semibold rounded-full bg-gray-100 text-gray-800">
                                            <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd" />
                                            </svg>
                                            Low
                                        </span>
                                        @break
                                @endswitch
                            </div>
                            <div class="flex space-x-3">
                                <a href="{{ route('maintenance-requests.show', $request) }}" class="text-blue-600 hover:text-blue-900 font-medium">View</a>
                                @if(!in_array($request->status, ['done','canceled']))
                                    <button type="button" class="text-orange-600 hover:text-orange-900 font-medium" onclick="openModal('statusModal{{ $request->id }}')">Edit</button>
                                @endif
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

            <!-- Pagination -->
            <div class="px-4 py-3 border-t border-gray-200 sm:px-6">
                <div class="flex justify-between items-center">
                    <div class="text-sm text-gray-700">
                        Showing {{ $maintenanceRequests->firstItem() ?? 0 }} to {{ $maintenanceRequests->lastItem() ?? 0 }} 
                        of {{ $maintenanceRequests->total() }} results
                    </div>
                    <div class="flex space-x-1">
                        {{ $maintenanceRequests->links() }}
                    </div>
                </div>
            </div>
        @else
            <div class="text-center py-12">
                <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01" />
                </svg>
                <h3 class="mt-2 text-sm font-medium text-gray-900">No maintenance requests found</h3>
                <p class="mt-1 text-sm text-gray-500">No requests match your current filters.</p>
            </div>
        @endif
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
            checkboxes.forEach(checkbox => {
                checkbox.checked = this.checked;
            });
            toggleBulkButton();
        });
    }

    checkboxes.forEach(checkbox => {
        checkbox.addEventListener('change', toggleBulkButton);
    });

    function toggleBulkButton() {
        const checked = document.querySelectorAll('.request-checkbox:checked').length;
        if (bulkBtn) {
            bulkBtn.disabled = checked === 0;
        }
    }

    // Show/hide fields for 'done' status in the modal
    document.querySelectorAll('.status-select').forEach(select => {
        select.addEventListener('change', function() {
            const modal = this.closest('.relative');
            const costsField = modal.querySelector('.costs-field');
            const fixField = modal.querySelector('.fix-field');
            const costsInput = modal.querySelector('input[name="costs"]');
            const fixTextarea = modal.querySelector('textarea[name="how_we_fixed_it"]');
            
            if (this.value === 'done') {
                costsField.style.display = 'block';
                fixField.style.display = 'block';
                costsInput.required = true;
                fixTextarea.required = true;
            } else {
                costsField.style.display = 'none';
                fixField.style.display = 'none';
                costsInput.required = false;
                fixTextarea.required = false;
            }
        });
    });

    // Bulk form status select
    const bulkStatusSelect = document.querySelector('.bulk-status-select');
    const bulkCostsField = document.querySelector('.bulk-costs-field');
    const bulkFixField = document.querySelector('.bulk-fix-field');
    
    if (bulkStatusSelect) {
        bulkStatusSelect.addEventListener('change', function() {
            if (this.value === 'done') {
                bulkCostsField.style.display = 'block';
                bulkFixField.style.display = 'block';
                bulkCostsField.querySelector('input').required = true;
                bulkFixField.querySelector('textarea').required = true;
            } else {
                bulkCostsField.style.display = 'none';
                bulkFixField.style.display = 'none';
                bulkCostsField.querySelector('input').required = false;
                bulkFixField.querySelector('textarea').required = false;
            }
        });
    }
});

function openModal(modalId) {
    document.getElementById(modalId).classList.remove('hidden');
}

function closeModal(modalId) {
    document.getElementById(modalId).classList.add('hidden');
}
</script>
@endsection
