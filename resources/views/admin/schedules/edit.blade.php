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
                                    <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" id="alert-modal-backdrop"></div>
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
            <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" id="modal-backdrop"></div>
            <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

            <div class="inline-block align-bottom bg-white rounded-lg px-6 pt-5 pb-4 text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-2xl sm:w-full sm:p-6 max-h-screen overflow-y-auto">
                <!-- Modal header with orange accent -->
                <div class="absolute top-0 left-0 right-0 h-2 bg-orange-600"></div>

                <div class="absolute top-0 right-0 pt-4 pr-4">
                    <button type="button" class="bg-white rounded-md text-gray-400 hover:text-gray-600" id="close-modal">
                        <span class="sr-only">Close</span>
                        <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
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
                            Edit Shift Assignment
                        </h3>
                        <p class="text-sm text-gray-500 mt-1" id="modal-subtitle"></p>

                        <div class="mt-6">
                            <input type="hidden" id="modal-employee-id">
                            <input type="hidden" id="modal-date">

                            <!-- Task Selection with orange focus -->
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

                            <!-- Time Inputs with orange focus -->
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
                                    <!-- Orange add button -->
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
                                <input type="color" id="color-picker" class="h-10 w-16 rounded border border-gray-300 cursor-pointer" value="#f97316"> <!-- Default orange color -->
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Footer buttons with orange primary button -->
                <div class="mt-6 sm:mt-4 sm:flex sm:flex-row-reverse">
                    <button type="button" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-orange-600 text-base font-medium text-white hover:bg-orange-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-orange-500 sm:ml-3 sm:w-auto sm:text-sm" id="save-shift">
                        Update Shift
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

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
    $(document).ready(function() {
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

        function updateScheduleCell(employeeId, date, shifts) {
            const $cell = $(`.schedule-cell[data-employee="${employeeId}"][data-date="${date}"]`);
            console.log(`🎯 Updating cell for employee ${employeeId} on ${date}`, $cell.length);

            if ($cell.length === 0) {
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

                $cell.removeClass('border-dashed border-gray-300')
                    .addClass('has-assignment')
                    .css('background-color', mainShift.color)
                    .html(`
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
        `)
                    .css('color', getContrastColor(mainShift.color));

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
            const $input = $(inputSelector);
            const $hidden = $(hiddenSelector);
            const $optionsContainer = $(optionsContainerId);

            $input.on('focus input', function() {
                const searchTerm = $input.val().toLowerCase();
                $optionsContainer.empty().show();
                options.filter(opt => opt.toLowerCase().includes(searchTerm)).forEach(opt => {
                    $('<div>').text(opt).addClass('px-3 py-2 cursor-pointer hover:bg-orange-50').appendTo($optionsContainer).on('click', () => {
                        $input.val(opt);
                        $hidden.val(opt);
                        $optionsContainer.parent().hide();
                    });
                });
                if (searchTerm && !options.includes(searchTerm)) {
                    $('<div>').html(`<strong>+ Add:</strong> "${searchTerm}"`).addClass('px-3 py-2 text-orange-600 cursor-pointer hover:bg-orange-50 border-t border-gray-200').appendTo($optionsContainer).on('click', () => {
                        options.push(searchTerm);
                        $input.val(searchTerm);
                        $hidden.val(searchTerm);
                        $optionsContainer.parent().hide();
                    });
                }
                $optionsContainer.parent().toggle(!!$input.val());
            });
            $(document).on('click', e => {
                if (!$input.is(e.target) && !$optionsContainer.parent().has(e.target).length) $optionsContainer.parent().hide();
            });
        }

        // Enhanced Employee Search and Role Filter
        $('#employee-search, #role-filter').on('input change', function() {
            filterEmployees();
        });

        function filterEmployees() {
            const searchTerm = $('#employee-search').val().toLowerCase();
            const selectedRole = $('#role-filter').val().toLowerCase();
            $('.employee-row').each(function() {
                const employeeName = $(this).data('employee-name');
                const employeeRole = $(this).data('employee-role');
                let showRow = true;
                if (searchTerm && !employeeName.includes(searchTerm)) showRow = false;
                if (selectedRole && employeeRole !== selectedRole) showRow = false;
                $(this).toggle(showRow);
            });
            const visibleEmployees = $('.employee-row:visible').length;
            const totalEmployees = $('.employee-row').length;
            let resultText = `Showing ${visibleEmployees} of ${totalEmployees} employees`;
            if (searchTerm || selectedRole) resultText += ' (filtered)';
            $('#filter-results').text(resultText);
        }

        $('#clear-filters').on('click', function() {
            $('#employee-search').val('');
            $('#role-filter').val('');
            $('.employee-row').show();
            $('#filter-results').text('Showing all employees');
        });

        // Add another shift - FIXED with assignment notes
        $('#add-another-shift').on('click', function() {
            additionalShiftCounter++;
            const start = $('#start-time').val();
            const end = $('#end-time').val();
            const shiftType = $('#shift-type').val() || 'regular';
            const role = $('#role').val() || 'general';
            const color = $('#color-picker').val() || '#f97316';
            const taskId = $('#task-select').val();
            const assignmentNotes = $('#assignment-notes').val(); // FIXED: Get notes

            if (!start || !end || start >= end) {
                showAlert('Invalid Shift', 'End time must be after start time.', 'error');
                return;
            }
            if (!taskId) {
                showAlert('Task Required', 'Please select a task for the shift.', 'error');
                return;
            }

            const shiftId = `temp_${shiftCounter++}`;
            const $template = $('#additional-shift-template').clone().removeClass('hidden').removeAttr('id').attr('data-shift-index', additionalShiftCounter);
            $template.find('.additional-task').val(taskId);
            $template.find('.additional-start').val(start);
            $template.find('.additional-end').val(end);
            $template.find('.additional-custom-shift-type').val(shiftType);
            $template.find('.additional-shift-type').val(shiftType);
            $template.find('.additional-custom-role').val(role);
            $template.find('.additional-role').val(role);
            $template.find('.additional-color').val(color);
            $template.find('.additional-notes').val(assignmentNotes); // FIXED: Set notes
            $template.find('.additional-shift span').text(`Additional Shift ${additionalShiftCounter}`);

            $('#additional-shifts-container').append($template);
            assignedTasks.add(taskId);
            setupAdditionalShiftDropdowns($template);

            // Clear form fields - INCLUDING NOTES
            $('#start-time').val('08:00');
            $('#end-time').val('17:00');
            $('#shift-type').val('');
            $('#role').val('');
            $('#color-picker').val('#3b82f6');
            $('#task-select').val('');
            $('#assignment-notes').val(''); // FIXED: Clear notes

            filterAvailableTasks();
        });

        // Remove additional shift
        $(document).on('click', '.remove-additional-shift', function() {
            const $shift = $(this).closest('.additional-shift');
            const taskId = $shift.find('.additional-task').val();
            if (taskId) assignedTasks.delete(taskId);
            $shift.remove();
        });

        // Open modal with existing data - FIXED with assignment notes
        $(document).on('click', '.schedule-cell', function(e) {
            e.preventDefault();
            const employeeId = $(this).data('employee');
            const employeeName = $(this).data('employee-name');
            const date = $(this).data('date');
            const keyWithoutTime = `${employeeId}_${date}`;
            const keyWithTime = `${employeeId}_${date} 00:00:00`;
            let existingShifts = window.scheduleData[keyWithoutTime] || window.scheduleData[keyWithTime] || [];

            console.log('🔍 Modal Debug - Key without time:', keyWithoutTime);
            console.log('🔍 Modal Debug - Key with time:', keyWithTime);
            console.log('🔍 Modal Debug - Existing Shifts:', existingShifts);

            $('#modal-employee-id').val(employeeId);
            $('#modal-date').val(date);
            $('#modal-subtitle').text(`${employeeName} - ${date}`);

            const $shiftContainer = $('#additional-shifts-container');
            $shiftContainer.empty();
            deletedShiftIds.clear();
            additionalShiftCounter = 0;

            if (existingShifts.length > 0) {
                const mainShift = existingShifts[0];
                console.log('🔍 Modal Debug - Main Shift:', mainShift);
                $('#task-select').val(mainShift.task_id || '').trigger('change');
                $('#start-time').val(mainShift.start || '08:00');
                $('#end-time').val(mainShift.end || '17:00');
                $('#custom_shift_type').val(mainShift.type || '');
                $('#shift-type').val(mainShift.type || '');
                $('#custom_role').val(mainShift.role || '');
                $('#role').val(mainShift.role || '');
                $('#color-picker').val(mainShift.color || '#3b82f6');
                $('#assignment-notes').val(mainShift.assignment_notes || ''); // FIXED: Set notes

                if (existingShifts.length > 1) {
                    for (let i = 1; i < existingShifts.length; i++) {
                        const additionalShift = existingShifts[i];
                        $('#add-another-shift').click();
                        setTimeout(() => {
                            const $lastAdditionalShift = $('.additional-shift:last');
                            $lastAdditionalShift.find('.additional-task').val(additionalShift.task_id);
                            $lastAdditionalShift.find('.additional-start').val(additionalShift.start);
                            $lastAdditionalShift.find('.additional-end').val(additionalShift.end);
                            $lastAdditionalShift.find('.additional-custom-shift-type').val(additionalShift.type);
                            $lastAdditionalShift.find('.additional-shift-type').val(additionalShift.type);
                            $lastAdditionalShift.find('.additional-custom-role').val(additionalShift.role);
                            $lastAdditionalShift.find('.additional-role').val(additionalShift.role);
                            $lastAdditionalShift.find('.additional-color').val(additionalShift.color);
                            $lastAdditionalShift.find('.additional-notes').val(additionalShift.assignment_notes || ''); // FIXED: Set notes
                        }, 100);
                    }
                }
            } else {
                console.log('📝 No existing shifts found - clearing form');
                $('#task-select').val('');
                $('#start-time').val('08:00');
                $('#end-time').val('17:00');
                $('#custom_shift_type').val('');
                $('#shift-type').val('');
                $('#custom_role').val('');
                $('#role').val('');
                $('#color-picker').val('#3b82f6');
                $('#assignment-notes').val(''); // FIXED: Clear notes
            }

            filterAvailableTasks();
            $('#assignmentModal').removeClass('hidden');
        });

        // Delete shift - remains the same
        $(document).on('click', '.delete-shift', function(e) {
            e.stopPropagation();
            console.log('🔴 Delete Shift Clicked');

            const shiftId = $(this).data('shift-id');
            const taskId = $(this).data('task-id');
            const employeeId = $(this).data('employee');
            const date = $(this).data('date');
            const shiftStart = $(this).data('shift-start');
            const shiftEnd = $(this).data('shift-end');

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
                    const $cell = $(this).closest('.schedule-cell');
                    $cell.removeClass('has-assignment')
                        .addClass('border-dashed border-gray-300')
                        .css('background-color', '')
                        .css('color', '')
                        .html('<span class="text-xs text-gray-500 font-medium">Click to assign</span>');
                }
            }

            filterAvailableTasks();
        });

        // Save shift - FIXED with assignment notes
        $('#save-shift').on('click', function() {
            const employeeId = $('#modal-employee-id').val();
            const date = $('#modal-date').val();
            let shifts = [];

            const key = `${employeeId}_${date}`;
            const keyWithTime = `${employeeId}_${date} 00:00:00`;
            const existingShifts = window.scheduleData[key] || window.scheduleData[keyWithTime] || [];

            const mainShift = {
                start: $('#start-time').val(),
                end: $('#end-time').val(),
                type: $('#shift-type').val() || 'regular',
                role: $('#role').val() || 'general',
                color: $('#color-picker').val() || '#3b82f6',
                task_id: $('#task-select').val(),
                assignment_notes: $('#assignment-notes').val() // FIXED: Include notes
            };

            if (existingShifts.length > 0 && existingShifts[0].id) {
                mainShift.id = existingShifts[0].id;
            }

            if (mainShift.task_id) shifts.push(mainShift);

            let additionalIndex = 1;
            $('#additional-shifts-container .additional-shift').each(function() {
                const additionalShift = {
                    start: $(this).find('.additional-start').val(),
                    end: $(this).find('.additional-end').val(),
                    type: $(this).find('.additional-shift-type').val() || 'regular',
                    role: $(this).find('.additional-role').val() || 'general',
                    color: $(this).find('.additional-color').val() || '#3b82f6',
                    task_id: $(this).find('.additional-task').val(),
                    assignment_notes: $(this).find('.additional-notes').val() // FIXED: Include notes
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
            $('#assignmentModal').addClass('hidden');
            showSuccessMessage('Shift updated locally. Click "Update Schedule" to save changes.');

            console.log('✅ Shift saved locally');
            console.log('📊 Current schedule data:', window.scheduleData);
        });

        // Update schedule handler - remains the same
        $('#update-schedule').on('click', function(e) {
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

            $('#schedule_data').val(JSON.stringify(scheduleArray));

            if (deletedShiftIds.size > 0) {
                const deletedIds = Array.from(deletedShiftIds).join(',');
                let $deletedInput = $('#deleted-shift-ids');

                if ($deletedInput.length === 0) {
                    $deletedInput = $('<input>').attr({
                        type: 'hidden',
                        id: 'deleted-shift-ids',
                        name: 'deleted_shift_ids'
                    }).appendTo('#schedule-form');
                }

                $deletedInput.val(deletedIds);
            }

            $('#schedule-form').submit();
        });

        // Update and publish handler - remains the same
        $('#update-and-publish').on('click', function(e) {
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

                    $('#schedule_data').val(JSON.stringify(scheduleArray));

                    if (deletedShiftIds.size > 0) {
                        const deletedIds = Array.from(deletedShiftIds).join(',');
                        let $deletedInput = $('#deleted-shift-ids');

                        if ($deletedInput.length === 0) {
                            $deletedInput = $('<input>').attr({
                                type: 'hidden',
                                id: 'deleted-shift-ids',
                                name: 'deleted_shift_ids'
                            }).appendTo('#schedule-form');
                        }

                        $deletedInput.val(deletedIds);
                    }

                    $('<input>').attr({
                        type: 'hidden',
                        name: 'publish',
                        value: 'true'
                    }).appendTo('#schedule-form');

                    $('#schedule-form').submit();
                }
            );
        });

        // Modal close handlers
        $('#close-modal, #cancel-modal, #modal-backdrop').on('click', function() {
            $('#assignmentModal').addClass('hidden');
        });

        // Alert functions - remain the same as in your original code
        function showAlert(title, message, type = 'info', callback = null) {
            const modal = $('#customAlertModal');
            const iconContainer = $('#alert-icon-container');
            const titleElement = $('#alert-modal-title');
            const messageElement = $('#alert-modal-message');
            const buttonsContainer = $('#alert-modal-buttons');
            titleElement.text(title);
            messageElement.text(message);
            let iconHtml = '', iconBgClass = '', buttonClass = '';
            switch(type) {
                case 'error': iconBgClass = 'bg-red-100'; buttonClass = 'bg-red-600 hover:bg-red-700 focus:ring-red-500'; iconHtml = '<svg class="h-6 w-6 text-red-600" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.732-.833-2.5 0L4.268 6.5c-.77.833.192 2.5 1.732 2.5z" /></svg>'; break;
                case 'success': iconBgClass = 'bg-green-100'; buttonClass = 'bg-green-600 hover:bg-green-700 focus:ring-green-500'; iconHtml = '<svg class="h-6 w-6 text-green-600" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>'; break;
                case 'warning': iconBgClass = 'bg-yellow-100'; buttonClass = 'bg-yellow-600 hover:bg-yellow-700 focus:ring-yellow-500'; iconHtml = '<svg class="h-6 w-6 text-yellow-600" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.732-.833-2.5 0L4.268 6.5c-.77.833.192 2.5 1.732 2.5z" /></svg>'; break;
                default: iconBgClass = 'bg-blue-100'; buttonClass = 'bg-blue-600 hover:bg-blue-700 focus:ring-blue-500'; iconHtml = '<svg class="h-6 w-6 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>';
            }
            iconContainer.removeClass().addClass(`mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full sm:mx-0 sm:h-10 sm:w-10 ${iconBgClass}`).html(iconHtml);
            buttonsContainer.html(`<button type="button" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 text-base font-medium text-white focus:outline-none focus:ring-2 focus:ring-offset-2 sm:ml-3 sm:w-auto sm:text-sm ${buttonClass}" id="alert-ok-btn">OK</button>`);
            modal.removeClass('hidden');
            $('#alert-ok-btn').off('click').on('click', () => { modal.addClass('hidden'); if (callback) callback(); });
            $('#alert-modal-backdrop').off('click').on('click', () => modal.addClass('hidden'));
        }

        function showConfirm(title, message, onConfirm, onCancel = null) {
            const modal = $('#customAlertModal');
            const iconContainer = $('#alert-icon-container');
            const titleElement = $('#alert-modal-title');
            const messageElement = $('#alert-modal-message');
            const buttonsContainer = $('#alert-modal-buttons');
            titleElement.text(title);
            messageElement.text(message);
            iconContainer.removeClass().addClass('mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-blue-100 sm:mx-0 sm:h-10 sm:w-10').html('<svg class="h-6 w-6 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>');
            buttonsContainer.html(`
            <button type="button" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-green-600 text-base font-medium text-white hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 sm:ml-3 sm:w-auto sm:text-sm" id="confirm-yes-btn">Yes</button>
            <button type="button" class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:mt-0 sm:w-auto sm:text-sm" id="confirm-no-btn">Cancel</button>
        `);
            modal.removeClass('hidden');
            $('#confirm-yes-btn').off('click').on('click', () => { modal.addClass('hidden'); if (onConfirm) onConfirm(); });
            $('#confirm-no-btn, #alert-modal-backdrop').off('click').on('click', () => { modal.addClass('hidden'); if (onCancel) onCancel(); });
        }

        function filterAvailableTasks() {
            assignedTasks.clear();
            Object.values(window.scheduleData).forEach(shifts => shifts.forEach(shift => { if (shift.task_id) assignedTasks.add(shift.task_id); }));
            $('#task-select option, .additional-task option').each(function() {
                const taskId = $(this).val();
                let taskAssignmentCount = Array.from(assignedTasks).filter(id => id === taskId).length;
                if (taskId && taskAssignmentCount > 0) {
                    $(this).prop('disabled', true).text($(this).text().replace(' (Assigned)', '') + ' (Assigned)');
                } else {
                    $(this).prop('disabled', false).text($(this).text().replace(' (Assigned)', ''));
                }
            });
        }

        function setupAdditionalShiftDropdowns($container) {
            setupCustomDropdown($container.find('.additional-custom-shift-type'), $container.find('.additional-shift-type'), $container.find('.additional-shift-type-options'), shiftTypes);
            setupCustomDropdown($container.find('.additional-custom-role'), $container.find('.additional-role'), $container.find('.additional-role-options'), scheduleRoles);
        }

        function showSuccessMessage(message) {
            const $notification = $(`
            <div class="fixed top-4 right-4 bg-green-500 text-white px-6 py-3 rounded-lg shadow-lg z-50 transform translate-x-full transition-transform duration-300">
                <div class="flex items-center">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                    </svg>
                    ${message}
                </div>
            </div>
        `);
            $('body').append($notification);
            setTimeout(() => $notification.removeClass('translate-x-full'), 100);
            setTimeout(() => { $notification.addClass('translate-x-full'); setTimeout(() => $notification.remove(), 300); }, 3000);
        }
    });
</script>
