{{-- resources/views/workbooks/show.blade.php --}}
@extends('layouts.app')

@section('title', 'Workbook: ' . $workbook->name)

@section('content')
    <div class="max-w-full mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <!-- Breadcrumb -->
        <nav class="mb-6">
            <ol class="flex items-center space-x-2 text-sm text-black-600">
                <li>
                    <a href="{{ route('workbooks.folders.index') }}" class="hover:text-orange-600 transition-colors">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z"/>
                        </svg>
                    </a>
                </li>
                <li>
                    <svg class="w-4 h-4 text-black-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                    </svg>
                </li>
                <li>
                    <a href="{{ route('workbooks.folders.show', $workbook->folder) }}" class="hover:text-orange-600 transition-colors">
                        {{ $workbook->folder->name }}
                    </a>
                </li>
                <li>
                    <svg class="w-4 h-4 text-black-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                    </svg>
                </li>
                <li class="font-semibold text-black-900">{{ $workbook->name }}</li>
            </ol>
        </nav>

        <!-- Success Toast -->
        @if(session('success'))
            <div id="successToast" 
                class="fixed top-4 right-4 z-[110] bg-green-500 text-white px-6 py-4 rounded-xl shadow-2xl
                       transform transition-transform duration-300 ease-out flex items-center space-x-3 max-w-sm">
                <div class="flex-shrink-0">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                    </svg>
                </div>
                <div class="flex-1">
                    <p class="font-semibold">Success!</p>
                    <p class="text-sm opacity-90">{{ session('success') }}</p>
                </div>
                <button type="button" onclick="document.getElementById('successToast').remove()"
                        class="flex-shrink-0 hover:bg-green-600 rounded-lg p-1 transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>
            <script>
                // slide in, auto hide
                const t = document.getElementById('successToast');
                if (t) { t.style.transform = 'translateX(0)'; setTimeout(()=> t.remove(), 5000); }
            </script>
        @endif

        <!-- Search and Filters Card -->
        <div class="bg-orange-50 shadow-lg rounded-xl border border-orange-200 mb-8">
            <div class="px-6 py-4 border-b border-orange-200 bg-orange-100">
                <div class="flex items-center justify-between">
                    <h3 class="text-lg font-bold text-black-900 flex items-center">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"/>
                        </svg>
                        Search & Filter
                    </h3>
                    <div class="flex items-center gap-3">
                        <a href="{{ route('workbooks.rows.create', [$workbook->folder, $workbook]) }}"
                           class="inline-flex items-center px-3 py-2 text-sm font-medium rounded-lg text-white bg-orange-600 hover:bg-orange-700">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                            </svg>
                            Add Row
                        </a>
                        <button type="button" id="toggleFilters" class="text-sm text-black-600 hover:text-black-900">
                            <span id="filterToggleText">Show Filters</span>
                            <svg id="filterToggleIcon" class="w-4 h-4 inline ml-1 transform transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                            </svg>
                        </button>
                    </div>
                </div>
            </div>

            <form method="GET" action="{{ route('workbooks.show', [$workbook->folder, $workbook]) }}" class="p-6">
                <!-- Global Search -->
                <div class="mb-6">
                    <label for="search" class="block text-sm font-semibold text-black-700 mb-2">Global Search</label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <svg class="h-5 w-5 text-black-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                            </svg>
                        </div>
                        <input type="text" name="search" id="search"
                               class="block w-full pl-10 rounded-xl border-orange-300 shadow-sm focus:border-orange-500 focus:ring-2 focus:ring-orange-500/20 sm:text-sm py-3 px-4"
                               placeholder="Search across all columns..."
                               value="{{ request('search') }}">
                    </div>
                </div>

                <!-- Advanced Filters (collapsible) -->
                <div id="advancedFilters" class="hidden space-y-6">
                    <hr class="border-orange-200">

                    <!-- Column Filters -->
                    <div>
                        <h4 class="text-sm font-semibold text-black-900 mb-4">Filter by Column</h4>
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                            @foreach($workbook->columns as $column)
                                <div>
                                    <label for="filter_{{ $column->id }}" class="block text-sm font-medium text-black-700 mb-2">
                                        {{ $column->name }}
                                    </label>
                                    <input type="text" name="filter_{{ $column->id }}" id="filter_{{ $column->id }}"
                                           class="block w-full rounded-lg border-orange-300 shadow-sm focus:border-orange-500 focus:ring-2 focus:ring-orange-500/20 sm:text-sm"
                                           placeholder="Filter {{ $column->name }}..."
                                           value="{{ $filters[$column->id] ?? '' }}">
                                </div>
                            @endforeach
                        </div>
                    </div>

                    <hr class="border-orange-200">

                    <!-- Sorting -->
                    <div>
                        <h4 class="text-sm font-semibold text-black-900 mb-4">Sorting</h4>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label for="sort_column" class="block text-sm font-medium text-black-700 mb-2">Sort by Column</label>
                                <select name="sort_column" id="sort_column"
                                        class="block w-full rounded-lg border-orange-300 shadow-sm focus:border-orange-500 focus:ring-2 focus:ring-orange-500/20 sm:text-sm">
                                    <option value="">Default (Date Created)</option>
                                    @foreach($workbook->columns as $column)
                                        <option value="{{ $column->id }}" {{ request('sort_column') == $column->id ? 'selected' : '' }}>
                                            {{ $column->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label for="sort_order" class="block text-sm font-medium text-black-700 mb-2">Sort Order</label>
                                <select name="sort_order" id="sort_order"
                                        class="block w-full rounded-lg border-orange-300 shadow-sm focus:border-orange-500 focus:ring-2 focus:ring-orange-500/20 sm:text-sm">
                                    <option value="asc" {{ request('sort_order') == 'asc' ? 'selected' : '' }}>Ascending</option>
                                    <option value="desc" {{ request('sort_order') == 'desc' ? 'selected' : 'selected' }}>Descending</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Actions -->
                <div class="flex flex-wrap gap-2 mt-6">
                    <button type="submit"
                            class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-lg text-white bg-orange-600 hover:bg-orange-700">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"/>
                        </svg>
                        Apply Filters
                    </button>
                    <a href="{{ route('workbooks.show', [$workbook->folder, $workbook]) }}"
                       class="inline-flex items-center px-4 py-2 border border-orange-300 text-sm font-medium rounded-lg text-black-700 bg-white hover:bg-orange-100">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                        Clear All
                    </a>
                </div>
            </form>
        </div>

        <!-- Data Table -->
        <div class="bg-orange-50 shadow-lg rounded-xl border border-orange-200 overflow-hidden">
            <div class="px-6 py-4 border-b border-orange-200 bg-orange-100">
                <div class="flex items-center justify-between">
                    <h3 class="text-lg font-bold text-black-900">Data</h3>
                    <span class="text-sm text-black-600">{{ $rows->count() }} row(s)</span>
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-orange-200">
                    <thead class="bg-orange-100">
                        <tr>
                            <th class="sticky left-0 z-10 bg-orange-100 px-6 py-3 text-left text-xs font-semibold text-black-900 uppercase tracking-wider border-r border-orange-200">
                                Actions
                            </th>
                            @foreach($workbook->columns as $column)
                                <th class="px-6 py-3 text-left text-xs font-semibold text-black-900 uppercase tracking-wider whitespace-nowrap">
                                    {{ $column->name }}
                                </th>
                            @endforeach
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-orange-200">
                        @forelse($rows as $row)
                            <tr class="hover:bg-orange-50 transition-colors">
                                <td class="sticky left-0 z-10 bg-white px-6 py-4 whitespace-nowrap text-sm border-r border-orange-200">
                                    <div class="flex gap-2">
                                        <a href="{{ route('workbooks.rows.edit', [$workbook->folder, $workbook, $row]) }}"
                                           class="inline-flex items-center px-2.5 py-1.5 text-xs font-medium rounded-lg text-white bg-orange-600 hover:bg-orange-700">
                                            <svg class="w-3.5 h-3.5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                            </svg>
                                            Edit
                                        </a>

                                        <form action="{{ route('workbooks.rows.destroy', [$workbook->folder, $workbook, $row]) }}"
                                              method="POST" class="inline"
                                              onsubmit="return confirm('Delete this row? This cannot be undone.');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit"
                                                    class="inline-flex items-center px-2.5 py-1.5 text-xs font-medium rounded-lg text-red-700 bg-red-50 border border-red-300 hover:bg-red-100">
                                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                                </svg>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                                @foreach($workbook->columns as $column)
                                    <td class="px-6 py-4 text-sm text-black-900">
                                        {{ $row->getCellValue($column->id) ?: '-' }}
                                    </td>
                                @endforeach
                            </tr>
                        @empty
                            <tr>
                                <td colspan="{{ $workbook->columns->count() + 1 }}" class="px-6 py-12 text-center">
                                    <div class="text-center">
                                        <svg class="w-12 h-12 mx-auto text-orange-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"/>
                                        </svg>
                                        <h3 class="text-sm font-semibold text-black-900 mb-2">No data available</h3>
                                        @if(request()->hasAny(['search', 'sort_column']) || !empty($filters))
                                            <p class="text-sm text-black-600 mb-4">No rows match your filters. Try adjusting your search criteria.</p>
                                            <a href="{{ route('workbooks.show', [$workbook->folder, $workbook]) }}"
                                               class="inline-flex items-center px-3 py-2 text-sm font-medium rounded-lg text-orange-700 bg-orange-100 hover:bg-orange-200">
                                                Clear Filters
                                            </a>
                                        @else
                                            <p class="text-sm text-black-600 mb-4">Get started by adding your first row of data.</p>
                                            <a href="{{ route('workbooks.rows.create', [$workbook->folder, $workbook]) }}"
                                               class="inline-flex items-center px-3 py-2 text-sm font-medium rounded-lg text-white bg-orange-600 hover:bg-orange-700">
                                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                                                </svg>
                                                Add First Row
                                            </a>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

@endsection

@push('styles')
<style>
    /* Custom scrollbar */
    .scrollbar-thin::-webkit-scrollbar { width: 6px; }
    .scrollbar-thin::-webkit-scrollbar-track { background: #fed7aa; border-radius: 3px; }
    .scrollbar-thin::-webkit-scrollbar-thumb { background: #fb923c; border-radius: 3px; }
    .scrollbar-thin::-webkit-scrollbar-thumb:hover { background: #ea580c; }
</style>
@endpush

@push('scripts')
<script>
    // Filters toggle
    document.addEventListener('DOMContentLoaded', () => {
        const toggleFiltersBtn = document.getElementById('toggleFilters');
        const advancedFilters  = document.getElementById('advancedFilters');
        const filterToggleText = document.getElementById('filterToggleText');
        const filterToggleIcon = document.getElementById('filterToggleIcon');

        if (toggleFiltersBtn && advancedFilters) {
            toggleFiltersBtn.addEventListener('click', function() {
                const hidden = advancedFilters.classList.contains('hidden');
                advancedFilters.classList.toggle('hidden');
                filterToggleText.textContent = hidden ? 'Hide Filters' : 'Show Filters';
                filterToggleIcon.classList.toggle('rotate-180', hidden);
            });

            @if(request()->hasAny(['sort_column']) || !empty($filters))
                advancedFilters.classList.remove('hidden');
                filterToggleText.textContent = 'Hide Filters';
                filterToggleIcon.classList.add('rotate-180');
            @endif
        }
    });
</script>
@endpush
