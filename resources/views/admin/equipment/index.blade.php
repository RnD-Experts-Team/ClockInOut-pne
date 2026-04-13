@extends('layouts.app')

@section('title', 'Equipment Tracker')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">

    {{-- Header --}}
    <div class="sm:flex sm:items-center sm:justify-between mb-6">
        <div>
            <h1 class="text-3xl font-bold text-gray-900">Equipment Tracker</h1>
            <p class="mt-2 text-sm text-gray-600">Track repair history, costs, and repair time per piece of equipment</p>
        </div>
        <div class="mt-4 sm:mt-0 flex flex-wrap gap-2">
            <a href="{{ route('admin.equipment.export', request()->query()) }}"
               class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-lg text-white bg-gradient-to-r from-blue-500 to-blue-600 hover:from-blue-600 hover:to-blue-700 transition-all shadow-sm">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                </svg>
                Export CSV
            </a>
            <a href="{{ route('admin.equipment.create') }}"
               class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-lg text-white bg-gradient-to-r from-green-500 to-green-600 hover:from-green-600 hover:to-green-700 transition-all shadow-sm">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                </svg>
                Add Equipment
            </a>
        </div>
    </div>

    {{-- Flash Messages --}}
    @if(session('success'))
        <div class="mb-4 p-4 bg-green-100 border border-green-300 text-green-800 rounded-lg">{{ session('success') }}</div>
    @endif

    {{-- Filters --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-5 mb-6">
        <form method="GET" action="{{ route('admin.equipment.index') }}" class="flex flex-wrap gap-4 items-end">
            {{-- Store --}}
            <div class="flex-1 min-w-[180px]">
                <label class="block text-xs font-semibold text-gray-600 mb-1">Store</label>
                <select name="store" class="w-full rounded-lg border-gray-300 shadow-sm text-sm focus:ring-blue-500 focus:border-blue-500">
                    <option value="">All Stores</option>
                    <option value="global" @selected($storeId === 'global')>Global</option>
                    @foreach($stores as $store)
                        <option value="{{ $store->id }}" @selected((string)$storeId === (string)$store->id)>
                            {{ $store->name }}
                        </option>
                    @endforeach
                </select>
            </div>
            {{-- Type --}}
            <div class="flex-1 min-w-[160px]">
                <label class="block text-xs font-semibold text-gray-600 mb-1">Type</label>
                <select name="type" class="w-full rounded-lg border-gray-300 shadow-sm text-sm focus:ring-blue-500 focus:border-blue-500">
                    <option value="">All Types</option>
                    @foreach($types as $t)
                        <option value="{{ $t }}" @selected($type === $t)>{{ $t }}</option>
                    @endforeach
                </select>
            </div>
            {{-- Date Range --}}
            <div class="flex-1 min-w-[140px]">
                <label class="block text-xs font-semibold text-gray-600 mb-1">From Date</label>
                <input type="date" name="from_date" value="{{ $fromDate }}"
                       class="w-full rounded-lg border-gray-300 shadow-sm text-sm focus:ring-blue-500 focus:border-blue-500">
            </div>
            <div class="flex-1 min-w-[140px]">
                <label class="block text-xs font-semibold text-gray-600 mb-1">To Date</label>
                <input type="date" name="to_date" value="{{ $toDate }}"
                       class="w-full rounded-lg border-gray-300 shadow-sm text-sm focus:ring-blue-500 focus:border-blue-500">
            </div>
            {{-- Show Inactive --}}
            <div class="flex items-center gap-2">
                <input type="checkbox" name="show_inactive" id="show_inactive" value="1"
                       @checked($showAll)
                       class="rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                <label for="show_inactive" class="text-sm text-gray-700">Show Inactive</label>
            </div>
            <div class="flex gap-2">
                <button type="submit"
                        class="px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded-lg hover:bg-blue-700 transition-colors">
                    Filter
                </button>
                <a href="{{ route('admin.equipment.index') }}"
                   class="px-4 py-2 bg-gray-100 text-gray-700 text-sm font-medium rounded-lg hover:bg-gray-200 transition-colors">
                    Reset
                </a>
            </div>
        </form>
    </div>

    {{-- Summary Cards --}}
    <div class="grid grid-cols-2 md:grid-cols-5 gap-4 mb-6">
        <div class="bg-gradient-to-br from-gray-50 to-gray-100 rounded-xl shadow-sm border border-gray-200 p-5">
            <p class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Equipment</p>
            <p class="text-3xl font-bold text-gray-900 mt-1">{{ $totals['equipment_count'] }}</p>
        </div>
        <div class="bg-gradient-to-br from-blue-50 to-blue-100 rounded-xl shadow-sm border border-blue-200 p-5">
            <p class="text-xs font-semibold text-blue-600 uppercase tracking-wider">Total Fixes</p>
            <p class="text-3xl font-bold text-blue-900 mt-1">{{ $totals['fix_count'] }}</p>
        </div>
        <div class="bg-gradient-to-br from-purple-50 to-purple-100 rounded-xl shadow-sm border border-purple-200 p-5">
            <p class="text-xs font-semibold text-purple-600 uppercase tracking-wider">Labor Cost</p>
            <p class="text-2xl font-bold text-purple-900 mt-1">${{ number_format($totals['labor_cost'], 2) }}</p>
        </div>
        <div class="bg-gradient-to-br from-yellow-50 to-yellow-100 rounded-xl shadow-sm border border-yellow-200 p-5">
            <p class="text-xs font-semibold text-yellow-700 uppercase tracking-wider">Purchase Cost</p>
            <p class="text-2xl font-bold text-yellow-900 mt-1">${{ number_format($totals['purchase_cost'], 2) }}</p>
        </div>
        <div class="bg-gradient-to-br from-green-50 to-green-100 rounded-xl shadow-sm border border-green-200 p-5">
            <p class="text-xs font-semibold text-green-700 uppercase tracking-wider">Grand Total</p>
            <p class="text-2xl font-bold text-green-900 mt-1">${{ number_format($totals['total_cost'], 2) }}</p>
        </div>
    </div>

    {{-- Equipment Table --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Equipment</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Type</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Store</th>
                        <th class="px-4 py-3 text-center text-xs font-semibold text-gray-600 uppercase tracking-wider"># Fixes</th>
                        <th class="px-4 py-3 text-right text-xs font-semibold text-gray-600 uppercase tracking-wider">Repair Time</th>
                        <th class="px-4 py-3 text-right text-xs font-semibold text-gray-600 uppercase tracking-wider">Labor Cost</th>
                        <th class="px-4 py-3 text-right text-xs font-semibold text-gray-600 uppercase tracking-wider">Purchase Cost</th>
                        <th class="px-4 py-3 text-right text-xs font-semibold text-gray-600 uppercase tracking-wider">Total</th>
                        <th class="px-4 py-3 text-center text-xs font-semibold text-gray-600 uppercase tracking-wider">Status</th>
                        <th class="px-4 py-3 text-center text-xs font-semibold text-gray-600 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($equipment as $item)
                        <tr class="hover:bg-gray-50 transition-colors">
                            <td class="px-4 py-3">
                                <a href="{{ route('admin.equipment.show', $item->id) }}"
                                   class="text-blue-600 hover:text-blue-800 font-medium">
                                    {{ $item->name }}
                                </a>
                                @if($item->serial_number)
                                    <p class="text-xs text-gray-400">SN: {{ $item->serial_number }}</p>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-sm text-gray-700">{{ $item->type ?? '—' }}</td>
                            <td class="px-4 py-3 text-sm text-gray-700">
                                @if($item->store)
                                    {{ $item->store->name }}
                                @elseif($item->mr_store_names)
                                    <span class="text-gray-700">{{ $item->mr_store_names }}</span>
                                @else
                                    <span class="text-xs text-gray-400 italic">Global</span>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-center">
                                <span class="inline-flex items-center justify-center w-8 h-8 rounded-full bg-blue-100 text-blue-800 text-sm font-bold">
                                    {{ $item->fix_count }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-right text-sm text-gray-700">{{ $item->total_repair_hours }}h</td>
                            <td class="px-4 py-3 text-right text-sm text-gray-700">${{ number_format($item->total_labor_cost, 2) }}</td>
                            <td class="px-4 py-3 text-right text-sm text-gray-700">${{ number_format($item->total_purchase_cost, 2) }}</td>
                            <td class="px-4 py-3 text-right text-sm font-semibold text-gray-900">${{ number_format($item->total_cost, 2) }}</td>
                            <td class="px-4 py-3 text-center">
                                @if($item->is_active)
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">Active</span>
                                @else
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-600">Inactive</span>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-center">
                                <div class="flex items-center justify-center gap-2">
                                    <a href="{{ route('admin.equipment.show', $item->id) }}"
                                       class="text-blue-600 hover:text-blue-800 text-xs font-medium">View</a>
                                    <a href="{{ route('admin.equipment.edit', $item->id) }}"
                                       class="text-gray-500 hover:text-gray-700 text-xs font-medium">Edit</a>
                                    @if($item->is_active)
                                        <form method="POST" action="{{ route('admin.equipment.destroy', $item->id) }}"
                                              onsubmit="return confirm('Deactivate this equipment?')" class="inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit"
                                                    class="text-red-500 hover:text-red-700 text-xs font-medium">Deactivate</button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="10" class="px-4 py-12 text-center text-gray-400">
                                No equipment found. <a href="{{ route('admin.equipment.create') }}" class="text-blue-600 hover:underline">Add the first one</a>.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

</div>
@endsection
