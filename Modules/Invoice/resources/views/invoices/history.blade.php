@extends('layouts.app')

@section('title', 'Invoice History')

@section('content')
<div class="max-w-full mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <!-- Header Section -->
    <div class="sm:flex sm:items-center sm:justify-between mb-6">
        <div>
            <h1 class="text-3xl font-bold text-black-900">Invoice History</h1>
            <p class="mt-2 text-sm text-black-600">View and manage all generated invoices</p>
        </div>
        <div class="mt-4 sm:mt-0 flex space-x-3">
            <a href="{{ route('invoice.generate') }}"
               class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-lg text-white bg-orange-600 hover:bg-orange-700">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                </svg>
                Generate New Invoice
            </a>
        </div>
    </div>

    <!-- Quick Stats -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-8">
        <div class="bg-orange-50 rounded-lg shadow-sm p-6 border border-orange-200">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <svg class="w-8 h-8 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-black-700">Total Invoices</p>
                    <p class="text-2xl font-bold text-orange-600">0</p>
                </div>
            </div>
        </div>

        <div class="bg-green-50 rounded-lg shadow-sm p-6 border border-green-200">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <svg class="w-8 h-8 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-black-700">This Month</p>
                    <p class="text-2xl font-bold text-green-600">0</p>
                </div>
            </div>
        </div>

        <div class="bg-blue-50 rounded-lg shadow-sm p-6 border border-blue-200">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <svg class="w-8 h-8 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-black-700">Total Amount</p>
                    <p class="text-2xl font-bold text-blue-600">$0</p>
                </div>
            </div>
        </div>

        <div class="bg-purple-50 rounded-lg shadow-sm p-6 border border-purple-200">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <svg class="w-8 h-8 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                    </svg>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-black-700">Active Stores</p>
                    <p class="text-2xl font-bold text-purple-600">{{ $stores->count() }}</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Invoices Table -->
    <div class="bg-orange-50 shadow-sm ring-1 ring-black ring-opacity-5 rounded-lg overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-orange-200">
                <thead class="bg-orange-100">
                    <tr>
                        <th class="px-4 py-3.5 text-left text-xs font-medium text-black-700 uppercase">Invoice #</th>
                        <th class="px-4 py-3.5 text-left text-xs font-medium text-black-700 uppercase">Store</th>
                        <th class="px-4 py-3.5 text-left text-xs font-medium text-black-700 uppercase">Period</th>
                        <th class="px-4 py-3.5 text-left text-xs font-medium text-black-700 uppercase">Total Amount</th>
                        <th class="px-4 py-3.5 text-left text-xs font-medium text-black-700 uppercase">Status</th>
                        <th class="px-4 py-3.5 text-left text-xs font-medium text-black-700 uppercase">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-orange-200 bg-orange-50">
                    <tr>
                        <td colspan="6" class="px-6 py-12 text-center text-gray-500">
                            <svg class="w-16 h-16 mx-auto mb-4 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                            </svg>
                            <p class="text-lg font-medium">No invoices generated yet</p>
                            <p class="text-sm mt-2">Start by generating your first invoice</p>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
