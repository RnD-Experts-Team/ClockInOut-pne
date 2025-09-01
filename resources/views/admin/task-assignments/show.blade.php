@extends('layouts.app')

@section('title', 'Task Assignment Details')

@section('content')
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
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
                                <span class="ml-1 text-sm font-medium text-gray-500 md:ml-2">Assignment #{{ $taskAssignment->id }}</span>
                            </div>
                        </li>
                    </ol>
                </nav>
                <h1 class="mt-2 text-3xl font-bold text-gray-900">Task Assignment Details</h1>
            </div>
            <div class="mt-4 sm:mt-0 flex space-x-3">
                <a href="{{ route('admin.task-assignments.edit', $taskAssignment) }}"
                   class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-orange-500">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                    </svg>
                    Edit Assignment
                </a>
                <a href="{{ route('admin.task-assignments.index') }}"
                   class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-orange-600 hover:bg-orange-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-orange-500">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                    </svg>
                    Back to List
                </a>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Main Content -->
            <div class="lg:col-span-2 space-y-6">
                <!-- Assignment Overview -->
                <div class="bg-white shadow rounded-lg overflow-hidden">
                    <div class="px-6 py-4 border-b border-gray-200">
                        <h3 class="text-lg leading-6 font-medium text-gray-900">Assignment Overview</h3>
                    </div>
                    <div class="px-6 py-4">
                        <dl class="grid grid-cols-1 gap-x-4 gap-y-6 sm:grid-cols-2">
                            <div>
                                <dt class="text-sm font-medium text-gray-500">Assignment ID</dt>
                                <dd class="mt-1 text-sm text-gray-900">#{{ $taskAssignment->id }}</dd>
                            </div>
                            <div>
                                <dt class="text-sm font-medium text-gray-500">Assigned Date</dt>
                                <dd class="mt-1 text-sm text-gray-900">{{ $taskAssignment->assigned_at ? $taskAssignment->assigned_at->format('M d, Y \a\t g:i A') : 'Not assigned' }}</dd>
                            </div>
                            <div>
                                <dt class="text-sm font-medium text-gray-500">Due Date</dt>
                                <dd class="mt-1 text-sm text-gray-900">{{ $taskAssignment->due_date ? $taskAssignment->due_date->format('M d, Y \a\t g:i A') : 'No due date' }}</dd>
                            </div>
                            <div>
                                <dt class="text-sm font-medium text-gray-500">Status</dt>
                                <dd class="mt-1">
                                    <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full
                                        @if($taskAssignment->status === 'completed') bg-green-100 text-green-800
                                        @elseif($taskAssignment->status === 'in_progress') bg-blue-100 text-blue-800
                                        @elseif($taskAssignment->status === 'pending') bg-yellow-100 text-yellow-800
                                        @else bg-gray-100 text-gray-800 @endif">
                                        {{ ucfirst(str_replace('_', ' ', $taskAssignment->status ?? 'pending')) }}
                                    </span>
                                </dd>
                            </div>
                            <div>
                                <dt class="text-sm font-medium text-gray-500">Priority</dt>
                                <dd class="mt-1">
                                    <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full
                                        @if($taskAssignment->priority === 'urgent') bg-red-100 text-red-800
                                        @elseif($taskAssignment->priority === 'high') bg-orange-100 text-orange-800
                                        @elseif($taskAssignment->priority === 'normal') bg-blue-100 text-blue-800
                                        @else bg-gray-100 text-gray-800 @endif">
                                        {{ ucfirst($taskAssignment->priority ?? 'normal') }}
                                    </span>
                                </dd>
                            </div>
                        </dl>
                    </div>
                </div>

                <!-- Task Details -->
                <div class="bg-white shadow rounded-lg overflow-hidden">
                    <div class="px-6 py-4 border-b border-gray-200">
                        <h3 class="text-lg leading-6 font-medium text-gray-900">Task Details</h3>
                    </div>
                    <div class="px-6 py-4">
                        @if($taskAssignment->maintenanceRequest)
                            <dl class="grid grid-cols-1 gap-x-4 gap-y-6">
                                <div>
                                    <dt class="text-sm font-medium text-gray-500">Description</dt>
                                    <dd class="mt-1 text-sm text-gray-900">
                                        {{ $taskAssignment->maintenanceRequest->description_of_issue ?? 'No description provided' }}
                                    </dd>
                                </div>
                                <div>
                                    <dt class="text-sm font-medium text-gray-500">Store</dt>
                                    <dd class="mt-1 text-sm text-gray-900">
                                        @if($taskAssignment->maintenanceRequest->store)
                                            <div class="flex items-center">
                                                <svg class="w-4 h-4 mr-2 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                                                </svg>
                                                {{ $taskAssignment->maintenanceRequest->store->name }}
                                                @if($taskAssignment->maintenanceRequest->store->store_number)
                                                    <span class="ml-2 text-xs text-gray-500">({{ $taskAssignment->maintenanceRequest->store->store_number }})</span>
                                                @endif
                                            </div>
                                        @else
                                            <span class="text-gray-500">No store assigned</span>
                                        @endif
                                    </dd>
                                </div>
                                @if($taskAssignment->maintenanceRequest->urgencyLevel)
                                    <div>
                                        <dt class="text-sm font-medium text-gray-500">Urgency Level</dt>
                                        <dd class="mt-1 text-sm text-gray-900">
                                            <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-red-100 text-red-800">
                                                {{ $taskAssignment->maintenanceRequest->urgencyLevel->name }}
                                            </span>
                                        </dd>
                                    </div>
                                @endif
                            </dl>
                        @else
                            <p class="text-gray-500 italic">No maintenance request associated with this assignment.</p>
                        @endif
                    </div>
                </div>

                <!-- Assignment Notes -->
                @if($taskAssignment->assignment_notes)
                    <div class="bg-white shadow rounded-lg overflow-hidden">
                        <div class="px-6 py-4 border-b border-gray-200">
                            <h3 class="text-lg leading-6 font-medium text-gray-900">Assignment Notes</h3>
                        </div>
                        <div class="px-6 py-4">
                            <div class="text-sm text-gray-900 whitespace-pre-wrap">{{ $taskAssignment->assignment_notes }}</div>
                        </div>
                    </div>
                @endif
            </div>

            <!-- Sidebar -->
            <div class="space-y-6">
                <!-- Assigned Employee -->
                <div class="bg-white shadow rounded-lg overflow-hidden">
                    <div class="px-6 py-4 border-b border-gray-200">
                        <h3 class="text-lg leading-6 font-medium text-gray-900">Assigned Employee</h3>
                    </div>
                    <div class="px-6 py-4">
                        @if($taskAssignment->assignedUser)
                            <div class="flex items-center">
                                <div class="flex-shrink-0 h-12 w-12">
                                    <div class="h-12 w-12 rounded-full bg-orange-100 flex items-center justify-center">
                                        <span class="text-lg font-medium text-orange-800">
                                            {{ strtoupper(substr($taskAssignment->assignedUser->name, 0, 1)) }}
                                        </span>
                                    </div>
                                </div>
                                <div class="ml-4">
                                    <div class="text-sm font-medium text-gray-900">{{ $taskAssignment->assignedUser->name }}</div>
                                    <div class="text-sm text-gray-500">{{ ucfirst($taskAssignment->assignedUser->role ?? 'Employee') }}</div>
                                    @if($taskAssignment->assignedUser->email)
                                        <div class="text-sm text-gray-500">{{ $taskAssignment->assignedUser->email }}</div>
                                    @endif
                                </div>
                            </div>
                        @else
                            <p class="text-gray-500 italic">No employee assigned</p>
                        @endif
                    </div>
                </div>

                <!-- Schedule Information -->
                @if($taskAssignment->scheduleShift)
                    <div class="bg-white shadow rounded-lg overflow-hidden">
                        <div class="px-6 py-4 border-b border-gray-200">
                            <h3 class="text-lg leading-6 font-medium text-gray-900">Schedule Details</h3>
                        </div>
                        <div class="px-6 py-4">
                            <dl class="space-y-3">
                                <div>
                                    <dt class="text-sm font-medium text-gray-500">Date</dt>
                                    <dd class="mt-1 text-sm text-gray-900">
                                        {{ \Carbon\Carbon::parse($taskAssignment->scheduleShift->date)->format('l, M d, Y') }}
                                    </dd>
                                </div>
                                <div>
                                    <dt class="text-sm font-medium text-gray-500">Shift Time</dt>
                                    <dd class="mt-1 text-sm text-gray-900">
                                        {{ $taskAssignment->scheduleShift->start_time }} - {{ $taskAssignment->scheduleShift->end_time }}
                                    </dd>
                                </div>
                                @if($taskAssignment->scheduleShift->shift_type)
                                    <div>
                                        <dt class="text-sm font-medium text-gray-500">Shift Type</dt>
                                        <dd class="mt-1 text-sm text-gray-900">{{ ucfirst($taskAssignment->scheduleShift->shift_type) }}</dd>
                                    </div>
                                @endif
                                @if($taskAssignment->scheduleShift->role)
                                    <div>
                                        <dt class="text-sm font-medium text-gray-500">Role</dt>
                                        <dd class="mt-1 text-sm text-gray-900">{{ ucfirst($taskAssignment->scheduleShift->role) }}</dd>
                                    </div>
                                @endif
                            </dl>
                        </div>
                    </div>
                @endif

                <!-- Assignment History -->
                <div class="bg-white shadow rounded-lg overflow-hidden">
                    <div class="px-6 py-4 border-b border-gray-200">
                        <h3 class="text-lg leading-6 font-medium text-gray-900">Assignment History</h3>
                    </div>
                    <div class="px-6 py-4">
                        <div class="flow-root">
                            <ul class="-mb-8">
                                <li>
                                    <div class="relative pb-8">
                                        <div class="relative flex space-x-3">
                                            <div>
                                                <span class="h-8 w-8 rounded-full bg-blue-500 flex items-center justify-center ring-8 ring-white">
                                                    <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                                                    </svg>
                                                </span>
                                            </div>
                                            <div class="min-w-0 flex-1 pt-1.5 flex justify-between space-x-4">
                                                <div>
                                                    <p class="text-sm text-gray-500">Task assigned
                                                        @if($taskAssignment->assignedBy)
                                                            by <span class="font-medium text-gray-900">{{ $taskAssignment->assignedBy->name }}</span>
                                                        @endif
                                                    </p>
                                                </div>
                                                <div class="text-right text-sm whitespace-nowrap text-gray-500">
                                                    {{ $taskAssignment->created_at->format('M d, Y') }}
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>

                <!-- Quick Actions -->
                <div class="bg-white shadow rounded-lg overflow-hidden">
                    <div class="px-6 py-4 border-b border-gray-200">
                        <h3 class="text-lg leading-6 font-medium text-gray-900">Quick Actions</h3>
                    </div>
                    <div class="px-6 py-4 space-y-3">
                        @if($taskAssignment->status !== 'completed')
                            <form action="{{ route('admin.task-assignments.update-status', $taskAssignment) }}" method="POST">
                                @csrf
                                @method('PATCH')
                                <input type="hidden" name="status" value="completed">
                                <button type="submit" class="w-full inline-flex justify-center items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500">
                                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                    </svg>
                                    Mark as Completed
                                </button>
                            </form>
                        @endif

                        @if($taskAssignment->status === 'pending')
                            <form action="{{ route('admin.task-assignments.update-status', $taskAssignment) }}" method="POST">
                                @csrf
                                @method('PATCH')
                                <input type="hidden" name="status" value="in_progress">
                                <button type="submit" class="w-full inline-flex justify-center items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                    </svg>
                                    Start Task
                                </button>
                            </form>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('styles')
    <style>
        .whitespace-pre-wrap {
            white-space: pre-wrap;
        }
    </style>
@endpush
