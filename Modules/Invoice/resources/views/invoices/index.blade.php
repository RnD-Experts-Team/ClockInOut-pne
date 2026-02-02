@extends('layouts.app')

@section('title', 'Store Invoices')

@section('content')
    <div class="max-w-full mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <!-- Header Section -->
        <div class="sm:flex sm:items-center sm:justify-between mb-6">
            <div>
                <h1 class="text-3xl font-bold text-black-900">Maintenance Invoices</h1>
                <p class="mt-2 text-sm text-black-600">Generate and manage store maintenance invoices</p>
            </div>
           
        </div>

        <!-- Quick Stats -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-8">
            <div class="bg-orange-50 rounded-lg shadow-sm p-6 border border-orange-200 hover:shadow-md transition-shadow">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <svg class="w-8 h-8 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-black-700">Total Invoices</p>
                        <p class="text-2xl font-bold text-orange-600">{{ $stats['total'] }}</p>
                    </div>
                </div>
            </div>

            <div class="bg-green-50 rounded-lg shadow-sm p-6 border border-green-200 hover:shadow-md transition-shadow">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <svg class="w-8 h-8 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-black-700">This Month</p>
                        <p class="text-2xl font-bold text-green-600">{{ $stats['this_month'] }}</p>
                    </div>
                </div>
            </div>

            <div class="bg-blue-50 rounded-lg shadow-sm p-6 border border-blue-200 hover:shadow-md transition-shadow">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <svg class="w-8 h-8 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-black-700">Total Amount</p>
                        <p class="text-2xl font-bold text-blue-600">${{ number_format($stats['total_amount'], 0) }}</p>
                    </div>
                </div>
            </div>

            <div class="bg-purple-50 rounded-lg shadow-sm p-6 border border-purple-200 hover:shadow-md transition-shadow">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <svg class="w-8 h-8 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6" />
                        </svg>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-black-700">Avg Invoice</p>
                        <p class="text-2xl font-bold text-purple-600">${{ number_format($stats['avg_invoice'], 0) }}</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Filters Section -->
        <div class="bg-orange-50 shadow-lg rounded-xl p-6 mb-8 border border-orange-200">
            <div class="flex justify-between items-center mb-4">
                <h2 class="text-lg font-semibold text-black-900">Filter Invoices</h2>
            </div>
            <form method="GET" action="{{ route('invoice.invoices.index') }}" class="space-y-6">
                <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
                    <div>
                        <label for="search" class="block text-sm font-semibold text-black-700 mb-2">Search</label>
                        <input type="text" name="search" id="search"
                               class="block w-full rounded-xl border-orange-300 shadow-sm focus:border-orange-500 focus:ring-2 focus:ring-orange-500/20 sm:text-sm py-3 px-4"
                               placeholder="Invoice #, Store..."
                               value="{{ request('search') }}">
                    </div>

                    <div>
                        <label for="store_filter" class="block text-sm font-semibold text-black-700 mb-2">Store</label>
                        <select name="store_filter" id="store_filter" class="block w-full rounded-xl border-orange-300 shadow-sm focus:border-orange-500 focus:ring-2 focus:ring-orange-500/20 sm:text-sm py-3 px-4">
                            <option value="">All Stores</option>
                            @foreach($stores as $store)
                                <option value="{{ $store->id }}" {{ request('store_filter') == $store->id ? 'selected' : '' }}>
                                    Store {{ $store->store_number }} - {{ $store->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label for="month_filter" class="block text-sm font-semibold text-black-700 mb-2">Month</label>
                        <input type="month" name="month_filter" id="month_filter"
                               class="block w-full rounded-xl border-orange-300 shadow-sm focus:border-orange-500 focus:ring-2 focus:ring-orange-500/20 sm:text-sm py-3 px-4"
                               value="{{ request('month_filter') }}">
                    </div>

                    <div class="flex items-end">
                        <div class="w-full space-y-2">
                            <button type="submit"
                                    class="w-full inline-flex justify-center items-center px-4 py-3 border border-transparent text-sm font-medium rounded-xl text-white bg-orange-600 hover:bg-orange-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-orange-500">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                                </svg>
                                Apply Filter
                            </button>
                            <a href="{{ route('invoice.invoices.index') }}"
                               class="w-full inline-flex justify-center items-center px-4 py-2 border border-orange-300 text-sm font-medium rounded-xl text-black-700 bg-white hover:bg-orange-100 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-orange-500">
                                Reset
                            </a>
                        </div>
                    </div>
                </div>
            </form>
        </div>

        <!-- Invoices Table -->
        <div class="bg-orange-50 shadow-sm ring-1 ring-black ring-opacity-5 rounded-lg overflow-hidden">
            @if($invoices->count() > 0)
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-orange-200">
                        <thead class="bg-orange-100">
                        <tr>
                            <th class="px-4 py-3.5 text-left text-xs font-medium text-black-700 uppercase tracking-wide">Invoice #</th>
                            <th class="px-4 py-3.5 text-left text-xs font-medium text-black-700 uppercase tracking-wide">Store</th>
                            <th class="px-4 py-3.5 text-left text-xs font-medium text-black-700 uppercase tracking-wide">Period</th>
                            <th class="px-4 py-3.5 text-left text-xs font-medium text-black-700 uppercase tracking-wide">Breakdown</th>
                            <th class="px-4 py-3.5 text-left text-xs font-medium text-black-700 uppercase tracking-wide">Total Amount</th>
                            <th class="px-4 py-3.5 text-left text-xs font-medium text-black-700 uppercase tracking-wide">Status</th>
                            <th class="px-4 py-3.5 text-left text-xs font-medium text-black-700 uppercase tracking-wide">Actions</th>
                        </tr>
                        </thead>
                        <tbody class="divide-y divide-orange-200 bg-orange-50">
                        @foreach($invoices as $invoice)
                            <tr class="{{ $invoice->isDraft() ? 'bg-yellow-50 border-l-4 border-yellow-500' : '' }} hover:bg-orange-100 transition-colors duration-150">
                                <td class="px-4 py-4 text-sm">
                                    <div class="font-bold text-black-900 flex items-center gap-2">
                                        {{ $invoice->invoice_number }}
                                        @if($invoice->isDraft())
                                            <span class="inline-flex items-center px-1.5 py-0.5 rounded text-xs font-medium bg-yellow-200 text-yellow-800">
                                                ⚠ Unsent
                                            </span>
                                        @endif
                                    </div>
                                    <div class="text-xs text-black-600">{{ $invoice->created_at->format('m/d/Y') }}</div>
                                </td>
                                <td class="px-4 py-4 text-sm">
                                    <div class="font-medium text-black-900">Store #{{ $invoice->store->store_number }}</div>
                                    <div class="text-xs text-black-600">{{ $invoice->store->name }}</div>
                                </td>
                                <td class="px-4 py-4 text-sm text-black-900">
                                    <div>{{ $invoice->period_display }}</div>
                                    <div class="text-xs text-black-600">{{ $invoice->days_in_period }} days</div>
                                </td>
                                <td class="px-4 py-4 text-sm">
                                    <div class="space-y-1 text-xs">
                                        <div class="flex items-center gap-2">
                                            <span class="text-blue-600">⏱</span>
                                            <span>Labor: ${{ number_format($invoice->labor_cost, 2) }}</span>
                                        </div>
                                        <div class="flex items-center gap-2">
                                            <span class="text-green-600">📋</span>
                                            <span>Materials: ${{ number_format($invoice->materials_cost, 2) }}</span>
                                        </div>
                                        <div class="flex items-center gap-2">
                                            <span class="text-purple-600">🛒</span>
                                            <span>Equipment: ${{ number_format($invoice->equipment_cost, 2) }}</span>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-4 py-4 text-sm">
                                    <div class="font-bold text-orange-600 text-lg">${{ number_format($invoice->grand_total, 2) }}</div>
                                    <div class="text-xs text-black-600">Tax: ${{ number_format($invoice->tax_amount, 2) }}</div>
                                </td>
                                <td class="px-4 py-4 text-sm">
                                    @if($invoice->isSent())
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800 border border-green-300">
                                            ✓ Sent
                                        </span>
                                    @else
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800 border-2 border-yellow-400 shadow-sm">
                                            ⏳ Draft
                                        </span>
                                    @endif
                                </td>
                                <td class="px-4 py-4 text-sm">
                                    <div class="flex space-x-2">
                                        <a href="{{ route('invoice.invoices.show', $invoice->id) }}" class="text-orange-600 hover:text-orange-700 font-medium" title="View Invoice">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                            </svg>
                                        </a>
                                        @if($invoice->image_path)
                                            <a href="{{ route('invoice.invoices.download', $invoice->id) }}" class="text-green-600 hover:text-green-700" title="Download Image">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                                                </svg>
                                            </a>
                                        @endif
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
                            Showing {{ $invoices->firstItem() }} to {{ $invoices->lastItem() }} of {{ $invoices->total() }} results
                        </div>
                        <div class="flex space-x-1">
                            {{ $invoices->links() }}
                        </div>
                    </div>
                </div>
            @else
                <div class="text-center py-12">
                    <svg class="mx-auto h-12 w-12 text-black-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                    <h3 class="mt-2 text-sm font-medium text-black-900">No invoices found</h3>
                    <p class="mt-1 text-sm text-black-700">Invoices will be generated automatically from completed invoice cards.</p>
                </div>
            @endif
        </div>
    </div>
@endsection
