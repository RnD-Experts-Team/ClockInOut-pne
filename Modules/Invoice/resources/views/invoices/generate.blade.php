@extends('layouts.app')

@section('title', 'Generate Invoice')

@section('content')
<div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <!-- Header -->
    <div class="mb-6">
        <a href="{{ route('invoice.index') }}" class="inline-flex items-center text-orange-600 hover:text-orange-700 mb-4">
            <svg class="h-5 w-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
            </svg>
            Back to Invoices
        </a>
        <h1 class="text-3xl font-bold text-black-900">Generate New Invoice</h1>
        <p class="mt-2 text-sm text-black-600">{{ __('invoice.select_store_and_date') }}</p>
    </div>

    <!-- Form -->
    <div class="bg-white shadow-lg rounded-xl overflow-hidden">
        <div class="px-6 py-4 border-b border-orange-200 bg-orange-50">
            <h2 class="text-xl font-semibold text-black-900">Invoice Details</h2>
        </div>

        <form action="{{ route('invoice.preview') }}" method="GET" class="p-6 space-y-6">
            <!-- Store Selection -->
            <div>
                <label for="store_id" class="block text-sm font-semibold text-black-700 mb-2">
                    Select Store <span class="text-red-500">*</span>
                </label>
                <select name="store_id" id="store_id" required
                        class="block w-full rounded-lg border-orange-300 shadow-sm focus:border-orange-500 focus:ring-2 focus:ring-orange-500/20 text-base py-3 px-4">
                    <option value="">Choose a store...</option>
                    @foreach($stores as $store)
                        <option value="{{ $store->id }}" {{ request('store_id') == $store->id ? 'selected' : '' }}>
                            {{ $store->store_number }} - {{ $store->name }}
                        </option>
                    @endforeach
                </select>
                <p class="mt-1 text-xs text-black-500">Select the store for which you want to generate an invoice</p>
            </div>

            <!-- Date Range -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label for="date_from" class="block text-sm font-semibold text-black-700 mb-2">
                        From Date <span class="text-red-500">*</span>
                    </label>
                    <input type="date" name="date_from" id="date_from" required
                           value="{{ request('date_from', now()->startOfMonth()->format('Y-m-d')) }}"
                           class="block w-full rounded-lg border-orange-300 shadow-sm focus:border-orange-500 focus:ring-2 focus:ring-orange-500/20 text-base py-3 px-4">
                </div>

                <div>
                    <label for="date_to" class="block text-sm font-semibold text-black-700 mb-2">
                        To Date <span class="text-red-500">*</span>
                    </label>
                    <input type="date" name="date_to" id="date_to" required
                           value="{{ request('date_to', now()->format('Y-m-d')) }}"
                           class="block w-full rounded-lg border-orange-300 shadow-sm focus:border-orange-500 focus:ring-2 focus:ring-orange-500/20 text-base py-3 px-4">
                </div>
            </div>

            <!-- Quick Date Presets -->
            <div class="bg-orange-50 rounded-lg p-4 border border-orange-200">
                <p class="text-sm font-semibold text-black-700 mb-3">Quick Date Presets:</p>
                <div class="flex flex-wrap gap-2">
                    <button type="button" onclick="setDateRange('this_month')" 
                            class="px-3 py-1.5 text-sm bg-white border border-orange-300 rounded-lg hover:bg-orange-100 transition-colors">
                        This Month
                    </button>
                    <button type="button" onclick="setDateRange('last_month')" 
                            class="px-3 py-1.5 text-sm bg-white border border-orange-300 rounded-lg hover:bg-orange-100 transition-colors">
                        Last Month
                    </button>
                    <button type="button" onclick="setDateRange('last_30_days')" 
                            class="px-3 py-1.5 text-sm bg-white border border-orange-300 rounded-lg hover:bg-orange-100 transition-colors">
                        Last 30 Days
                    </button>
                    <button type="button" onclick="setDateRange('last_90_days')" 
                            class="px-3 py-1.5 text-sm bg-white border border-orange-300 rounded-lg hover:bg-orange-100 transition-colors">
                        Last 90 Days
                    </button>
                </div>
            </div>

            <!-- Info Box -->
            <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                <div class="flex items-start">
                    <svg class="h-5 w-5 text-blue-600 mt-0.5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    <div class="text-sm text-blue-800">
                        <p class="font-semibold mb-1">What will be included:</p>
                        <ul class="list-disc list-inside space-y-1 ml-2">
                            <li>All completed invoice cards for the selected store and date range</li>
                            <li>Labor costs calculated from technician work hours</li>
                            <li>Materials purchased by technicians with receipts</li>
                            <li>Admin equipment purchases for the store</li>
                            <li>Mileage tracking and payments</li>
                        </ul>
                    </div>
                </div>
            </div>

            <!-- Action Buttons -->
            <div class="flex gap-3 pt-4">
                <a href="{{ route('invoice.index') }}" 
                   class="flex-1 inline-flex justify-center items-center px-6 py-3 border-2 border-orange-200 rounded-lg text-base font-medium text-orange-700 bg-white hover:bg-orange-50 transition-all">
                    Cancel
                </a>
                <button type="submit" 
                        class="flex-1 inline-flex justify-center items-center px-6 py-3 border border-transparent rounded-lg text-base font-medium text-white bg-orange-600 hover:bg-orange-700 shadow-lg hover:shadow-xl transition-all">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                    </svg>
                    Preview Invoice
                </button>
            </div>
        </form>
    </div>
</div>

<script>
function setDateRange(preset) {
    const dateFrom = document.getElementById('date_from');
    const dateTo = document.getElementById('date_to');
    const today = new Date();
    
    switch(preset) {
        case 'this_month':
            dateFrom.value = new Date(today.getFullYear(), today.getMonth(), 1).toISOString().split('T')[0];
            dateTo.value = today.toISOString().split('T')[0];
            break;
        case 'last_month':
            const lastMonth = new Date(today.getFullYear(), today.getMonth() - 1, 1);
            const lastMonthEnd = new Date(today.getFullYear(), today.getMonth(), 0);
            dateFrom.value = lastMonth.toISOString().split('T')[0];
            dateTo.value = lastMonthEnd.toISOString().split('T')[0];
            break;
        case 'last_30_days':
            const thirtyDaysAgo = new Date(today);
            thirtyDaysAgo.setDate(today.getDate() - 30);
            dateFrom.value = thirtyDaysAgo.toISOString().split('T')[0];
            dateTo.value = today.toISOString().split('T')[0];
            break;
        case 'last_90_days':
            const ninetyDaysAgo = new Date(today);
            ninetyDaysAgo.setDate(today.getDate() - 90);
            dateFrom.value = ninetyDaysAgo.toISOString().split('T')[0];
            dateTo.value = today.toISOString().split('T')[0];
            break;
    }
}
</script>

@if($errors->any())
<div class="fixed bottom-4 right-4 bg-red-500 text-white px-6 py-3 rounded-lg shadow-lg z-50">
    {{ $errors->first() }}
</div>
@endif
@endsection
