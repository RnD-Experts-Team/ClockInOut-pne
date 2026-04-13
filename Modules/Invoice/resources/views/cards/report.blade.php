@extends('layouts.app')

@section('title', 'Invoice Card Status Report')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">

    {{-- Header --}}
    <div class="sm:flex sm:items-center sm:justify-between mb-6">
        <div>
            <h1 class="text-3xl font-bold text-gray-900">Invoice Card Status Report</h1>
            <p class="mt-2 text-sm text-gray-600">Finance overview of all invoice cards by month</p>
        </div>
    </div>

    {{-- Filters --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-5 mb-6">
        <form method="GET" action="{{ route('invoice.cards-report.index') }}" class="flex flex-wrap gap-4 items-end">
            {{-- Month/Year --}}
            <div class="flex-1 min-w-[160px]">
                <label class="block text-xs font-semibold text-gray-600 mb-1">Month</label>
                <input type="month" name="month" value="{{ $month }}"
                       class="w-full rounded-lg border-gray-300 shadow-sm text-sm focus:ring-blue-500 focus:border-blue-500">
            </div>
            {{-- Store --}}
            <div class="flex-1 min-w-[160px]">
                <label class="block text-xs font-semibold text-gray-600 mb-1">Store</label>
                <select name="store" class="w-full rounded-lg border-gray-300 shadow-sm text-sm focus:ring-blue-500 focus:border-blue-500">
                    <option value="">All Stores</option>
                    @foreach($stores as $store)
                        <option value="{{ $store->id }}" @selected($storeId == $store->id)>{{ $store->name }}</option>
                    @endforeach
                </select>
            </div>
            {{-- Technician --}}
            <div class="flex-1 min-w-[160px]">
                <label class="block text-xs font-semibold text-gray-600 mb-1">Technician</label>
                <select name="user" class="w-full rounded-lg border-gray-300 shadow-sm text-sm focus:ring-blue-500 focus:border-blue-500">
                    <option value="">All Technicians</option>
                    @foreach($users as $user)
                        <option value="{{ $user->id }}" @selected($userId == $user->id)>{{ $user->name }}</option>
                    @endforeach
                </select>
            </div>
            {{-- Status --}}
            <div class="flex-1 min-w-[140px]">
                <label class="block text-xs font-semibold text-gray-600 mb-1">Status</label>
                <select name="status" class="w-full rounded-lg border-gray-300 shadow-sm text-sm focus:ring-blue-500 focus:border-blue-500">
                    <option value="all">All</option>
                    <option value="completed" @selected($status === 'completed')>Completed</option>
                    <option value="in_progress" @selected($status === 'in_progress')>In Progress</option>
                    <option value="not_done" @selected($status === 'not_done')>Not Done</option>
                </select>
            </div>
            {{-- Invoice Status --}}
            <div class="flex-1 min-w-[160px]">
                <label class="block text-xs font-semibold text-gray-600 mb-1">Invoice Status</label>
                <select name="invoice_status" class="w-full rounded-lg border-gray-300 shadow-sm text-sm focus:ring-blue-500 focus:border-blue-500">
                    <option value="all">All</option>
                    <option value="Invoiced" @selected($invoiceStatus === 'Invoiced')>Invoiced</option>
                    <option value="Pending Invoice" @selected($invoiceStatus === 'Pending Invoice')>Pending Invoice</option>
                </select>
            </div>
            <div class="flex gap-2">
                <button type="submit"
                        class="px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded-lg hover:bg-blue-700 transition-colors">
                    Apply
                </button>
                <a href="{{ route('invoice.cards-report.index') }}"
                   class="px-4 py-2 bg-gray-100 text-gray-700 text-sm font-medium rounded-lg hover:bg-gray-200 transition-colors">
                    Reset
                </a>
            </div>
        </form>
    </div>

    {{-- Summary Cards --}}
    <div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-7 gap-3 mb-6">
        <div class="bg-gradient-to-br from-gray-50 to-gray-100 rounded-xl border border-gray-200 p-4">
            <p class="text-xs font-semibold text-gray-500 uppercase">Total Cards</p>
            <p class="text-2xl font-bold text-gray-900 mt-0.5">{{ $summary['total'] }}</p>
        </div>
        <div class="bg-gradient-to-br from-green-50 to-green-100 rounded-xl border border-green-200 p-4">
            <p class="text-xs font-semibold text-green-600 uppercase">Completed</p>
            <p class="text-2xl font-bold text-green-900 mt-0.5">{{ $summary['completed_count'] }}</p>
        </div>
        <div class="bg-gradient-to-br from-yellow-50 to-yellow-100 rounded-xl border border-yellow-200 p-4">
            <p class="text-xs font-semibold text-yellow-700 uppercase">Open</p>
            <p class="text-2xl font-bold text-yellow-900 mt-0.5">{{ $summary['open_count'] }}</p>
        </div>
        <div class="bg-gradient-to-br from-blue-50 to-blue-100 rounded-xl border border-blue-200 p-4">
            <p class="text-xs font-semibold text-blue-600 uppercase">Completed Cost</p>
            <p class="text-xl font-bold text-blue-900 mt-0.5">${{ number_format($summary['total_cost'], 0) }}</p>
        </div>
        <div class="bg-gradient-to-br from-orange-50 to-orange-100 rounded-xl border border-orange-200 p-4">
            <p class="text-xs font-semibold text-orange-700 uppercase">Outstanding</p>
            <p class="text-xl font-bold text-orange-900 mt-0.5">${{ number_format($summary['outstanding_cost'], 0) }}</p>
        </div>
        <div class="bg-gradient-to-br from-purple-50 to-purple-100 rounded-xl border border-purple-200 p-4">
            <p class="text-xs font-semibold text-purple-600 uppercase">Invoiced</p>
            <p class="text-xl font-bold text-purple-900 mt-0.5">${{ number_format($summary['invoiced_total'], 0) }}</p>
        </div>
        <div class="bg-gradient-to-br from-red-50 to-red-100 rounded-xl border border-red-200 p-4">
            <p class="text-xs font-semibold text-red-600 uppercase">Pending Invoice</p>
            <p class="text-xl font-bold text-red-900 mt-0.5">${{ number_format($summary['pending_total'], 0) }}</p>
        </div>
    </div>

    {{-- Tabs --}}
    <div x-data="{ tab: 'completed' }">
        <div class="flex border-b border-gray-200 mb-4">
            <button @click="tab = 'completed'"
                    :class="tab === 'completed' ? 'border-blue-600 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700'"
                    class="px-6 py-3 text-sm font-medium border-b-2 transition-colors">
                Completed
                <span class="ml-2 inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-700">{{ $completed->count() }}</span>
            </button>
            <button @click="tab = 'open'"
                    :class="tab === 'open' ? 'border-blue-600 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700'"
                    class="px-6 py-3 text-sm font-medium border-b-2 transition-colors">
                Open
                <span class="ml-2 inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-700">{{ $open->count() }}</span>
            </button>
        </div>

        {{-- Tab 1: Completed --}}
        <div x-show="tab === 'completed'">
            <div class="flex justify-end mb-3">
                <a href="{{ route('invoice.cards-report.export.completed', request()->query()) }}"
                   class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-blue-600 text-white text-xs font-medium rounded-lg hover:bg-blue-700 transition-colors">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                    Export CSV
                </a>
            </div>
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase">#</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Technician</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Store</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Start Date</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase">End Date</th>
                                <th class="px-4 py-3 text-center text-xs font-semibold text-gray-600 uppercase">Status</th>
                                <th class="px-4 py-3 text-center text-xs font-semibold text-gray-600 uppercase">⚠</th>
                                <th class="px-4 py-3 text-right text-xs font-semibold text-gray-600 uppercase">Labor</th>
                                <th class="px-4 py-3 text-right text-xs font-semibold text-gray-600 uppercase">Materials</th>
                                <th class="px-4 py-3 text-right text-xs font-semibold text-gray-600 uppercase">Mileage</th>
                                <th class="px-4 py-3 text-right text-xs font-semibold text-gray-600 uppercase">Total</th>
                                <th class="px-4 py-3 text-center text-xs font-semibold text-gray-600 uppercase">Invoice</th>
                                <th class="px-4 py-3 text-center text-xs font-semibold text-gray-600 uppercase">View</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @forelse($completed as $card)
                                <tr class="{{ $card->cross_month ? 'bg-yellow-50 hover:bg-yellow-100' : 'hover:bg-gray-50' }} transition-colors">
                                    <td class="px-4 py-3 text-sm font-medium text-gray-900">{{ $card->id }}</td>
                                    <td class="px-4 py-3 text-sm text-gray-700">{{ $card->user?->name ?? '—' }}</td>
                                    <td class="px-4 py-3 text-sm text-gray-700">{{ $card->store?->name ?? '—' }}</td>
                                    <td class="px-4 py-3 text-sm text-gray-600">{{ $card->start_time?->format('M d, Y') }}</td>
                                    <td class="px-4 py-3 text-sm text-gray-600">{{ $card->end_time?->format('M d, Y') ?? '—' }}</td>
                                    <td class="px-4 py-3 text-center">
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">Completed</span>
                                    </td>
                                    <td class="px-4 py-3 text-center">
                                        @if($card->cross_month)
                                            <span title="Card spans across months" class="text-yellow-600 text-base">⚠</span>
                                        @endif
                                    </td>
                                    <td class="px-4 py-3 text-right text-sm text-gray-700">${{ number_format((float)$card->labor_cost, 2) }}</td>
                                    <td class="px-4 py-3 text-right text-sm text-gray-700">${{ number_format((float)$card->materials_cost, 2) }}</td>
                                    <td class="px-4 py-3 text-right text-sm text-gray-700">${{ number_format((float)$card->mileage_payment, 2) }}</td>
                                    <td class="px-4 py-3 text-right text-sm font-semibold text-gray-900">${{ number_format((float)$card->total_cost, 2) }}</td>
                                    <td class="px-4 py-3 text-center">
                                        @if($card->invoice_status === 'Invoiced')
                                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-purple-100 text-purple-800">Invoiced</span>
                                        @else
                                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">Needs Invoice</span>
                                        @endif
                                    </td>
                                    <td class="px-4 py-3 text-center">
                                        <a href="{{ route('invoice.cards.show', $card->id) }}"
                                           class="text-blue-600 hover:text-blue-800 text-xs font-medium">View</a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="13" class="px-4 py-10 text-center text-gray-400 text-sm">No completed cards found for this period.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        {{-- Tab 2: Open Cards --}}
        <div x-show="tab === 'open'">
            <div class="flex justify-end mb-3">
                <a href="{{ route('invoice.cards-report.export.open', request()->query()) }}"
                   class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-blue-600 text-white text-xs font-medium rounded-lg hover:bg-blue-700 transition-colors">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                    Export CSV
                </a>
            </div>
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase">#</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Technician</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Store</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Start Date</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase">End Date</th>
                                <th class="px-4 py-3 text-center text-xs font-semibold text-gray-600 uppercase">Status</th>
                                <th class="px-4 py-3 text-center text-xs font-semibold text-gray-600 uppercase">⚠</th>
                                <th class="px-4 py-3 text-center text-xs font-semibold text-gray-600 uppercase">Days Open</th>
                                <th class="px-4 py-3 text-right text-xs font-semibold text-gray-600 uppercase">Labor</th>
                                <th class="px-4 py-3 text-right text-xs font-semibold text-gray-600 uppercase">Materials</th>
                                <th class="px-4 py-3 text-right text-xs font-semibold text-gray-600 uppercase">Mileage</th>
                                <th class="px-4 py-3 text-right text-xs font-semibold text-gray-600 uppercase">Total</th>
                                <th class="px-4 py-3 text-center text-xs font-semibold text-gray-600 uppercase">View</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @forelse($open as $card)
                                <tr class="{{ $card->cross_month ? 'bg-yellow-50 hover:bg-yellow-100' : 'hover:bg-gray-50' }} transition-colors">
                                    <td class="px-4 py-3 text-sm font-medium text-gray-900">{{ $card->id }}</td>
                                    <td class="px-4 py-3 text-sm text-gray-700">{{ $card->user?->name ?? '—' }}</td>
                                    <td class="px-4 py-3 text-sm text-gray-700">{{ $card->store?->name ?? '—' }}</td>
                                    <td class="px-4 py-3 text-sm text-gray-600">{{ $card->start_time?->format('M d, Y') }}</td>
                                    <td class="px-4 py-3 text-sm text-gray-600">
                                        {{ $card->end_time?->format('M d, Y') ?? '—' }}
                                    </td>
                                    <td class="px-4 py-3 text-center">
                                        @php
                                            $statusColors = ['in_progress' => 'bg-blue-100 text-blue-800', 'not_done' => 'bg-red-100 text-red-800'];
                                        @endphp
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium {{ $statusColors[$card->status] ?? 'bg-gray-100 text-gray-600' }}">
                                            {{ ucfirst(str_replace('_', ' ', $card->status)) }}
                                        </span>
                                    </td>
                                    <td class="px-4 py-3 text-center">
                                        @if($card->cross_month)
                                            <span title="Card spans across months" class="text-yellow-600 text-base">⚠</span>
                                        @endif
                                    </td>
                                    <td class="px-4 py-3 text-center">
                                        @if($card->days_open !== null)
                                            <span class="{{ $card->days_open > 7 ? 'text-red-600 font-semibold' : 'text-gray-700' }}">
                                                {{ $card->days_open }}d
                                            </span>
                                        @else
                                            —
                                        @endif
                                    </td>
                                    <td class="px-4 py-3 text-right text-sm text-gray-700">${{ number_format((float)$card->labor_cost, 2) }}</td>
                                    <td class="px-4 py-3 text-right text-sm text-gray-700">${{ number_format((float)$card->materials_cost, 2) }}</td>
                                    <td class="px-4 py-3 text-right text-sm text-gray-700">${{ number_format((float)$card->mileage_payment, 2) }}</td>
                                    <td class="px-4 py-3 text-right text-sm font-semibold text-gray-900">${{ number_format((float)$card->total_cost, 2) }}</td>
                                    <td class="px-4 py-3 text-center">
                                        <a href="{{ route('invoice.cards.show', $card->id) }}"
                                           class="text-blue-600 hover:text-blue-800 text-xs font-medium">View</a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="13" class="px-4 py-10 text-center text-gray-400 text-sm">No open cards found for this period.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

</div>

@push('scripts')
<script>
    // Auto-open form if validation errors on manual fix
    @if($errors->has('equipment_id') || $errors->has('description_of_issue'))
        document.addEventListener('DOMContentLoaded', function () {
            const form = document.getElementById('manualFixForm');
            if (form) form.classList.remove('hidden');
        });
    @endif
</script>
@endpush

@endsection
