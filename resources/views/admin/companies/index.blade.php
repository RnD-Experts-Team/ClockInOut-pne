@extends('layouts.app')

@section('title', 'Company Management')

@section('content')
    <div class="max-w-full mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <!-- Header Section -->
        <div class="sm:flex sm:items-center sm:justify-between mb-6">
            <div>
                <h1 class="text-3xl font-bold text-black-900">Company Management</h1>
                <p class="mt-2 text-sm text-black-700">Manage maintenance service providers and vendors</p>
            </div>
            <div class="mt-4 sm:mt-0 flex space-x-3">
                <a href="{{ route('companies.create') }}"
                   class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-lg text-white bg-orange-600 hover:bg-orange-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-orange-500 transition-all duration-300 hover:shadow-lg">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                    </svg>
                    Add Company
                </a>
                @if(isset($companies) && $companies->count() > 0)
                    <a href="{{ route('companies.export', request()->query()) }}"
                       class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-lg text-white bg-primary  focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 transition-all duration-300 hover:shadow-lg">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                        Export CSV
                    </a>
                @endif
            </div>
        </div>

        <!-- Quick Stats Overview -->
        @if(isset($stats))
            <div class="grid grid-cols-1 md:grid-cols-5 gap-4 mb-8">
                <div class="bg-orange-50 rounded-lg shadow-sm p-6 border border-orange-200 hover:shadow-md transition-shadow">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <svg class="w-8 h-8 text-black-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-2m-14 0h2m0 0V9a2 2 0 012-2h2a2 2 0 012 2v10" />
                            </svg>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-black-700">Total Companies</p>
                            <p class="text-2xl font-bold text-black-600">{{ $stats['total'] ?? 0 }}</p>
                        </div>
                    </div>
                </div>
                <div class="bg-orange-50 rounded-lg shadow-sm p-6 border border-orange-200 hover:shadow-md transition-shadow">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <svg class="w-8 h-8 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-black-700">Active Status</p>
                            <p class="text-2xl font-bold text-green-600">{{ $stats['active'] ?? 0 }}</p>
                        </div>
                    </div>
                </div>
                <div class="bg-orange-50 rounded-lg shadow-sm p-6 border border-orange-200 hover:shadow-md transition-shadow">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <svg class="w-8 h-8 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4" />
                            </svg>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-black-700">Inactive Status</p>
                            <p class="text-2xl font-bold text-gray-600">{{ $stats['inactive'] ?? 0 }}</p>
                        </div>
                    </div>
                </div>
                <div class="bg-orange-50 rounded-lg shadow-sm p-6 border border-orange-200 hover:shadow-md transition-shadow">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <svg class="w-8 h-8 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                            </svg>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-black-700">Total Payments</p>
                            <p class="text-2xl font-bold text-purple-600">{{ $stats['total_payments'] ?? 0 }}</p>
                        </div>
                    </div>
                </div>
                <div class="bg-orange-50 rounded-lg shadow-sm p-6 border border-orange-200 hover:shadow-md transition-shadow">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <svg class="w-8 h-8 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-black-700">Total Amount</p>
                            <p class="text-2xl font-bold text-green-600">${{ number_format($stats['total_amount'] ?? 0, 2) }}</p>
                        </div>
                    </div>
                </div>
            </div>
        @endif

        <!-- Filters Section -->
        <div class="bg-orange-50 shadow-lg rounded-xl p-6 mb-8 transition-all duration-300 hover:shadow-xl border border-orange-100">
            <div class="flex justify-between items-center mb-4">
                <h2 class="text-lg font-semibold text-black-900">Filter Companies</h2>
                <button type="button" id="toggleFilters" class="text-sm text-black-600 hover:text-black-700">
                    <span id="toggleText">Hide Filters</span>
                    <svg id="toggleIcon" class="w-4 h-4 inline ml-1 transform transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                    </svg>
                </button>
            </div>
            <form method="GET" action="{{ route('companies.index') }}" id="filterForm" class="space-y-6">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <div>
                        <label for="search" class="block text-sm font-semibold text-black-800 mb-2">Search</label>
                        <input type="text" name="search" id="search"
                               class="block w-full rounded-xl border-orange-200 shadow-sm focus:border-orange-500 focus:ring-2 focus:ring-orange-500/20 sm:text-sm transition-all duration-200 hover:border-orange-500 py-3 px-4"
                               placeholder="Company name, contact, email..."
                               value="{{ request('search') }}">
                    </div>
                    <div>
                        <label for="is_active" class="block text-sm font-semibold text-black-800 mb-2">Status</label>
                        <select name="is_active" id="is_active" class="block w-full rounded-xl border-orange-200 shadow-sm focus:border-orange-500 focus:ring-2 focus:ring-orange-500/20 sm:text-sm transition-all duration-200 hover:border-orange-500 py-3 px-4">
                            <option value="" {{ !request('is_active') ? 'selected' : '' }}>All Companies</option>
                            <option value="1" {{ request('is_active') === '1' ? 'selected' : '' }}>Active</option>
                            <option value="0" {{ request('is_active') === '0' ? 'selected' : '' }}>Inactive</option>
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
                            <a href="{{ route('companies.index') }}"
                               class="w-full inline-flex justify-center items-center px-4 py-2 border border-orange-200 text-sm font-medium rounded-xl text-black-700 bg-white hover:bg-orange-100 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-orange-500 transition-all duration-200">
                                Reset
                            </a>
                        </div>
                    </div>
                </div>
            </form>
        </div>

        <!-- Companies Table -->
        <div class="bg-orange-50 shadow-sm ring-1 ring-black ring-opacity-5 rounded-lg overflow-hidden">
            @if(isset($companies) && $companies->count() > 0)
                <!-- Desktop Table View -->
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-orange-200">
                        <thead class="bg-orange-100">
                        <tr>
                            <th class="px-6 py-3.5 text-left text-xs font-medium text-black-700 uppercase tracking-wide">Company Info</th>
                            <th class="px-6 py-3.5 text-left text-xs font-medium text-black-700 uppercase tracking-wide">Contact Details</th>
                            <th class="px-6 py-3.5 text-left text-xs font-medium text-black-700 uppercase tracking-wide">Payment History</th>
                            <th class="px-6 py-3.5 text-left text-xs font-medium text-black-700 uppercase tracking-wide">Status</th>
                            <th class="px-6 py-3.5 text-left text-xs font-medium text-black-700 uppercase tracking-wide">Actions</th>
                        </tr>
                        </thead>
                        <tbody class="divide-y divide-orange-200 bg-orange-50">
                        @foreach($companies as $company)
                            <tr class="hover:bg-orange-100 transition-colors duration-150">
                                <!-- Company Info -->
                                <td class="px-6 py-4 text-sm">
                                    <div class="space-y-1">
                                        <div class="font-medium text-black-900">{{ $company->name }}</div>
                                        @if($company->address)
                                            <div class="text-xs text-black-700">{{ Str::limit($company->address, 50) }}</div>
                                        @endif
                                        @if($company->website)
                                            <div class="text-xs">
                                                <a href="{{ $company->website }}" target="_blank" class="text-black-600 hover:text-black-800">
                                                    {{ Str::limit($company->website, 30) }}
                                                </a>
                                            </div>
                                        @endif
                                        <div class="text-xs text-black-600">
                                            Added {{ $company->created_at ? $company->created_at->format('M d, Y') : 'Unknown' }}
                                        </div>
                                    </div>
                                </td>

                                <!-- Contact Details -->
                                <td class="px-6 py-4 text-sm">
                                    <div class="space-y-1">
                                        @if($company->contact_person)
                                            <div class="text-black-900">{{ $company->contact_person }}</div>
                                        @endif
                                        @if($company->phone)
                                            <div class="text-xs text-black-700">{{ $company->phone }}</div>
                                        @endif
                                        @if($company->email)
                                            <div class="text-xs text-black-600">
                                                <a href="mailto:{{ $company->email }}">{{ $company->email }}</a>
                                            </div>
                                        @endif
                                    </div>
                                </td>

                                <!-- Payment History -->
                                <td class="px-6 py-4 text-sm">
                                    <div class="space-y-1">
                                        <div class="font-semibold text-black-900">{{ $company->payments_count ?? 0 }} payments</div>
                                        @if($company->payments_sum_cost)
                                            <div class="text-sm text-green-600 font-medium">${{ number_format($company->payments_sum_cost, 2) }}</div>
                                            <div class="text-xs text-black-700">Total amount</div>
                                        @else
                                            <div class="text-xs text-black-600">No payments yet</div>
                                        @endif
                                    </div>
                                </td>

                                <!-- Status -->
                                <td class="px-6 py-4 text-sm">
                                    @if($company->is_active)
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                            <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                                            </svg>
                                            Active
                                        </span>
                                    @else
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                                            <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                                            </svg>
                                            Inactive
                                        </span>
                                    @endif
                                </td>

                                <!-- Actions -->
                                <td class="px-6 py-4 text-sm">
                                    <div class="flex space-x-3">
                                        <a href="{{ route('companies.show', $company) }}"
                                           class="text-black-600 hover:text-black-700 font-medium transition-colors duration-150">
                                            View
                                        </a>
                                        <a href="{{ route('companies.edit', $company) }}"
                                           class="text-black-600 hover:text-black-700 font-medium transition-colors duration-150">
                                            Edit
                                        </a>
                                        @if(($company->payments_count ?? 0) == 0)
                                            <form action="{{ route('companies.destroy', $company) }}" method="POST" class="inline"
                                                  onsubmit="return confirm('Are you sure you want to delete this company?')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="text-red-600 hover:text-red-900 font-medium transition-colors duration-150">
                                                    Delete
                                                </button>
                                            </form>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                @if(method_exists($companies, 'links'))
                    <div class="px-4 py-3 border-t border-orange-200 sm:px-6">
                        <div class="flex justify-between items-center">
                            <div class="text-sm text-black-700">
                                Showing {{ $companies->firstItem() ?? 0 }} to {{ $companies->lastItem() ?? 0 }}
                                of {{ $companies->total() }} results
                            </div>
                            <div class="flex space-x-1">
                                {{ $companies->links() }}
                            </div>
                        </div>
                    </div>
                @endif
            @else
                <div class="text-center py-12">
                    <svg class="mx-auto h-12 w-12 text-black-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-2m-14 0h2m0 0V9a2 2 0 012-2h2a2 2 0 012 2v10" />
                    </svg>
                    <h3 class="mt-2 text-sm font-medium text-black-900">No companies found</h3>
                    <p class="mt-1 text-sm text-black-700">Get started by adding a new company.</p>
                    <div class="mt-6">
                        <a href="{{ route('companies.create') }}"
                           class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-lg text-white bg-orange-600 hover:bg-orange-700">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                            </svg>
                            Add Company
                        </a>
                    </div>
                </div>
            @endif
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
        });
    </script>

@endsection
