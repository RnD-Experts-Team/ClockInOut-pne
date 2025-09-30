{{-- resources/views/workbooks/edit.blade.php --}}
@extends('layouts.app')

@section('title', 'Edit Workbook: ' . $workbook->name)

@section('content')
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
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
                    <a href="{{ route('workbooks.folders.show', $folder) }}" class="hover:text-orange-600 transition-colors">
                        {{ $folder->name }}
                    </a>
                </li>
                <li>
                    <svg class="w-4 h-4 text-black-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                    </svg>
                </li>
                <li class="font-semibold text-black-900">Edit {{ $workbook->name }}</li>
            </ol>
        </nav>

        <!-- Header -->
        <div class="mb-6">
            <h1 class="text-3xl font-bold text-black-900">Edit Workbook</h1>
            <p class="mt-2 text-sm text-black-600">Update workbook details and manage columns</p>
        </div>

        <!-- Edit Form -->
        <div class="bg-orange-50 shadow-lg rounded-xl border border-orange-200 overflow-hidden">
            <div class="px-6 py-4 border-b border-orange-200 bg-orange-100">
                <h2 class="text-xl font-bold text-black-900">Workbook Details</h2>
            </div>

            <form action="{{ route('workbooks.update', [$folder, $workbook]) }}" method="POST" class="p-6 space-y-6">
                @csrf
                @method('PUT')

                <div>
                    <label for="workbook_name" class="block text-sm font-medium text-black-700 mb-2">Workbook Name *</label>
                    <input type="text" name="name" id="workbook_name" value="{{ $workbook->name }}" required
                           class="block w-full rounded-lg border-orange-300 shadow-sm focus:border-orange-500 focus:ring-2 focus:ring-orange-500/20 sm:text-sm">
                </div>

                <div>
                    <label for="workbook_description" class="block text-sm font-medium text-black-700 mb-2">Description</label>
                    <textarea name="description" id="workbook_description" rows="3"
                              class="block w-full rounded-lg border-orange-300 shadow-sm focus:border-orange-500 focus:ring-2 focus:ring-orange-500/20 sm:text-sm">{{ $workbook->description }}</textarea>
                </div>

                <hr class="border-orange-200">

                <div class="bg-yellow-50 border-l-4 border-yellow-400 p-4 rounded-lg">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <svg class="h-5 w-5 text-yellow-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                            </svg>
                        </div>
                        <div class="ml-3">
                            <p class="text-sm text-yellow-700">
                                <strong>Warning:</strong> Deleting columns will permanently delete all associated data.
                            </p>
                        </div>
                    </div>
                </div>

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
                        @foreach($workbook->columns as $index => $column)
                            <div class="flex gap-2 column-row">
                                <input type="hidden" name="columns[{{ $index }}][id]" value="{{ $column->id }}">
                                <input type="text" name="columns[{{ $index }}][name]" value="{{ $column->name }}" required
                                       class="flex-1 rounded-lg border-orange-300 shadow-sm focus:border-orange-500 focus:ring-2 focus:ring-orange-500/20 sm:text-sm"
                                       placeholder="Column name">
                                <button type="button" class="remove-column-btn px-3 py-2 text-sm font-medium rounded-lg text-red-700 bg-red-50 border border-red-300 hover:bg-red-100">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                    </svg>
                                </button>
                            </div>
                        @endforeach
                    </div>
                </div>

                <div class="flex justify-between items-center pt-6 border-t border-orange-200">
                    <a href="{{ route('workbooks.show', [$folder, $workbook]) }}"
                       class="inline-flex items-center px-4 py-2 text-sm font-medium rounded-lg text-black-700 bg-white border border-orange-300 hover:bg-orange-100">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                        </svg>
                        Cancel
                    </a>
                    <button type="submit"
                            class="inline-flex items-center px-4 py-2 text-sm font-medium rounded-lg text-white bg-orange-600 hover:bg-orange-700">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                        </svg>
                        Update Workbook
                    </button>
                </div>
            </form>
        </div>
    </div>

    @push('scripts')
    <script>
        let columnIndex = {{ $workbook->columns->count() }};

        document.getElementById('addColumnBtn').addEventListener('click', function() {
            const container = document.getElementById('columnsContainer');
            const newColumn = document.createElement('div');
            newColumn.className = 'flex gap-2 column-row';
            newColumn.innerHTML = `
                <input type="text" name="columns[${columnIndex}][name]" required
                       class="flex-1 rounded-lg border-orange-300 shadow-sm focus:border-orange-500 focus:ring-2 focus:ring-orange-500/20 sm:text-sm"
                       placeholder="Column name">
                <button type="button" class="remove-column-btn px-3 py-2 text-sm font-medium rounded-lg text-red-700 bg-red-50 border border-red-300 hover:bg-red-100">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                    </svg>
                </button>
            `;
            container.appendChild(newColumn);
            columnIndex++;
            updateRemoveButtons();
        });

        document.getElementById('columnsContainer').addEventListener('click', function(e) {
            if (e.target.closest('.remove-column-btn')) {
                const row = e.target.closest('.column-row');
                const hasId = row.querySelector('input[type="hidden"]');

                if (hasId) {
                    if (!confirm('This will delete all data in this column. Are you sure?')) return;
                }

                row.remove();
                updateRemoveButtons();
            }
        });

        function updateRemoveButtons() {
            const rows = document.querySelectorAll('.column-row');
            rows.forEach(row => {
                const btn = row.querySelector('.remove-column-btn');
                btn.disabled = rows.length === 1;
            });
        }

        updateRemoveButtons();
    </script>
    @endpush
@endsection
