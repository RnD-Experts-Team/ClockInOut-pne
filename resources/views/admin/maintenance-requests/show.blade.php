{{-- resources/views/maintenance-requests/show.blade.php --}}
@extends('layouts.app')

@section('title', 'Maintenance Request Details')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <div class="flex justify-between items-center mb-6">
        <div>
            <h1 class="text-3xl font-bold text-gray-900">Maintenance Request #{{ $maintenanceRequest->entry_number }}</h1>
            <p class="mt-2 text-sm text-gray-700">View and manage maintenance request details</p>
        </div>
        <a href="{{ route('maintenance-requests.index') }}" 
           class="inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-lg text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500 transition-all duration-200">
            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
            </svg>
            Back to List
        </a>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <!-- Main Request Details -->
        <div class="lg:col-span-2 space-y-6">
            <!-- Basic Information -->
            <div class="bg-white shadow-sm ring-1 ring-gray-900/5 rounded-lg p-6">
                <h2 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
                    <svg class="w-5 h-5 mr-2 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    Request Details
                </h2>
                <dl class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Store</dt>
                        <dd class="mt-1 text-sm text-gray-900">{{ $maintenanceRequest->store }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Equipment</dt>
                        <dd class="mt-1 text-sm text-gray-900">{{ $maintenanceRequest->equipment_with_issue }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Requester</dt>
                        <dd class="mt-1 text-sm text-gray-900">{{ $maintenanceRequest->requester->full_name }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Reviewed By</dt>
                        <dd class="mt-1 text-sm text-gray-900">{{ $maintenanceRequest->reviewedByManager->full_name }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Urgency Level</dt>
                        <dd class="mt-1">
                            @switch($maintenanceRequest->urgencyLevel->name)
                                @case('Impacts Sales')
                                    <span class="inline-flex items-center px-3 py-1 text-sm font-semibold rounded-full bg-red-100 text-red-800">
                                        <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                                        </svg>
                                        {{ $maintenanceRequest->urgencyLevel->name }}
                                    </span>
                                    @break
                                @case('High')
                                    <span class="inline-flex items-center px-3 py-1 text-sm font-semibold rounded-full bg-orange-100 text-orange-800">
                                        <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                                        </svg>
                                        {{ $maintenanceRequest->urgencyLevel->name }}
                                    </span>
                                    @break
                                @case('Medium')
                                    <span class="inline-flex items-center px-3 py-1 text-sm font-semibold rounded-full bg-blue-100 text-blue-800">
                                        <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd" />
                                        </svg>
                                        {{ $maintenanceRequest->urgencyLevel->name }}
                                    </span>
                                    @break
                                @case('Low')
                                    <span class="inline-flex items-center px-3 py-1 text-sm font-semibold rounded-full bg-gray-100 text-gray-800">
                                        <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd" />
                                        </svg>
                                        {{ $maintenanceRequest->urgencyLevel->name }}
                                    </span>
                                    @break
                            @endswitch
                        </dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Basic Troubleshoot Done</dt>
                        <dd class="mt-1">
                            <span class="inline-flex items-center px-2 py-1 text-sm font-semibold rounded-full {{ $maintenanceRequest->basic_troubleshoot_done ? 'bg-green-100 text-green-800' : 'bg-orange-100 text-orange-800' }}">
                                @if($maintenanceRequest->basic_troubleshoot_done)
                                    <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                                    </svg>
                                    Yes
                                @else
                                    <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                                    </svg>
                                    No
                                @endif
                            </span>
                        </dd>
                    </div>
                    <div class="md:col-span-2">
                        <dt class="text-sm font-medium text-gray-500">Description</dt>
                        <dd class="mt-1 text-sm text-gray-900 whitespace-pre-wrap">{{ $maintenanceRequest->description_of_issue }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Request Date</dt>
                        <dd class="mt-1 text-sm text-gray-900">{{ $maintenanceRequest->request_date->format('M d, Y') }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Submitted Date</dt>
                        <dd class="mt-1 text-sm text-gray-900">{{ $maintenanceRequest->date_submitted->format('M d, Y H:i:s') }}</dd>
                    </div>
                </dl>
            </div>

            <!-- Attachments -->
            @if($maintenanceRequest->attachments->count() > 0)
                <div class="bg-white shadow-sm ring-1 ring-gray-900/5 rounded-lg p-6">
                    <h2 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
                        <svg class="w-5 h-5 mr-2 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13" />
                        </svg>
                        Attachments
                    </h2>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        @foreach($maintenanceRequest->attachments as $attachment)
                            <div class="border border-gray-200 rounded-lg p-4 hover:border-blue-300 transition-colors duration-150">
                                <div class="flex items-center">
                                    <div class="flex-shrink-0 mr-4">
                                        <svg class="w-8 h-8 text-gray-400" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M4 4a2 2 0 012-2h4.586A2 2 0 0112 2.586L15.414 6A2 2 0 0116 7.414V16a2 2 0 01-2 2H6a2 2 0 01-2-2V4zm2 6a1 1 0 011-1h6a1 1 0 110 2H7a1 1 0 01-1-1zm1 3a1 1 0 100 2h6a1 1 0 100-2H7z" clip-rule="evenodd" />
                                        </svg>
                                    </div>
                                    <div class="flex-grow min-w-0">
                                        <h3 class="text-sm font-medium text-gray-900 truncate">{{ $attachment->file_name }}</h3>
                                        <p class="text-sm text-gray-500">{{ $attachment->content_type }}</p>
                                        <p class="text-sm text-gray-500">{{ $attachment->formatted_file_size }}</p>
                                        <a href="{{ $attachment->download_url }}" 
                                           class="mt-2 inline-flex items-center text-sm text-blue-600 hover:text-blue-800 font-medium" 
                                           target="_blank">
                                            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                            </svg>
                                            Download
                                        </a>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif

            <!-- Links -->
            @if($maintenanceRequest->links->count() > 0)
                <div class="bg-white shadow-sm ring-1 ring-gray-900/5 rounded-lg p-6">
                    <h2 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
                        <svg class="w-5 h-5 mr-2 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1" />
                        </svg>
                        Related Links
                    </h2>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        @foreach($maintenanceRequest->links as $link)
                            <a href="{{ $link->download_url }}" 
                               class="flex items-center p-3 border border-gray-200 rounded-lg hover:border-blue-300 hover:bg-blue-50 transition-colors duration-150 group" 
                               target="_blank">
                                <svg class="w-5 h-5 mr-3 text-gray-400 group-hover:text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1" />
                                </svg>
                                <span class="text-sm font-medium text-gray-700 group-hover:text-blue-700">{{ $link->description }}</span>
                            </a>
                        @endforeach
                    </div>
                </div>
            @endif
        </div>

        <!-- Status and Actions Sidebar -->
        <div class="space-y-6">
            <!-- Current Status -->
            <div class="bg-white shadow-sm ring-1 ring-gray-900/5 rounded-lg p-6">
                <h2 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
                    <svg class="w-5 h-5 mr-2 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                    </svg>
                    Current Status
                </h2>
                <div class="text-center">
                    @switch($maintenanceRequest->status)
                        @case('on_hold')
                            <svg class="w-16 h-16 mx-auto mb-3 text-orange-500" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8 7a1 1 0 00-1 1v4a1 1 0 001 1h4a1 1 0 001-1V8a1 1 0 00-1-1H8z" clip-rule="evenodd" />
                            </svg>
                            <h3 class="text-xl font-semibold text-orange-600 mb-2">On Hold</h3>
                            @break
                        @case('in_progress')
                            <svg class="w-16 h-16 mx-auto mb-3 text-blue-500" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM9.555 7.168A1 1 0 008 8v4a1 1 0 001.555.832l3-2a1 1 0 000-1.664l-3-2z" clip-rule="evenodd" />
                            </svg>
                            <h3 class="text-xl font-semibold text-blue-600 mb-2">In Progress</h3>
                            @break
                        @case('done')
                            <svg class="w-16 h-16 mx-auto mb-3 text-green-500" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                            </svg>
                            <h3 class="text-xl font-semibold text-green-600 mb-2">Done</h3>
                            @break
                        @case('canceled')
                            <svg class="w-16 h-16 mx-auto mb-3 text-red-500" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                            </svg>
                            <h3 class="text-xl font-semibold text-red-600 mb-2">Canceled</h3>
                            @break
                    @endswitch
                    
                    @if($maintenanceRequest->status === 'done')
                        <div class="mt-4 p-4 bg-green-50 rounded-lg">
                            <div class="grid grid-cols-1 gap-4">
                                <div class="text-center">
                                    <h4 class="text-sm font-medium text-gray-900 mb-2">Total Costs:</h4>
                                    <p class="text-2xl font-bold text-green-700">${{ number_format($maintenanceRequest->costs, 2) }}</p>
                                </div>
                                <div>
                                    <h4 class="text-sm font-medium text-gray-900 mb-2">How We Fixed It:</h4>
                                    <p class="text-sm text-gray-700 whitespace-pre-wrap bg-white p-3 rounded border">{{ $maintenanceRequest->how_we_fixed_it }}</p>
                                </div>
                            </div>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Status Update Actions -->
            @if(!in_array($maintenanceRequest->status, ['done', 'canceled']))
                <div class="bg-white shadow-sm ring-1 ring-gray-900/5 rounded-lg p-6">
                    <h2 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
                        <svg class="w-5 h-5 mr-2 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                        </svg>
                        Update Status
                    </h2>
                    <form action="{{ route('maintenance-requests.update-status', $maintenanceRequest) }}" method="POST">
                        @csrf
                        @method('PATCH')
                        <div class="space-y-4">
                            <div>
                                <label for="status" class="block text-sm font-medium text-gray-700 mb-2">New Status</label>
                                <select name="status" id="status" class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm" required>
                                    @if($maintenanceRequest->status === 'on_hold')
                                        <option value="in_progress">In Progress</option>
                                        <option value="done">Done</option>
                                        <option value="canceled">Canceled</option>
                                    @elseif($maintenanceRequest->status === 'in_progress')
                                        <option value="on_hold">On Hold</option>
                                        <option value="done">Done</option>
                                        <option value="canceled">Canceled</option>
                                    @endif
                                </select>
                            </div>
                            <div id="costsField" style="display: none;">
                                <label for="costs" class="block text-sm font-medium text-gray-700 mb-2">Costs *</label>
                                <input type="number" name="costs" id="costs" step="0.01" min="0" class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm" placeholder="0.00">
                            </div>
                            <div id="fixField" style="display: none;">
                                <label for="how_we_fixed_it" class="block text-sm font-medium text-gray-700 mb-2">How We Fixed It *</label>
                                <textarea name="how_we_fixed_it" id="how_we_fixed_it" rows="4" class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm" placeholder="Please describe how the issue was fixed..."></textarea>
                            </div>
                            <button type="submit" class="w-full inline-flex justify-center items-center px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors duration-200 font-medium">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                                </svg>
                                Update Status
                            </button>
                        </div>
                    </form>
                </div>
            @endif

            <!-- Delete Action -->
            <div class="bg-white shadow-sm ring-1 ring-gray-900/5 rounded-lg p-6 border-red-200">
                <h2 class="text-lg font-semibold text-red-900 mb-4 flex items-center">
                    <svg class="w-5 h-5 mr-2 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.34 16.5c-.77.833.192 2.5 1.732 2.5z" />
                    </svg>
                    Danger Zone
                </h2>
                <p class="text-sm text-red-700 mb-4">This action cannot be undone. This will permanently delete the maintenance request.</p>
                <form action="{{ route('maintenance-requests.destroy', $maintenanceRequest) }}" 
                      method="POST"
                      onsubmit="return confirm('Are you sure you want to delete this maintenance request? This action cannot be undone.')">
                    @csrf
                    @method('DELETE')
                    <button type="submit" 
                            class="w-full inline-flex justify-center items-center px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 transition-colors duration-200 font-medium">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                        </svg>
                        Delete Request
                    </button>
                </form>
            </div>
        </div>
    </div>

    <!-- Status History -->
    @if($maintenanceRequest->statusHistories->count() > 0)
        <div class="mt-8 bg-white shadow-sm ring-1 ring-gray-900/5 rounded-lg p-6">
            <h2 class="text-lg font-semibold text-gray-900 mb-6 flex items-center">
                <svg class="w-5 h-5 mr-2 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                Status History
            </h2>
            <div class="flow-root">
                <ul class="-mb-8">
                    @foreach($maintenanceRequest->statusHistories->sortByDesc('changed_at') as $index => $history)
                        <li>
                            <div class="relative pb-8">
                                @if(!$loop->last)
                                    <span class="absolute top-4 left-4 -ml-px h-full w-0.5 bg-gray-200" aria-hidden="true"></span>
                                @endif
                                <div class="relative flex space-x-3">
                                    <div>
                                        <span class="h-8 w-8 rounded-full flex items-center justify-center ring-8 ring-white
                                            @switch($history->new_status)
                                                @case('on_hold') bg-orange-500 @break
                                                @case('in_progress') bg-blue-500 @break
                                                @case('done') bg-green-500 @break
                                                @case('canceled') bg-red-500 @break
                                            @endswitch">
                                            @switch($history->new_status)
                                                @case('on_hold')
                                                    <svg class="w-4 h-4 text-white" fill="currentColor" viewBox="0 0 20 20">
                                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8 7a1 1 0 00-1 1v4a1 1 0 001 1h4a1 1 0 001-1V8a1 1 0 00-1-1H8z" clip-rule="evenodd" />
                                                    </svg>
                                                    @break
                                                @case('in_progress')
                                                    <svg class="w-4 h-4 text-white" fill="currentColor" viewBox="0 0 20 20">
                                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM9.555 7.168A1 1 0 008 8v4a1 1 0 001.555.832l3-2a1 1 0 000-1.664l-3-2z" clip-rule="evenodd" />
                                                    </svg>
                                                    @break
                                                @case('done')
                                                    <svg class="w-4 h-4 text-white" fill="currentColor" viewBox="0 0 20 20">
                                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                                                    </svg>
                                                    @break
                                                @case('canceled')
                                                    <svg class="w-4 h-4 text-white" fill="currentColor" viewBox="0 0 20 20">
                                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                                                    </svg>
                                                    @break
                                            @endswitch
                                        </span>
                                    </div>
                                    <div class="min-w-0 flex-1 pt-1.5 flex justify-between space-x-4">
                                        <div>
                                            <p class="text-sm text-gray-500">
                                                Status changed from 
                                                <span class="font-medium text-gray-900">{{ $history->old_status ? str_replace('_', ' ', $history->old_status) : 'N/A' }}</span>
                                                to 
                                                <span class="font-medium text-gray-900">{{ str_replace('_', ' ', $history->new_status) }}</span>
                                                @if($history->changedByUser)
                                                    by <span class="font-medium text-gray-900">{{ $history->changedByUser->name }}</span>
                                                @endif
                                            </p>
                                            @if($history->notes)
                                                <p class="mt-2 text-sm text-gray-700 bg-gray-50 rounded-md p-2">{{ $history->notes }}</p>
                                            @endif
                                        </div>
                                        <div class="text-right text-sm whitespace-nowrap text-gray-500">
                                            <time datetime="{{ $history->changed_at->toDateTimeString() }}">
                                                {{ $history->changed_at->format('M d, Y H:i') }}
                                            </time>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </li>
                    @endforeach
                </ul>
            </div>
        </div>
    @endif
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const statusSelect = document.getElementById('status');
    const costsField = document.getElementById('costsField');
    const fixField = document.getElementById('fixField');
    const costsInput = document.getElementById('costs');
    const fixTextarea = document.getElementById('how_we_fixed_it');

    if (statusSelect) {
        statusSelect.addEventListener('change', function() {
            if (this.value === 'done') {
                costsField.style.display = 'block';
                fixField.style.display = 'block';
                costsInput.required = true;
                fixTextarea.required = true;
            } else {
                costsField.style.display = 'none';
                fixField.style.display = 'none';
                costsInput.required = false;
                fixTextarea.required = false;
            }
        });
    }
});
</script>
@endsection
