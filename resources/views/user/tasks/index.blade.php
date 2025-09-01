@extends('layouts.app')

@section('title', __('messages.my_tasks'))

@section('content')
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8" dir="{{ getDirection() }}">
        <!-- Header Section -->
        <div class="sm:flex sm:items-center sm:justify-between mb-6">
            <div>
                <h1 class="text-3xl font-bold text-gray-900">{{ __('messages.my_tasks_title') }}</h1>
                <p class="mt-2 text-sm text-gray-600">{{ __('messages.my_tasks_subtitle') }}</p>
            </div>
        </div>

        <!-- Tasks List -->
        <div class="bg-white shadow-xl rounded-xl overflow-hidden border border-gray-200">
            @if($taskAssignments && $taskAssignments->count() > 0)
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gradient-to-r from-gray-50 to-gray-100">
                        <tr>
                            <th class="px-6 py-3 {{ isRtl() ? 'text-right' : 'text-left' }} text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('messages.task_id') }}</th>
                            <th class="px-6 py-3 {{ isRtl() ? 'text-right' : 'text-left' }} text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('messages.maintenance_request') }}</th>
                            <th class="px-6 py-3 {{ isRtl() ? 'text-right' : 'text-left' }} text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('messages.store') }}</th>
                            <th class="px-6 py-3 {{ isRtl() ? 'text-right' : 'text-left' }} text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('messages.equipment') }}</th>
                            <th class="px-6 py-3 {{ isRtl() ? 'text-right' : 'text-left' }} text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('messages.assignment_notes') }}</th>
                            <th class="px-6 py-3 {{ isRtl() ? 'text-right' : 'text-left' }} text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('messages.due_date') }}</th>
                            <th class="px-6 py-3 {{ isRtl() ? 'text-right' : 'text-left' }} text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('messages.priority') }}</th>
                            <th class="px-6 py-3 {{ isRtl() ? 'text-right' : 'text-left' }} text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('messages.status') }}</th>
                            <th class="px-6 py-3 {{ isRtl() ? 'text-right' : 'text-left' }} text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('messages.actions') }}</th>
                        </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                        @foreach($taskAssignments as $task)
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                    #{{ $task->id }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    @if($task->maintenanceRequest)
                                        <div>
                                            <div class="font-medium text-gray-900">{{ __('messages.request_number') }} #{{ $task->maintenanceRequest->entry_number }}</div>
                                            <div class="text-gray-500">{{ Str::limit($task->maintenanceRequest->description_of_issue, 50) }}</div>
                                        </div>
                                    @else
                                        N/A
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    @if($task->maintenanceRequest && $task->maintenanceRequest->store)
                                        <div>
                                            <div class="font-medium">{{ $task->maintenanceRequest->store->store_number }}</div>
                                            <div class="text-gray-500">{{ $task->maintenanceRequest->store->name }}</div>
                                        </div>
                                    @else
                                        N/A
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    {{ $task->maintenanceRequest->equipment_with_issue ?? __('messages.no_data') }}
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-500 max-w-xs">
                                    @if($task->scheduleShift && $task->scheduleShift->assignment_notes)
                                        <div class="relative">
                                            <div class="truncate" title="{{ $task->scheduleShift->assignment_notes }}">
                                                {{ Str::limit($task->scheduleShift->assignment_notes, 50) }}
                                            </div>
                                            @if(strlen($task->scheduleShift->assignment_notes) > 50)
                                                <button type="button"
                                                        class="text-blue-600 hover:text-blue-800 text-xs mt-1"
                                                        onclick="showNotesModal('{{ $task->id }}', '{{ addslashes($task->scheduleShift->assignment_notes) }}')">
                                                    {{ __('messages.view_full_notes') }}
                                                </button>
                                            @endif
                                        </div>
                                    @else
                                        <span class="text-gray-400 italic">{{ __('messages.no_notes') }}</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    @if($task->due_date)
                                        <div>
                                            <div class="font-medium">{{ $task->due_date->format('M d, Y') }}</div>
                                            <div class="text-gray-500">{{ $task->due_date->format('g:i A') }}</div>
                                        </div>
                                    @else
                                        {{ __('messages.no_due_date') }}
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full
                                        @if($task->priority === 'high') bg-red-100 text-red-800
                                        @elseif($task->priority === 'medium') bg-yellow-100 text-yellow-800
                                        @else bg-green-100 text-green-800 @endif">
                                        {{ __("messages." . ($task->priority ? $task->priority . '_priority' : 'normal_priority')) }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full
                                        @if($task->status === 'pending') bg-yellow-100 text-yellow-800
                                        @elseif($task->status === 'in_progress') bg-blue-100 text-blue-800
                                        @else bg-green-100 text-green-800 @endif">
                                        {{ __("messages." . $task->status) }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-right {{ isRtl() ? 'text-left' : '' }} text-sm font-medium">
                                    @if($task->status !== 'completed')
                                        <form action="{{ route('user.tasks.update-status', $task) }}" method="POST" class="inline">
                                            @csrf
                                            @method('PATCH')
                                            <input type="hidden" name="status" value="in_progress">
                                            <button type="submit" class="text-orange-600 hover:text-orange-900 {{ isRtl() ? 'ml-3' : 'mr-3' }}">{{ __('messages.start') }}</button>
                                        </form>
                                        <div class="inline-block relative">
                                            <button type="button" onclick="document.getElementById('complete-form-{{ $task->id }}').classList.toggle('hidden')" class="text-green-600 hover:text-green-900">{{ __('messages.complete') }}</button>
                                            <form action="{{ route('user.tasks.update-status', $task) }}" method="POST" id="complete-form-{{ $task->id }}" class="hidden absolute z-10 bg-white shadow-lg p-4 rounded-lg border border-gray-200 mt-2 w-80">
                                                @csrf
                                                @method('PATCH')
                                                <input type="hidden" name="status" value="completed">
                                                <div class="mb-3">
                                                    <label for="costs-{{ $task->id }}" class="block text-sm font-medium text-gray-700">{{ __('messages.costs') }}</label>
                                                    <input type="number" name="costs" id="costs-{{ $task->id }}" step="0.01" min="0" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-orange-500 focus:ring-orange-500 sm:text-sm" required>
                                                </div>
                                                <div class="mb-3">
                                                    <label for="how_we_fixed_it-{{ $task->id }}" class="block text-sm font-medium text-gray-700">{{ __('messages.how_we_fixed_it') }}</label>
                                                    <textarea name="how_we_fixed_it" id="how_we_fixed_it-{{ $task->id }}" rows="4" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-orange-500 focus:ring-orange-500 sm:text-sm" required></textarea>
                                                </div>
                                                <div class="flex justify-end space-x-2">
                                                    <button type="submit" class="inline-flex items-center px-3 py-1 bg-green-600 text-white rounded-md hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500">{{ __('messages.submit') }}</button>
                                                    <button type="button" onclick="document.getElementById('complete-form-{{ $task->id }}').classList.add('hidden')" class="inline-flex items-center px-3 py-1 bg-gray-200 text-gray-700 rounded-md hover:bg-gray-300">{{ __('messages.cancel') }}</button>
                                                </div>
                                            </form>
                                        </div>
                                    @else
                                        <span class="text-gray-400">{{ __('messages.task_complete') }}</span>
                                    @endif
                                </td>                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="text-center py-16">
                    <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                    </svg>
                    <h3 class="mt-4 text-lg font-medium text-gray-900">{{ __('messages.no_tasks_assigned') }}</h3>
                    <p class="mt-2 text-sm text-gray-500">{{ __('messages.no_tasks_message') }}</p>
                </div>
            @endif
        </div>
    </div>

    <style>
        /* RTL Support for Tailwind Classes */
        [dir="rtl"] .text-left {
            text-align: right;
        }

        [dir="rtl"] .text-right {
            text-align: left;
        }
    </style>
@endsection
