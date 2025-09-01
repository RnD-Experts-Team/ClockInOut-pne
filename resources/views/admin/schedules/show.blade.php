@extends('layouts.app')

@section('title', 'View Schedule')

@section('content')
    <div class="max-w-full mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <!-- Header Section -->
        <div class="sm:flex sm:items-center sm:justify-between mb-6">
            <div>
                <h1 class="text-3xl font-bold text-gray-900">{{ $schedule->name }}</h1>
                <p class="mt-2 text-sm text-gray-600">
                    {{ \Carbon\Carbon::parse($schedule->start_date)->format('M d') }} -
                    {{ \Carbon\Carbon::parse($schedule->end_date)->format('M d, Y') }}
                </p>
            </div>
            <div class="flex flex-wrap items-center gap-3 mt-4 sm:mt-0">
            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                @if($schedule->status === 'draft') bg-gray-100 text-gray-800
                @elseif($schedule->status === 'published') bg-blue-100 text-blue-800
                @elseif($schedule->status === 'active') bg-green-100 text-green-800
                @else bg-gray-100 text-gray-800
                @endif">
                {{ ucfirst($schedule->status) }}
            </span>

                @if($schedule->canEdit())
                    <a href="{{ route('admin.schedules.edit', $schedule) }}"
                       class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-lg text-white bg-orange-600 hover:bg-orange-700">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                        </svg>
                        Edit Schedule
                    </a>
                @endif

                <a href="{{ route('admin.schedules.index') }}"
                   class="inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-lg text-gray-700 bg-white hover:bg-gray-50">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                    </svg>
                    Back to Schedules
                </a>
            </div>
        </div>

        <!-- Schedule Statistics -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
            <div class="bg-white overflow-hidden shadow rounded-lg">
                <div class="p-5">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <svg class="h-6 w-6 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                            </svg>
                        </div>
                        <div class="ml-5 w-0 flex-1">
                            <dl>
                                <dt class="text-sm font-medium text-gray-500 truncate">Total Shifts</dt>
                                <dd class="text-lg font-medium text-gray-900">{{ $schedule->shifts->count() }}</dd>
                            </dl>
                        </div>
                    </div>
                </div>
            </div>

            <div class="bg-white overflow-hidden shadow rounded-lg">
                <div class="p-5">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <svg class="h-6 w-6 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                        </div>
                        <div class="ml-5 w-0 flex-1">
                            <dl>
                                <dt class="text-sm font-medium text-gray-500 truncate">Total Hours</dt>
                                <dd class="text-lg font-medium text-gray-900">
                                    {{ $schedule->shifts->sum(function($shift) {
                                        try {
                                            $start = \Carbon\Carbon::parse($shift->start_time);
                                            $end = \Carbon\Carbon::parse($shift->end_time);
                                            return $end->diffInHours($start);
                                        } catch (\Exception $e) {
                                            return 0;
                                        }
                                    }) }}
                                </dd>
                            </dl>
                        </div>
                    </div>
                </div>
            </div>

            <div class="bg-white overflow-hidden shadow rounded-lg">
                <div class="p-5">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <svg class="h-6 w-6 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3a4 4 0 118 0v4m-4 0l-8 4v12l8-4m0 0l8 4M12 11v8"></path>
                            </svg>
                        </div>
                        <div class="ml-5 w-0 flex-1">
                            <dl>
                                <dt class="text-sm font-medium text-gray-500 truncate">Employees</dt>
                                <dd class="text-lg font-medium text-gray-900">{{ $schedule->shifts->pluck('user_id')->unique()->count() }}</dd>
                            </dl>
                        </div>
                    </div>
                </div>
            </div>

            <div class="bg-white overflow-hidden shadow rounded-lg">
                <div class="p-5">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <svg class="h-6 w-6 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3a4 4 0 118 0v4m-4 0l-8 4v12l8-4m0 0l8 4M12 11v8"></path>
                            </svg>
                        </div>
                        <div class="ml-5 w-0 flex-1">
                            <dl>
                                <dt class="text-sm font-medium text-gray-500 truncate">Total Tasks</dt>
                                <dd class="text-lg font-medium text-gray-900">
                                    {{ $schedule->shifts->sum(function($shift) {
                                        return $shift->taskAssignments->count();
                                    }) }}
                                </dd>
                            </dl>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Schedule Grid Display with Tasks -->
        <div class="bg-white shadow rounded-lg overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200">
                <h3 class="text-lg leading-6 font-medium text-gray-900">Weekly Schedule Overview</h3>
            </div>

            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Employee</th>
                        @php
                            $startOfWeek = \Carbon\Carbon::parse($schedule->start_date)->startOfWeek();
                            $weekDays = [];
                            for($i = 0; $i < 7; $i++) {
                                $weekDays[] = $startOfWeek->copy()->addDays($i);
                            }
                        @endphp
                        @foreach($weekDays as $day)
                            <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">
                                <div class="flex flex-col items-center">
                                    <span class="font-semibold">{{ $day->format('D') }}</span>
                                    <span class="text-xs">{{ $day->format('m/d') }}</span>
                                </div>
                            </th>
                        @endforeach
                    </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                    @php
                        $employeeShifts = $schedule->shifts->groupBy('user_id');
                    @endphp

                    @foreach($employeeShifts as $userId => $userShifts)
                        @php $user = $userShifts->first()->user; @endphp
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center">
                                    <div class="flex-shrink-0 h-10 w-10">
                                        <div class="h-10 w-10 rounded-full bg-orange-100 flex items-center justify-center">
                                            <span class="text-sm font-medium text-orange-800">
                                                {{ strtoupper(substr($user->name ?? 'U', 0, 1)) }}
                                            </span>
                                        </div>
                                    </div>
                                    <div class="ml-4">
                                        <div class="text-sm font-medium text-gray-900">{{ $user->name ?? 'Unknown User' }}</div>
                                        <div class="text-sm text-gray-500">{{ $user->role ?? 'Employee' }}</div>
                                    </div>
                                </div>
                            </td>

                            @foreach($weekDays as $day)
                                @php
                                    $dayShifts = $userShifts->filter(function($shift) use ($day) {
                                        return \Carbon\Carbon::parse($shift->date)->isSameDay($day);
                                    });
                                @endphp

                                <td class="px-2 py-4 text-center align-top">
                                    @if($dayShifts->count() > 0)
                                        @foreach($dayShifts as $shift)
                                            <div class="mb-2 p-3 rounded-lg text-white text-xs min-h-[100px]"
                                                 style="background-color: {{ $shift->color ?? '#3b82f6' }}">

                                                <!-- Time and Role -->
                                                <div class="font-semibold mb-1">
                                                    {{ \Carbon\Carbon::parse($shift->start_time)->format('H:i') }} -
                                                    {{ \Carbon\Carbon::parse($shift->end_time)->format('H:i') }}
                                                </div>
                                                <div class="opacity-90 mb-2">{{ ucfirst($shift->role ?? 'general') }}</div>

                                                <!-- Tasks Section -->
                                                @if($shift->taskAssignments->count() > 0)
                                                    <div class="border-t border-white border-opacity-30 pt-2 mt-2">
                                                        <div class="flex items-center mb-1">
                                                            <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                                                <path fill-rule="evenodd" d="M6 2a1 1 0 00-1 1v1H4a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V6a2 2 0 00-2-2h-1V3a1 1 0 10-2 0v1H7V3a1 1 0 00-1-1zm0 5a1 1 0 000 2h8a1 1 0 100-2H6z" clip-rule="evenodd"></path>
                                                            </svg>
                                                            <span class="font-medium text-xs">Tasks ({{ $shift->taskAssignments->count() }})</span>
                                                        </div>
                                                        @foreach($shift->taskAssignments as $assignment)
                                                            <div class="text-xs opacity-90 mb-1 text-left bg-white bg-opacity-20 p-1 rounded">
                                                                <div class="font-medium flex items-center">
                                                                    <svg class="w-2 h-2 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                                                        <path fill-rule="evenodd" d="M5.05 4.05a7 7 0 119.9 9.9L10 18.9l-4.95-4.95a7 7 0 010-9.9zM10 11a2 2 0 100-4 2 2 0 000 4z" clip-rule="evenodd"></path>
                                                                    </svg>
                                                                    {{ $assignment->maintenanceRequest->store->name ?? 'Unknown Store' }}
                                                                </div>
                                                                <div class="truncate max-w-[120px] mt-1"
                                                                     title="{{ $assignment->maintenanceRequest->description_of_issue ?? 'No description' }}">
                                                                    {{ Str::limit($assignment->maintenanceRequest->description_of_issue ?? 'No description', 35) }}
                                                                </div>
                                                                @if(isset($assignment->maintenanceRequest->urgencyLevel))
                                                                    <span class="inline-block px-1 py-0.5 rounded text-xs font-medium mt-1
                                                                        @if($assignment->maintenanceRequest->urgencyLevel->name === 'High') bg-red-500 text-white
                                                                        @elseif($assignment->maintenanceRequest->urgencyLevel->name === 'Medium') bg-yellow-500 text-white
                                                                        @else bg-green-500 text-white
                                                                        @endif">
                                                                        {{ $assignment->maintenanceRequest->urgencyLevel->name ?? 'Normal' }}
                                                                    </span>
                                                                @endif
                                                            </div>
                                                            @if(!$loop->last)
                                                                <hr class="border-white border-opacity-30 my-1">
                                                            @endif
                                                        @endforeach
                                                    </div>
                                                @else
                                                    <div class="border-t border-white border-opacity-30 pt-2 mt-2 text-xs opacity-75">
                                                        No tasks assigned
                                                    </div>
                                                @endif
                                            </div>
                                        @endforeach
                                    @else
                                        <div class="text-gray-400 italic text-sm py-8">No shift</div>
                                    @endif
                                </td>
                            @endforeach
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Task Assignments Summary (if any) -->
        @php
            $allTaskAssignments = $schedule->shifts->flatMap->taskAssignments;
        @endphp

        @if($allTaskAssignments->count() > 0)
            <div class="mt-8 bg-white shadow rounded-lg overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h3 class="text-lg leading-6 font-medium text-gray-900">Task Assignments Summary</h3>
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Employee</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Store</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Task Description</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Priority</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                        </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                        @foreach($allTaskAssignments as $assignment)
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                    {{ $assignment->assignedUser->name ?? 'Unknown' }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    {{ \Carbon\Carbon::parse($assignment->assigned_date)->format('M d, Y') }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    {{ $assignment->maintenanceRequest->store->name ?? 'N/A' }}
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-900">
                                    <div class="max-w-xs">
                                        {{ Str::limit($assignment->maintenanceRequest->description_of_issue ?? 'No description', 100) }}
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    @if(isset($assignment->maintenanceRequest->urgencyLevel))
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                            @if($assignment->maintenanceRequest->urgencyLevel->name === 'High') bg-red-100 text-red-800
                                            @elseif($assignment->maintenanceRequest->urgencyLevel->name === 'Medium') bg-yellow-100 text-yellow-800
                                            @else bg-green-100 text-green-800
                                            @endif">
                                            {{ $assignment->maintenanceRequest->urgencyLevel->name }}
                                        </span>
                                    @else
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                                            Normal
                                        </span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                        @if($assignment->status === 'pending') bg-yellow-100 text-yellow-800
                                        @elseif($assignment->status === 'in_progress') bg-blue-100 text-blue-800
                                        @elseif($assignment->status === 'completed') bg-green-100 text-green-800
                                        @else bg-gray-100 text-gray-800
                                        @endif">
                                        {{ ucfirst(str_replace('_', ' ', $assignment->status)) }}
                                    </span>
                                </td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        @endif
    </div>
@endsection
