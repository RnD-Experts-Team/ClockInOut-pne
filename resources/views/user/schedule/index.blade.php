@extends('layouts.app')

@section('title', __('messages.my_schedule'))

@section('content')
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8" dir="{{ getDirection() }}">
        <!-- Header Section -->
        <div class="sm:flex sm:items-center sm:justify-between mb-6">
            <div>
                <h1 class="text-3xl font-bold text-gray-900">{{ __('messages.my_schedule_title') }}</h1>
                <p class="mt-2 text-sm text-gray-600">{{ __('messages.my_schedule_subtitle') }}</p>
            </div>

            <!-- Week Navigation -->
            <div class="flex items-center space-x-4 {{ isRtl() ? 'rtl:space-x-reverse' : '' }}">
                <a href="{{ route('user.schedule.index', ['start_date' => $previousWeek->format('Y-m-d')]) }}"
                   class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md hover:bg-gray-50">
                    {{ __('messages.previous_week') }}
                </a>
                <span class="text-sm font-medium text-gray-900">
        {{ $currentWeekStart->format('M d') }} - {{ $currentWeekEnd->format('M d, Y') }}
    </span>
                <a href="{{ route('user.schedule.index', ['start_date' => $nextWeek->format('Y-m-d')]) }}"
                   class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md hover:bg-gray-50">
                    {{ __('messages.next_week') }}
                </a>
            </div>
        </div>

        <!-- Weekly Stats -->
        <!-- Weekly Stats - Updated Grid with 6 cards -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-8">
            <!-- Existing Card 1: Scheduled Weekly Hours -->
            <div class="bg-white rounded-lg shadow p-6">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <svg class="h-8 w-8 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                    <div class="ml-4 {{ isRtl() ? 'mr-4 ml-0' : '' }}">
                        <p class="text-sm font-medium text-gray-600">{{ __('messages.scheduled_hours') }}</p>
                        <p class="text-2xl font-bold text-gray-900">{{ $weeklyHours ?? 0 }}</p>
                        <p class="text-xs text-gray-500">{{ __('messages.from_schedule') }}</p>
                    </div>
                </div>
            </div>

            <!-- NEW Card 2: Actual Hours Worked -->
            <div class="bg-white rounded-lg shadow p-6">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <svg class="h-8 w-8 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                    <div class="ml-4 {{ isRtl() ? 'mr-4 ml-0' : '' }}">
                        <p class="text-sm font-medium text-gray-600">{{ __('messages.actual_hours_worked') }}</p>
                        <p class="text-2xl font-bold text-green-900">{{ $actualHoursWorked ?? '00:00' }}</p>
                        <p class="text-xs text-gray-500">{{ __('messages.from_clocking') }}</p>
                    </div>
                </div>
            </div>

            <!-- Existing Card 3: Tasks This Week -->
            <div class="bg-white rounded-lg shadow p-6">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <svg class="h-8 w-8 text-orange-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2H9z"></path>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 9l2 2 4-4"></path>
                        </svg>
                    </div>
                    <div class="ml-4 {{ isRtl() ? 'mr-4 ml-0' : '' }}">
                        <p class="text-sm font-medium text-gray-600">{{ __('messages.tasks_this_week') }}</p>
                        <p class="text-2xl font-bold text-gray-900">{{ $tasksThisWeek ?? 0 }}</p>
                    </div>
                </div>
            </div>

            <!-- NEW Card 4: Weekly Earnings -->
            <div class="bg-white rounded-lg shadow p-6">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <svg class="h-8 w-8 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1"></path>
                        </svg>
                    </div>
                    <div class="ml-4 {{ isRtl() ? 'mr-4 ml-0' : '' }}">
                        <p class="text-sm font-medium text-gray-600">{{ __('messages.weekly_earnings') }}</p>
                        <p class="text-2xl font-bold text-emerald-900">${{ number_format($weeklyEarnings ?? 0, 2) }}</p>
                        <p class="text-xs text-gray-500">{{ __('messages.based_on_actual_hours') }}</p>
                    </div>
                </div>
            </div>

            <!-- NEW Card 5: Days Worked -->
            <div class="bg-white rounded-lg shadow p-6">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <svg class="h-8 w-8 text-purple-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 002 2v12a2 2 0 002 2z"></path>
                        </svg>
                    </div>
                    <div class="ml-4 {{ isRtl() ? 'mr-4 ml-0' : '' }}">
                        <p class="text-sm font-medium text-gray-600">{{ __('messages.days_worked') }}</p>
                        <p class="text-2xl font-bold text-purple-900">{{ $daysWorked ?? 0 }}</p>
                        <p class="text-xs text-gray-500">{{ __('messages.this_week') }}</p>
                    </div>
                </div>
            </div>

            <!-- NEW Card 6: Average Daily Hours -->
            <div class="bg-white rounded-lg shadow p-6">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <svg class="h-8 w-8 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                        </svg>
                    </div>
                    <div class="ml-4 {{ isRtl() ? 'mr-4 ml-0' : '' }}">
                        <p class="text-sm font-medium text-gray-600">{{ __('messages.average_daily_hours') }}</p>
                        <p class="text-2xl font-bold text-indigo-900">{{ $averageDailyHours ?? '00:00' }}</p>
                        <p class="text-xs text-gray-500">{{ __('messages.per_working_day') }}</p>
                    </div>
                </div>
            </div>
        </div>


        <!-- Week Calendar -->
        @if(isset($weekDays) && $weekDays->count() > 0)
            <div class="bg-white shadow-xl rounded-xl overflow-hidden border border-gray-200 mb-8">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h3 class="text-lg font-medium text-gray-900">{{ __('messages.weekly_schedule') }}</h3>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-7 divide-y md:divide-y-0 md:divide-x divide-gray-200">
                    @foreach($weekDays as $day)
                        <div class="p-4 min-h-[200px]">
                            <div class="font-medium text-sm text-gray-900 mb-2">
                                {{ __("messages." . strtolower($day['date']->format('l'))) }}
                                <span class="block text-xs text-gray-500">{{ $day['date']->format('M d') }}</span>
                            </div>

                            @if($day['shifts']->count() > 0)
                                @foreach($day['shifts'] as $shift)
                                    <div class="bg-blue-100 rounded-lg p-2 mb-2 text-sm">
                                        <div class="font-medium text-blue-900">
                                            {{ $shift->start_time }} - {{ $shift->end_time }}
                                        </div>
                                        @if($shift->role)
                                            <div class="text-blue-700 text-xs">{{ ucfirst($shift->role) }}</div>
                                        @endif
                                        @if($shift->task_id)
                                            <div class="text-blue-600 text-xs mt-1">
                                                <svg class="w-3 h-3 inline mr-1 {{ isRtl() ? 'ml-1 mr-0' : '' }}" fill="currentColor" viewBox="0 0 20 20">
                                                    <path d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                                </svg>
                                                {{ __('messages.task_assigned') }}
                                            </div>
                                        @endif
                                    </div>
                                @endforeach
                            @else
                                <div class="text-gray-400 text-xs">{{ __('messages.no_shifts') }}</div>
                            @endif
                        </div>
                    @endforeach
                </div>
            </div>
        @endif

        <!-- Current Task Assignments -->
        @if(isset($taskAssignments) && $taskAssignments->count() > 0)
            <div class="bg-white shadow-xl rounded-xl overflow-hidden border border-gray-200">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h3 class="text-lg font-medium text-gray-900">{{ __('messages.active_task_assignments') }}</h3>
                </div>

                <div class="divide-y divide-gray-200">
                    @foreach($taskAssignments as $task)
                        <div class="p-6 hover:bg-gray-50">
                            <div class="flex items-center justify-between">
                                <div class="flex-1">
                                    <div class="flex items-center">
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                            @if($task->priority === 'high') bg-red-100 text-red-800
                                            @elseif($task->priority === 'medium') bg-yellow-100 text-yellow-800
                                            @else bg-green-100 text-green-800 @endif">
                                            {{ __("messages." . ($task->priority ? $task->priority . '_priority' : 'normal_priority')) }}
                                        </span>
                                        <span class="ml-2 {{ isRtl() ? 'mr-2 ml-0' : '' }} text-sm text-gray-500">{{ __('messages.task_id') }} #{{ $task->id }}</span>
                                    </div>

                                    @if($task->maintenanceRequest)
                                        <h4 class="mt-2 text-sm font-medium text-gray-900">
                                            {{ __('messages.request_number') }} #{{ $task->maintenanceRequest->entry_number }}
                                        </h4>
                                        <p class="mt-1 text-sm text-gray-600">
                                            {{ Str::limit($task->maintenanceRequest->description_of_issue, 100) }}
                                        </p>
                                        @if($task->maintenanceRequest->store)
                                            <p class="mt-1 text-xs text-gray-500">
                                                {{ __('messages.store') }}: {{ $task->maintenanceRequest->store->store_number }} - {{ $task->maintenanceRequest->store->name }}
                                            </p>
                                        @endif
                                    @endif
                                </div>

                                <div class="ml-4 {{ isRtl() ? 'mr-4 ml-0' : '' }} text-right {{ isRtl() ? 'text-left' : '' }}">
                                    @if($task->due_date)
                                        <div class="text-sm text-gray-900">{{ __('messages.due_date') }}: {{ $task->due_date->format('M d, Y') }}</div>
                                        <div class="text-xs text-gray-500">{{ $task->due_date->format('g:i A') }}</div>
                                    @endif

                                    <div class="mt-2">
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                            @if($task->status === 'pending') bg-yellow-100 text-yellow-800
                                            @elseif($task->status === 'in_progress') bg-blue-100 text-blue-800
                                            @else bg-green-100 text-green-800 @endif">
                                            {{ __("messages." . $task->status) }}
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif

        <!-- Empty State -->
        @if((!isset($scheduleShifts) || $scheduleShifts->count() === 0) && (!isset($taskAssignments) || $taskAssignments->count() === 0))
            <div class="text-center py-16">
                <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                </svg>
                <h3 class="mt-4 text-lg font-medium text-gray-900">{{ __('messages.no_schedule_or_tasks') }}</h3>
                <p class="mt-2 text-sm text-gray-500">{{ __('messages.no_schedule_message') }}</p>
            </div>
        @endif
    </div>

    <style>
        /* RTL Support for Tailwind Classes */
        [dir="rtl"] .sm\:text-right {
            text-align: right;
        }

        [dir="rtl"] .sm\:pr-4 {
            padding-right: 1rem;
        }

        [dir="rtl"] .sm\:pl-3 {
            padding-left: 0.75rem;
        }
    </style>
@endsection
