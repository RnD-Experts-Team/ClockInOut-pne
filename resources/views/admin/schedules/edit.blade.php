@extends('layouts.app')

@section('title', 'Edit Schedule')

@section('content')
    <div class="max-w-full mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <!-- Header Section -->
        <div class="sm:flex sm:items-center sm:justify-between mb-6">
            <div>
                <h1 class="text-3xl font-bold text-gray-900">Edit Schedule</h1>
                <p class="mt-2 text-sm text-gray-600">Update employee schedules with task assignments</p>
            </div>
            <div class="flex flex-wrap items-center gap-3 mt-4 sm:mt-0">
                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                    @if($schedule->status === 'draft') bg-blue-100 text-blue-800
                    @elseif($schedule->status === 'published') bg-green-100 text-green-800
                    @elseif($schedule->status === 'active') bg-orange-100 text-orange-800
                    @else bg-gray-100 text-gray-800 @endif">
                    {{ ucfirst($schedule->status) }}
                </span>
                <a href="{{ route('admin.schedules.index') }}"
                   class="inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-lg text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-orange-500 transition-all duration-300">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                    </svg>
                    Back to Schedules
                </a>
            </div>
        </div>

        <!-- Schedule Form Panel -->
        <div class="bg-white shadow rounded-lg overflow-hidden">
            <div class="p-6">
                <form id="schedule-form" action="{{ route('admin.schedules.update', $schedule) }}" method="POST">
                    @csrf
                    @method('PUT')
                    <input type="hidden" name="schedule_data" id="schedule_data">
                    <input type="hidden" id="deleted-shift-ids" name="deleted_shift_ids">
                    <input type="hidden" name="start_date" id="start_date" value="{{ $schedule->start_date->format('Y-m-d') }}">
                    <input type="hidden" name="end_date" id="end_date" value="{{ $schedule->end_date->format('Y-m-d') }}">
                    <input type="hidden" name="name" id="schedule_name" value="{{ $schedule->name }}">

                    <!-- Controls Section -->
                    <div class="bg-gray-50 rounded-lg p-6 mb-6 border border-gray-200">
                        <div class="flex flex-wrap items-center gap-4 justify-between">
                            <!-- Week Display (Read-only for edit) -->
                            <div class="flex items-center bg-white border border-gray-300 rounded-lg overflow-hidden shadow-sm">
                                <div class="px-6 py-2 font-medium text-gray-900 border-r border-gray-200" id="week-display">
                                    Week: {{ $startDate->format('M d') }} - {{ $endDate->format('M d, Y') }}
                                </div>
                            </div>

                            <!-- Custom Alert Modal -->
                            <div class="fixed inset-0 z-[60] overflow-y-auto hidden" id="customAlertModal" aria-labelledby="alert-modal-title" role="dialog" aria-modal="true">
                                <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                                    <div class="fixed bg-opacity-75 transition-opacity" id="alert-modal-backdrop"></div>
                                    <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

                                    <div class="inline-block align-bottom bg-white rounded-lg px-6 pt-5 pb-4 text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full sm:p-6">
                                        <div class="sm:flex sm:items-start">
                                            <div class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full sm:mx-0 sm:h-10 sm:w-10" id="alert-icon-container">
                                                <!-- Icon will be inserted here -->
                                            </div>
                                            <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left w-full">
                                                <h3 class="text-lg leading-6 font-medium text-gray-900" id="alert-modal-title">
                                                    <!-- Title will be inserted here -->
                                                </h3>
                                                <div class="mt-2">
                                                    <p class="text-sm text-gray-500" id="alert-modal-message">
                                                        <!-- Message will be inserted here -->
                                                    </p>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="mt-5 sm:mt-4 sm:flex sm:flex-row-reverse" id="alert-modal-buttons">
                                            <!-- Buttons will be inserted here -->
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Action Buttons -->
                            <div class="flex items-center gap-3">
                                <button type="button" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-lg text-white bg-orange-600 hover:bg-orange-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-orange-500 transition-all duration-300" id="update-schedule">
                                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4"></path>
                                    </svg>
                                    Update Schedule
                                </button>

                                @if($schedule->status === 'draft')
                                    <button type="button" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-lg text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 transition-all duration-300" id="update-and-publish">
                                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"></path>
                                        </svg>
                                        Update & Publish
                                    </button>
                                @endif
                            </div>
                        </div>

                        <!-- Search and Filter Section -->
                        <div class="mt-4 grid grid-cols-1 md:grid-cols-3 gap-4">
                            <!-- Employee Search -->
                            <div>
                                <div class="relative">
                                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                        <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                                        </svg>
                                    </div>
                                    <input type="text" id="employee-search" class="block w-full pl-10 pr-3 py-2 border border-gray-300 rounded-lg leading-5 bg-white placeholder-gray-500 focus:outline-none focus:placeholder-gray-400 focus:ring-1 focus:ring-orange-500 focus:border-orange-500" placeholder="Search employees by name...">
                                </div>
                            </div>

                            <!-- Role Filter Dropdown -->
                            <div>
                                <div class="relative">
                                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                        <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                                        </svg>
                                    </div>
                                    <select id="role-filter" class="block w-full pl-10 pr-3 py-2 border border-gray-300 rounded-lg leading-5 bg-white focus:outline-none focus:ring-1 focus:ring-orange-500 focus:border-orange-500">
                                        <option value="">All Roles</option>
                                        <option value="admin">Admin</option>
                                        <option value="user">User</option>
                                        @foreach($userRoles as $role)
                                            @if(!in_array(strtolower($role), ['admin', 'user']))
                                                <option value="{{ strtolower($role) }}">{{ ucfirst($role) }}</option>
                                            @endif
                                        @endforeach
                                    </select>
                                </div>
                            </div>

                            <!-- Empty third column for consistency -->
                            <div></div>
                        </div>

                        <!-- Filter Results and Clear Button -->
                        <div class="mt-3 flex justify-between items-center">
                            <div class="text-sm text-gray-500" id="filter-results">
                                Showing all employees
                            </div>
                            <div class="flex items-center space-x-2">
                                <button type="button" id="clear-filters" class="text-sm text-orange-600 hover:text-orange-800 font-medium">
                                    Clear Filters
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Schedule Grid -->
                    <div class="bg-white border border-gray-200 rounded-lg overflow-hidden shadow-sm">
                        <!-- Grid Header -->
                        <div class="grid grid-cols-8 bg-gray-50 border-b border-gray-200" id="grid-header">
                            <div class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                <div class="flex items-center">
                                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                                    </svg>
                                    Employee
                                </div>
                            </div>
                            @foreach ($currentWeek as $date)
                                <div class="px-6 py-4 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    <div class="flex flex-col items-center">
                                        <span class="text-sm font-semibold text-gray-900">{{ $date->format('D') }}</span>
                                        <span class="text-xs text-gray-500">{{ $date->format('m/d') }}</span>
                                    </div>
                                </div>
                            @endforeach
                        </div>

                        <!-- Grid Body -->
                        <div class="divide-y divide-gray-200" id="grid-body">
                            @foreach ($employees as $employee)
                                <div class="grid grid-cols-8 hover:bg-gray-50 transition-colors duration-150 employee-row"
                                     data-employee-name="{{ strtolower($employee->name) }}"
                                     data-employee-role="{{ strtolower($employee->role ?? 'employee') }}">
                                    <!-- Employee Info -->
                                    <div class="px-6 py-4 whitespace-nowrap">
                                        <div class="flex items-center">
                                            <div class="flex-shrink-0 h-10 w-10">
                                                <div class="h-10 w-10 rounded-full bg-orange-100 flex items-center justify-center">
                                                    <span class="text-sm font-medium text-orange-800">
                                                        {{ strtoupper(substr($employee->name, 0, 1)) }}
                                                    </span>
                                                </div>
                                            </div>
                                            <div class="ml-4">
                                                <div class="text-sm font-medium text-gray-900">{{ $employee->name }}</div>
                                                <div class="text-sm text-gray-500">{{ ucfirst($employee->role ?? 'Employee') }}</div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Schedule Cells -->
                                    @foreach ($currentWeek as $date)
                                        <div class="px-2 py-4 text-center">
                                            <div class="schedule-cell min-h-[80px] border-2 border-dashed border-gray-300 rounded-lg cursor-pointer hover:border-orange-400 hover:bg-orange-50 transition-all duration-200 flex flex-col items-center justify-center"
                                                 data-employee="{{ $employee->id }}"
                                                 data-employee-name="{{ $employee->name }}"
                                                 data-date="{{ $date->format('Y-m-d') }}"
                                                 data-day="{{ $date->format('l') }}">
                                                <span class="text-xs text-gray-500 font-medium">Click to assign</span>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            @endforeach
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Enhanced Assignment Modal (Same as Create) -->
    <div class="fixed inset-0 z-50 overflow-y-auto hidden" id="assignmentModal" aria-labelledby="modal-title" role="dialog" aria-modal="true">
        <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <div class="fixed   bg-opacity-75 transition-opacity" id="modal-backdrop"></div>
            <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

            <div class="inline-block align-bottom bg-white rounded-lg px-6 pt-5 pb-4 text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-2xl sm:w-full sm:p-6">
                <!-- Modal header with orange accent -->
                <div class="absolute top-0 left-0 right-0 h-2 bg-orange-600"></div>

                <div class="absolute top-0 right-0 pt-4 pr-4">
                    <button type="button" class="bg-white rounded-md text-gray-400 hover:text-gray-600" id="close-modal">
                        <span class="sr-only">Close</span>
                        <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>

                <div class="sm:flex sm:items-start">
                    <!-- Icon with orange background -->
                    <div class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-orange-100 sm:mx-0 sm:h-10 sm:w-10">
                        <svg class="h-6 w-6 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>

                    <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left w-full">
                        <!-- Title with orange text -->
                        <h3 class="text-lg leading-6 font-medium text-orange-800" id="modal-title">
                            Assign Shift
                        </h3>
                        <p class="text-sm text-gray-500 mt-1" id="modal-subtitle"></p>

                        <div class="mt-6">
                            <input type="hidden" id="modal-employee-id">
                            <input type="hidden" id="modal-date">

                            <!-- Task Selection -->
                            <div class="mb-6">
                                <label for="task-select" class="block text-sm font-medium text-gray-700 mb-2">
                                    Assign Task <span class="text-red-500">*</span>
                                </label>
                                <select class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-orange-500 focus:ring-orange-500 sm:text-sm" id="task-select" required>
                                    <option value="">Select Task</option>
                                    @foreach($availableTasks as $task)
                                        <option value="{{ $task->id }}">
                                            {{ $task->store->name }} - {{ Str::limit($task->description_of_issue, 50) }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <!-- Time Inputs -->
                            <div class="grid grid-cols-2 gap-4 mb-6">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Start Time</label>
                                    <input type="time" id="start-time" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-orange-500 focus:ring-orange-500 sm:text-sm" value="08:00">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">End Time</label>
                                    <input type="time" id="end-time" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-orange-500 focus:ring-orange-500 sm:text-sm" value="17:00">
                                </div>
                            </div>

                            <!-- Shift Type Custom Dropdown -->
                            <div class="mb-4">
                                <label class="block text-sm font-medium text-gray-700 mb-2">Shift Type</label>
                                <div class="custom-select-container relative">
                                    <input type="hidden" id="shift-type" name="shift_type">
                                    <div class="custom-select-input relative">
                                        <input type="text" id="custom_shift_type"
                                               class="block w-full pr-10 border-gray-300 rounded-md shadow-sm focus:border-orange-500 focus:ring-orange-500 sm:text-sm"
                                               placeholder="Select or type shift type..."
                                               autocomplete="off">
                                        <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none">
                                            <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                                            </svg>
                                        </div>
                                    </div>
                                    <div class="custom-select-dropdown absolute z-50 w-full mt-1 bg-white border border-gray-300 rounded-md shadow-lg max-h-60 overflow-auto hidden">
                                        <div class="py-1" id="shift-type-options"></div>
                                    </div>
                                </div>
                            </div>

                            <!-- Role Custom Dropdown -->
                            <div class="mb-6">
                                <label class="block text-sm font-medium text-gray-700 mb-2">Role</label>
                                <div class="custom-select-container relative">
                                    <input type="hidden" id="role" name="role">
                                    <div class="custom-select-input relative">
                                        <input type="text" id="custom_role"
                                               class="block w-full pr-10 border-gray-300 rounded-md shadow-sm focus:border-orange-500 focus:ring-orange-500 sm:text-sm"
                                               placeholder="Select or type role..."
                                               autocomplete="off">
                                        <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none">
                                            <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                                            </svg>
                                        </div>
                                    </div>
                                    <div class="custom-select-dropdown absolute z-50 w-full mt-1 bg-white border border-gray-300 rounded-md shadow-lg max-h-60 overflow-auto hidden">
                                        <div class="py-1" id="role-options"></div>
                                    </div>
                                </div>
                            </div>

                            <!-- Assignment Notes -->
                            <div class="mt-6">
                                <label class="block text-sm font-medium text-gray-700 mb-2">Assignment Notes</label>
                                <textarea id="assignment-notes"
                                          rows="3"
                                          class="block w-full rounded-md border-gray-300 shadow-sm focus:border-orange-500 focus:ring-orange-500 sm:text-sm"
                                          placeholder="Add any special instructions or notes for this shift..."></textarea>
                                <p class="mt-1 text-xs text-gray-500">Optional: Add any special instructions, requirements, or notes for this shift assignment.</p>
                            </div>

                            <!-- Multiple Shifts Section -->
                            <div class="border-t border-gray-200 pt-4">
                                <div class="flex items-center justify-between mb-4">
                                    <h4 class="text-sm font-medium text-gray-700">Additional Shifts</h4>
                                    <button type="button" class="text-sm text-orange-600 hover:text-orange-800 font-medium" id="add-another-shift">
                                        + Add Another Shift
                                    </button>
                                </div>

                                <!-- Additional Shifts Container -->
                                <div id="additional-shifts-container" class="space-y-3">
                                    <!-- Additional shifts will be added here -->
                                </div>
                            </div>

                            <!-- Shift Color -->
                            <div class="mt-6">
                                <label class="block text-sm font-medium text-gray-700 mb-2">Shift Color</label>
                                <input type="color" id="color-picker" class="h-10 w-16 rounded border border-gray-300 cursor-pointer" value="#3b82f6">
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Footer buttons -->
                <div class="mt-6 sm:mt-4 sm:flex sm:flex-row-reverse">
                    <button type="button" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-orange-600 text-base font-medium text-white hover:bg-orange-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-orange-500 sm:ml-3 sm:w-auto sm:text-sm" id="save-shift">
                        Save Shift
                    </button>
                    <button type="button" class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-orange-500 sm:mt-0 sm:w-auto sm:text-sm" id="cancel-modal">
                        Cancel
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Hidden Template for Additional Shifts -->
    <div id="additional-shift-template" class="hidden additional-shift" data-shift-index="0">
        <div class="flex items-center justify-between mb-3">
            <span class="text-sm font-semibold text-gray-800">Additional Shift 0</span>
            <button type="button" class="text-red-600 hover:text-red-800 remove-additional-shift">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
            </button>
        </div>
        <div class="mb-4">
            <label class="block text-sm font-medium text-gray-700 mb-2">Assign Task <span class="text-red-500">*</span></label>
            <select class="additional-task block w-full rounded-md border-gray-300 shadow-sm focus:border-orange-500 focus:ring-orange-500 sm:text-sm" required>
                <option value="">Select Task</option>
                @foreach($availableTasks as $task)
                    <option value="{{ $task->id }}">{{ $task->store->name }} - {{ Str::limit($task->description_of_issue, 50) }}</option>
                @endforeach
            </select>
        </div>
        <div class="grid grid-cols-2 gap-3 mb-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Start Time</label>
                <input type="time" class="additional-start block w-full rounded-md border-gray-300 shadow-sm focus:border-orange-500 focus:ring-orange-500 sm:text-sm" value="08:00">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">End Time</label>
                <input type="time" class="additional-end block w-full rounded-md border-gray-300 shadow-sm focus:border-orange-500 focus:ring-orange-500 sm:text-sm" value="17:00">
            </div>
        </div>
        <div class="mb-4">
            <label class="block text-sm font-medium text-gray-700 mb-2">Shift Type</label>
            <div class="custom-select-container relative">
                <input type="hidden" class="additional-shift-type" name="additional_shift_type_0">
                <div class="custom-select-input relative">
                    <input type="text" class="additional-custom-shift-type block w-full pr-10 border-gray-300 rounded-md shadow-sm focus:border-orange-500 focus:ring-orange-500 sm:text-sm" placeholder="Select or type shift type..." autocomplete="off">
                    <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none">
                        <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                    </div>
                </div>
                <div class="custom-select-dropdown absolute z-50 w-full mt-1 bg-white border border-gray-300 rounded-md shadow-lg max-h-60 overflow-auto hidden">
                    <div class="py-1 additional-shift-type-options"></div>
                </div>
            </div>
        </div>
        <div class="mb-4">
            <label class="block text-sm font-medium text-gray-700 mb-2">Role</label>
            <div class="custom-select-container relative">
                <input type="hidden" class="additional-role" name="additional_role_0">
                <div class="custom-select-input relative">
                    <input type="text" class="additional-custom-role block w-full pr-10 border-gray-300 rounded-md shadow-sm focus:border-orange-500 focus:ring-orange-500 sm:text-sm" placeholder="Select or type role..." autocomplete="off">
                    <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none">
                        <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                    </div>
                </div>
                <div class="custom-select-dropdown absolute z-50 w-full mt-1 bg-white border border-gray-300 rounded-md shadow-lg max-h-60 overflow-auto hidden">
                    <div class="py-1 additional-role-options"></div>
                </div>
            </div>
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Shift Color</label>
            <input type="color" class="additional-color h-8 w-12 rounded border border-gray-300 cursor-pointer" value="#3b82f6">
        </div>
    </div>
@endsection

<style>
    .schedule-cell.has-assignment {
        border: 2px solid #059669;
        background: linear-gradient(135deg, #10b981, #047857);
        color: white;
        font-weight: 500;
        position: relative;
    }

    .schedule-cell.has-assignment:hover {
        border-color: #047857;
        background: linear-gradient(135deg, #059669, #065f46);
    }

    .shift-content {
        text-align: center;
        width: 100%;
        padding: 4px;
    }

    .shift-time {
        font-weight: 600;
        margin-bottom: 2px;
        font-size: 10px;
    }

    .shift-role {
        font-size: 8px;
        opacity: 0.9;
        margin-bottom: 1px;
        text-transform: uppercase;
    }

    .shift-hours {
        font-size: 8px;
        opacity: 0.8;
        font-weight: 500;
    }

    .task-badge {
        position: absolute;
        top: 2px;
        right: 2px;
        background: rgba(255, 255, 255, 0.3);
        color: white;
        font-size: 7px;
        padding: 1px 3px;
        border-radius: 2px;
        font-weight: 600;
    }

    @media (max-width: 768px) {
        .grid-cols-8 {
            display: block;
        }

        .schedule-cell {
            min-height: 60px;
        }
    }
</style>

{{--<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>--}}
<script>
    document.addEventListener('DOMContentLoaded', function() {
        let deletedShiftIds = new Set(); // Track deleted shift IDs
        let shiftCounter = 0; // For new temporary shifts
        let additionalShiftCounter = 0; // For additional shifts in modal

        // ✅ Initialize with existing schedule data
        window.scheduleData = @json($existingScheduleData ?? []);
        let assignedTasks = new Set();
        let shiftTypes = @json($shiftTypes ?? []);
        let scheduleRoles = @json($scheduleRoles ?? []);

        // ✅ Debug: Check if data is loaded
        console.log('✅ Loaded existing schedule data:', window.scheduleData);
        console.log('✅ Keys in schedule data:', Object.keys(window.scheduleData));

        // If no data, there's an issue with the controller
        if (Object.keys(window.scheduleData).length === 0) {
            console.error('❌ No existing schedule data found!');
        }

        // Setup custom dropdowns for main modal
        setupCustomDropdown('#custom_shift_type', '#shift-type', '#shift-type-options', shiftTypes);
        setupCustomDropdown('#custom_role', '#role', '#role-options', scheduleRoles);

        // ✅ Pre-populate existing shifts on load
        setTimeout(() => {
            prePopulateScheduleGrid();
            attachModalCloseHandlers(); // FIXED: Attach modal close handlers after DOM is ready
        }, 300);

        function prePopulateScheduleGrid() {
            console.log('🎨 Pre-populating schedule grid...');
            Object.entries(window.scheduleData).forEach(([key, shifts]) => {
                const [employeeId, dateWithTime] = key.split('_');
                const date = dateWithTime.split(' ')[0];
                console.log(`📅 Loading shifts for employee ${employeeId} on ${date}:`, shifts);
                updateScheduleCell(employeeId, date, shifts);
                shifts.forEach(shift => {
                    if (shift.task_id) assignedTasks.add(shift.task_id);
                });
            });
            console.log('✅ Grid pre-population complete!');
        }

        // FIXED: Modal close handlers function
        function attachModalCloseHandlers() {
            // Use event delegation for robust modal closing
            document.addEventListener('click', function(e) {
                // Handle close modal button (X button)
                if (e.target.id === 'close-modal' ||
                    e.target.closest('#close-modal') ||
                    e.target.matches('[data-dismiss="modal"]') ||
                    e.target.closest('[data-dismiss="modal"]')) {
                    e.preventDefault();
                    e.stopPropagation();
                    closeModal();
                }

                // Handle cancel modal button
                if (e.target.id === 'cancel-modal' ||
                    e.target.closest('#cancel-modal')) {
                    e.preventDefault();
                    e.stopPropagation();
                    closeModal();
                }

                // Handle modal backdrop click
                if (e.target.id === 'modal-backdrop' ||
                    e.target.classList.contains('modal-backdrop')) {
                    e.preventDefault();
                    e.stopPropagation();
                    closeModal();
                }
            });

            // Handle ESC key to close modal
            document.addEventListener('keydown', function(e) {
                if (e.key === 'Escape') {
                    closeModal();
                }
            });

            // Direct event listeners as backup
            const closeModalBtn = document.getElementById('close-modal');
            const cancelModalBtn = document.getElementById('cancel-modal');
            const modalBackdrop = document.getElementById('modal-backdrop');

            if (closeModalBtn) {
                closeModalBtn.addEventListener('click', function(e) {
                    e.preventDefault();
                    e.stopPropagation();
                    closeModal();
                });
            }

            if (cancelModalBtn) {
                cancelModalBtn.addEventListener('click', function(e) {
                    e.preventDefault();
                    e.stopPropagation();
                    closeModal();
                });
            }

            if (modalBackdrop) {
                modalBackdrop.addEventListener('click', function(e) {
                    if (e.target === modalBackdrop) {
                        e.preventDefault();
                        e.stopPropagation();
                        closeModal();
                    }
                });
            }
        }

        // FIXED: Centralized modal close function
        function closeModal() {
            const modal = document.getElementById('assignmentModal');
            if (modal) {
                modal.classList.add('hidden');
                console.log('✅ Modal closed');
            }
        }

        function updateScheduleCell(employeeId, date, shifts) {
            const cell = document.querySelector(`.schedule-cell[data-employee="${employeeId}"][data-date="${date}"]`);
            console.log(`🎯 Updating cell for employee ${employeeId} on ${date}`, cell ? 1 : 0);

            if (!cell) {
                console.error(`❌ Cell not found for employee ${employeeId} on ${date}`);
                return;
            }

            if (shifts.length > 0) {
                const mainShift = shifts[0];
                const totalHours = shifts.reduce((total, shift) => {
                    const start = new Date(`2000-01-01 ${shift.start}`);
                    const end = new Date(`2000-01-01 ${shift.end}`);
                    return total + (end - start) / (1000 * 60 * 60);
                }, 0);

                let splitIndicator = shifts.length > 1 ? '<div class="text-xs bg-yellow-200 text-yellow-800 px-1 rounded mt-1">SPLIT</div>' : '';
                const hasNotes = shifts.some(shift => shift.assignment_notes && shift.assignment_notes.trim() !== '');
                let notesIndicator = hasNotes ? '<div class="text-xs bg-blue-200 text-blue-800 px-1 rounded mt-1">NOTES</div>' : '';

                cell.classList.remove('border-dashed', 'border-gray-300');
                cell.classList.add('has-assignment');
                cell.style.backgroundColor = mainShift.color;
                cell.innerHTML = `
                    <div class="shift-content p-2 relative text-center">
                        <div class="shift-time text-xs font-semibold">${mainShift.start} - ${mainShift.end}</div>
                        <div class="shift-role text-xs opacity-90">${mainShift.role}</div>
                        <div class="shift-hours text-xs opacity-80">${totalHours.toFixed(1)}h</div>
                        <div class="task-badge absolute top-1 right-1 bg-white bg-opacity-30 text-xs px-1 rounded">T</div>
                        <button type="button" class="delete-shift absolute top-1 right-6 bg-white bg-opacity-30 text-xs px-1 rounded hover:bg-red-200 hover:text-red-800 focus:outline-none"
                                data-shift-id="${mainShift.id || `temp_${shiftCounter++}`}"
                                data-task-id="${mainShift.task_id}"
                                data-employee="${employeeId}"
                                data-date="${date}"
                                data-shift-start="${mainShift.start}"
                                data-shift-end="${mainShift.end}">
                            <span class="sr-only">Delete Shift</span>
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                        ${splitIndicator}
                        ${notesIndicator}
                    </div>
                `;
                cell.style.color = getContrastColor(mainShift.color);

                console.log(`✅ Updated cell for employee ${employeeId} on ${date} with shift ID ${mainShift.id}`);
            }
        }

        function getContrastColor(hexcolor) {
            hexcolor = hexcolor.replace('#', '');
            const r = parseInt(hexcolor.substr(0, 2), 16);
            const g = parseInt(hexcolor.substr(2, 2), 16);
            const b = parseInt(hexcolor.substr(4, 2), 16);
            const brightness = ((r * 299) + (g * 587) + (b * 114)) / 1000;
            return brightness > 128 ? '#000000' : '#ffffff';
        }

        function setupCustomDropdown(inputSelector, hiddenSelector, optionsContainerId, options) {
            const input = document.querySelector(inputSelector);
            const hidden = document.querySelector(hiddenSelector);
            const optionsContainer = document.querySelector(optionsContainerId);

            if (!input || !hidden || !optionsContainer) return;

            function handleInputFocus() {
                const searchTerm = input.value.toLowerCase();
                optionsContainer.innerHTML = '';
                optionsContainer.style.display = 'block';

                options.filter(opt => opt.toLowerCase().includes(searchTerm)).forEach(opt => {
                    const optionDiv = document.createElement('div');
                    optionDiv.textContent = opt;
                    optionDiv.className = 'px-3 py-2 cursor-pointer hover:bg-orange-50';
                    optionDiv.addEventListener('click', () => {
                        input.value = opt;
                        hidden.value = opt;
                        optionsContainer.parentElement.style.display = 'none';
                    });
                    optionsContainer.appendChild(optionDiv);
                });

                if (searchTerm && !options.includes(searchTerm)) {
                    const addNewDiv = document.createElement('div');
                    addNewDiv.innerHTML = `<strong>+ Add:</strong> "${searchTerm}"`;
                    addNewDiv.className = 'px-3 py-2 text-orange-600 cursor-pointer hover:bg-orange-50 border-t border-gray-200';
                    addNewDiv.addEventListener('click', () => {
                        options.push(searchTerm);
                        input.value = searchTerm;
                        hidden.value = searchTerm;
                        optionsContainer.parentElement.style.display = 'none';
                    });
                    optionsContainer.appendChild(addNewDiv);
                }
                optionsContainer.parentElement.style.display = input.value ? 'block' : 'none';
            }

            input.addEventListener('focus', handleInputFocus);
            input.addEventListener('input', handleInputFocus);

            document.addEventListener('click', e => {
                if (!input.contains(e.target) && !optionsContainer.parentElement.contains(e.target)) {
                    optionsContainer.parentElement.style.display = 'none';
                }
            });
        }

        // Enhanced Employee Search and Role Filter
        const employeeSearch = document.getElementById('employee-search');
        const roleFilter = document.getElementById('role-filter');

        if (employeeSearch) employeeSearch.addEventListener('input', filterEmployees);
        if (roleFilter) roleFilter.addEventListener('change', filterEmployees);

        function filterEmployees() {
            const searchTerm = employeeSearch ? employeeSearch.value.toLowerCase() : '';
            const selectedRole = roleFilter ? roleFilter.value.toLowerCase() : '';

            document.querySelectorAll('.employee-row').forEach(function(row) {
                const employeeName = row.dataset.employeeName;
                const employeeRole = row.dataset.employeeRole;
                let showRow = true;

                if (searchTerm && !employeeName.includes(searchTerm)) showRow = false;
                if (selectedRole && employeeRole !== selectedRole) showRow = false;

                row.style.display = showRow ? '' : 'none';
            });

            const visibleEmployees = document.querySelectorAll('.employee-row:not([style*="display: none"])').length;
            const totalEmployees = document.querySelectorAll('.employee-row').length;
            let resultText = `Showing ${visibleEmployees} of ${totalEmployees} employees`;
            if (searchTerm || selectedRole) resultText += ' (filtered)';
            const filterResults = document.getElementById('filter-results');
            if (filterResults) filterResults.textContent = resultText;
        }

        const clearFiltersBtn = document.getElementById('clear-filters');
        if (clearFiltersBtn) {
            clearFiltersBtn.addEventListener('click', function() {
                if (employeeSearch) employeeSearch.value = '';
                if (roleFilter) roleFilter.value = '';
                document.querySelectorAll('.employee-row').forEach(row => {
                    row.style.display = '';
                });
                const filterResults = document.getElementById('filter-results');
                if (filterResults) filterResults.textContent = 'Showing all employees';
            });
        }

        // Add another shift - FIXED with assignment notes
        const addAnotherShiftBtn = document.getElementById('add-another-shift');
        if (addAnotherShiftBtn) {
            addAnotherShiftBtn.addEventListener('click', function() {
                additionalShiftCounter++;
                const start = document.getElementById('start-time').value;
                const end = document.getElementById('end-time').value;
                const shiftType = document.getElementById('shift-type').value || 'regular';
                const role = document.getElementById('role').value || 'general';
                const color = document.getElementById('color-picker').value || '#f97316';
                const taskId = document.getElementById('task-select').value;
                const assignmentNotes = document.getElementById('assignment-notes').value;

                if (!start || !end || start >= end) {
                    showAlert('Invalid Shift', 'End time must be after start time.', 'error');
                    return;
                }
                if (!taskId) {
                    showAlert('Task Required', 'Please select a task for the shift.', 'error');
                    return;
                }

                const shiftId = `temp_${shiftCounter++}`;
                const template = document.getElementById('additional-shift-template');
                if (!template) {
                    console.error('Additional shift template not found');
                    return;
                }

                const templateClone = template.cloneNode(true);
                templateClone.classList.remove('hidden');
                templateClone.removeAttribute('id');
                templateClone.setAttribute('data-shift-index', additionalShiftCounter);

                templateClone.querySelector('.additional-task').value = taskId;
                templateClone.querySelector('.additional-start').value = start;
                templateClone.querySelector('.additional-end').value = end;
                templateClone.querySelector('.additional-custom-shift-type').value = shiftType;
                templateClone.querySelector('.additional-shift-type').value = shiftType;
                templateClone.querySelector('.additional-custom-role').value = role;
                templateClone.querySelector('.additional-role').value = role;
                templateClone.querySelector('.additional-color').value = color;
                templateClone.querySelector('.additional-notes').value = assignmentNotes;
                templateClone.querySelector('.additional-shift span').textContent = `Additional Shift ${additionalShiftCounter}`;

                document.getElementById('additional-shifts-container').appendChild(templateClone);
                assignedTasks.add(taskId);
                setupAdditionalShiftDropdowns(templateClone);

                // Clear form fields - INCLUDING NOTES
                document.getElementById('start-time').value = '08:00';
                document.getElementById('end-time').value = '17:00';
                document.getElementById('shift-type').value = '';
                document.getElementById('role').value = '';
                document.getElementById('color-picker').value = '#3b82f6';
                document.getElementById('task-select').value = '';
                document.getElementById('assignment-notes').value = '';

                filterAvailableTasks();
            });
        }

        // Remove additional shift
        document.addEventListener('click', function(e) {
            if (e.target.closest('.remove-additional-shift')) {
                const shift = e.target.closest('.additional-shift');
                const taskId = shift.querySelector('.additional-task').value;
                if (taskId) assignedTasks.delete(taskId);
                shift.remove();
            }
        });

        // Open modal with existing data - FIXED with assignment notes
        document.addEventListener('click', function(e) {
            if (e.target.closest('.schedule-cell')) {
                e.preventDefault();
                const cell = e.target.closest('.schedule-cell');
                const employeeId = cell.dataset.employee;
                const employeeName = cell.dataset.employeeName;
                const date = cell.dataset.date;
                const keyWithoutTime = `${employeeId}_${date}`;
                const keyWithTime = `${employeeId}_${date} 00:00:00`;
                let existingShifts = window.scheduleData[keyWithoutTime] || window.scheduleData[keyWithTime] || [];

                console.log('🔍 Modal Debug - Key without time:', keyWithoutTime);
                console.log('🔍 Modal Debug - Key with time:', keyWithTime);
                console.log('🔍 Modal Debug - Existing Shifts:', existingShifts);

                document.getElementById('modal-employee-id').value = employeeId;
                document.getElementById('modal-date').value = date;
                document.getElementById('modal-subtitle').textContent = `${employeeName} - ${date}`;

                const shiftContainer = document.getElementById('additional-shifts-container');
                shiftContainer.innerHTML = '';
                deletedShiftIds.clear();
                additionalShiftCounter = 0;

                if (existingShifts.length > 0) {
                    const mainShift = existingShifts[0];
                    console.log('🔍 Modal Debug - Main Shift:', mainShift);

                    const taskSelect = document.getElementById('task-select');
                    taskSelect.value = mainShift.task_id || '';
                    taskSelect.dispatchEvent(new Event('change'));

                    document.getElementById('start-time').value = mainShift.start || '08:00';
                    document.getElementById('end-time').value = mainShift.end || '17:00';
                    document.getElementById('custom_shift_type').value = mainShift.type || '';
                    document.getElementById('shift-type').value = mainShift.type || '';
                    document.getElementById('custom_role').value = mainShift.role || '';
                    document.getElementById('role').value = mainShift.role || '';
                    document.getElementById('color-picker').value = mainShift.color || '#3b82f6';
                    document.getElementById('assignment-notes').value = mainShift.assignment_notes || '';

                    if (existingShifts.length > 1) {
                        for (let i = 1; i < existingShifts.length; i++) {
                            const additionalShift = existingShifts[i];
                            if (addAnotherShiftBtn) addAnotherShiftBtn.click();
                            setTimeout(() => {
                                const lastAdditionalShift = document.querySelector('.additional-shift:last-child');
                                if (lastAdditionalShift) {
                                    lastAdditionalShift.querySelector('.additional-task').value = additionalShift.task_id;
                                    lastAdditionalShift.querySelector('.additional-start').value = additionalShift.start;
                                    lastAdditionalShift.querySelector('.additional-end').value = additionalShift.end;
                                    lastAdditionalShift.querySelector('.additional-custom-shift-type').value = additionalShift.type;
                                    lastAdditionalShift.querySelector('.additional-shift-type').value = additionalShift.type;
                                    lastAdditionalShift.querySelector('.additional-custom-role').value = additionalShift.role;
                                    lastAdditionalShift.querySelector('.additional-role').value = additionalShift.role;
                                    lastAdditionalShift.querySelector('.additional-color').value = additionalShift.color;
                                    lastAdditionalShift.querySelector('.additional-notes').value = additionalShift.assignment_notes || '';
                                }
                            }, 100);
                        }
                    }
                } else {
                    console.log('📝 No existing shifts found - clearing form');
                    document.getElementById('task-select').value = '';
                    document.getElementById('start-time').value = '08:00';
                    document.getElementById('end-time').value = '17:00';
                    document.getElementById('custom_shift_type').value = '';
                    document.getElementById('shift-type').value = '';
                    document.getElementById('custom_role').value = '';
                    document.getElementById('role').value = '';
                    document.getElementById('color-picker').value = '#3b82f6';
                    document.getElementById('assignment-notes').value = '';
                }

                filterAvailableTasks();
                document.getElementById('assignmentModal').classList.remove('hidden');
            }
        });

        // Delete shift - remains the same
        document.addEventListener('click', function(e) {
            if (e.target.closest('.delete-shift')) {
                e.stopPropagation();
                console.log('🔴 Delete Shift Clicked');

                const deleteButton = e.target.closest('.delete-shift');
                const shiftId = deleteButton.dataset.shiftId;
                const taskId = deleteButton.dataset.taskId;
                const employeeId = deleteButton.dataset.employee;
                const date = deleteButton.dataset.date;
                const shiftStart = deleteButton.dataset.shiftStart;
                const shiftEnd = deleteButton.dataset.shiftEnd;

                if (shiftId && !shiftId.toString().startsWith('temp_')) {
                    deletedShiftIds.add(parseInt(shiftId));
                }

                if (taskId) {
                    assignedTasks.delete(parseInt(taskId));
                }

                const possibleKeys = [`${employeeId}_${date}`, `${employeeId}_${date} 00:00:00`];
                let targetKey = null;
                for (const key of possibleKeys) {
                    if (window.scheduleData[key]) {
                        targetKey = key;
                        break;
                    }
                }

                if (targetKey && window.scheduleData[targetKey]) {
                    window.scheduleData[targetKey] = window.scheduleData[targetKey].filter(shift => {
                        if (shift.id && shiftId && !shiftId.toString().startsWith('temp_')) {
                            return shift.id != shiftId;
                        } else {
                            const timeMatch = shift.start === shiftStart && shift.end === shiftEnd;
                            const taskMatch = shift.task_id == taskId;
                            return !(timeMatch && taskMatch);
                        }
                    });

                    if (window.scheduleData[targetKey].length === 0) {
                        delete window.scheduleData[targetKey];
                    }

                    if (window.scheduleData[targetKey] && window.scheduleData[targetKey].length > 0) {
                        updateScheduleCell(employeeId, date, window.scheduleData[targetKey]);
                    } else {
                        const cell = deleteButton.closest('.schedule-cell');
                        cell.classList.remove('has-assignment');
                        cell.classList.add('border-dashed', 'border-gray-300');
                        cell.style.backgroundColor = '';
                        cell.style.color = '';
                        cell.innerHTML = '<span class="text-xs text-gray-500 font-medium">Click to assign</span>';
                    }
                }

                filterAvailableTasks();
            }
        });

        // Save shift - FIXED with assignment notes
        const saveShiftBtn = document.getElementById('save-shift');
        if (saveShiftBtn) {
            saveShiftBtn.addEventListener('click', function() {
                const employeeId = document.getElementById('modal-employee-id').value;
                const date = document.getElementById('modal-date').value;
                let shifts = [];

                const key = `${employeeId}_${date}`;
                const keyWithTime = `${employeeId}_${date} 00:00:00`;
                const existingShifts = window.scheduleData[key] || window.scheduleData[keyWithTime] || [];

                const mainShift = {
                    start: document.getElementById('start-time').value,
                    end: document.getElementById('end-time').value,
                    type: document.getElementById('shift-type').value || 'regular',
                    role: document.getElementById('role').value || 'general',
                    color: document.getElementById('color-picker').value || '#3b82f6',
                    task_id: document.getElementById('task-select').value,
                    assignment_notes: document.getElementById('assignment-notes').value
                };

                if (existingShifts.length > 0 && existingShifts[0].id) {
                    mainShift.id = existingShifts[0].id;
                }

                if (mainShift.task_id) shifts.push(mainShift);

                let additionalIndex = 1;
                document.querySelectorAll('#additional-shifts-container .additional-shift').forEach(function(shiftElement) {
                    const additionalShift = {
                        start: shiftElement.querySelector('.additional-start').value,
                        end: shiftElement.querySelector('.additional-end').value,
                        type: shiftElement.querySelector('.additional-shift-type').value || 'regular',
                        role: shiftElement.querySelector('.additional-role').value || 'general',
                        color: shiftElement.querySelector('.additional-color').value || '#3b82f6',
                        task_id: shiftElement.querySelector('.additional-task').value,
                        assignment_notes: shiftElement.querySelector('.additional-notes').value
                    };

                    if (existingShifts[additionalIndex] && existingShifts[additionalIndex].id) {
                        additionalShift.id = existingShifts[additionalIndex].id;
                    }

                    if (additionalShift.task_id) {
                        shifts.push(additionalShift);
                        additionalIndex++;
                    }
                });

                if (shifts.length === 0) {
                    showAlert('No Shifts', 'Please add at least one shift.', 'warning');
                    return;
                }

                const targetKey = window.scheduleData[key] ? key : keyWithTime;
                window.scheduleData[targetKey] = shifts;

                updateScheduleCell(employeeId, date, shifts);

                shifts.forEach(shift => {
                    if (shift.task_id) assignedTasks.add(parseInt(shift.task_id));
                });

                filterAvailableTasks();
                closeModal(); // FIXED: Use centralized close function
                showSuccessMessage('Shift updated locally. Click "Update Schedule" to save changes.');

                console.log('✅ Shift saved locally');
                console.log('📊 Current schedule data:', window.scheduleData);
            });
        }

        // Update schedule handler
        const updateScheduleBtn = document.getElementById('update-schedule');
        if (updateScheduleBtn) {
            updateScheduleBtn.addEventListener('click', function(e) {
                e.preventDefault();

                if (Object.keys(window.scheduleData).length === 0) {
                    showAlert('No Shifts Added', 'Please add some shifts before updating the schedule.', 'warning');
                    return;
                }

                const scheduleArray = Object.entries(window.scheduleData).map(([key, shifts]) => {
                    const [employeeId, date] = key.split('_');
                    return {
                        employee_id: employeeId,
                        start_date: date.split(' ')[0],
                        shifts
                    };
                });

                document.getElementById('schedule_data').value = JSON.stringify(scheduleArray);

                if (deletedShiftIds.size > 0) {
                    const deletedIds = Array.from(deletedShiftIds).join(',');
                    let deletedInput = document.getElementById('deleted-shift-ids');

                    if (!deletedInput) {
                        deletedInput = document.createElement('input');
                        deletedInput.type = 'hidden';
                        deletedInput.id = 'deleted-shift-ids';
                        deletedInput.name = 'deleted_shift_ids';
                        document.getElementById('schedule-form').appendChild(deletedInput);
                    }

                    deletedInput.value = deletedIds;
                }

                document.getElementById('schedule-form').submit();
            });
        }

        // Update and publish handler
        const updateAndPublishBtn = document.getElementById('update-and-publish');
        if (updateAndPublishBtn) {
            updateAndPublishBtn.addEventListener('click', function(e) {
                e.preventDefault();

                if (Object.keys(window.scheduleData).length === 0) {
                    showAlert('No Shifts Added', 'Please add some shifts before updating the schedule.', 'warning');
                    return;
                }

                showConfirm(
                    'Update & Publish Schedule',
                    'Are you sure you want to update and publish this schedule? Employees will be notified.',
                    function() {
                        const scheduleArray = Object.entries(window.scheduleData).map(([key, shifts]) => {
                            const [employeeId, date] = key.split('_');
                            return {
                                employee_id: employeeId,
                                start_date: date.split(' ')[0],
                                shifts
                            };
                        });

                        document.getElementById('schedule_data').value = JSON.stringify(scheduleArray);

                        if (deletedShiftIds.size > 0) {
                            const deletedIds = Array.from(deletedShiftIds).join(',');
                            let deletedInput = document.getElementById('deleted-shift-ids');

                            if (!deletedInput) {
                                deletedInput = document.createElement('input');
                                deletedInput.type = 'hidden';
                                deletedInput.id = 'deleted-shift-ids';
                                deletedInput.name = 'deleted_shift_ids';
                                document.getElementById('schedule-form').appendChild(deletedInput);
                            }

                            deletedInput.value = deletedIds;
                        }

                        const publishInput = document.createElement('input');
                        publishInput.type = 'hidden';
                        publishInput.name = 'publish';
                        publishInput.value = 'true';
                        document.getElementById('schedule-form').appendChild(publishInput);

                        document.getElementById('schedule-form').submit();
                    }
                );
            });
        }

        // Alert functions
        function showAlert(title, message, type = 'info', callback = null) {
            const modal = document.getElementById('customAlertModal');
            const iconContainer = document.getElementById('alert-icon-container');
            const titleElement = document.getElementById('alert-modal-title');
            const messageElement = document.getElementById('alert-modal-message');
            const buttonsContainer = document.getElementById('alert-modal-buttons');

            if (!modal || !iconContainer || !titleElement || !messageElement || !buttonsContainer) {
                console.error('Alert modal elements not found');
                return;
            }

            titleElement.textContent = title;
            messageElement.textContent = message;

            let iconHtml = '', iconBgClass = '', buttonClass = '';
            switch(type) {
                case 'error':
                    iconBgClass = 'bg-red-100';
                    buttonClass = 'bg-red-600 hover:bg-red-700 focus:ring-red-500';
                    iconHtml = '<svg class="h-6 w-6 text-red-600" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.732-.833-2.5 0L4.268 6.5c-.77.833.192 2.5 1.732 2.5z" /></svg>';
                    break;
                case 'success':
                    iconBgClass = 'bg-green-100';
                    buttonClass = 'bg-green-600 hover:bg-green-700 focus:ring-green-500';
                    iconHtml = '<svg class="h-6 w-6 text-green-600" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>';
                    break;
                case 'warning':
                    iconBgClass = 'bg-yellow-100';
                    buttonClass = 'bg-yellow-600 hover:bg-yellow-700 focus:ring-yellow-500';
                    iconHtml = '<svg class="h-6 w-6 text-yellow-600" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.732-.833-2.5 0L4.268 6.5c-.77.833.192 2.5 1.732 2.5z" /></svg>';
                    break;
                default:
                    iconBgClass = 'bg-blue-100';
                    buttonClass = 'bg-blue-600 hover:bg-blue-700 focus:ring-blue-500';
                    iconHtml = '<svg class="h-6 w-6 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>';
            }

            iconContainer.className = `mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full sm:mx-0 sm:h-10 sm:w-10 ${iconBgClass}`;
            iconContainer.innerHTML = iconHtml;
            buttonsContainer.innerHTML = `<button type="button" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 text-base font-medium text-white focus:outline-none focus:ring-2 focus:ring-offset-2 sm:ml-3 sm:w-auto sm:text-sm ${buttonClass}" id="alert-ok-btn">OK</button>`;
            modal.classList.remove('hidden');

            const alertOkBtn = document.getElementById('alert-ok-btn');
            if (alertOkBtn) {
                alertOkBtn.addEventListener('click', () => {
                    modal.classList.add('hidden');
                    if (callback) callback();
                });
            }

            const alertBackdrop = document.getElementById('alert-modal-backdrop');
            if (alertBackdrop) {
                alertBackdrop.addEventListener('click', () => modal.classList.add('hidden'));
            }
        }

        function showConfirm(title, message, onConfirm, onCancel = null) {
            const modal = document.getElementById('customAlertModal');
            const iconContainer = document.getElementById('alert-icon-container');
            const titleElement = document.getElementById('alert-modal-title');
            const messageElement = document.getElementById('alert-modal-message');
            const buttonsContainer = document.getElementById('alert-modal-buttons');

            if (!modal || !iconContainer || !titleElement || !messageElement || !buttonsContainer) {
                console.error('Confirm modal elements not found');
                return;
            }

            titleElement.textContent = title;
            messageElement.textContent = message;
            iconContainer.className = 'mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-blue-100 sm:mx-0 sm:h-10 sm:w-10';
            iconContainer.innerHTML = '<svg class="h-6 w-6 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>';
            buttonsContainer.innerHTML = `
                <button type="button" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-green-600 text-base font-medium text-white hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 sm:ml-3 sm:w-auto sm:text-sm" id="confirm-yes-btn">Yes</button>
                <button type="button" class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:mt-0 sm:w-auto sm:text-sm" id="confirm-no-btn">Cancel</button>
            `;
            modal.classList.remove('hidden');

            const confirmYesBtn = document.getElementById('confirm-yes-btn');
            const confirmNoBtn = document.getElementById('confirm-no-btn');
            const alertBackdrop = document.getElementById('alert-modal-backdrop');

            if (confirmYesBtn) {
                confirmYesBtn.addEventListener('click', () => {
                    modal.classList.add('hidden');
                    if (onConfirm) onConfirm();
                });
            }

            [confirmNoBtn, alertBackdrop].forEach(element => {
                if (element) {
                    element.addEventListener('click', () => {
                        modal.classList.add('hidden');
                        if (onCancel) onCancel();
                    });
                }
            });
        }

        function filterAvailableTasks() {
            assignedTasks.clear();
            Object.values(window.scheduleData).forEach(shifts =>
                shifts.forEach(shift => {
                    if (shift.task_id) assignedTasks.add(shift.task_id);
                })
            );

            const allTaskOptions = document.querySelectorAll('#task-select option, .additional-task option');
            allTaskOptions.forEach(function(option) {
                const taskId = option.value;
                let taskAssignmentCount = Array.from(assignedTasks).filter(id => id === taskId).length;

                if (taskId && taskAssignmentCount > 0) {
                    option.disabled = true;
                    option.textContent = option.textContent.replace(' (Assigned)', '') + ' (Assigned)';
                } else {
                    option.disabled = false;
                    option.textContent = option.textContent.replace(' (Assigned)', '');
                }
            });
        }

        function setupAdditionalShiftDropdowns(container) {
            setupCustomDropdown(
                container.querySelector('.additional-custom-shift-type'),
                container.querySelector('.additional-shift-type'),
                container.querySelector('.additional-shift-type-options'),
                shiftTypes
            );
            setupCustomDropdown(
                container.querySelector('.additional-custom-role'),
                container.querySelector('.additional-role'),
                container.querySelector('.additional-role-options'),
                scheduleRoles
            );
        }

        function showSuccessMessage(message) {
            const notification = document.createElement('div');
            notification.className = 'fixed top-4 right-4 bg-green-500 text-white px-6 py-3 rounded-lg shadow-lg z-50 transform translate-x-full transition-transform duration-300';
            notification.innerHTML = `
                <div class="flex items-center">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                    </svg>
                    ${message}
                </div>
            `;

            document.body.appendChild(notification);
            setTimeout(() => notification.classList.remove('translate-x-full'), 100);
            setTimeout(() => {
                notification.classList.add('translate-x-full');
                setTimeout(() => notification.remove(), 300);
            }, 3000);
        }
    });
</script>
