{{-- resources/views/workbooks/folders/show.blade.php --}}
@extends('layouts.app')

@section('title', 'Folder: ' . $folder->name)

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
                <li class="font-semibold text-black-900">{{ $folder->name }}</li>
            </ol>
        </nav>

        <!-- Header Section -->
        <div class="sm:flex sm:items-center sm:justify-between mb-6">
            <div>
                <div class="flex items-center space-x-3">
                    <svg class="w-10 h-10 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z"/>
                    </svg>
                    <h1 class="text-3xl font-bold text-black-900">{{ $folder->name }}</h1>
                </div>
                @if($folder->description)
                    <p class="mt-2 text-sm text-black-600">{{ $folder->description }}</p>
                @endif
            </div>
            <div class="mt-4 sm:mt-0">
                <button type="button" data-modal-target="createWorkbookModal"
                        class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-lg text-white bg-orange-600 hover:bg-orange-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-orange-500 transition-all duration-300 hover:shadow-lg">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                    </svg>
                    New Workbook
                </button>
            </div>
        </div>

        <!-- Search and Filters -->
        <div class="bg-orange-50 shadow-lg rounded-xl p-6 mb-8 border border-orange-200">
            <form method="GET" action="{{ route('workbooks.folders.show', $folder) }}" class="space-y-4">
                <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                    <div class="md:col-span-2">
                        <label for="search" class="block text-sm font-semibold text-black-700 mb-2">Search Workbooks</label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <svg class="h-5 w-5 text-black-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                                </svg>
                            </div>
                            <input type="text" name="search" id="search"
                                   class="block w-full pl-10 rounded-xl border-orange-300 shadow-sm focus:border-orange-500 focus:ring-2 focus:ring-orange-500/20 sm:text-sm py-3 px-4"
                                   placeholder="Search by name or description..."
                                   value="{{ request('search') }}">
                        </div>
                    </div>
                    <div>
                        <label for="sort_by" class="block text-sm font-semibold text-black-700 mb-2">Sort By</label>
                        <select name="sort_by" id="sort_by"
                                class="block w-full rounded-xl border-orange-300 shadow-sm focus:border-orange-500 focus:ring-2 focus:ring-orange-500/20 sm:text-sm py-3 px-4">
                            <option value="created_at" {{ request('sort_by') == 'created_at' ? 'selected' : '' }}>Date Created</option>
                            <option value="name" {{ request('sort_by') == 'name' ? 'selected' : '' }}>Name</option>
                        </select>
                    </div>
                    <div>
                        <label for="sort_order" class="block text-sm font-semibold text-black-700 mb-2">Order</label>
                        <select name="sort_order" id="sort_order"
                                class="block w-full rounded-xl border-orange-300 shadow-sm focus:border-orange-500 focus:ring-2 focus:ring-orange-500/20 sm:text-sm py-3 px-4">
                            <option value="asc" {{ request('sort_order') == 'asc' ? 'selected' : '' }}>Ascending</option>
                            <option value="desc" {{ request('sort_order') == 'desc' ? 'selected' : '' }}>Descending</option>
                        </select>
                    </div>
                </div>
                <div class="flex gap-2">
                    <button type="submit"
                            class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-lg text-white bg-orange-600 hover:bg-orange-700">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"/>
                        </svg>
                        Apply Filters
                    </button>
                    <a href="{{ route('workbooks.folders.show', $folder) }}"
                       class="inline-flex items-center px-4 py-2 border border-orange-300 text-sm font-medium rounded-lg text-black-700 bg-white hover:bg-orange-100">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                        Clear
                    </a>
                </div>
            </form>
        </div>

        <!-- Workbooks Grid -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            @forelse($workbooks as $workbook)
                <div class="bg-orange-50 rounded-xl shadow-lg border border-orange-200 hover:shadow-xl transition-all duration-300 overflow-hidden">
                    <div class="p-6">
                        <div class="flex items-start justify-between mb-4">
                            <div class="flex items-center space-x-3">
                                <div class="flex-shrink-0">
                                    <svg class="w-10 h-10 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                    </svg>
                                </div>
                                <div>
                                    <h3 class="text-lg font-bold text-black-900">{{ $workbook->name }}</h3>
                                    <p class="text-sm text-black-600 mt-1">
                                        <svg class="w-4 h-4 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17V7m0 10a2 2 0 01-2 2H5a2 2 0 01-2-2V7a2 2 0 012-2h2a2 2 0 012 2m0 10a2 2 0 002 2h2a2 2 0 002-2M9 7a2 2 0 012-2h2a2 2 0 012 2m0 10V7m0 10a2 2 0 002 2h2a2 2 0 002-2V7a2 2 0 00-2-2h-2a2 2 0 00-2 2"/>
                                        </svg>
                                        {{ $workbook->columns->count() }} column(s)
                                    </p>
                                </div>
                            </div>
                        </div>

                        @if($workbook->description)
                            <p class="text-sm text-black-700 mb-4">{{ Str::limit($workbook->description, 100) }}</p>
                        @else
                            <p class="text-sm text-black-500 italic mb-4">No description</p>
                        @endif

                        @if($workbook->columns->count() > 0)
                            <div class="mb-4">
                                <div class="flex flex-wrap gap-2">
                                    @foreach($workbook->columns->take(5) as $column)
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-orange-200 text-orange-800">
                                            {{ $column->name }}
                                        </span>
                                    @endforeach
                                    @if($workbook->columns->count() > 5)
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-orange-300 text-orange-900">
                                            +{{ $workbook->columns->count() - 5 }} more
                                        </span>
                                    @endif
                                </div>
                            </div>
                        @endif

                        <div class="text-xs text-black-500">
                            Created {{ $workbook->created_at->diffForHumans() }}
                        </div>
                    </div>

                    <div class="px-6 py-4 bg-orange-100 border-t border-orange-200 flex justify-between items-center">
                        <a href="{{ route('workbooks.show', [$folder, $workbook]) }}"
                           class="inline-flex items-center px-3 py-2 text-sm font-medium rounded-lg text-white bg-orange-600 hover:bg-orange-700 transition-all duration-200">
                            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                            </svg>
                            Open
                        </a>

                        <div class="flex gap-2">
                            <a href="{{ route('workbooks.edit', [$folder, $workbook]) }}"
                               class="inline-flex items-center px-3 py-2 text-sm font-medium rounded-lg text-black-700 bg-white border border-orange-300 hover:bg-orange-100 transition-all duration-200">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                </svg>
                            </a>

                            <form action="{{ route('workbooks.destroy', [$folder, $workbook]) }}"
                                  method="POST"
                                  onsubmit="return confirm('Delete this workbook and all its data? This cannot be undone.');"
                                  class="inline">
                                @csrf
                                @method('DELETE')
                                <button type="submit"
                                        class="inline-flex items-center px-3 py-2 text-sm font-medium rounded-lg text-red-700 bg-red-50 border border-red-300 hover:bg-red-100 transition-all duration-200">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                    </svg>
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            @empty
                <div class="col-span-full">
                    <div class="bg-orange-100 rounded-xl p-12 text-center border-2 border-dashed border-orange-300">
                        <svg class="w-16 h-16 mx-auto text-orange-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                        </svg>
                        <h3 class="text-lg font-semibold text-black-900 mb-2">No workbooks found</h3>
                        @if(request()->has('search'))
                            <p class="text-black-600">No workbooks match your search. Try different keywords or <a href="{{ route('workbooks.folders.show', $folder) }}" class="text-orange-600 hover:text-orange-700 underline">clear filters</a>.</p>
                        @else
                            <p class="text-black-600 mb-4">Get started by creating your first workbook in this folder.</p>
                            <button type="button" data-modal-target="createWorkbookModal"
                                    class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-lg text-white bg-orange-600 hover:bg-orange-700">
                                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                                </svg>
                                Create First Workbook
                            </button>
                        @endif
                    </div>
                </div>
            @endforelse
        </div>
    </div>

    <!-- Create Workbook Modal -->
    <div id="createWorkbookModal" class="fixed inset-0 bg-black bg-opacity-50 hidden items-center justify-center z-50 overflow-y-auto">
        <div class="bg-orange-50 rounded-xl shadow-2xl max-w-2xl w-full mx-4 my-8 overflow-hidden">
            <div class="px-6 py-4 border-b border-orange-200 bg-orange-100">
                <div class="flex justify-between items-center">
                    <h3 class="text-xl font-bold text-black-900">Create New Workbook</h3>
                    <button type="button" class="close-modal text-black-700 hover:text-black-900">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>
            </div>
            <form action="{{ route('workbooks.store', $folder) }}" method="POST" class="p-6 space-y-4 max-h-[80vh] overflow-y-auto">
                @csrf
                <div>
                    <label for="workbook_name" class="block text-sm font-medium text-black-700 mb-2">Workbook Name *</label>
                    <input type="text" name="name" id="workbook_name" required
                           class="block w-full rounded-lg border-orange-300 shadow-sm focus:border-orange-500 focus:ring-2 focus:ring-orange-500/20 sm:text-sm"
                           placeholder="e.g., Q1 Sales, Customer List">
                </div>
                <div>
                    <label for="workbook_description" class="block text-sm font-medium text-black-700 mb-2">Description</label>
                    <textarea name="description" id="workbook_description" rows="2"
                              class="block w-full rounded-lg border-orange-300 shadow-sm focus:border-orange-500 focus:ring-2 focus:ring-orange-500/20 sm:text-sm"
                              placeholder="Optional description..."></textarea>
                </div>

                <hr class="border-orange-200">

                <div>
                    <div class="flex justify-between items-center mb-3">
                        <h4 class="text-sm font-semibold text-black-900">Columns</h4>
                        <button type="button" id="addColumnBtn"
                                class="inline-flex items-center px-3 py-1.5 text-xs font-medium rounded-lg text-white bg-orange-600 hover:bg-orange-700">
                            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                            </svg>
                            Add Column
                        </button>
                    </div>
                    <div id="columnsContainer" class="space-y-2">
                        <div class="flex gap-2 column-row">
                            <input type="text" name="columns[]" required
                                   class="flex-1 rounded-lg border-orange-300 shadow-sm focus:border-orange-500 focus:ring-2 focus:ring-orange-500/20 sm:text-sm"
                                   placeholder="Column name">
                            <button type="button" class="remove-column-btn px-3 py-2 text-sm font-medium rounded-lg text-red-700 bg-red-50 border border-red-300 hover:bg-red-100 disabled:opacity-50 disabled:cursor-not-allowed" disabled>
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                </svg>
                            </button>
                        </div>
                    </div>
                </div>

                <div class="flex justify-end gap-3 pt-4 border-t border-orange-200">
                    <button type="button" class="close-modal px-4 py-2 text-sm font-medium rounded-lg text-black-700 bg-white border border-orange-300 hover:bg-orange-100">
                        Cancel
                    </button>
                    <button type="submit"
                            class="inline-flex items-center px-4 py-2 text-sm font-medium rounded-lg text-white bg-orange-600 hover:bg-orange-700">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                        </svg>
                        Create Workbook
                    </button>
                </div>
            </form>
        </div>
    </div>

    @push('scripts')
    <script>
        // Modal handling
        document.querySelectorAll('[data-modal-target]').forEach(button => {
            button.addEventListener('click', function() {
                const modalId = this.getAttribute('data-modal-target');
                const modal = document.getElementById(modalId);
                modal.classList.remove('hidden');
                modal.classList.add('flex');
            });
        });

        document.querySelectorAll('.close-modal').forEach(button => {
            button.addEventListener('click', function() {
                this.closest('[id$="Modal"]').classList.add('hidden');
                this.closest('[id$="Modal"]').classList.remove('flex');
            });
        });

        // Close modal on outside click
        window.addEventListener('click', function(e) {
            if (e.target.id && e.target.id.endsWith('Modal')) {
                e.target.classList.add('hidden');
                e.target.classList.remove('flex');
            }
        });

        // Column management
        let columnCount = 1;

        document.getElementById('addColumnBtn').addEventListener('click', function() {
            columnCount++;
            const container = document.getElementById('columnsContainer');
            const newColumn = document.createElement('div');
            newColumn.className = 'flex gap-2 column-row';
            newColumn.innerHTML = `
                <input type="text" name="columns[]" required
                       class="flex-1 rounded-lg border-orange-300 shadow-sm focus:border-orange-500 focus:ring-2 focus:ring-orange-500/20 sm:text-sm"
                       placeholder="Column name">
                <button type="button" class="remove-column-btn px-3 py-2 text-sm font-medium rounded-lg text-red-700 bg-red-50 border border-red-300 hover:bg-red-100">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                    </svg>
                </button>
            `;
            container.appendChild(newColumn);
            updateRemoveButtons();
        });

        document.getElementById('columnsContainer').addEventListener('click', function(e) {
            if (e.target.closest('.remove-column-btn')) {
                e.target.closest('.column-row').remove();
                columnCount--;
                updateRemoveButtons();
            }
        });

        function updateRemoveButtons() {
            const rows = document.querySelectorAll('.column-row');
            rows.forEach((row) => {
                const btn = row.querySelector('.remove-column-btn');
                btn.disabled = rows.length === 1;
            });
        }
    </script>
    @endpush
@endsection
