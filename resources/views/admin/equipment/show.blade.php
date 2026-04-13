@extends('layouts.app')

@section('title', $item->name . ' — Equipment Detail')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">

    {{-- Back + Header --}}
    <div class="mb-6">
        <a href="{{ route('admin.equipment.index') }}"
           class="text-sm text-blue-600 hover:text-blue-800 flex items-center gap-1 mb-3">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
            </svg>
            Back to Equipment Tracker
        </a>
        <div class="sm:flex sm:items-start sm:justify-between">
            <div>
                <h1 class="text-3xl font-bold text-gray-900">{{ $item->name }}</h1>
                <div class="flex flex-wrap gap-2 mt-2">
                    @if($item->type)
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                            {{ $item->type }}
                        </span>
                    @endif
                    @if($item->store)
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-700">
                            {{ $item->store->name }}
                        </span>
                    @else
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-purple-100 text-purple-700">
                            Global
                        </span>
                    @endif
                    @if(!$item->is_active)
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-700">
                            Inactive
                        </span>
                    @endif
                </div>
            </div>
            <div class="mt-4 sm:mt-0 flex gap-2">
                <a href="{{ route('admin.equipment.export-detail', $item->id) }}"
                   class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-lg text-white bg-blue-600 hover:bg-blue-700 transition-colors">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                    Export CSV
                </a>
                <a href="{{ route('admin.equipment.edit', $item->id) }}"
                   class="inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-lg text-gray-700 bg-white hover:bg-gray-50 transition-colors">
                    Edit
                </a>
            </div>
        </div>
        @if($item->serial_number || $item->model || $item->notes)
            <div class="mt-3 text-sm text-gray-600 space-y-0.5">
                @if($item->model)<p><span class="font-medium">Model:</span> {{ $item->model }}</p>@endif
                @if($item->serial_number)<p><span class="font-medium">Serial #:</span> {{ $item->serial_number }}</p>@endif
                @if($item->notes)<p><span class="font-medium">Notes:</span> {{ $item->notes }}</p>@endif
            </div>
        @endif
    </div>

    {{-- Flash --}}
    @if(session('success'))
        <div class="mb-4 p-4 bg-green-100 border border-green-300 text-green-800 rounded-lg">{{ session('success') }}</div>
    @endif

    {{-- Summary Totals --}}
    <div class="grid grid-cols-2 md:grid-cols-5 gap-4 mb-6">
        <div class="bg-gradient-to-br from-blue-50 to-blue-100 rounded-xl border border-blue-200 p-5">
            <p class="text-xs font-semibold text-blue-600 uppercase tracking-wider">Total Fixes</p>
            <p class="text-3xl font-bold text-blue-900 mt-1">{{ $summary['fix_count'] }}</p>
        </div>
        <div class="bg-gradient-to-br from-gray-50 to-gray-100 rounded-xl border border-gray-200 p-5">
            <p class="text-xs font-semibold text-gray-600 uppercase tracking-wider">Repair Time</p>
            <p class="text-2xl font-bold text-gray-900 mt-1">{{ $summary['total_repair_hours'] }}h</p>
        </div>
        <div class="bg-gradient-to-br from-purple-50 to-purple-100 rounded-xl border border-purple-200 p-5">
            <p class="text-xs font-semibold text-purple-600 uppercase tracking-wider">Labor Cost</p>
            <p class="text-2xl font-bold text-purple-900 mt-1">${{ number_format($summary['total_labor_cost'], 2) }}</p>
        </div>
        <div class="bg-gradient-to-br from-yellow-50 to-yellow-100 rounded-xl border border-yellow-200 p-5">
            <p class="text-xs font-semibold text-yellow-700 uppercase tracking-wider">Purchase Cost</p>
            <p class="text-2xl font-bold text-yellow-900 mt-1">${{ number_format($summary['total_purchase_cost'], 2) }}</p>
        </div>
        <div class="bg-gradient-to-br from-green-50 to-green-100 rounded-xl border border-green-200 p-5">
            <p class="text-xs font-semibold text-green-700 uppercase tracking-wider">Grand Total</p>
            <p class="text-2xl font-bold text-green-900 mt-1">${{ number_format($summary['total_cost'], 2) }}</p>
        </div>
    </div>

    {{-- MR Timeline Table --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
        <div class="px-5 py-4 border-b border-gray-100">
            <h2 class="text-lg font-semibold text-gray-800">Repair History</h2>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Ticket #</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Date</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Store</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Fixed By</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Description</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase">How Fixed</th>
                        <th class="px-4 py-3 text-center text-xs font-semibold text-gray-600 uppercase">Status</th>
                        <th class="px-4 py-3 text-right text-xs font-semibold text-gray-600 uppercase">Repair Time</th>
                        <th class="px-4 py-3 text-right text-xs font-semibold text-gray-600 uppercase">Labor</th>
                        <th class="px-4 py-3 text-right text-xs font-semibold text-gray-600 uppercase">Purchases</th>
                        <th class="px-4 py-3 text-right text-xs font-semibold text-gray-600 uppercase">Total</th>
                        <th class="px-4 py-3 text-center text-xs font-semibold text-gray-600 uppercase">Re-assign</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($breakdown as $row)
                        @php $mr = $row['mr']; @endphp
                        <tr class="hover:bg-gray-50 transition-colors">
                            <td class="px-4 py-3">
                                <a href="{{ route('maintenance-requests.show', $mr->id) }}"
                                   class="text-blue-600 hover:text-blue-800 text-sm font-medium">
                                    {{ $mr->entry_number ?? '#' . $mr->id }}
                                </a>
                                @if($mr->source === 'manual')
                                    <span class="ml-1 inline-flex items-center px-1.5 py-0.5 rounded text-xs font-medium bg-orange-100 text-orange-800">Manual</span>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-sm text-gray-700">
                                {{ $mr->request_date?->format('M d, Y') ?? '—' }}
                            </td>
                            <td class="px-4 py-3 text-sm text-gray-700">{{ $mr->store?->name ?? '—' }}</td>
                            <td class="px-4 py-3 text-sm text-gray-700">
                                @php
                                    $fixedBy = $mr->source === 'manual'
                                        ? $mr->createdByUser
                                        : ($mr->assignedToUser ?? null);
                                @endphp
                                @if($fixedBy)
                                    <div class="flex items-center gap-1.5">
                                        <div class="w-6 h-6 rounded-full flex items-center justify-center text-xs font-bold text-white
                                            {{ $mr->source === 'manual' ? 'bg-orange-400' : 'bg-blue-500' }}">
                                            {{ strtoupper(substr($fixedBy->name, 0, 1)) }}
                                        </div>
                                        <span class="truncate max-w-[100px]" title="{{ $fixedBy->name }}">{{ $fixedBy->name }}</span>
                                    </div>
                                @else
                                    <span class="text-gray-400">—</span>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-sm text-gray-700 max-w-xs">
                                <span class="line-clamp-2">{{ $mr->description_of_issue }}</span>
                            </td>
                            <td class="px-4 py-3 text-sm text-gray-700 max-w-xs">
                                <div class="line-clamp-2">{{ $mr->how_we_fixed_it ?? '—' }}</div>
                                @if($mr->before_image || $mr->after_image)
                                    <div class="flex gap-2 mt-1.5">
                                        @if($mr->before_image)
                                            <a href="{{ Storage::url($mr->before_image) }}" target="_blank"
                                               class="inline-flex items-center gap-0.5 px-1.5 py-0.5 rounded text-xs font-medium bg-gray-100 text-gray-600 hover:bg-gray-200">
                                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                                Before
                                            </a>
                                        @endif
                                        @if($mr->after_image)
                                            <a href="{{ Storage::url($mr->after_image) }}" target="_blank"
                                               class="inline-flex items-center gap-0.5 px-1.5 py-0.5 rounded text-xs font-medium bg-green-100 text-green-700 hover:bg-green-200">
                                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                                After
                                            </a>
                                        @endif
                                    </div>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-center">
                                @php
                                    $statusColors = [
                                        'done'        => 'bg-green-100 text-green-800',
                                        'in_progress' => 'bg-blue-100 text-blue-800',
                                        'on_hold'     => 'bg-yellow-100 text-yellow-800',
                                        'canceled'    => 'bg-red-100 text-red-800',
                                    ];
                                @endphp
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium {{ $statusColors[$mr->status] ?? 'bg-gray-100 text-gray-600' }}">
                                    {{ ucfirst(str_replace('_', ' ', $mr->status)) }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-right text-sm text-gray-700">{{ $row['repair_hours'] }}h</td>
                            <td class="px-4 py-3 text-right text-sm text-gray-700">${{ number_format($row['labor_cost'], 2) }}</td>
                            <td class="px-4 py-3 text-right text-sm text-gray-700">${{ number_format($row['purchase_cost'], 2) }}</td>
                            <td class="px-4 py-3 text-right text-sm font-semibold text-gray-900">${{ number_format($row['total_cost'], 2) }}</td>
                            <td class="px-4 py-3 text-center">
                                {{-- Re-assign form --}}
                                <form method="POST"
                                      action="{{ route('admin.equipment.mr.reassign', $mr->id) }}"
                                      class="flex items-center gap-1 justify-center">
                                    @csrf
                                    @method('PATCH')
                                    <select name="equipment_id"
                                            class="text-xs rounded border-gray-300 focus:ring-blue-500 focus:border-blue-500"
                                            style="max-width:130px">
                                        @foreach(\App\Models\Equipment::active()->orderBy('name')->get() as $eq)
                                            <option value="{{ $eq->id }}" @selected($eq->id === $item->id)>{{ $eq->name }}</option>
                                        @endforeach
                                    </select>
                                    <button type="submit"
                                            class="px-2 py-1 bg-gray-100 text-gray-600 hover:bg-gray-200 rounded text-xs font-medium transition-colors">
                                        Save
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="12" class="px-4 py-12 text-center text-gray-400">
                                No maintenance requests linked to this equipment yet.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

</div>
@endsection
