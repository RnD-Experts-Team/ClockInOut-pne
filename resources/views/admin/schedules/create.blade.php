@extends('layouts.app')

@section('title', 'Create New Schedule')

@section('content')
    <div class="max-w-full mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <!-- Header Section -->
        <div class="sm:flex sm:items-center sm:justify-between mb-6">
            <div>
                <h1 class="text-3xl font-bold text-gray-900">Create New Schedule</h1>
                <p class="mt-2 text-sm text-gray-600">Create and manage employee schedules with task assignments</p>
            </div>
            <div class="flex flex-wrap items-center gap-3 mt-4 sm:mt-0">
                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                    Draft
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
                <form id="schedule-form" action="{{ route('admin.schedules.store') }}" method="POST">
                    @csrf
                    <input type="hidden" name="schedule_data" id="schedule_data">
                    <input type="hidden" name="start_date" id="start_date" value="{{ $startDate->format('Y-m-d') }}">
                    <input type="hidden" name="end_date" id="end_date" value="{{ $endDate->format('Y-m-d') }}">
                    <input type="hidden" name="name" id="schedule_name">

                    <!-- Controls Section -->
                    <div class="bg-gray-50 rounded-lg p-6 mb-6 border border-gray-200">
                        <div class="flex flex-wrap items-center gap-4 justify-between">
                            <!-- Week Navigation -->
                            <div class="flex items-center bg-white border border-gray-300 rounded-lg overflow-hidden shadow-sm">
                                <button type="button" class="px-4 py-2 text-gray-600 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-orange-500" id="prev-week">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                                    </svg>
                                </button>
                                <div class="px-6 py-2 font-medium text-gray-900 border-l border-r border-gray-200" id="week-display">
                                    Week: {{ $startDate->format('M d') }} - {{ $endDate->format('M d, Y') }}
                                </div>
                                <button type="button" class="px-4 py-2 text-gray-600 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-orange-500" id="next-week">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                                    </svg>
                                </button>
                            </div>
                            <div class="fixed inset-0 z-[60] overflow-y-auto hidden" id="customAlertModal" aria-labelledby="alert-modal-title" role="dialog" aria-modal="true">
                                <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                                    <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" id="alert-modal-backdrop"></div>
                                    <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

                                    <div class="inline-block align-bottom bg-white rounded-lg px-6 pt-5 pb-4 text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full sm:p-6">
                                        <div class="sm:flex sm:items-start">
                                            <!-- Icon Container -->
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
                                <button type="button" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-lg text-white bg-orange-600 hover:bg-orange-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-orange-500 transition-all duration-300" id="save-draft">
                                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4"></path>
                                    </svg>
                                    Save Draft
                                </button>

                                <button type="button" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-lg text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 transition-all duration-300" id="publish-schedule">
                                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"></path>
                                    </svg>
                                    Publish
                                </button>
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

                            <!-- ✅ Enhanced Role Filter Dropdown with Admin/User -->
                            <div>
                                <div class="relative">
                                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                        <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                                        </svg>
                                    </div>
                                    <select id="role-filter" class="block w-full pl-10 pr-3 py-2 border border-gray-300 rounded-lg leading-5 bg-white focus:outline-none focus:ring-1 focus:ring-orange-500 focus:border-orange-500">
                                        <option value="">All Roles</option>
                                        <!-- ✅ Explicitly add Admin and User options -->
                                        <option value="admin">Admin</option>
                                        <option value="user">User</option>
                                        <!-- ✅ Add dynamic roles from database -->
                                        @foreach($userRoles as $role)
                                            @if(!in_array(strtolower($role), ['admin', 'user']))
                                                <option value="{{ strtolower($role) }}">{{ ucfirst($role) }}</option>
                                            @endif
                                        @endforeach
                                    </select>
                                </div>
                            </div>

                            <!-- ✅ Add User Type Quick Filter -->

                        </div>

                        <!-- Filter Results and Clear Button -->
                        <div class="mt-3 flex justify-between items-center">
                            <div class="text-sm text-gray-500" id="filter-results">
                                Showing all employees
                            </div>
                            <div class="flex items-center space-x-2">
                                <!-- ✅ Role Count Badges -->

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

    <!-- Enhanced Assignment Modal -->
    <!-- Enhanced Assignment Modal -->
    <div class="fixed inset-0 z-50 overflow-y-auto hidden" id="assignmentModal" aria-labelledby="modal-title" role="dialog" aria-modal="true">
        <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" id="modal-backdrop"></div>
            <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

            <div class="inline-block align-bottom bg-white rounded-lg px-6 pt-5 pb-4 text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-2xl sm:w-full sm:p-6">
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
                            Assign Shift
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

                            <!-- Shift Type Custom Dropdown with orange focus -->
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

                            <!-- Role Custom Dropdown with orange focus -->
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
                        Save Shift
                    </button>
                    <button type="button" class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-orange-500 sm:mt-0 sm:w-auto sm:text-sm" id="cancel-modal">
                        Cancel
                    </button>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('styles')
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
        .shift-content[data-notes]:hover::after {
            content: attr(data-notes);
            position: absolute;
            bottom: 100%;
            left: 50%;
            transform: translateX(-50%);
            background: rgba(0, 0, 0, 0.8);
            color: white;
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 11px;
            white-space: nowrap;
            z-index: 1000;
            max-width: 200px;
            overflow: hidden;
            text-overflow: ellipsis;
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
@endpush

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
    window.routeUrls = {
        deleteShiftType: '{{ route("admin.schedules.delete-shift-type") }}',
        deleteRole: '{{ route("admin.schedules.delete-role") }}'
    };
    $(document).ready(function() {
        // Make scheduleData GLOBAL by attaching to window
        window.scheduleData = window.scheduleData || {};
        let assignedTasks = new Set();
        let additionalShiftCounter = 0;

        // Load from database via controller
        let shiftTypes = @json($shiftTypes ?? []);
        let scheduleRoles = @json($scheduleRoles ?? []);

        console.log('✅ Loaded Shift Types from database:', shiftTypes);
        console.log('✅ Loaded Roles from database:', scheduleRoles);

        // Setup custom dropdowns
        setupCustomDropdown('custom_shift_type', 'shift-type', 'shift-type-options', shiftTypes);
        setupCustomDropdown('custom_role', 'role', 'role-options', scheduleRoles);

        function setupCustomDropdown(inputId, hiddenId, optionsContainerId, options) {
            const $input = $(`#${inputId}`);
            const $hidden = $(`#${hiddenId}`);
            const $dropdown = $input.closest('.custom-select-container').find('.custom-select-dropdown');
            const $optionsContainer = $(`#${optionsContainerId}`);

            function renderOptions(searchTerm = '') {
                const filteredOptions = options.filter(option =>
                    option.toLowerCase().includes(searchTerm.toLowerCase())
                );

                $optionsContainer.empty();

                // Show existing options from database with delete icons
                filteredOptions.forEach(option => {
                    const $option = $(`
                <div class="px-3 py-2 cursor-pointer hover:bg-blue-50 text-sm flex items-center justify-between group">
                    <span class="option-text">${option}</span>
                    <button type="button" class="delete-option hidden group-hover:block text-red-500 hover:text-red-700 ml-2" data-option="${option}" data-type="${inputId.includes('shift') ? 'shift_type' : 'role'}">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                        </svg>
                    </button>
                </div>
            `);

                    // Handle option selection
                    $option.find('.option-text').on('click', function(e) {
                        e.stopPropagation();
                        $input.val(option);
                        $hidden.val(option);
                        $dropdown.addClass('hidden');
                    });

                    // Handle delete button
                    $option.find('.delete-option').on('click', function(e) {
                        e.stopPropagation();
                        const optionToDelete = $(this).data('option');
                        const type = $(this).data('type');
                        deleteOption(optionToDelete, type);
                    });

                    $optionsContainer.append($option);
                });

                // Allow adding new options
                if (searchTerm && !options.some(opt => opt.toLowerCase() === searchTerm.toLowerCase())) {
                    const $addNew = $(`
                <div class="px-3 py-2 text-blue-600 cursor-pointer hover:bg-blue-50 font-medium border-t border-gray-200">
                    <strong>+ Add:</strong> "${searchTerm}"
                </div>
            `);
                    $addNew.on('click', function() {
                        options.push(searchTerm);
                        $input.val(searchTerm);
                        $hidden.val(searchTerm);
                        $dropdown.addClass('hidden');
                    });
                    $optionsContainer.append($addNew);
                }
            }

            function deleteOption(optionValue, type) {
                showConfirm(
                    'Delete Confirmation',
                    `Are you sure you want to delete "${optionValue}"? This will remove it from the database and all associated records.`,
                    function() {
                        const url = type === 'shift_type'
                            ? window.routeUrls.deleteShiftType
                            : window.routeUrls.deleteRole;

                        const data = type === 'shift_type'
                            ? { shift_type: optionValue, _token: $('meta[name="csrf-token"]').attr('content') }
                            : { role: optionValue, _token: $('meta[name="csrf-token"]').attr('content') };

                        $.ajax({
                            url: url,
                            type: 'POST',  // Change to POST
                            data: {
                                ...data,
                                _method: 'DELETE'  // Laravel method spoofing
                            },
                            success: function(response) {
                                if (response.success) {
                                    const index = options.indexOf(optionValue);
                                    if (index > -1) {
                                        options.splice(index, 1);
                                    }

                                    if ($input.val() === optionValue) {
                                        $input.val('');
                                        $hidden.val('');
                                    }

                                    renderOptions($input.val());
                                    showSuccessMessage(response.message);
                                }
                            },
                            error: function(xhr) {
                                console.log('AJAX Error Response:', xhr.responseJSON); // Debug the actual error
                                console.log('Status:', xhr.status);
                                const errorMessage = xhr.responseJSON?.message || 'Error deleting item';
                                showAlert('Error', errorMessage, 'error');
                            }
                        });
                    }
                );
            }


            $input.on('focus', function() {
                renderOptions($input.val());
                $dropdown.removeClass('hidden');
            });

            $input.on('input', function() {
                renderOptions($input.val());
                $dropdown.removeClass('hidden');
            });

            // Close on outside click
            $(document).on('click', function(e) {
                if (!$input.closest('.custom-select-container').is(e.target) &&
                    !$input.closest('.custom-select-container').has(e.target).length) {
                    $dropdown.addClass('hidden');
                }
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

                // Apply name filter
                if (searchTerm && !employeeName.includes(searchTerm)) {
                    showRow = false;
                }

                // Apply role filter
                if (selectedRole && employeeRole !== selectedRole) {
                    showRow = false;
                }

                // Show/hide row
                if (showRow) {
                    $(this).show();
                } else {
                    $(this).hide();
                }
            });

            // Update results counter
            const visibleEmployees = $('.employee-row:visible').length;
            const totalEmployees = $('.employee-row').length;

            let resultText = `Showing ${visibleEmployees} of ${totalEmployees} employees`;
            if (searchTerm || selectedRole) {
                resultText += ' (filtered)';
            }

            $('#filter-results').text(resultText);
        }

        // Clear filters function
        $('#clear-filters').on('click', function() {
            $('#employee-search').val('');
            $('#role-filter').val('');
            $('.employee-row').show();
            $('#filter-results').text('Showing all employees');
        });

        // Add additional shift functionality
// Add additional shift functionality with all fields
        $('#add-another-shift').on('click', function() {
            additionalShiftCounter++;
            const shiftHtml = `
    <div class="additional-shift bg-gray-50 border border-gray-200 rounded-lg p-4" data-shift-index="${additionalShiftCounter}">
        <div class="flex items-center justify-between mb-3">
            <span class="text-sm font-semibold text-gray-800">Additional Shift ${additionalShiftCounter}</span>
            <button type="button" class="text-red-600 hover:text-red-800 remove-additional-shift">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
            </button>
        </div>

        <!-- Task Selection for Additional Shift -->
        <div class="mb-4">
            <label class="block text-sm font-medium text-gray-700 mb-2">
                Assign Task <span class="text-red-500">*</span>
            </label>
            <select class="additional-task block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm" required>
                <option value="">Select Task</option>
                @foreach($availableTasks as $task)
            <option value="{{ $task->id }}">
                        {{ $task->store->name }} - {{ Str::limit($task->description_of_issue, 50) }}
            </option>
@endforeach
            </select>
        </div>

        <!-- Time Inputs for Additional Shift -->
        <div class="grid grid-cols-2 gap-3 mb-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Start Time</label>
                <input type="time" class="additional-start block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm" value="08:00">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">End Time</label>
                <input type="time" class="additional-end block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm" value="17:00">
            </div>
        </div>

        <!-- Shift Type for Additional Shift -->
        <div class="mb-4">
            <label class="block text-sm font-medium text-gray-700 mb-2">Shift Type</label>
            <div class="custom-select-container relative">
                <input type="hidden" class="additional-shift-type" name="additional_shift_type_${additionalShiftCounter}">
                <div class="custom-select-input relative">
                    <input type="text" class="additional-custom-shift-type block w-full pr-10 border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm"
                           placeholder="Select or type shift type..."
                           autocomplete="off">
                    <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none">
                        <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                        </svg>
                    </div>
                </div>
                <div class="custom-select-dropdown absolute z-50 w-full mt-1 bg-white border border-gray-300 rounded-md shadow-lg max-h-60 overflow-auto hidden">
                    <div class="py-1 additional-shift-type-options"></div>
                </div>
            </div>
        </div>

        <!-- Role for Additional Shift -->
        <div class="mb-4">
            <label class="block text-sm font-medium text-gray-700 mb-2">Role</label>
            <div class="custom-select-container relative">
                <input type="hidden" class="additional-role" name="additional_role_${additionalShiftCounter}">
                <div class="custom-select-input relative">
                    <input type="text" class="additional-custom-role block w-full pr-10 border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm"
                           placeholder="Select or type role..."
                           autocomplete="off">
                    <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none">
                        <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                        </svg>
                    </div>
                </div>
                <div class="custom-select-dropdown absolute z-50 w-full mt-1 bg-white border border-gray-300 rounded-md shadow-lg max-h-60 overflow-auto hidden">
                    <div class="py-1 additional-role-options"></div>
                </div>
            </div>
        </div>

        <!-- Shift Color for Additional Shift -->
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Shift Color</label>
            <input type="color" class="additional-color h-8 w-12 rounded border border-gray-300 cursor-pointer" value="#3b82f6">
        </div>
    </div>
    `;

            const $newShift = $(shiftHtml);
            $('#additional-shifts-container').append($newShift);

            // Setup custom dropdowns for the new additional shift
            const $shiftContainer = $newShift;
            setupAdditionalShiftDropdowns($shiftContainer);

            // Update task filter for new additional shift
            filterAdditionalShiftTasks($shiftContainer);
        });

        // Remove additional shift
        $(document).on('click', '.remove-additional-shift', function() {
            $(this).closest('.additional-shift').remove();
        });

        // Open modal
        $(document).on('click', '.schedule-cell', function(e) {
            e.preventDefault();

            const employeeId = $(this).data('employee');
            const employeeName = $(this).data('employee-name');
            const date = $(this).data('date');

            $('#modal-employee-id').val(employeeId);
            $('#modal-date').val(date);
            $('#modal-subtitle').text(`${employeeName} - ${date}`);

            // Reset form
            $('#task-select').val('');
            $('#start-time').val('08:00');
            $('#end-time').val('17:00');
            $('#custom_shift_type').val('');
            $('#shift-type').val('');
            $('#custom_role').val('');
            $('#assignment-notes').val('');
            $('#role').val('');
            $('#color-picker').val('#3b82f6');

            // Clear additional shifts
            $('#additional-shifts-container').empty();
            additionalShiftCounter = 0;

            filterAvailableTasks();
            $('#assignmentModal').removeClass('hidden');
        });

        function filterAvailableTasks() {
            assignedTasks.clear();
            Object.values(window.scheduleData).forEach(shifts => {
                shifts.forEach(shift => {
                    if (shift.task_id) {
                        assignedTasks.add(shift.task_id);
                    }
                });
            });

            $('#task-select option').each(function() {
                const taskId = $(this).val();

                // Count how many times this task has been assigned across all employees
                let taskAssignmentCount = 0;
                Object.values(window.scheduleData).forEach(shifts => {
                    shifts.forEach(shift => {
                        if (shift.task_id === taskId) {
                            taskAssignmentCount++;
                        }
                    });
                });

                // Disable if task is already assigned once
                if (taskId && taskAssignmentCount > 0) {
                    $(this).prop('disabled', true).text($(this).text().replace(' (Assigned)', '') + ' (Assigned)');
                } else {
                    $(this).prop('disabled', false);
                    // Remove (Assigned) text if it was previously added
                    $(this).text($(this).text().replace(' (Assigned)', ''));
                }
            });

        }

         // Save shift - Updated to handle additional shifts with all fields
        $('#save-shift').on('click', function() {
            const employeeId = $('#modal-employee-id').val();
            const date = $('#modal-date').val();
            const taskId = $('#task-select').val();
            const color = $('#color-picker').val();
            const assignmentNotes = $('#assignment-notes').val(); // Get the notes

            // Validation for main shift (keep existing validation)
            if (!taskId) {
                showAlert('Task Required', 'Please select a task before proceeding.', 'error');
                return;
            }

            const mainStartTime = $('#start-time').val();
            const mainEndTime = $('#end-time').val();

            if (!mainStartTime || !mainEndTime) {
                showAlert('Time Required', 'Please enter both start and end times.', 'error');
                return;
            }

            if (mainStartTime >= mainEndTime) {
                showAlert('Invalid Time Range', 'End time must be after start time.', 'error');
                return;
            }

            // Update shift object creation to include notes
            const shifts = [{
                start: mainStartTime,
                end: mainEndTime,
                color: color,
                task_id: taskId,
                assignment_notes: assignmentNotes // Add notes to shift object
            }];

            // Collect and validate additional shifts
            let validAdditionalShifts = true;
            $('.additional-shift').each(function() {
                const $this = $(this);
                const additionalTask = $this.find('.additional-task').val();
                const additionalStart = $this.find('.additional-start').val();
                const additionalEnd = $this.find('.additional-end').val();
                const additionalColor = $this.find('.additional-color').val();
                const additionalNotes = $this.find('.additional-notes').val(); // Get additional shift notes

                if (additionalStart && additionalEnd) {
                    // Validate required fields for additional shift
                    if (!additionalTask) {
                        showAlert('Additional Shift Task Required', 'Please select a task for all additional shifts.', 'error');
                        validAdditionalShifts = false;
                        return false;
                    }

                    if (additionalStart >= additionalEnd) {
                        showAlert('Invalid Additional Shift', 'End time must be after start time for all additional shifts.', 'error');
                        validAdditionalShifts = false;
                        return false;
                    }

                    shifts.push({
                        start: additionalStart,
                        end: additionalEnd,
                        color: additionalColor,
                        task_id: additionalTask,
                        assignment_notes: additionalNotes // Add notes to additional shift
                    });
                }
            });

            if (!validAdditionalShifts) {
                return;
            }

            // Save shifts to global scheduleData
            const key = `${employeeId}_${date}`;
            window.scheduleData[key] = shifts;

            updateScheduleCell(employeeId, date, shifts);

            // Add all task IDs to assigned tasks
            shifts.forEach(shift => {
                if (shift.task_id) {
                    assignedTasks.add(shift.task_id);
                }
            });

            $('#assignmentModal').addClass('hidden');
            console.log('✅ Shifts saved:', shifts);

            // Show success message
            showSuccessMessage(`${shifts.length === 1 ? 'Shift' : shifts.length + ' shifts'} saved successfully!`);
        });

        function updateScheduleCell(employeeId, date, shifts) {
            const $cell = $(`.schedule-cell[data-employee="${employeeId}"][data-date="${date}"]`);

            if (shifts.length > 0) {
                const mainShift = shifts[0];
                const totalHours = shifts.reduce((total, shift) => {
                    const start = new Date(`2000-01-01 ${shift.start}`);
                    const end = new Date(`2000-01-01 ${shift.end}`);
                    return total + (end - start) / (1000 * 60 * 60);
                }, 0);

                let splitIndicator = shifts.length > 1 ? '<div class="text-xs bg-yellow-200 text-yellow-800 px-1 rounded mt-1">SPLIT</div>' : '';

                // Check if any shift has notes
                const hasNotes = shifts.some(shift => shift.assignment_notes && shift.assignment_notes.trim() !== '');
                let notesIndicator = hasNotes ? '<div class="text-xs bg-blue-200 text-blue-800 px-1 rounded mt-1">NOTES</div>' : '';

                $cell.removeClass('border-dashed border-gray-300')
                    .addClass('has-assignment')
                    .css('background-color', mainShift.color)
                    .html(`
                <div class="shift-content p-2 relative text-center">
                    <div class="shift-time text-xs font-semibold">${mainShift.start} - ${mainShift.end}</div>
                    <div class="shift-hours text-xs opacity-80">${totalHours.toFixed(1)}h</div>
                    <div class="task-badge absolute top-1 right-1 bg-white bg-opacity-30 text-xs px-1 rounded">T</div>
                    ${splitIndicator}
                    ${notesIndicator}
                </div>
            `)
                    .css('color', getContrastColor(mainShift.color));
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

        // Success message function
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

            setTimeout(() => {
                $notification.removeClass('translate-x-full');
            }, 100);

            setTimeout(() => {
                $notification.addClass('translate-x-full');
                setTimeout(() => {
                    $notification.remove();
                }, 300);
            }, 3000);
        }

        // Week navigation
        let currentStartDate = new Date('{{ $startDate->format("Y-m-d") }}');
        let currentEndDate = new Date('{{ $endDate->format("Y-m-d") }}');

        $('#prev-week').on('click', function(e) {
            e.preventDefault();
            const newStartDate = new Date(currentStartDate);
            newStartDate.setDate(newStartDate.getDate() - 7);
            const newEndDate = new Date(currentEndDate);
            newEndDate.setDate(newEndDate.getDate() - 7);

            const startDateString = newStartDate.toISOString().split('T')[0];
            const endDateString = newEndDate.toISOString().split('T')[0];

            window.location.href = `{{ route('admin.schedules.create') }}?start_date=${startDateString}&end_date=${endDateString}`;
        });

        $('#next-week').on('click', function(e) {
            e.preventDefault();
            const newStartDate = new Date(currentStartDate);
            newStartDate.setDate(newStartDate.getDate() + 7);
            const newEndDate = new Date(currentEndDate);
            newEndDate.setDate(newEndDate.getDate() + 7);

            const startDateString = newStartDate.toISOString().split('T')[0];
            const endDateString = newEndDate.toISOString().split('T')[0];

            window.location.href = `{{ route('admin.schedules.create') }}?start_date=${startDateString}&end_date=${endDateString}`;
        });

        // Save Draft Button
        $('#save-draft').on('click', function(e) {
            e.preventDefault();

            if (Object.keys(window.scheduleData).length === 0) {
                showAlert('No Shifts Added', 'Please add some shifts before saving the schedule.', 'warning');
                return;
            }


            const scheduleArray = Object.entries(window.scheduleData).map(([key, shifts]) => {
                const [employeeId, date] = key.split('_');
                return { employee_id: employeeId, start_date: date, shifts: shifts };
            });

            $('#schedule_data').val(JSON.stringify(scheduleArray));
            const scheduleName = `Schedule for Week ${$('#week-display').text().replace('Week: ', '')}`;
            $('#schedule_name').val(scheduleName);

            $('#schedule-form').submit();
        });

        // Publish Schedule Button
        $('#publish-schedule').on('click', function(e) {
            e.preventDefault();

            if (Object.keys(window.scheduleData).length === 0) {
                alert('Please add some shifts before publishing the schedule.');
                return;
            }

            showConfirm(
                'Publish Schedule',
                'Are you sure you want to publish this schedule? Employees will be notified and the schedule will become active.',
                function() {
                    // ... rest of the publish code goes here
                    const scheduleArray = Object.entries(window.scheduleData).map(([key, shifts]) => {
                        const [employeeId, date] = key.split('_');
                        return { employee_id: employeeId, start_date: date, shifts: shifts };
                    });

                    $('#schedule_data').val(JSON.stringify(scheduleArray));
                    const scheduleName = `Schedule for Week ${$('#week-display').text().replace('Week: ', '')}`;
                    $('#schedule_name').val(scheduleName);

                    // Add publish flag
                    $('<input>').attr({
                        type: 'hidden',
                        name: 'publish',
                        value: 'true'
                    }).appendTo('#schedule-form');

                    $('#schedule-form').submit();
                }
            );

            if (userConfirmed) {
                const scheduleArray = Object.entries(window.scheduleData).map(([key, shifts]) => {
                    const [employeeId, date] = key.split('_');
                    return { employee_id: employeeId, start_date: date, shifts: shifts };
                });

                $('#schedule_data').val(JSON.stringify(scheduleArray));
                const scheduleName = `Schedule for Week ${$('#week-display').text().replace('Week: ', '')}`;
                $('#schedule_name').val(scheduleName);

                // Add publish flag
                $('<input>').attr({
                    type: 'hidden',
                    name: 'publish',
                    value: 'true'
                }).appendTo('#schedule-form');

                $('#schedule-form').submit();
            }
        });

        // Modal close handlers
        $('#close-modal, #cancel-modal, #modal-backdrop').on('click', function() {
            $('#assignmentModal').addClass('hidden');
        });

        // Custom Alert Functions
        function showAlert(title, message, type = 'info', callback = null) {
            const modal = $('#customAlertModal');
            const iconContainer = $('#alert-icon-container');
            const titleElement = $('#alert-modal-title');
            const messageElement = $('#alert-modal-message');
            const buttonsContainer = $('#alert-modal-buttons');

            // Set title and message
            titleElement.text(title);
            messageElement.text(message);

            // Configure based on alert type
            let iconHtml = '';
            let iconBgClass = '';
            let buttonClass = '';

            switch(type) {
                case 'error':
                    iconBgClass = 'bg-red-100';
                    buttonClass = 'bg-red-600 hover:bg-red-700 focus:ring-red-500';
                    iconHtml = `<svg class="h-6 w-6 text-red-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.732-.833-2.5 0L4.268 6.5c-.77.833.192 2.5 1.732 2.5z" />
            </svg>`;
                    break;
                case 'success':
                    iconBgClass = 'bg-green-100';
                    buttonClass = 'bg-green-600 hover:bg-green-700 focus:ring-green-500';
                    iconHtml = `<svg class="h-6 w-6 text-green-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>`;
                    break;
                case 'warning':
                    iconBgClass = 'bg-yellow-100';
                    buttonClass = 'bg-yellow-600 hover:bg-yellow-700 focus:ring-yellow-500';
                    iconHtml = `<svg class="h-6 w-6 text-yellow-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.732-.833-2.5 0L4.268 6.5c-.77.833.192 2.5 1.732 2.5z" />
            </svg>`;
                    break;
                default: // info
                    iconBgClass = 'bg-blue-100';
                    buttonClass = 'bg-blue-600 hover:bg-blue-700 focus:ring-blue-500';
                    iconHtml = `<svg class="h-6 w-6 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>`;
            }

            // Set icon
            iconContainer.removeClass().addClass(`mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full sm:mx-0 sm:h-10 sm:w-10 ${iconBgClass}`);
            iconContainer.html(iconHtml);

            // Set buttons
            buttonsContainer.html(`
        <button type="button" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 text-base font-medium text-white focus:outline-none focus:ring-2 focus:ring-offset-2 sm:ml-3 sm:w-auto sm:text-sm ${buttonClass}" id="alert-ok-btn">
            OK
        </button>
    `);

            // Show modal
            modal.removeClass('hidden');

            // Handle OK button click
            $('#alert-ok-btn').off('click').on('click', function() {
                modal.addClass('hidden');
                if (callback && typeof callback === 'function') {
                    callback();
                }
            });

            // Handle backdrop click
            $('#alert-modal-backdrop').off('click').on('click', function() {
                modal.addClass('hidden');
            });
        }

        function showConfirm(title, message, onConfirm, onCancel = null) {
            const modal = $('#customAlertModal');
            const iconContainer = $('#alert-icon-container');
            const titleElement = $('#alert-modal-title');
            const messageElement = $('#alert-modal-message');
            const buttonsContainer = $('#alert-modal-buttons');

            // Set title and message
            titleElement.text(title);
            messageElement.text(message);

            // Set question icon
            iconContainer.removeClass().addClass('mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-blue-100 sm:mx-0 sm:h-10 sm:w-10');
            iconContainer.html(`
        <svg class="h-6 w-6 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
        </svg>
    `);

            // Set confirm/cancel buttons
            buttonsContainer.html(`
        <button type="button" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-green-600 text-base font-medium text-white hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 sm:ml-3 sm:w-auto sm:text-sm" id="confirm-yes-btn">
            Yes
        </button>
        <button type="button" class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:mt-0 sm:w-auto sm:text-sm" id="confirm-no-btn">
            Cancel
        </button>
    `);

            // Show modal
            modal.removeClass('hidden');

            // Handle Yes button click
            $('#confirm-yes-btn').off('click').on('click', function() {
                modal.addClass('hidden');
                if (onConfirm && typeof onConfirm === 'function') {
                    onConfirm();
                }
            });

            // Handle Cancel button click
            $('#confirm-no-btn').off('click').on('click', function() {
                modal.addClass('hidden');
                if (onCancel && typeof onCancel === 'function') {
                    onCancel();
                }
            });

            // Handle backdrop click (acts as cancel)
            $('#alert-modal-backdrop').off('click').on('click', function() {
                modal.addClass('hidden');
                if (onCancel && typeof onCancel === 'function') {
                    onCancel();
                }
            });
        }

// Setup dropdowns for additional shifts
        function setupAdditionalShiftDropdowns($container) {
            const $shiftTypeInput = $container.find('.additional-custom-shift-type');
            const $shiftTypeHidden = $container.find('.additional-shift-type');
            const $shiftTypeDropdown = $container.find('.custom-select-dropdown').first();
            const $shiftTypeOptions = $container.find('.additional-shift-type-options');

            const $roleInput = $container.find('.additional-custom-role');
            const $roleHidden = $container.find('.additional-role');
            const $roleDropdown = $container.find('.custom-select-dropdown').last();
            const $roleOptions = $container.find('.additional-role-options');

            // Setup shift type dropdown
            setupAdditionalDropdown($shiftTypeInput, $shiftTypeHidden, $shiftTypeDropdown, $shiftTypeOptions, shiftTypes);

            // Setup role dropdown
            setupAdditionalDropdown($roleInput, $roleHidden, $roleDropdown, $roleOptions, scheduleRoles);
        }

        function setupAdditionalDropdown($input, $hidden, $dropdown, $optionsContainer, options) {
            function renderOptions(searchTerm = '') {
                const filteredOptions = options.filter(option =>
                    option.toLowerCase().includes(searchTerm.toLowerCase())
                );

                $optionsContainer.empty();

                // Show existing options with delete icons
                filteredOptions.forEach(option => {
                    const $option = $(`
                <div class="px-3 py-2 cursor-pointer hover:bg-blue-50 text-sm flex items-center justify-between group">
                    <span class="option-text">${option}</span>
                    <button type="button" class="delete-option hidden group-hover:block text-red-500 hover:text-red-700 ml-2" data-option="${option}">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                        </svg>
                    </button>
                </div>
            `);

                    $option.find('.option-text').on('click', function(e) {
                        e.stopPropagation();
                        $input.val(option);
                        $hidden.val(option);
                        $dropdown.addClass('hidden');
                    });

                    // Handle delete (same logic as main dropdown)
                    $option.find('.delete-option').on('click', function(e) {
                        e.stopPropagation();
                        const optionToDelete = $(this).data('option');
                        // Determine type based on input class
                        const type = $input.hasClass('additional-custom-shift-type') ? 'shift_type' : 'role';
                        deleteOption(optionToDelete, type);
                    });

                    $optionsContainer.append($option);
                });

                // Allow adding new options
                if (searchTerm && !options.some(opt => opt.toLowerCase() === searchTerm.toLowerCase())) {
                    const $addNew = $(`
                <div class="px-3 py-2 text-blue-600 cursor-pointer hover:bg-blue-50 font-medium border-t border-gray-200">
                    <strong>+ Add:</strong> "${searchTerm}"
                </div>
            `);
                    $addNew.on('click', function() {
                        options.push(searchTerm);
                        $input.val(searchTerm);
                        $hidden.val(searchTerm);
                        $dropdown.addClass('hidden');
                    });
                    $optionsContainer.append($addNew);
                }
            }

            // Include the same deleteOption function here...
            // (Copy the deleteOption function from above)

            // Rest of the function remains the same...
        }

// Filter tasks for additional shifts
        function filterAdditionalShiftTasks($container) {
            const $taskSelect = $container.find('.additional-task');

            $taskSelect.find('option').each(function() {
                const taskId = $(this).val();

                // Count how many times this task has been assigned
                let taskAssignmentCount = 0;
                Object.values(window.scheduleData).forEach(shifts => {
                    shifts.forEach(shift => {
                        if (shift.task_id === taskId) {
                            taskAssignmentCount++;
                        }
                    });
                });

                // Disable if task is already assigned once
                if (taskId && taskAssignmentCount > 0) {
                    $(this).prop('disabled', true).text($(this).text().replace(' (Assigned)', '') + ' (Assigned)');
                } else {
                    $(this).prop('disabled', false);
                    $(this).text($(this).text().replace(' (Assigned)', ''));
                }
            });
        }

    });
</script>

