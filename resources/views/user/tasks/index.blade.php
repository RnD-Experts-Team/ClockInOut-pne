@extends('layouts.app')

@section('title', __('messages.my_tasks'))

@section('content')
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8" dir="{{ getDirection() }}">
        <!-- ✅ Enhanced Header Section -->
        <div class="bg-gradient-to-r from-orange-50 to-orange-100 rounded-2xl p-6 mb-8 border border-orange-200">
            <div class="sm:flex sm:items-center sm:justify-between">
                <div>
                    <div class="flex items-center space-x-3 {{ isRtl() ? 'rtl:space-x-reverse' : '' }} mb-4">
                        <div class="flex-shrink-0">
                            <div class="bg-orange-500 rounded-xl p-3">
                                <svg class="h-8 w-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"></path>
                                </svg>
                            </div>
                        </div>
                        <div>
                            <h1 class="text-3xl font-bold text-gray-900">{{ __('messages.my_tasks_title') }}</h1>
                            <p class="mt-1 text-lg text-gray-600">{{ __('messages.my_tasks_subtitle') }}</p>
                        </div>
                    </div>

                    <!-- ✅ Task Statistics -->
                    @if($taskAssignments && $taskAssignments->count() > 0)
                        @php
                            $totalTasks = $taskAssignments->count();
                            $pendingTasks = $taskAssignments->where('status', 'pending')->count();
                            $inProgressTasks = $taskAssignments->where('status', 'in_progress')->count();
                            $completedTasks = $taskAssignments->where('status', 'completed')->count();
                        @endphp

                        <div class="grid grid-cols-3 gap-4 mt-6">
                            <div class="bg-white rounded-xl p-4 border border-orange-200 shadow-sm">
                                <div class="text-2xl font-bold text-orange-600">{{ $totalTasks }}</div>
                                <div class="text-sm text-gray-600">Total Tasks</div>
                            </div>
                            <div class="bg-white rounded-xl p-4 border border-orange-200 shadow-sm">
                                <div class="text-2xl font-bold text-blue-600">{{ $pendingTasks + $inProgressTasks }}</div>
                                <div class="text-sm text-gray-600">Active Tasks</div>
                            </div>
                            <div class="bg-white rounded-xl p-4 border border-orange-200 shadow-sm">
                                <div class="text-2xl font-bold text-green-600">{{ $completedTasks }}</div>
                                <div class="text-sm text-gray-600">Completed</div>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- ✅ Card-Based Tasks List (No Horizontal Scrolling) -->
        <div class="space-y-6">
            @if($taskAssignments && $taskAssignments->count() > 0)
                @foreach($taskAssignments as $task)
                    <div class="bg-white shadow-xl rounded-2xl border border-gray-100 overflow-hidden hover:shadow-2xl transition-all duration-300 transform hover:scale-[1.02]">
                        <!-- Task Header -->
                        <div class="bg-gradient-to-r from-orange-50 to-orange-100 px-6 py-4 border-b border-orange-200">
                            <div class="flex items-center justify-between">
                                <div class="flex items-center space-x-4 {{ isRtl() ? 'rtl:space-x-reverse' : '' }}">
                                    <div class="flex-shrink-0">
                                        <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-bold text-orange-800 bg-orange-200">
                                            #{{ $task->id }}
                                        </span>
                                    </div>
                                    <div class="min-w-0 flex-1">
                                        <h3 class="text-lg font-bold text-gray-900 truncate">
                                            @if($task->maintenanceRequest)
                                                Request Number #{{ $task->maintenanceRequest->entry_number }}
                                            @else
                                                Task #{{ $task->id }}
                                            @endif
                                        </h3>
                                        <p class="text-sm text-gray-600 line-clamp-2">
                                            {{ $task->maintenanceRequest->description_of_issue ?? 'No description available' }}
                                        </p>
                                    </div>
                                </div>
                                <div class="flex items-center space-x-3 {{ isRtl() ? 'rtl:space-x-reverse' : '' }} flex-shrink-0">
                                    <!-- Priority Badge -->
                                    <span class="px-3 py-1 inline-flex text-xs leading-5 font-bold rounded-full
                                        @if($task->priority === 'high') bg-red-100 text-red-800 border border-red-200
                                        @elseif($task->priority === 'medium') bg-yellow-100 text-yellow-800 border border-yellow-200
                                        @else bg-green-100 text-green-800 border border-green-200 @endif">
                                        {{ ucfirst($task->priority ?? 'normal') }} Priority
                                    </span>

                                    <!-- Status Badge -->
                                    <span class="px-3 py-1 inline-flex text-xs leading-5 font-bold rounded-full
                                        @if($task->status === 'pending') bg-yellow-100 text-yellow-800 border border-yellow-200
                                        @elseif($task->status === 'in_progress') bg-blue-100 text-blue-800 border border-blue-200
                                        @else bg-green-100 text-green-800 border border-green-200 @endif">
                                        {{ ucfirst(str_replace('_', ' ', $task->status)) }}
                                    </span>
                                </div>
                            </div>
                        </div>

                        <!-- Task Content -->
                        <div class="p-6">
                            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                                <!-- Left Column -->
                                <div class="space-y-4">
                                    <!-- Store Information -->
                                    @if($task->maintenanceRequest && $task->maintenanceRequest->store)
                                        <div class="bg-gray-50 rounded-xl p-4">
                                            <div class="flex items-center space-x-3 {{ isRtl() ? 'rtl:space-x-reverse' : '' }}">
                                                <div class="flex-shrink-0">
                                                    <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-3-5h1m-1 4h1M21 21l-3-3"></path>
                                                    </svg>
                                                </div>
                                                <div class="min-w-0 flex-1">
                                                    <div class="text-sm font-semibold text-gray-900">Store</div>
                                                    <div class="text-lg font-bold text-gray-800">{{ $task->maintenanceRequest->store->store_number }}</div>
                                                    <div class="text-sm text-gray-600 truncate">{{ $task->maintenanceRequest->store->name }}</div>
                                                </div>
                                            </div>
                                        </div>
                                    @endif

                                    <!-- Equipment -->
                                    <div class="bg-gray-50 rounded-xl p-4">
                                        <div class="flex items-start space-x-3 {{ isRtl() ? 'rtl:space-x-reverse' : '' }}">
                                            <div class="flex-shrink-0 mt-0.5">
                                                <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path>
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                                </svg>
                                            </div>
                                            <div class="min-w-0 flex-1">
                                                <div class="text-sm font-semibold text-gray-900">Equipment</div>
                                                <div class="text-sm text-gray-600 break-words">{{ $task->maintenanceRequest->equipment_with_issue ?? 'No equipment specified' }}</div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Right Column -->
                                <div class="space-y-4">
                                    <!-- Due Date -->
                                    @if($task->due_date)
                                        <div class="bg-gray-50 rounded-xl p-4">
                                            <div class="flex items-center space-x-3 {{ isRtl() ? 'rtl:space-x-reverse' : '' }}">
                                                <div class="flex-shrink-0">
                                                    <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                                    </svg>
                                                </div>
                                                <div>
                                                    <div class="text-sm font-semibold text-gray-900">Due Date</div>
                                                    <div class="text-lg font-bold text-orange-600">{{ $task->due_date->format('M d, Y') }}</div>
                                                    <div class="text-sm text-orange-500">{{ $task->due_date->format('g:i A') }}</div>
                                                </div>
                                            </div>
                                        </div>
                                    @endif

                                    <!-- ✅ Fixed Assignment Notes with Proper Word Wrapping -->
                                    @if($task->scheduleShift && $task->scheduleShift->assignment_notes)
                                        <div class="bg-gray-50 rounded-xl p-4">
                                            <div class="flex items-start space-x-3 {{ isRtl() ? 'rtl:space-x-reverse' : '' }}">
                                                <div class="flex-shrink-0 mt-1">
                                                    <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                                    </svg>
                                                </div>
                                                <div class="min-w-0 flex-1">
                                                    <div class="text-sm font-semibold text-gray-900 mb-2">Assignment Notes</div>
                                                    <div class="text-sm text-gray-600 leading-relaxed break-words overflow-wrap-anywhere">
                                                        @if(strlen($task->scheduleShift->assignment_notes) > 100)
                                                            <div id="notes-short-{{ $task->id }}">
                                                                {{ Str::limit($task->scheduleShift->assignment_notes, 100) }}
                                                                <button type="button"
                                                                        onclick="toggleNotes('{{ $task->id }}')"
                                                                        class="text-orange-600 hover:text-orange-800 font-medium ml-1">
                                                                    Read More
                                                                </button>
                                                            </div>
                                                            <div id="notes-full-{{ $task->id }}" class="hidden">
                                                                {{ $task->scheduleShift->assignment_notes }}
                                                                <button type="button"
                                                                        onclick="toggleNotes('{{ $task->id }}')"
                                                                        class="text-orange-600 hover:text-orange-800 font-medium ml-1">
                                                                    Read Less
                                                                </button>
                                                            </div>
                                                        @else
                                                            {{ $task->scheduleShift->assignment_notes }}
                                                        @endif
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    @endif
                                </div>
                            </div>

                            <!-- Action Buttons -->
                            <div class="flex justify-end items-center mt-6 pt-6 border-t border-gray-200 space-x-3 {{ isRtl() ? 'rtl:space-x-reverse' : '' }}">
                                @if($task->status !== 'completed')
                                    <!-- Start Button -->
                                    @if($task->status === 'pending')
                                        <form action="{{ route('user.tasks.update-status', $task) }}" method="POST" class="inline">
                                            @csrf
                                            @method('PATCH')
                                            <input type="hidden" name="status" value="in_progress">
                                            <button type="submit" class="inline-flex items-center px-4 py-2 border border-orange-300 text-sm font-medium rounded-xl text-orange-700 bg-orange-50 hover:bg-orange-100 hover:border-orange-400 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-orange-500 transition-all duration-200 transform hover:scale-105">
                                                <svg class="{{ isRtl() ? 'ml-2 -mr-1' : '-ml-1 mr-2' }} h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.828 14.828a4 4 0 01-5.656 0M9 10h1m4 0h1m-6 4h1m4 0h1m-6-8h1m4 0h1M9 6h1m4 0h1"></path>
                                                </svg>
                                                Start
                                            </button>
                                        </form>
                                    @endif

                                    <!-- Complete Button -->
                                    <div class="inline-block relative">
                                        <button type="button"
                                                onclick="toggleCompleteForm('{{ $task->id }}')"
                                                class="inline-flex items-center px-6 py-2 border border-green-300 text-sm font-medium rounded-xl text-green-700 bg-green-50 hover:bg-green-100 hover:border-green-400 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 transition-all duration-200 transform hover:scale-105">
                                            <svg class="{{ isRtl() ? 'ml-2 -mr-1' : '-ml-1 mr-2' }} h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                            </svg>
                                            Complete
                                        </button>
                                    </div>
                                @else
                                    <span class="inline-flex items-center px-4 py-2 rounded-xl text-sm font-medium bg-green-100 text-green-800 border border-green-200">
                                        <svg class="{{ isRtl() ? 'ml-2 -mr-1' : '-ml-1 mr-2' }} h-4 w-4" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                                        </svg>
                                        Task Complete
                                    </span>
                                @endif
                            </div>
                        </div>
                    </div>

                    <!-- ✅ Complete Task Modal (Fixed Position) -->
                    <div id="complete-form-{{ $task->id }}" class="hidden fixed inset-0 z-50 overflow-y-auto">
                        <div class="flex items-center justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                            <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" onclick="toggleCompleteForm('{{ $task->id }}')"></div>

                            <div class="inline-block align-bottom bg-white rounded-2xl text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                                <form action="{{ route('user.tasks.update-status', $task) }}" method="POST" class="p-6">
                                    @csrf
                                    @method('PATCH')
                                    <input type="hidden" name="status" value="completed">

                                    <!-- Modal Header -->
                                    <div class="flex items-center justify-between mb-6">
                                        <div class="flex items-center space-x-3 {{ isRtl() ? 'rtl:space-x-reverse' : '' }}">
                                            <div class="flex-shrink-0">
                                                <div class="bg-green-500 rounded-full p-2">
                                                    <svg class="h-6 w-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                                    </svg>
                                                </div>
                                            </div>
                                            <h3 class="text-xl font-bold text-gray-900">Complete Task</h3>
                                        </div>
                                        <button type="button" onclick="toggleCompleteForm('{{ $task->id }}')" class="text-gray-400 hover:text-gray-600 transition-colors duration-200">
                                            <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                            </svg>
                                        </button>
                                    </div>

                                    <!-- Task Info -->
                                    <div class="bg-orange-50 rounded-xl p-4 mb-6 border border-orange-200">
                                        <div class="text-sm font-medium text-orange-800 mb-1">Task #{{ $task->id }}</div>
                                        <div class="text-xs text-orange-600 break-words">{{ $task->maintenanceRequest->description_of_issue ?? 'No description' }}</div>
                                    </div>

                                    <!-- Costs Input -->
                                    <div class="mb-6">
                                        <label for="costs-{{ $task->id }}" class="block text-sm font-bold text-gray-700 mb-2">
                                            <svg class="inline h-4 w-4 {{ isRtl() ? 'ml-1' : 'mr-1' }} text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1"></path>
                                            </svg>
                                            Costs
                                            <span class="text-red-500">*</span>
                                        </label>
                                        <div class="relative">
                                            <div class="absolute inset-y-0 {{ isRtl() ? 'right-0 pr-3' : 'left-0 pl-3' }} flex items-center pointer-events-none">
                                                <span class="text-gray-500 text-sm">$</span>
                                            </div>
                                            <input type="number"
                                                   name="costs"
                                                   id="costs-{{ $task->id }}"
                                                   step="0.01"
                                                   min="0"
                                                   class="block w-full {{ isRtl() ? 'pr-8' : 'pl-8' }} py-3 rounded-xl border-gray-300 shadow-sm focus:border-green-500 focus:ring-green-500 transition-all duration-200"
                                                   placeholder="0.00"
                                                   required>
                                        </div>
                                    </div>

                                    <!-- Fix Description -->
                                    <div class="mb-6">
                                        <label for="how_we_fixed_it-{{ $task->id }}" class="block text-sm font-bold text-gray-700 mb-2">
                                            <svg class="inline h-4 w-4 {{ isRtl() ? 'ml-1' : 'mr-1' }} text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                            </svg>
                                            How We Fixed It
                                            <span class="text-red-500">*</span>
                                        </label>
                                        <textarea name="how_we_fixed_it"
                                                  id="how_we_fixed_it-{{ $task->id }}"
                                                  rows="4"
                                                  class="block w-full rounded-xl border-gray-300 shadow-sm focus:border-green-500 focus:ring-green-500 transition-all duration-200"
                                                  placeholder="Describe how you fixed the issue..."
                                                  required></textarea>
                                    </div>

                                    <!-- Action Buttons -->
                                    <div class="flex {{ isRtl() ? 'flex-row-reverse' : '' }} justify-end space-x-3 {{ isRtl() ? 'rtl:space-x-reverse' : '' }}">
                                        <button type="button"
                                                onclick="toggleCompleteForm('{{ $task->id }}')"
                                                class="inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-xl text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500 transition-all duration-200">
                                            Cancel
                                        </button>
                                        <button type="submit"
                                                class="inline-flex items-center px-6 py-2 border border-transparent text-sm font-bold rounded-xl text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 transition-all duration-200 transform hover:scale-105 shadow-lg">
                                            <svg class="{{ isRtl() ? 'ml-2 -mr-1' : '-ml-1 mr-2' }} h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                            </svg>
                                            Complete Task
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                @endforeach
            @else
                <!-- ✅ Enhanced Empty State -->
                <div class="bg-white shadow-xl rounded-2xl border border-gray-100">
                    <div class="text-center py-20 px-6">
                        <div class="bg-gradient-to-br from-orange-100 to-orange-200 rounded-full p-8 w-32 h-32 mx-auto mb-6 flex items-center justify-center">
                            <svg class="h-16 w-16 text-orange-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"></path>
                            </svg>
                        </div>
                        <h3 class="text-xl font-bold text-gray-900 mb-2">No Tasks Assigned</h3>
                        <p class="text-sm text-gray-500 max-w-md mx-auto">You don't have any tasks assigned at the moment. Check back later.</p>
                    </div>
                </div>
            @endif
        </div>
    </div>

    <!-- ✅ Enhanced JavaScript -->
    <script>
        function toggleCompleteForm(taskId) {
            const modal = document.getElementById('complete-form-' + taskId);
            modal.classList.toggle('hidden');

            // Close other open modals
            document.querySelectorAll('[id^="complete-form-"]').forEach(function(element) {
                if (element.id !== 'complete-form-' + taskId) {
                    element.classList.add('hidden');
                }
            });

            // Focus on first input when opened
            if (!modal.classList.contains('hidden')) {
                setTimeout(() => {
                    const firstInput = modal.querySelector('input[name="costs"]');
                    if (firstInput) firstInput.focus();
                }, 100);
            }
        }

        // ✅ Toggle notes functionality
        function toggleNotes(taskId) {
            const shortDiv = document.getElementById('notes-short-' + taskId);
            const fullDiv = document.getElementById('notes-full-' + taskId);

            shortDiv.classList.toggle('hidden');
            fullDiv.classList.toggle('hidden');
        }
    </script>

    <style>
        /* ✅ Enhanced Custom Styles */
        .hover\:scale-\[1\.02\]:hover {
            transform: scale(1.02);
        }

        .hover\:scale-105:hover {
            transform: scale(1.05);
        }

        /* ✅ Proper text wrapping for long strings */
        .break-words {
            word-wrap: break-word;
            word-break: break-word;
        }

        .overflow-wrap-anywhere {
            overflow-wrap: anywhere;
        }

        /* ✅ Line clamp for descriptions */
        .line-clamp-2 {
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }

        /* Smooth animations */
        .transition-all {
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }

        /* Enhanced shadows */
        .shadow-2xl {
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
        }

        /* RTL Support */
        [dir="rtl"] .text-left {
            text-align: right;
        }

        [dir="rtl"] .text-right {
            text-align: left;
        }
    </style>
@endsection
