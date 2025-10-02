{{-- resources/views/workbooks/folders/index.blade.php --}}
@extends('layouts.app')

@section('title', 'Workbook Folders')

@section('content')
    <div class="max-w-full mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <!-- Header Section -->
        <div class="sm:flex sm:items-center sm:justify-between mb-6">
            <div>
                <h1 class="text-3xl font-bold text-black-900">Workbook Manager</h1>
                <p class="mt-2 text-sm text-black-600">Organize data in customizable workbooks and folders</p>
            </div>
            <div class="mt-4 sm:mt-0">
                <button type="button" data-modal-target="createFolderModal"
                        class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-lg text-white bg-orange-600 hover:bg-orange-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-orange-500 transition-all duration-300 hover:shadow-lg">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                    </svg>
                    New Folder
                </button>
            </div>
        </div>

        <!-- Search and Filters -->
        <div class="bg-orange-50 shadow-lg rounded-xl p-6 mb-8 border border-orange-200">
            <form method="GET" action="{{ route('workbooks.folders.index') }}" class="space-y-4">
                <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                    <div class="md:col-span-2">
                        <label for="search" class="block text-sm font-semibold text-black-700 mb-2">Search Folders</label>
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
                            <option value="workbooks_count" {{ request('sort_by') == 'workbooks_count' ? 'selected' : '' }}>Workbook Count</option>
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
                            class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-lg text-white bg-orange-600 hover:bg-orange-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-orange-500">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"/>
                        </svg>
                        Apply Filters
                    </button>
                    <a href="{{ route('workbooks.folders.index') }}"
                       class="inline-flex items-center px-4 py-2 border border-orange-300 text-sm font-medium rounded-lg text-black-700 bg-white hover:bg-orange-100">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                        Clear
                    </a>
                </div>
            </form>
        </div>

        <!-- Folders Grid -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            @forelse($folders as $folder)
                <div class="bg-orange-50 rounded-xl shadow-lg border border-orange-200 hover:shadow-xl transition-all duration-300 overflow-hidden">
                    <div class="p-6">
                        <div class="flex items-start justify-between mb-4">
                            <div class="flex items-center space-x-3">
                                <div class="flex-shrink-0">
                                    <svg class="w-10 h-10 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z"/>
                                    </svg>
                                </div>
                                <div>
                                    <h3 class="text-lg font-bold text-black-900">{{ $folder->name }}</h3>
                                    <p class="text-sm text-black-600 mt-1">
                                        <svg class="w-4 h-4 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
                                        </svg>
                                        {{ $folder->workbooks_count }} workbook(s)
                                    </p>
                                </div>
                            </div>
                        </div>
                        @if($folder->description)
                            <p class="text-sm text-black-700 mb-4">{{ Str::limit($folder->description, 100) }}</p>
                        @else
                            <p class="text-sm text-black-500 italic mb-4">No description</p>
                        @endif
                        <div class="text-xs text-black-500 mb-4">
                            Created {{ $folder->created_at->diffForHumans() }}
                        </div>
                    </div>
                    <div class="px-6 py-4 bg-orange-100 border-t border-orange-200 flex justify-between items-center">
                        <a href="{{ route('workbooks.folders.show', $folder) }}"
                           class="inline-flex items-center px-3 py-2 text-sm font-medium rounded-lg text-white bg-orange-600 hover:bg-orange-700 transition-all duration-200">
                            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                            </svg>
                            Open
                        </a>
                        <div class="flex gap-2">
                            <button type="button" data-modal-target="editFolderModal"
                                    data-folder-id="{{ $folder->id }}"
                                    data-folder-name="{{ $folder->name }}"
                                    data-folder-description="{{ $folder->description }}"
                                    class="edit-folder-btn inline-flex items-center px-3 py-2 text-sm font-medium rounded-lg text-black-700 bg-white border border-orange-300 hover:bg-orange-100 transition-all duration-200">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                </svg>
                            </button>
                            <form action="{{ route('workbooks.folders.destroy', $folder) }}" method="POST" class="inline"
                                  onsubmit="return confirm('Delete this folder and all its workbooks? This cannot be undone.')">
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
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z"/>
                        </svg>
                        <h3 class="text-lg font-semibold text-black-900 mb-2">No folders found</h3>
                        @if(request()->has('search'))
                            <p class="text-black-600">No folders match your search. Try different keywords or <a href="{{ route('workbooks.folders.index') }}" class="text-orange-600 hover:text-orange-700 underline">clear filters</a>.</p>
                        @else
                            <p class="text-black-600 mb-4">Get started by creating your first folder to organize your workbooks.</p>
                            <button type="button" data-modal-target="createFolderModal"
                                    class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-lg text-white bg-orange-600 hover:bg-orange-700">
                                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                                </svg>
                                Create First Folder
                            </button>
                        @endif
                    </div>
                </div>
            @endforelse
        </div>
    </div>

<!-- Create Folder Modal -->
<div id="createFolderModal" class="fixed inset-0 bg-black bg-opacity-50 hidden z-50 flex items-center justify-center p-4">
    <div class="bg-orange-50 rounded-xl shadow-2xl max-w-2xl w-full overflow-hidden" onclick="event.stopPropagation()">
        <div class="px-6 py-4 border-b border-orange-200 bg-orange-100">
            <div class="flex justify-between items-center">
                <h3 class="text-xl font-bold text-black-900">Create New Folder</h3>
                <button type="button" class="close-modal text-black-700 hover:text-black-900">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>
        </div>
        <form action="{{ route('workbooks.folders.store') }}" method="POST" class="p-6 space-y-4">
            @csrf
            <div>
                <label for="name" class="block text-sm font-medium text-black-700 mb-2">Folder Name *</label>
                <input type="text" name="name" id="name" required
                       class="block w-full rounded-lg border-orange-300 shadow-sm focus:border-orange-500 focus:ring-2 focus:ring-orange-500/20 sm:text-sm py-2.5"
                       placeholder="e.g., Sales Data, Customer Records">
            </div>
            <div>
                <label for="description" class="block text-sm font-medium text-black-700 mb-2">Description</label>
                <textarea name="description" id="description" rows="4"
                          class="block w-full rounded-lg border-orange-300 shadow-sm focus:border-orange-500 focus:ring-2 focus:ring-orange-500/20 sm:text-sm"
                          placeholder="Optional description..."></textarea>
            </div>
            <div class="flex justify-end gap-3 pt-4">
                <button type="button" class="close-modal px-5 py-2.5 text-sm font-medium rounded-lg text-black-700 bg-white border border-orange-300 hover:bg-orange-100">
                    Cancel
                </button>
                <button type="submit"
                        class="inline-flex items-center px-5 py-2.5 text-sm font-medium rounded-lg text-white bg-orange-600 hover:bg-orange-700">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                    </svg>
                    Create Folder
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Edit Folder Modal -->
<div id="editFolderModal" class="fixed inset-0 bg-black bg-opacity-50 hidden z-50 flex items-center justify-center p-4">
    <div class="bg-orange-50 rounded-xl shadow-2xl max-w-2xl w-full overflow-hidden" onclick="event.stopPropagation()">
        <div class="px-6 py-4 border-b border-orange-200 bg-orange-100">
            <div class="flex justify-between items-center">
                <h3 class="text-xl font-bold text-black-900">Edit Folder</h3>
                <button type="button" class="close-modal text-black-700 hover:text-black-900">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>
        </div>
        <form id="editFolderForm" method="POST" class="p-6 space-y-4">
            @csrf
            @method('PUT')
            <div>
                <label for="edit_name" class="block text-sm font-medium text-black-700 mb-2">Folder Name *</label>
                <input type="text" name="name" id="edit_name" required
                       class="block w-full rounded-lg border-orange-300 shadow-sm focus:border-orange-500 focus:ring-2 focus:ring-orange-500/20 sm:text-sm py-2.5">
            </div>
            <div>
                <label for="edit_description" class="block text-sm font-medium text-black-700 mb-2">Description</label>
                <textarea name="description" id="edit_description" rows="4"
                          class="block w-full rounded-lg border-orange-300 shadow-sm focus:border-orange-500 focus:ring-2 focus:ring-orange-500/20 sm:text-sm"></textarea>
            </div>
            <div class="flex justify-end gap-3 pt-4">
                <button type="button" class="close-modal px-5 py-2.5 text-sm font-medium rounded-lg text-black-700 bg-white border border-orange-300 hover:bg-orange-100">
                    Cancel
                </button>
                <button type="submit"
                        class="inline-flex items-center px-5 py-2.5 text-sm font-medium rounded-lg text-white bg-orange-600 hover:bg-orange-700">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                    </svg>
                    Update Folder
                </button>
            </div>
        </form>
    </div>
</div>

    @push('scripts')
<script>
    // Modal handling - Open modal
    document.querySelectorAll('[data-modal-target]').forEach(button => {
        button.addEventListener('click', function(e) {
            e.preventDefault();
            const modalId = this.getAttribute('data-modal-target');
            const modal = document.getElementById(modalId);
            if (modal) {
                modal.classList.remove('hidden');
                modal.classList.add('flex');
            }
        });
    });

    // Modal handling - Close modal
    document.querySelectorAll('.close-modal').forEach(button => {
        button.addEventListener('click', function(e) {
            e.preventDefault();
            const modal = this.closest('[id$="Modal"]');
            if (modal) {
                modal.classList.add('hidden');
                modal.classList.remove('flex');
            }
        });
    });

    // Close modal on outside click
    document.querySelectorAll('[id$="Modal"]').forEach(modal => {
        modal.addEventListener('click', function(e) {
            if (e.target === this) {
                this.classList.add('hidden');
                this.classList.remove('flex');
            }
        });
    });

    // Edit folder modal
    document.querySelectorAll('.edit-folder-btn').forEach(button => {
        button.addEventListener('click', function(e) {
            e.preventDefault();
            const folderId = this.dataset.folderId;
            const folderName = this.dataset.folderName;
            const folderDescription = this.dataset.folderDescription;
            
            document.getElementById('edit_name').value = folderName;
            document.getElementById('edit_description').value = folderDescription || '';
            document.getElementById('editFolderForm').action = `/workbooks/folders/${folderId}`;
            
            // Open the edit modal
            const modal = document.getElementById('editFolderModal');
            modal.classList.remove('hidden');
            modal.classList.add('flex');
        });
    });

    // ESC key to close modals
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            document.querySelectorAll('[id$="Modal"]').forEach(modal => {
                modal.classList.add('hidden');
                modal.classList.remove('flex');
            });
        }
    });
</script>
@endpush
@endsection
