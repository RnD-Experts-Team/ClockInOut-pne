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
            <div class="mt-4 sm:mt-0 flex space-x-3">
                <a href="/invoicetest"
                   class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-lg text-white bg-orange-600 hover:bg-orange-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-orange-500 transition-all duration-300 hover:shadow-lg">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                    </svg>
                    Generate Image Invoice
                </a>
                <button type="button"
                        class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-lg text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 transition-all duration-300 hover:shadow-lg">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                    Export CSV
                </button>
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
                        <p class="text-2xl font-bold text-orange-600">24</p>
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
                        <p class="text-2xl font-bold text-green-600">8</p>
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
                        <p class="text-2xl font-bold text-blue-600">$58,421</p>
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
                        <p class="text-2xl font-bold text-purple-600">$2,434</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Filters Section -->
        <div class="bg-orange-50 shadow-lg rounded-xl p-6 mb-8 border border-orange-200">
            <div class="flex justify-between items-center mb-4">
                <h2 class="text-lg font-semibold text-black-900">Filter Invoices</h2>
            </div>
            <form method="GET" action="#" class="space-y-6">
                <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
                    <div>
                        <label for="search" class="block text-sm font-semibold text-black-700 mb-2">Search</label>
                        <input type="text" name="search" id="search"
                               class="block w-full rounded-xl border-orange-300 shadow-sm focus:border-orange-500 focus:ring-2 focus:ring-orange-500/20 sm:text-sm py-3 px-4"
                               placeholder="Invoice #, Store...">
                    </div>

                    <div>
                        <label for="store_filter" class="block text-sm font-semibold text-black-700 mb-2">Store</label>
                        <select name="store_filter" id="store_filter" class="block w-full rounded-xl border-orange-300 shadow-sm focus:border-orange-500 focus:ring-2 focus:ring-orange-500/20 sm:text-sm py-3 px-4">
                            <option value="">All Stores</option>
                            <option value="1">Store 3 - Downtown</option>
                            <option value="2">Store 5 - Mall Rd</option>
                            <option value="3">Store 7 - Airport</option>
                        </select>
                    </div>

                    <div>
                        <label for="month_filter" class="block text-sm font-semibold text-black-700 mb-2">Month</label>
                        <select name="month_filter" id="month_filter" class="block w-full rounded-xl border-orange-300 shadow-sm focus:border-orange-500 focus:ring-2 focus:ring-orange-500/20 sm:text-sm py-3 px-4">
                            <option value="">All Months</option>
                            <option value="2025-11" selected>November 2025</option>
                            <option value="2025-10">October 2025</option>
                            <option value="2025-09">September 2025</option>
                        </select>
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
                            <a href="#"
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
                    <!-- Invoice Row 1 - SENT (Normal styling) -->
                    <tr class="hover:bg-orange-100 transition-colors duration-150">
                        <td class="px-4 py-4 text-sm">
                            <div class="font-bold text-black-900">INT-0015</div>
                            <div class="text-xs text-black-600">11/30/2025</div>
                        </td>
                        <td class="px-4 py-4 text-sm">
                            <div class="font-medium text-black-900">Store #005</div>
                            <div class="text-xs text-black-600">Mall Rd</div>
                        </td>
                        <td class="px-4 py-4 text-sm text-black-900">
                            <div>Nov 1-30, 2025</div>
                            <div class="text-xs text-black-600">30 days</div>
                        </td>
                        <td class="px-4 py-4 text-sm">
                            <div class="space-y-1 text-xs">
                                <div class="flex items-center gap-2">
                                    <span class="text-blue-600">⏱</span>
                                    <span>Labor: $571.25</span>
                                </div>
                                <div class="flex items-center gap-2">
                                    <span class="text-green-600">📋</span>
                                    <span>Materials: $235.00</span>
                                </div>
                                <div class="flex items-center gap-2">
                                    <span class="text-purple-600">🛒</span>
                                    <span>Equipment: $3,405.00</span>
                                </div>
                            </div>
                        </td>
                        <td class="px-4 py-4 text-sm">
                            <div class="font-bold text-orange-600 text-lg">$4,421.81</div>
                            <div class="text-xs text-black-600">Tax: $210.56</div>
                        </td>
                        <td class="px-4 py-4 text-sm">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800 border border-green-300">
                                ✓ Sent
                            </span>
                        </td>
                        <td class="px-4 py-4 text-sm">
                            <div class="flex space-x-2">
                                <a href="/invoicetest" class="text-orange-600 hover:text-orange-700 font-medium" title="View Invoice">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                    </svg>
                                </a>
                                <button class="text-green-600 hover:text-green-700" title="Download PDF">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                                    </svg>
                                </button>
                                <button class="text-blue-600 hover:text-blue-700" title="Send via Email">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                                    </svg>
                                </button>
                            </div>
                        </td>
                    </tr>

                    <!-- Invoice Row 2 - SENT (Normal styling) -->
                    <tr class="hover:bg-orange-100 transition-colors duration-150">
                        <td class="px-4 py-4 text-sm">
                            <div class="font-bold text-black-900">INT-0015</div>
                            <div class="text-xs text-black-600">11/30/2025</div>
                        </td>
                        <td class="px-4 py-4 text-sm">
                            <div class="font-medium text-black-900">Store #005</div>
                            <div class="text-xs text-black-600">Mall Rd</div>
                        </td>
                        <td class="px-4 py-4 text-sm text-black-900">
                            <div>Nov 1-30, 2025</div>
                            <div class="text-xs text-black-600">30 days</div>
                        </td>
                        <td class="px-4 py-4 text-sm">
                            <div class="space-y-1 text-xs">
                                <div class="flex items-center gap-2">
                                    <span class="text-blue-600">⏱</span>
                                    <span>Labor: $571.25</span>
                                </div>
                                <div class="flex items-center gap-2">
                                    <span class="text-green-600">📋</span>
                                    <span>Materials: $235.00</span>
                                </div>
                                <div class="flex items-center gap-2">
                                    <span class="text-purple-600">🛒</span>
                                    <span>Equipment: $3,405.00</span>
                                </div>
                            </div>
                        </td>
                        <td class="px-4 py-4 text-sm">
                            <div class="font-bold text-orange-600 text-lg">$4,421.81</div>
                            <div class="text-xs text-black-600">Tax: $210.56</div>
                        </td>
                        <td class="px-4 py-4 text-sm">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                Sent
                            </span>
                        </td>
                        <td class="px-4 py-4 text-sm">
                            <div class="flex space-x-2">
                                <a href="/invoicetest" class="text-orange-600 hover:text-orange-700 font-medium">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                    </svg>
                                </a>
                                <button class="text-green-600 hover:text-green-700">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                                    </svg>
                                </button>
                                <button class="text-blue-600 hover:text-blue-700">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                                    </svg>
                                </button>
                            </div>
                        </td>
                    </tr>

                    <!-- Invoice Row 2 - SENT (Normal styling) -->
                    <tr class="hover:bg-orange-100 transition-colors duration-150">
                        <td class="px-4 py-4 text-sm">
                            <div class="font-bold text-black-900">INT-0014</div>
                            <div class="text-xs text-black-600">10/31/2025</div>
                        </td>
                        <td class="px-4 py-4 text-sm">
                            <div class="font-medium text-black-900">Store #003</div>
                            <div class="text-xs text-black-600">Downtown</div>
                        </td>
                        <td class="px-4 py-4 text-sm text-black-900">
                            <div>Oct 1-31, 2025</div>
                            <div class="text-xs text-black-600">31 days</div>
                        </td>
                        <td class="px-4 py-4 text-sm">
                            <div class="space-y-1 text-xs">
                                <div class="flex items-center gap-2">
                                    <span class="text-blue-600">⏱</span>
                                    <span>Labor: $425.00</span>
                                </div>
                                <div class="flex items-center gap-2">
                                    <span class="text-green-600">📋</span>
                                    <span>Materials: $185.50</span>
                                </div>
                                <div class="flex items-center gap-2">
                                    <span class="text-purple-600">🛒</span>
                                    <span>Equipment: $1,250.00</span>
                                </div>
                            </div>
                        </td>
                        <td class="px-4 py-4 text-sm">
                            <div class="font-bold text-orange-600 text-lg">$1,953.03</div>
                            <div class="text-xs text-black-600">Tax: $92.53</div>
                        </td>
                        <td class="px-4 py-4 text-sm">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800 border border-green-300">
                                ✓ Sent
                            </span>
                        </td>
                        <td class="px-4 py-4 text-sm">
                            <div class="flex space-x-2">
                                <a href="/invoicetest" class="text-orange-600 hover:text-orange-700 font-medium" title="View Invoice">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                    </svg>
                                </a>
                                <button class="text-green-600 hover:text-green-700" title="Download PDF">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                                    </svg>
                                </button>
                                <button class="text-blue-600 hover:text-blue-700" title="Send via Email">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                                    </svg>
                                </button>
                            </div>
                        </td>
                    </tr>

                    <!-- Invoice Row 3 - DRAFT (Yellow highlighting - NOT SENT) -->
                    <tr class="bg-yellow-50 border-l-4 border-yellow-500 hover:bg-yellow-100 transition-colors duration-150">
                        <td class="px-4 py-4 text-sm">
                            <div class="font-bold text-black-900 flex items-center gap-2">
                                INT-0013
                                <span class="inline-flex items-center px-1.5 py-0.5 rounded text-xs font-medium bg-yellow-200 text-yellow-800">
                                    ⚠ Unsent
                                </span>
                            </div>
                            <div class="text-xs text-black-600">09/30/2025</div>
                        </td>
                        <td class="px-4 py-4 text-sm">
                            <div class="font-medium text-black-900">Store #007</div>
                            <div class="text-xs text-black-600">Airport</div>
                        </td>
                        <td class="px-4 py-4 text-sm text-black-900">
                            <div>Sep 1-30, 2025</div>
                            <div class="text-xs text-black-600">30 days</div>
                        </td>
                        <td class="px-4 py-4 text-sm">
                            <div class="space-y-1 text-xs">
                                <div class="flex items-center gap-2">
                                    <span class="text-blue-600">⏱</span>
                                    <span>Labor: $650.00</span>
                                </div>
                                <div class="flex items-center gap-2">
                                    <span class="text-green-600">📋</span>
                                    <span>Materials: $340.75</span>
                                </div>
                                <div class="flex items-center gap-2">
                                    <span class="text-purple-600">🛒</span>
                                    <span>Equipment: $0.00</span>
                                </div>
                            </div>
                        </td>
                        <td class="px-4 py-4 text-sm">
                            <div class="font-bold text-orange-600 text-lg">$1,040.29</div>
                            <div class="text-xs text-black-600">Tax: $49.54</div>
                        </td>
                        <td class="px-4 py-4 text-sm">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800 border-2 border-yellow-400 shadow-sm">
                                ⏳ Draft
                            </span>
                        </td>
                        <td class="px-4 py-4 text-sm">
                            <div class="flex space-x-2">
                                <a href="/invoicetest" class="text-orange-600 hover:text-orange-700 font-medium" title="View Invoice">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                    </svg>
                                </a>
                                <button class="text-amber-600 hover:text-amber-700" title="Generate & Send">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8" />
                                    </svg>
                                </button>
                            </div>
                        </td>
                    </tr>>
                    <tr class="hover:bg-orange-100 transition-colors duration-150">
                        <td class="px-4 py-4 text-sm">
                            <div class="font-bold text-black-900">INT-0014</div>
                            <div class="text-xs text-black-600">10/31/2025</div>
                        </td>
                        <td class="px-4 py-4 text-sm">
                            <div class="font-medium text-black-900">Store #003</div>
                            <div class="text-xs text-black-600">Downtown</div>
                        </td>
                        <td class="px-4 py-4 text-sm text-black-900">
                            <div>Oct 1-31, 2025</div>
                            <div class="text-xs text-black-600">31 days</div>
                        </td>
                        <td class="px-4 py-4 text-sm">
                            <div class="space-y-1 text-xs">
                                <div class="flex items-center gap-2">
                                    <span class="text-blue-600">⏱</span>
                                    <span>Labor: $425.00</span>
                                </div>
                                <div class="flex items-center gap-2">
                                    <span class="text-green-600">📋</span>
                                    <span>Materials: $185.50</span>
                                </div>
                                <div class="flex items-center gap-2">
                                    <span class="text-purple-600">🛒</span>
                                    <span>Equipment: $1,250.00</span>
                                </div>
                            </div>
                        </td>
                        <td class="px-4 py-4 text-sm">
                            <div class="font-bold text-orange-600 text-lg">$1,953.03</div>
                            <div class="text-xs text-black-600">Tax: $92.53</div>
                        </td>
                        <td class="px-4 py-4 text-sm">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                Sent
                            </span>
                        </td>
                        <td class="px-4 py-4 text-sm">
                            <div class="flex space-x-2">
                                <a href="/invoicetest" class="text-orange-600 hover:text-orange-700 font-medium">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                    </svg>
                                </a>
                                <button class="text-green-600 hover:text-green-700">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                                    </svg>
                                </button>
                                <button class="text-blue-600 hover:text-blue-700">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                                    </svg>
                                </button>
                            </div>
                        </td>
                    </tr>

                    <!-- Invoice Row 3 -->
                    <tr class="hover:bg-orange-100 transition-colors duration-150">
                        <td class="px-4 py-4 text-sm">
                            <div class="font-bold text-black-900">INT-0013</div>
                            <div class="text-xs text-black-600">09/30/2025</div>
                        </td>
                        <td class="px-4 py-4 text-sm">
                            <div class="font-medium text-black-900">Store #007</div>
                            <div class="text-xs text-black-600">Airport</div>
                        </td>
                        <td class="px-4 py-4 text-sm text-black-900">
                            <div>Sep 1-30, 2025</div>
                            <div class="text-xs text-black-600">30 days</div>
                        </td>
                        <td class="px-4 py-4 text-sm">
                            <div class="space-y-1 text-xs">
                                <div class="flex items-center gap-2">
                                    <span class="text-blue-600">⏱</span>
                                    <span>Labor: $650.00</span>
                                </div>
                                <div class="flex items-center gap-2">
                                    <span class="text-green-600">📋</span>
                                    <span>Materials: $340.75</span>
                                </div>
                                <div class="flex items-center gap-2">
                                    <span class="text-purple-600">🛒</span>
                                    <span>Equipment: $0.00</span>
                                </div>
                            </div>
                        </td>
                        <td class="px-4 py-4 text-sm">
                            <div class="font-bold text-orange-600 text-lg">$1,040.29</div>
                            <div class="text-xs text-black-600">Tax: $49.54</div>
                        </td>
                        <td class="px-4 py-4 text-sm">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                                Draft
                            </span>
                        </td>
                        <td class="px-4 py-4 text-sm">
                            <div class="flex space-x-2">
                                <a href="/invoicetest" class="text-orange-600 hover:text-orange-700 font-medium">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                    </svg>
                                </a>
                                <button class="text-green-600 hover:text-green-700">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                                    </svg>
                                </button>
                                <button class="text-blue-600 hover:text-blue-700">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                                    </svg>
                                </button>
                            </div>
                        </td>
                    </tr>
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div class="px-4 py-3 border-t border-orange-200 sm:px-6">
                <div class="flex justify-between items-center">
                    <div class="text-sm text-black-700">
                        Showing 1 to 3 of 24 results
                    </div>
                    <div class="flex space-x-1">
                        <button class="px-3 py-1 border border-orange-300 rounded text-sm text-black-700 hover:bg-orange-100">Previous</button>
                        <button class="px-3 py-1 bg-orange-600 text-white rounded text-sm">1</button>
                        <button class="px-3 py-1 border border-orange-300 rounded text-sm text-black-700 hover:bg-orange-100">2</button>
                        <button class="px-3 py-1 border border-orange-300 rounded text-sm text-black-700 hover:bg-orange-100">3</button>
                        <button class="px-3 py-1 border border-orange-300 rounded text-sm text-black-700 hover:bg-orange-100">Next</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
