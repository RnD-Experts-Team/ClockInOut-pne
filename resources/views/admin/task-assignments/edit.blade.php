@extends('layouts.app')

@section('title', 'Edit Task Assignment')

@section('content')
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <!-- Header Section -->
        <div class="sm:flex sm:items-center sm:justify-between mb-6">
            <div>
                <nav class="flex" aria-label="Breadcrumb">
                    <ol class="inline-flex items-center space-x-1 md:space-x-3">
                        <li class="inline-flex items-center">
                            <a href="{{ route('admin.task-assignments.index') }}" class="inline-flex items-center text-sm font-medium text-gray-700 hover:text-orange-600">
                                Task Assignments
                            </a>
                        </li>
                        <li>
                            <div class="flex items-center">
                                <svg class="w-6 h-6 text-gray-400" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"></path>
                                </svg>
                                <a href="{{ route('admin.task-assignments.show', $taskAssignment) }}" class="ml-1 text-sm font-medium text-gray-700 hover:text-orange-600 md:ml-2">Assignment #{{ $taskAssignment->id }}</a>
                            </div>
                        </li>
                        <li>
                            <div class="flex items-center">
                                <svg class="w-6 h-6 text-gray-400" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"></path>
                                </svg>
                                <span class="ml-1 text-sm font-medium text-gray-500 md:ml-2">Edit</span>
                            </div>
                        </li>
                    </ol>
                </nav>
                <h1 class="mt-2 text-3xl font-bold text-gray-900">Edit Task Assignment</h1>
                <p class="mt-2 text-sm text-gray-600">Update assignment details and settings</p>
            </div>
        </div>

        <!-- Error Messages -->
        @if ($errors->any())
            <div class="mb-6 bg-red-50 border border-red-200 rounded-md p-4">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <svg class="h-5 w-5 text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                    <div class="ml-3">
                        <h3 class="text-sm font-medium text-red-800">There were errors with your submission:</h3>
                        <div class="mt-2 text-sm text-red-700">
                            <ul class="list-disc list-inside space-y-1">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        @endif

        <!-- Edit Form -->
        <div class="bg-white shadow rounded-lg">
            <form action="{{ route('admin.task-assignments.update', $taskAssignment) }}" method="POST">
                @csrf
                @method('PUT')

                <div class="px-6 py-4 border-b border-gray-200">
                    <h3 class="text-lg leading-6 font-medium text-gray-900">Assignment Information</h3>
                </div>

                <div class="px-6 py-4 space-y-6">
                    <!-- Task Information (Read-only) -->
                    <div class="bg-gray-50 rounded-lg p-4">
                        <h4 class="text-sm font-medium text-gray-900 mb-3">Task Details</h4>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-500">Task Description</label>
                                <p class="mt-1 text-sm text-gray-900">
                                    {{ $taskAssignment->maintenanceRequest->description_of_issue ?? 'No description' }}
                                </p>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-500">Store</label>
                                <p class="mt-1 text-sm text-gray-900">
                                    {{ $taskAssignment->maintenanceRequest->store->name ?? 'No store assigned' }}
                                </p>
                            </div>
                        </div>
                    </div>

                    <!-- Assigned Employee -->
                    <div>
                        <label for="assigned_user_id" class="block text-sm font-medium text-gray-700">Assigned Employee <span class="text-red-500">*</span></label>
                        <select name="assigned_user_id" id="assigned_user_id"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-orange-500 focus:ring-orange-500 @error('assigned_user_id') border-red-300 @enderror">
                            <option value="">Select an employee...</option>
                            @foreach($employees as $employee)
                                <option value="{{ $employee->id }}"
                                    {{ old('assigned_user_id', $taskAssignment->assigned_user_id) == $employee->id ? 'selected' : '' }}>
                                    {{ $employee->name }} ({{ ucfirst($employee->role ?? 'Employee') }})
                                </option>
                            @endforeach
                        </select>
                        @error('assigned_user_id')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Status and Priority -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- Status -->
                        <div>
                            <label for="status" class="block text-sm font-medium text-gray-700">Status <span class="text-red-500">*</span></label>
                            <select name="status" id="status"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-orange-500 focus:ring-orange-500 @error('status') border-red-300 @enderror">
                                <option value="pending" {{ old('status', $taskAssignment->status) === 'pending' ? 'selected' : '' }}>
                                    Pending
                                </option>
                                <option value="in_progress" {{ old('status', $taskAssignment->status) === 'in_progress' ? 'selected' : '' }}>
                                    In Progress
                                </option>
                                <option value="completed" {{ old('status', $taskAssignment->status) === 'completed' ? 'selected' : '' }}>
                                    Completed
                                </option>
                            </select>
                            @error('status')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Priority -->
                        <div>
                            <label for="priority" class="block text-sm font-medium text-gray-700">Priority <span class="text-red-500">*</span></label>
                            <select name="priority" id="priority"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-orange-500 focus:ring-orange-500 @error('priority') border-red-300 @enderror">
                                <option value="normal" {{ old('priority', $taskAssignment->priority) === 'normal' ? 'selected' : '' }}>
                                    Normal
                                </option>
                                <option value="high" {{ old('priority', $taskAssignment->priority) === 'high' ? 'selected' : '' }}>
                                    High
                                </option>
                                <option value="urgent" {{ old('priority', $taskAssignment->priority) === 'urgent' ? 'selected' : '' }}>
                                    Urgent
                                </option>
                            </select>
                            @error('priority')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <!-- Due Date -->
                    <div>
                        <label for="due_date" class="block text-sm font-medium text-gray-700">Due Date</label>
                        <input type="datetime-local" name="due_date" id="due_date"
                               value="{{ old('due_date', $taskAssignment->due_date ? $taskAssignment->due_date->format('Y-m-d\TH:i') : '') }}"
                               class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-orange-500 focus:ring-orange-500 @error('due_date') border-red-300 @enderror">
                        @error('due_date')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Assignment Notes -->
                    <div>
                        <label for="assignment_notes" class="block text-sm font-medium text-gray-700">Assignment Notes</label>
                        <textarea name="assignment_notes" id="assignment_notes" rows="4"
                                  class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-orange-500 focus:ring-orange-500 @error('assignment_notes') border-red-300 @enderror"
                                  placeholder="Additional notes or instructions for this assignment...">{{ old('assignment_notes', $taskAssignment->assignment_notes) }}</textarea>
                        @error('assignment_notes')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Schedule Information (if available) -->
                    @if($taskAssignment->scheduleShift)
                        <div class="bg-blue-50 rounded-lg p-4">
                            <h4 class="text-sm font-medium text-blue-900 mb-3">Schedule Information</h4>
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 text-sm">
                                <div>
                                    <label class="block font-medium text-blue-800">Date</label>
                                    <p class="text-blue-700">{{ \Carbon\Carbon::parse($taskAssignment->scheduleShift->date)->format('l, M d, Y') }}</p>
                                </div>
                                <div>
                                    <label class="block font-medium text-blue-800">Time</label>
                                    <p class="text-blue-700">{{ $taskAssignment->scheduleShift->start_time }} - {{ $taskAssignment->scheduleShift->end_time }}</p>
                                </div>
                                <div>
                                    <label class="block font-medium text-blue-800">Shift Type</label>
                                    <p class="text-blue-700">{{ ucfirst($taskAssignment->scheduleShift->shift_type ?? 'Regular') }}</p>
                                </div>
                            </div>
                        </div>
                    @endif
                </div>

                <!-- Form Actions -->
                <div class="px-6 py-4 bg-gray-50 border-t border-gray-200 flex justify-between items-center">
                    <div class="flex space-x-3">
                        <a href="{{ route('admin.task-assignments.show', $taskAssignment) }}"
                           class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-orange-500">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                            </svg>
                            Cancel
                        </a>
                        <a href="{{ route('admin.task-assignments.index') }}"
                           class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-orange-500">
                            Back to List
                        </a>
                    </div>

                    <button type="submit"
                            class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-orange-600 hover:bg-orange-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-orange-500">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                        </svg>
                        Update Assignment
                    </button>
                </div>
            </form>
        </div>
    </div>
@endsection

@push('styles')
    <style>
        .is-invalid {
            border-color: #ef4444;
        }
    </style>
@endpush

    <script>
        $(document).ready(function() {
            // Auto-save draft functionality (optional)
            let autoSaveTimeout;

            $('select, textarea').on('change input', function() {
                clearTimeout(autoSaveTimeout);
                autoSaveTimeout = setTimeout(function() {
                    console.log('Auto-save triggered (implement if needed)');
                }, 2000);
            });

            // Form validation feedback
            $('form').on('submit', function() {
                $(this).find('button[type="submit"]').prop('disabled', true).text('Updating...');
            });
        });
    </script>
