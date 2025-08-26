@extends('layouts.app')

@section('title', 'Apartment Lease Management')

@section('content')
    <div id="apartmentDashboard" class="max-w-full mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <!-- Header Section -->
        <div class="sm:flex sm:items-center sm:justify-between mb-6">
            <div>
                <h1 class="text-3xl font-bold text-black-900">Apartment Lease Management</h1>
                <p class="mt-2 text-sm text-black-700">Manage apartment leases, tenant information, and expiration tracking</p>
            </div>
            <div class="mt-4 sm:mt-0 flex space-x-3">
                <!-- View Lease List Button -->
                <button type="button" id="openApartmentLeaseListModal"
                        class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-lg text-white bg-orange-600 hover:bg-orange-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-orange-500 transition-all duration-300 hover:shadow-lg">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a4 4 0 01-4-4V5a4 4 0 014-4h10a4 4 0 014 4v10a4 4 0 01-4 4z"></path>
                    </svg>
                    View Lease List
                </button>

                <!-- Add New Lease Button -->
                <a href="{{ route('admin.apartment-leases.create') }}"
                   class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-lg text-white bg-orange-600 hover:bg-orange-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-orange-500 transition-all duration-300 hover:shadow-lg">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                    </svg>
                    Add New Lease
                </a>

                <!-- Export CSV Button -->
                <a href="{{ route('admin.apartment-leases.export', request()->query()) }}"
                   class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-lg text-white bg-orange-600 hover:bg-orange-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-orange-500 transition-all duration-300 hover:shadow-lg">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                    Export CSV
                </a>

                <!-- Import Excel Button -->
                <form id="importExcelForm" action="{{ route('admin.apartment-leases.import-xlsx') }}" method="POST" enctype="multipart/form-data" class="hidden">
                    @csrf
                    <input id="xlsx_file_input" name="xlsx_file" type="file" accept=".xlsx,.xls" required>
                </form>

                <label for="xlsx_file_input" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-lg text-white bg-orange-600 hover:bg-orange-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-orange-500 transition-all duration-300 hover:shadow-lg cursor-pointer">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M9 19l3 3m0 0l3-3m-3 3V10" />
                    </svg>
                    Import Excel
                </label>

                <!-- Export Image Button -->
                <button id="exportImageBtn"
                        class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-lg text-white bg-orange-600 hover:bg-orange-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-orange-500 transition-all duration-300 hover:shadow-lg">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                    </svg>
                    Export as Image
                </button>
            </div>
        </div>

        <script>
            document.getElementById('xlsx_file_input').addEventListener('change', function() {
                if (this.files.length > 0) {
                    document.getElementById('importExcelForm').submit();
                }
            });
        </script>

        <!-- Quick Stats Overview -->
        <div class="grid grid-cols-1 md:grid-cols-6 gap-4 mb-8">
            <div class="bg-orange-50 rounded-lg shadow-sm p-6 border border-orange-200 hover:shadow-md transition-shadow">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <svg class="w-8 h-8 text-black-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2H5a2 2 0 00-2-2V7" />
                        </svg>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-black-500">Total Apartments</p>
                        <p class="text-2xl font-bold text-black-600">{{ $stats['total'] ?? 0 }}</p>
                    </div>
                </div>
            </div>
            <div class="bg-orange-50 rounded-lg shadow-sm p-6 border border-orange-200 hover:shadow-md transition-shadow">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <svg class="w-8 h-8 text-black-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1" />
                        </svg>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-black-500">Total Monthly Rent</p>
                        <p class="text-2xl font-bold text-black-600">${{ number_format($stats['total_monthly_rent'] ?? 0) }}</p>
                    </div>
                </div>
            </div>
            <div class="bg-orange-50 rounded-lg shadow-sm p-6 border border-orange-200 hover:shadow-md transition-shadow">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <svg class="w-8 h-8 text-black-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                        </svg>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-black-500">Number of Family</p>
                        <p class="text-2xl font-bold text-black-600">{{ $stats['families'] ?? 0 }}</p>
                    </div>
                </div>
            </div>
            <div class="bg-orange-50 rounded-lg shadow-sm p-6 border border-orange-200 hover:shadow-md transition-shadow">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <svg class="w-8 h-8 text-black-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                        </svg>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-black-500">Number of Cars</p>
                        <p class="text-2xl font-bold text-black-600">{{ $stats['total_cars'] ?? 0 }}</p>
                    </div>
                </div>
            </div>
            <div class="bg-orange-50 rounded-lg shadow-sm p-6 border border-orange-200 hover:shadow-md transition-shadow">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <svg class="w-8 h-8 text-black-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                        </svg>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-black-500">Number of AT</p>
                        <p class="text-2xl font-bold text-black-600">{{ $stats['total_at'] ?? 0 }}</p>
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
                        <p class="text-sm font-medium text-black-500">Expiration Warning</p>
                        <p class="text-2xl font-bold text-black-600">{{ $stats['expiring_soon'] ?? 0 }}</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Filters Section -->
        <div class="bg-orange-50 shadow-lg rounded-xl p-6 mb-8 transition-all duration-300 hover:shadow-xl border border-orange-100">
            <div class="flex justify-between items-center mb-4">
                <h2 class="text-lg font-semibold text-black-900">Filter Apartments</h2>
                <button type="button" id="toggleFilters" class="text-sm text-black-600 hover:text-black-800">
                    <span id="toggleText">Hide Filters</span>
                    <svg id="toggleIcon" class="w-4 h-4 inline ml-1 transform transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                    </svg>
                </button>
            </div>
            <form method="GET" action="{{ route('admin.apartment-leases.index') }}" id="filterForm" class="space-y-6">
                <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
                    <div>
                        <label for="search" class="block text-sm font-semibold text-black-800 mb-2">Search</label>
                        <input type="text" name="search" id="search"
                               class="block w-full rounded-xl border-orange-200 shadow-sm focus:border-orange-500 focus:ring-2 focus:ring-orange-500/20 sm:text-sm transition-all duration-200 hover:border-orange-400 py-3 px-4"
                               placeholder="Store number, address, tenant..."
                               value="{{ request('search') }}">
                    </div>
                    <div>
                        <label for="family_filter" class="block text-sm font-semibold text-black-800 mb-2">Family Status</label>
                        <select name="family_filter" id="family_filter" class="block w-full rounded-xl border-orange-200 shadow-sm focus:border-orange-500 focus:ring-2 focus:ring-orange-500/20 sm:text-sm transition-all duration-200 hover:border-orange-400 py-3 px-4">
                            <option value="all" {{ request('family_filter') === 'all' || !request('family_filter') ? 'selected' : '' }}>All</option>
                            <option value="no" {{ request('family_filter') === 'no' ? 'selected' : '' }}>No</option>
                            <option value="yes" {{ request('family_filter') === 'yes' ? 'selected' : '' }}>Yes</option>
                        </select>
                    </div>
                    <div>
                        <label for="car_filter" class="block text-sm font-semibold text-black-800 mb-2">Car Ownership</label>
                        <select name="car_filter" id="car_filter" class="block w-full rounded-xl border-orange-200 shadow-sm focus:border-orange-500 focus:ring-2 focus:ring-orange-500/20 sm:text-sm transition-all duration-200 hover:border-orange-400 py-3 px-4">
                            <option value="all" {{ request('car_filter') === 'all' || !request('car_filter') ? 'selected' : '' }}>All</option>
                            <option value="no_car" {{ request('car_filter') === 'no_car' ? 'selected' : '' }}>No Car</option>
                            <option value="with_car" {{ request('car_filter') === 'with_car' ? 'selected' : '' }}>With Car</option>
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
                            <a href="{{ route('admin.apartment-leases.index') }}"
                               class="w-full inline-flex justify-center items-center px-4 py-2 border border-orange-200 text-sm font-medium rounded-xl text-black-700 bg-orange-50 hover:bg-orange-100 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-orange-500 transition-all duration-200">
                                Reset
                            </a>
                        </div>
                    </div>
                </div>
            </form>
        </div>

        <!-- Apartments Table -->
        <div class="bg-orange-50 shadow-sm ring-1 ring-orange-900/5 rounded-lg overflow-hidden">
            @if($leases->count() > 0)
                <!-- Desktop Table View -->
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-orange-300">
                        <thead class="bg-orange-100">
                        <tr>
                            <th class="px-3 py-3.5 text-left text-xs font-medium text-black-500 uppercase tracking-wide">Apartment Info</th>
                            <th class="px-3 py-3.5 text-left text-xs font-medium text-black-500 uppercase tracking-wide">Financial</th>
                            <th class="px-3 py-3.5 text-left text-xs font-medium text-black-500 uppercase tracking-wide">AT & Details</th>
                            <th class="px-3 py-3.5 text-left text-xs font-medium text-black-500 uppercase tracking-wide">Lease Details</th>
                            <th class="px-3 py-3.5 text-left text-xs font-medium text-black-500 uppercase tracking-wide">Expiration Status</th>
                            <th class="px-3 py-3.5 text-left text-xs font-medium text-black-500 uppercase tracking-wide">Actions</th>
                        </tr>
                        </thead>
                        <tbody class="divide-y divide-orange-200 bg-orange-50">
                        @foreach($leases as $lease)
                            <tr class="hover:bg-orange-100 transition-colors duration-150">
                                <!-- Apartment Info -->
                                <td class="px-3 py-4 text-sm">
                                    <div class="space-y-1">
                                        @if($lease->store_number)
                                            <div class="font-medium text-black-900">Store #{{ $lease->store_number }}</div>
                                        @endif
                                        <div class="text-black-700 text-xs max-w-xs">{{ Str::limit($lease->apartment_address, 50) }}</div>
                                        @if($lease->drive_time)
                                            <div class="text-xs text-black-600">{{ $lease->drive_time }} drive</div>
                                        @endif
                                    </div>
                                </td>

                                <!-- Financial -->
                                <td class="px-3 py-4 text-sm">
                                    <div class="space-y-1">
                                        <div class="font-semibold text-black-600">${{ number_format($lease->total_rent, 0) }}/mo</div>
                                        <div class="text-xs text-black-500">Rent: ${{ number_format($lease->rent, 0) }}</div>
                                        @if($lease->utilities)
                                            <div class="text-xs text-black-600">Utilities: ${{ number_format($lease->utilities, 0) }}</div>
                                        @endif
                                    </div>
                                </td>

                                <!-- AT & Details -->
                                <td class="px-3 py-4 text-sm">
                                    <div class="space-y-1">
                                        <div class="font-medium text-black-900">{{ $lease->number_of_AT }} AT</div>
                                        <div class="flex space-x-2">
                                            @if($lease->is_family === 'Yes' || $lease->is_family === 'yes')
                                                <span class="inline-flex items-center px-1.5 py-0.5 rounded text-xs font-medium bg-green-100 text-green-800">
                                                    Family
                                                </span>
                                            @endif
                                            @if($lease->has_car > 0)
                                                <span class="inline-flex items-center px-1.5 py-0.5 rounded text-xs font-medium bg-orange-100 text-black-800">
                                                    {{ $lease->has_car }} Car{{ $lease->has_car > 1 ? 's' : '' }}
                                                </span>
                                            @endif
                                        </div>
                                    </div>
                                </td>

                                <!-- Lease Details -->
                                <td class="px-3 py-4 text-sm">
                                    <div class="space-y-1">
                                        <div class="font-medium text-black-900 text-xs">{{ Str::limit($lease->lease_holder, 25) }}</div>
                                        @if($lease->expiration_date)
                                            <div class="text-xs text-black-700">
                                                Expires: {{ $lease->formatted_expiration_date }}
                                            </div>
                                        @endif
                                        @if($lease->notes)
                                            <div class="text-xs text-black-500">{{ Str::limit($lease->notes, 30) }}</div>
                                        @endif
                                    </div>
                                </td>

                                <!-- Expiration Status -->
                                <td class="px-3 py-4 text-sm">
                                    @if($lease->expiration_date)
                                        @php
                                            $expirationDate = \Carbon\Carbon::parse($lease->expiration_date);
                                            $now = \Carbon\Carbon::now();

                                            if ($expirationDate->isPast()) {
                                                $timeAgo = $expirationDate->diffForHumans($now, true);
                                                $formattedDate = $expirationDate->format('F j, Y') . ' (' . $timeAgo . ' ago)';
                                                $statusClass = 'bg-orange-100 text-black-800';
                                            } else {
                                                $timeFromNow = $now->diffForHumans($expirationDate, true);
                                                $formattedDate = $expirationDate->format('F j, Y') . ' (in ' . $timeFromNow . ')';

                                                // Different colors based on how soon it expires
                                                $daysUntil = $expirationDate->diffInDays($now);
                                                if ($daysUntil <= 30) {
                                                    $statusClass = 'bg-orange-100 text-black-800';
                                                } elseif ($daysUntil <= 90) {
                                                    $statusClass = 'bg-orange-100 text-black-800';
                                                } else {
                                                    $statusClass = 'text-black-700';
                                                }
                                            }
                                        @endphp

                                        <div class="space-y-1">
                                            <div class="text-xs font-medium text-black-700">Lease Expiration Date</div>
                                            <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium {{ $statusClass }}">
                                                {{ $formattedDate }}
                                            </span>
                                        </div>
                                    @endif
                                </td>
                                <!-- Actions -->
                                <td class="px-3 py-4 text-sm">
                                    <div class="flex space-x-2">
                                        <a href="{{ route('admin.apartment-leases.show', $lease) }}"
                                           class="inline-flex items-center text-black-600 hover:text-black-900 font-medium transition-colors duration-150">
                                            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                            </svg>
                                            View
                                        </a>
                                        <a href="{{ route('admin.apartment-leases.edit', $lease) }}"
                                           class="inline-flex items-center text-black-600 hover:text-black-900 font-medium transition-colors duration-150">
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
                        <div class="bg-orange-50 border border-orange-200 rounded-lg p-4 shadow-sm">
                            <div class="flex justify-between items-start mb-3">
                                <div>
                                    @if($lease->store_number)
                                        <p class="text-sm font-medium text-black-900">Store #{{ $lease->store_number }}</p>
                                    @endif
                                    <p class="text-xs text-black-700">{{ Str::limit($lease->apartment_address, 40) }}</p>
                                </div>
                                <div class="text-right">
                                    <p class="text-sm font-semibold text-black-600">${{ number_format($lease->total_rent, 0) }}/mo</p>
                                    <p class="text-xs text-black-500">{{ $lease->number_of_AT }} AT</p>
                                </div>
                            </div>

                            @php $status = $lease->expiration_status @endphp
                            @if($status['status'] !== 'no_date' && $status['status'] !== 'active')
                                <div class="mb-3 p-2 bg-orange-100 rounded">
                                    <p class="text-xs font-medium text-black-900">{{ $status['message'] }}</p>
                                </div>
                            @endif

                            <div class="flex justify-between items-center">
                                <div class="flex space-x-4 text-xs text-black-700">
                                    <span>{{ Str::limit($lease->lease_holder, 25) }}</span>
                                    @if($lease->expiration_date)
                                        <span>{{ $lease->formatted_expiration_date }}</span>
                                    @endif
                                </div>
                                <div>
                                    <a href="{{ route('admin.apartment-leases.show', $lease) }}"
                                       class="text-black-600 hover:text-black-900">
                                        View
                                    </a>
                                </div>
                            </div>
                        </div>
                    @endforeach
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
                    <p class="mt-1 text-sm text-black-500">Get started by creating a new lease.</p>
                    <div class="mt-6">
                        <a href="{{ route('admin.apartment-leases.create') }}"
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

        <!-- Modal for Apartment Lease List -->
        <div id="apartmentLeaseListModal" class="fixed inset-0 bg-black bg-opacity-50 hidden z-50" onclick="closeModal('apartmentLeaseListModal')">
            <div class="flex items-center justify-center min-h-screen">
                <div class="bg-orange-50 rounded-lg shadow-lg p-6 w-full max-w-4xl max-h-[80vh] overflow-y-auto relative" onclick="event.stopPropagation()">
                    <div class="modal-content">
                        <div id="apartmentLeaseListContent" class="p-4">
                            <p>Loading...</p>
                        </div>
                    </div>
                    <div class="mt-4 text-right">
                        <button onclick="generateScreenshot('apartmentLeaseListModal', 'apartment-lease-list')"
                                class="inline-flex items-center px-4 py-2 bg-red-600 text-white text-sm font-medium rounded hover:bg-red-700 mr-2">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                            </svg>
                            Screenshot
                        </button>
                        <button onclick="closeModal('apartmentLeaseListModal')"
                                class="inline-flex items-center px-4 py-2 bg-orange-600 text-white text-sm font-medium rounded hover:bg-orange-700">
                            Close
                        </button>
                    </div>
                </div>
            </div>
        </div>

    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Filter toggle
            const toggleFilters = document.getElementById('toggleFilters');
            const filterForm = document.getElementById('filterForm');
            const toggleText = document.getElementById('toggleText');
            const toggleIcon = document.getElementById('toggleIcon');

            if (toggleFilters && filterForm && toggleText && toggleIcon) {
                toggleFilters.addEventListener('click', function() {
                    const isHidden = filterForm.style.display === 'none';
                    filterForm.style.display = isHidden ? 'block' : 'none';
                    toggleText.textContent = isHidden ? 'Hide Filters' : 'Show Filters';
                    toggleIcon.style.transform = isHidden ? 'rotate(0deg)' : 'rotate(-90deg)';
                });
            }

            // Excel file import handler
            const xlsxFileInput = document.getElementById('xlsx_file_input');
            const importExcelForm = document.getElementById('importExcelForm');

            if (xlsxFileInput && importExcelForm) {
                xlsxFileInput.addEventListener('change', function() {
                    if (this.files.length > 0) {
                        importExcelForm.submit();
                    }
                });
            }

            // Handle both modal buttons - "View Apartment Lease List" and "Export as Image"
            const openApartmentLeaseListModal = document.getElementById('openApartmentLeaseListModal');
            const exportImageBtn = document.getElementById('exportImageBtn');

            if (openApartmentLeaseListModal) {
                openApartmentLeaseListModal.addEventListener('click', function() {
                    openModalWithContent();
                });
            }

            if (exportImageBtn) {
                exportImageBtn.addEventListener('click', function() {
                    openModalWithContent();
                });
            }

            function openModalWithContent() {
                const modal = document.getElementById('apartmentLeaseListModal');
                if (modal) {
                    modal.classList.remove('hidden');
                    loadApartmentLeaseList();
                    // Prevent body scrolling
                    document.body.style.overflow = 'hidden';
                }
            }

            // Table sorting functionality
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

        // Load apartment lease list content
        function loadApartmentLeaseList() {
            fetch('{{ route('admin.apartment-leases.list') }}')
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

                    document.getElementById('apartmentLeaseListContent').innerHTML = content ? content.outerHTML : '<p>Error loading content</p>';
                })
                .catch(error => {
                    console.error('Error:', error);
                    document.getElementById('apartmentLeaseListContent').innerHTML = '<p>Error loading content</p>';
                });
        }

        // Load html2canvas library
        function loadHtml2Canvas() {
            return new Promise((resolve, reject) => {
                if (window.html2canvas) {
                    resolve(window.html2canvas);
                    return;
                }
                const script = document.createElement('script');
                script.src = 'https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js';
                script.onload = () => resolve(window.html2canvas);
                script.onerror = reject;
                document.head.appendChild(script);
            });
        }

        // Enhanced screenshot functionality with language selection
        function generateScreenshot(modalId, type) {
            showLanguageSelection(modalId, type);
        }

        function showLanguageSelection(modalId, type) {
            const languageModal = document.createElement('div');
            languageModal.id = 'languageSelectionModal';
            languageModal.className = 'fixed inset-0 bg-black bg-opacity-50 z-[110] flex items-center justify-center';
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

            loadHtml2Canvas().then(html2canvas => {
                const modal = document.getElementById(modalId);
                const contentElement = modal.querySelector('.modal-content');

                if (!contentElement) {
                    showScreenshotError(modalId, 'Content not found for screenshot');
                    return;
                }

                // Configure html2canvas options
                const options = {
                    backgroundColor: '#ffffff',
                    scale: 2,
                    useCORS: true,
                    allowTaint: true,
                    scrollX: 0,
                    scrollY: 0,
                    width: contentElement.scrollWidth,
                    height: contentElement.scrollHeight,
                    logging: false
                };

                html2canvas(contentElement, options).then(canvas => {
                    canvas.toBlob(blob => {
                        if (!blob) {
                            showScreenshotError(modalId, 'Failed to generate screenshot');
                            return;
                        }

                        const url = URL.createObjectURL(blob);
                        const now = new Date();
                        const dateStr = now.toISOString().split('T')[0];
                        const timeStr = now.toTimeString().split(' ')[0].replace(/:/g, '-');
                        const languageSuffix = language === 'ar' ? '-arabic' : '-english';
                        const filename = `${type}${languageSuffix}-${dateStr}-${timeStr}.png`;

                        hideScreenshotLoading(modalId);
                        showScreenshotReady(modalId, url, url, filename);

                        // Reset language styles after screenshot
                        resetLanguageStyles();
                    }, 'image/png', 0.95);
                }).catch(error => {
                    console.error('html2canvas error:', error);
                    showScreenshotError(modalId, 'Failed to capture screenshot');
                    resetLanguageStyles();
                });
            }).catch(error => {
                console.error('Failed to load html2canvas:', error);
                showScreenshotError(modalId, 'Failed to load screenshot library');
                resetLanguageStyles();
            });
        }

        function applyLanguageStyles(language) {
            const contentElement = document.querySelector('#apartmentLeaseListContent');
            if (!contentElement) return;

            if (language === 'ar') {
                // Apply Arabic styling
                contentElement.style.direction = 'rtl';
                contentElement.style.textAlign = 'right';

                // Update header text to Arabic
                const header = contentElement.querySelector('h1');
                if (header) {
                    header.setAttribute('data-original-text', header.textContent);
                    header.textContent = 'قائمة عقود إيجار الشقق';
                }

                // Update table headers to Arabic
                const elementsToTranslate = {
                    'Store #': 'رقم المتجر',
                    'Store Number': 'رقم المتجر',
                    'Address': 'العنوان',
                    'Apartment Address': 'عنوان الشقة',
                    'Total Rent': 'إجمالي الإيجار',
                    'Number of AT': 'عدد AT',
                    'Lease Holder': 'صاحب العقد',
                    'Expiration Date': 'تاريخ انتهاء الصلاحية',
                    'Family': 'العائلة',
                    'Cars': 'السيارات',
                    'TOTAL': 'المجموع',
                    'Apartments': 'شقة',
                    'Generated on': 'تم الإنشاء في',
                    'Yes': 'نعم',
                    'No': 'لا'
                };

                // Translate headers and common elements
                Object.keys(elementsToTranslate).forEach(englishText => {
                    const elements = contentElement.querySelectorAll('th, td, p, span');
                    elements.forEach(el => {
                        if (el.textContent.includes(englishText)) {
                            el.setAttribute('data-original-text', el.textContent);
                            el.textContent = el.textContent.replace(englishText, elementsToTranslate[englishText]);
                        }
                    });
                });

                // Update date format
                const dateElement = contentElement.querySelector('p');
                if (dateElement && dateElement.textContent.includes('Generated on')) {
                    dateElement.setAttribute('data-original-text', dateElement.textContent);
                    const now = new Date();
                    const arabicDate = now.toLocaleDateString('ar-SA', {
                        year: 'numeric',
                        month: 'long',
                        day: 'numeric',
                        hour: '2-digit',
                        minute: '2-digit'
                    });
                    dateElement.textContent = `تم الإنشاء في ${arabicDate}`;
                }

            } else {
                // English is default, no changes needed
                resetLanguageStyles();
            }
        }

        function resetLanguageStyles() {
            const contentElement = document.querySelector('#apartmentLeaseListContent');
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
                screenshotButton.innerHTML = `
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                </svg>
                Screenshot
            `;
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

        function closeModal(modalId) {
            const modal = document.getElementById(modalId);
            if (modal) {
                modal.classList.add('hidden');
                // Restore body scrolling
                document.body.style.overflow = 'auto';
            }
        }

        // Close modal when clicking outside
        window.onclick = function(event) {
            const modal = document.getElementById('apartmentLeaseListModal');
            if (event.target === modal) {
                closeModal('apartmentLeaseListModal');
            }
        }
    </script>

@endsection
