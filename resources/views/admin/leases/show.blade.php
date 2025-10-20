@extends('layouts.app')

@section('title', 'Lease Details')

@section('content')
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <div class="flex justify-between items-center mb-6">
            <div>
                <h1 class="text-3xl font-bold text-black-900">
                    Lease Details - Store #{{ $lease->store_number }}
                </h1>
                @if($lease->store)
                    <p class="mt-2 text-sm text-black-700 font-semibold">
                        📍 {{ $lease->store->name }}
                    </p>
                @else
                    <p class="mt-2 text-sm text-red-600">
                        ⚠️ No store linked
                    </p>
                @endif
                @if($lease->name && $lease->name !== ($lease->store->name ?? ''))
                    <p class="mt-1 text-xs text-black-600 italic">
                        Lease Name: {{ $lease->name }}
                    </p>
                @endif
            </div>
            <div class="flex space-x-3">
                <a href="{{ route('leases.edit', $lease) }}"
                   class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-lg text-white bg-orange-600 hover:bg-orange-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-orange-500 transition-all duration-200">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                    </svg>
                    Edit Lease
                </a>
                <a href="{{ route('leases.index') }}"
                   class="inline-flex items-center px-4 py-2 border border-orange-300 text-sm font-medium rounded-lg text-black-700 bg-orange-50 hover:bg-orange-100 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-orange-500 transition-all duration-200">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                    </svg>
                    Back to List
                </a>
            </div>
        </div>

        <!-- NEW: Renewal Status Alert -->
        @if($lease->renewal_date)
            @php $renewalStatusInfo = $lease->renewal_status_info; @endphp
            <div class="mb-6 {{ $lease->is_renewal_overdue ? 'bg-red-50 border-l-4 border-red-400' : 'bg-blue-50 border-l-4 border-blue-400' }} p-4 rounded-lg shadow-sm">
                <div class="flex items-center justify-between">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <svg class="h-5 w-5 {{ $lease->is_renewal_overdue ? 'text-red-400' : 'text-blue-400' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                @if($lease->is_renewal_overdue)
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                                @else
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                @endif
                            </svg>
                        </div>
                        <div class="ml-3">
                            <h3 class="text-sm font-medium {{ $lease->is_renewal_overdue ? 'text-red-800' : 'text-blue-800' }}">
                                {{ $lease->is_renewal_overdue ? '🚨 Renewal Overdue' : '🔔 Renewal Scheduled' }}
                            </h3>
                            <div class="mt-2 text-sm {{ $lease->is_renewal_overdue ? 'text-red-700' : 'text-blue-700' }}">
                                <p>
                                    <strong>Status:</strong>
                                    <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium {{ $renewalStatusInfo['class'] }}">
                                        {{ $renewalStatusInfo['message'] }}
                                    </span>
                                </p>
                                <p class="mt-1">
                                    <strong>Due Date:</strong> {{ $lease->formatted_renewal_date }}
                                    @if($lease->days_until_renewal)
                                        ({{ abs($lease->days_until_renewal) }} days {{ $lease->days_until_renewal < 0 ? 'overdue' : 'remaining' }})
                                    @endif
                                </p>
                                @if($lease->renewal_notes)
                                    <p class="mt-1"><strong>Notes:</strong> {{ $lease->renewal_notes }}</p>
                                @endif
                            </div>
                        </div>
                    </div>
                    <div class="flex space-x-2">
                        @if($lease->renewal_status === 'pending')
                            <button onclick="sendRenewalReminder({{ $lease->id }})"
                                    class="inline-flex items-center px-3 py-1 border border-blue-300 text-xs font-medium rounded text-blue-700 bg-blue-100 hover:bg-blue-200">
                                🔔 Send Reminder
                            </button>
                            <button onclick="completeRenewal({{ $lease->id }})"
                                    class="inline-flex items-center px-3 py-1 border border-green-300 text-xs font-medium rounded text-green-700 bg-green-100 hover:bg-green-200">
                                ✅ Complete Renewal
                            </button>
                        @elseif($lease->renewal_status === 'completed')
                            <span class="inline-flex items-center px-3 py-1 text-xs font-medium rounded text-green-700 bg-green-100">
                                ✅ Renewal Completed
                            </span>
                        @endif
                    </div>
                </div>
            </div>
        @endif

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <!-- Main Information -->
            <div class="lg:col-span-2 space-y-6">
                <!-- Basic Store Information -->
                <div class="bg-orange-50 shadow-sm ring-1 ring-orange-900/5 rounded-lg p-6">
                    <h2 class="text-lg font-semibold text-black-900 mb-4 flex items-center">
                        <svg class="w-5 h-5 mr-2 text-black-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-2m-14 0h2m0 0V9a2 2 0 012-2h2a2 2 0 012 2v10" />
                        </svg>
                        Store Information
                    </h2>
                    <dl class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <dt class="text-sm font-medium text-black-500">Store Number</dt>
                            <dd class="mt-1 text-sm text-black-900">{{ $lease->store_number ?? 'N/A' }}</dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-black-500">Store Name</dt>
                            <dd class="mt-1 text-sm text-black-900 font-semibold">
                                @if($lease->store)
                                    {{ $lease->store->name }}
                                @else
                                    <span class="text-red-600">No Store Linked</span>
                                @endif
                            </dd>
                        </div>
                        @if($lease->name && $lease->name !== ($lease->store->name ?? ''))
                            <div class="md:col-span-2">
                                <dt class="text-sm font-medium text-black-500">Lease Name</dt>
                                <dd class="mt-1 text-sm text-black-900 italic">{{ $lease->name }}</dd>
                            </div>
                        @endif
                        <div class="md:col-span-2">
                            <dt class="text-sm font-medium text-black-500">Store Address</dt>
                            <dd class="mt-1 text-sm text-black-900">{{ $lease->store_address ?? 'N/A' }}</dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-black-500">SQF</dt>
                            <dd class="mt-1 text-sm text-black-900">{{ $lease->sqf ? number_format($lease->sqf) . ' SQF' : 'N/A' }}</dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-black-500">HVAC</dt>
                            <dd class="mt-1">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $lease->hvac ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                {{ $lease->hvac ? 'Available' : 'Not Available' }}
                            </span>
                            </dd>
                        </div>
                    </dl>
                </div>

                <!-- Store Details (additional store info from Store model) -->
                @if($lease->store)
                    <div class="bg-orange-50 shadow-sm ring-1 ring-orange-900/5 rounded-lg p-6">
                        <h2 class="text-lg font-semibold text-black-900 mb-4 flex items-center">
                            <svg class="w-5 h-5 mr-2 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.746 0 3.332.477 4.5 1.253v13C19.832 18.477 18.246 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                            </svg>
                            Store Record Details
                        </h2>
                        <dl class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <dt class="text-sm font-medium text-black-500">Store ID</dt>
                                <dd class="mt-1 text-sm text-black-900">{{ $lease->store->id }}</dd>
                            </div>
                            <div>
                                <dt class="text-sm font-medium text-black-500">Store Status</dt>
                                <dd class="mt-1">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $lease->store->is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                        {{ $lease->store->is_active ? 'Active' : 'Inactive' }}
                                    </span>
                                </dd>
                            </div>
                            @if($lease->store->address && $lease->store->address !== $lease->store_address)
                                <div class="md:col-span-2">
                                    <dt class="text-sm font-medium text-black-500">Store Address (from Store Record)</dt>
                                    <dd class="mt-1 text-sm text-black-900">{{ $lease->store->address }}</dd>
                                </div>
                            @endif
                        </dl>
                    </div>
                @endif

                <!-- Financial Information -->
                <div class="bg-orange-50 shadow-sm ring-1 ring-orange-900/5 rounded-lg p-6">
                    <h2 class="text-lg font-semibold text-black-900 mb-4 flex items-center">
                        <svg class="w-5 h-5 mr-2 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1" />
                        </svg>
                        Financial Details
                    </h2>
                    <div class="grid grid-cols-2 md:grid-cols-3 gap-6">
                        <div class="text-center p-4 bg-orange-100 rounded-lg">
                            <dt class="text-sm font-medium text-black-500">AWS</dt>
                            <dd class="mt-1 text-lg font-semibold text-black-600">${{ number_format($lease->aws ?? 0, 2) }}</dd>
                        </div>
                        <div class="text-center p-4 bg-orange-100 rounded-lg">
                            <dt class="text-sm font-medium text-black-500">Base Rent</dt>
                            <dd class="mt-1 text-lg font-semibold text-black-600">${{ number_format($lease->base_rent ?? 0, 2) }}</dd>
                        </div>
                        <div class="text-center p-4 bg-orange-100 rounded-lg">
                            <dt class="text-sm font-medium text-black-500">CAM</dt>
                            <dd class="mt-1 text-lg font-semibold text-black-600">${{ number_format($lease->cam ?? 0, 2) }}</dd>
                        </div>
                        <div class="text-center p-4 bg-orange-100 rounded-lg">
                            <dt class="text-sm font-medium text-black-500">Insurance</dt>
                            <dd class="mt-1 text-lg font-semibold text-black-600">${{ number_format($lease->insurance ?? 0, 2) }}</dd>
                        </div>
                        <div class="text-center p-4 bg-orange-100 rounded-lg">
                            <dt class="text-sm font-medium text-black-500">RE Taxes</dt>
                            <dd class="mt-1 text-lg font-semibold text-black-600">${{ number_format($lease->re_taxes ?? 0, 2) }}</dd>
                        </div>
                        <div class="text-center p-4 bg-orange-100 rounded-lg">
                            <dt class="text-sm font-medium text-black-500">Others</dt>
                            <dd class="mt-1 text-lg font-semibold text-black-600">${{ number_format($lease->others ?? 0, 2) }}</dd>
                        </div>
                    </div>
                    <div class="mt-6 p-4 bg-orange-100 rounded-lg">
                        <div class="flex justify-between items-center">
                            <span class="text-lg font-medium text-black-900">Total Monthly Rent:</span>
                            <span class="text-xl font-bold text-black-600">${{ number_format($lease->total_rent, 2) }}</span>
                        </div>
                    </div>
                </div>

                <!-- Term Information -->
                <div class="bg-orange-50 shadow-sm ring-1 ring-orange-900/5 rounded-lg p-6">
                    <h2 class="text-lg font-semibold text-black-900 mb-4 flex items-center">
                        <svg class="w-5 h-5 mr-2 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                        </svg>
                        Lease Terms & Expiration Dates
                    </h2>
                    @if($lease->term_expiration_dates->count() > 0)
                        <div class="space-y-4">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <dt class="text-sm font-medium text-black-500">Renewal Options</dt>
                                    <dd class="mt-1 text-sm text-black-900">{{ $lease->renewal_options ?? 'N/A' }}</dd>
                                </div>
                                <div>
                                    <dt class="text-sm font-medium text-black-500">Total Terms Available</dt>
                                    <dd class="mt-1 text-sm text-black-900">{{ $lease->term_expiration_dates->count() }} terms</dd>
                                </div>
                            </div>

                            <div class="mt-4">
                                <h3 class="text-sm font-medium text-black-900 mb-3">All Term Expiration Dates</h3>
                                <div class="space-y-2">
                                    @foreach($lease->term_expiration_dates as $term)
                                        <div class="flex justify-between items-center p-3 {{ $term['is_initial'] ? 'bg-orange-100 border-l-4 border-orange-400' : 'bg-orange-100' }} rounded">
                                            <span class="font-medium {{ $term['is_initial'] ? 'text-black-900' : 'text-black-900' }}">{{ $term['term'] }}</span>
                                            <span class="text-sm {{ $term['is_initial'] ? 'text-black-700' : 'text-black-700' }}">{{ $term['expiration_date']->format('M d, Y') }}</span>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    @else
                        <p class="text-black-500">No term information available</p>
                    @endif
                </div>

                <!-- NEW: Renewal Management Details -->
                @if($lease->renewal_date || auth()->user()->isAdmin())
                    <div class="bg-blue-50 shadow-sm ring-1 ring-blue-900/5 rounded-lg p-6">
                        <h2 class="text-lg font-semibold text-black-900 mb-4 flex items-center">
                            <svg class="w-5 h-5 mr-2 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            Renewal Management
                        </h2>
                        @if($lease->renewal_date)
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div>
                                    <dt class="text-sm font-medium text-black-500">Renewal Date</dt>
                                    <dd class="mt-1 text-sm text-black-900 font-semibold">{{ $lease->formatted_renewal_date }}</dd>
                                </div>
                                <div>
                                    <dt class="text-sm font-medium text-black-500">Days Until Renewal</dt>
                                    <dd class="mt-1 text-sm text-black-900">
                                        @if($lease->days_until_renewal !== null)
                                            @if($lease->days_until_renewal < 0)
                                                <span class="text-red-600 font-semibold">{{ abs($lease->days_until_renewal) }} days overdue</span>
                                            @elseif($lease->days_until_renewal == 0)
                                                <span class="text-orange-600 font-semibold">Due today</span>
                                            @else
                                                <span class="text-blue-600">{{ $lease->days_until_renewal }} days remaining</span>
                                            @endif
                                        @else
                                            N/A
                                        @endif
                                    </dd>
                                </div>
                                <div>
                                    <dt class="text-sm font-medium text-black-500">Renewal Status</dt>
                                    <dd class="mt-1">
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $renewalStatusInfo['class'] }}">
                                            {{ $renewalStatusInfo['message'] }}
                                        </span>
                                    </dd>
                                </div>
                                @if($lease->renewalCreatedBy)
                                    <div>
                                        <dt class="text-sm font-medium text-black-500">Set By</dt>
                                        <dd class="mt-1 text-sm text-black-900">{{ $lease->renewalCreatedBy->name }}</dd>
                                    </div>
                                @endif
                                @if($lease->renewal_reminder_sent_at)
                                    <div class="md:col-span-2">
                                        <dt class="text-sm font-medium text-black-500">Last Reminder Sent</dt>
                                        <dd class="mt-1 text-sm text-black-900">{{ $lease->renewal_reminder_sent_at->format('M j, Y g:i A') }}</dd>
                                    </div>
                                @endif
                                @if($lease->renewal_notes)
                                    <div class="md:col-span-2">
                                        <dt class="text-sm font-medium text-black-500">Renewal Notes</dt>
                                        <dd class="mt-1 text-sm text-black-900 bg-blue-100 p-3 rounded whitespace-pre-wrap">{{ $lease->renewal_notes }}</dd>
                                    </div>
                                @endif
                                @if($lease->renewal_completed_at)
                                    <div class="md:col-span-2">
                                        <dt class="text-sm font-medium text-black-500">Completed At</dt>
                                        <dd class="mt-1 text-sm text-green-700 font-semibold">{{ $lease->renewal_completed_at->format('M j, Y g:i A') }}</dd>
                                    </div>
                                @endif
                            </div>
                        @else
                            <div class="text-center py-6">
                                <svg class="mx-auto h-12 w-12 text-gray-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                                <h3 class="text-sm font-medium text-gray-900 mb-2">No Renewal Date Set</h3>
                                <p class="text-sm text-gray-600 mb-4">This lease doesn't have a renewal date configured yet.</p>
                                <button onclick="setRenewalDate({{ $lease->id }})"
                                        class="inline-flex items-center px-4 py-2 border border-blue-300 text-sm font-medium rounded-lg text-blue-700 bg-blue-100 hover:bg-blue-200">
                                    📅 Set Renewal Date
                                </button>
                            </div>
                        @endif
                    </div>
                @endif

                <!-- Landlord Information -->
                <div class="bg-orange-50 shadow-sm ring-1 ring-orange-900/5 rounded-lg p-6">
                    <h2 class="text-lg font-semibold text-black-900 mb-4 flex items-center">
                        <svg class="w-5 h-5 mr-2 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                        </svg>
                        Landlord Information
                    </h2>
                    <dl class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <dt class="text-sm font-medium text-black-500">Landlord Name</dt>
                            <dd class="mt-1 text-sm text-black-900">{{ $lease->landlord_name ?? 'N/A' }}</dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-black-500">Email</dt>
                            <dd class="mt-1 text-sm text-black-900">
                                @if($lease->landlord_email)
                                    <a href="mailto:{{ $lease->landlord_email }}" class="text-black-600 hover:text-black-800">{{ $lease->landlord_email }}</a>
                                @else
                                    N/A
                                @endif
                            </dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-black-500">Phone</dt>
                            <dd class="mt-1 text-sm text-black-900">
                                @if($lease->landlord_phone)
                                    <a href="tel:{{ $lease->landlord_phone }}" class="text-black-600 hover:text-black-800">{{ $lease->landlord_phone }}</a>
                                @else
                                    N/A
                                @endif
                            </dd>
                        </div>
                        <div class="md:col-span-2">
                            <dt class="text-sm font-medium text-black-500">Landlord Address</dt>
                            <dd class="mt-1 text-sm text-black-900">{{ $lease->landlord_address ?? 'N/A' }}</dd>
                        </div>
                        <div class="md:col-span-2">
                            <dt class="text-sm font-medium text-black-500">Landlord Responsibilities</dt>
                            <dd class="mt-1 text-sm text-black-900 whitespace-pre-wrap">{{ $lease->landlord_responsibility ?? 'N/A' }}</dd>
                        </div>
                    </dl>
                </div>

                <!-- Comments -->
                @if($lease->comments)
                    <div class="bg-orange-50 shadow-sm ring-1 ring-orange-900/5 rounded-lg p-6">
                        <h2 class="text-lg font-semibold text-black-900 mb-4 flex items-center">
                            <svg class="w-5 h-5 mr-2 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 8h10M7 12h4m1 8l-4-4H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-1.586z" />
                            </svg>
                            Comments
                        </h2>
                        <div class="bg-orange-100 rounded-lg p-4">
                            <p class="text-sm text-black-900 whitespace-pre-wrap">{{ $lease->comments }}</p>
                        </div>
                    </div>
                @endif
            </div>

            <!-- Sidebar with Key Metrics -->
            <div class="space-y-6">
                <!-- Current Status -->
                <div class="bg-orange-50 shadow-sm ring-1 ring-orange-900/5 rounded-lg p-6">
                    <h2 class="text-lg font-semibold text-black-900 mb-4">Current Status</h2>
                    <div class="space-y-4">
                        @php
                            $currentTerm = $lease->current_term_info;
                            $timeUntilLastTerm = $lease->time_until_last_term_ends;
                            $leaseToSalesRatio = $lease->lease_to_sales_ratio;
                            $timeUntilFranchiseExpires = $lease->time_until_franchise_expires;
                        @endphp

                        @if($currentTerm)
                            <div class="p-4 bg-orange-100 rounded-lg">
                                <h3 class="text-sm font-medium text-black-900">Current Term</h3>
                                <p class="text-lg font-semibold text-black-700">{{ $currentTerm['term_name'] }}</p>
                                <p class="text-sm text-black-700">
                                    {{ $currentTerm['time_left']['expired'] ? 'Expired' : $currentTerm['time_left']['formatted'] . ' remaining' }}
                                </p>
                                <p class="text-xs text-black-500">Until: {{ $currentTerm['expiration_date']->format('M d, Y') }}</p>
                            </div>
                        @endif

                        @if($timeUntilLastTerm)
                            <div class="p-4 bg-orange-100 rounded-lg">
                                <h3 class="text-sm font-medium text-black-900">Last Term Ends</h3>
                                <p class="text-lg font-semibold {{ $timeUntilLastTerm['expired'] ? 'text-red-700' : 'text-black-700' }}">
                                    {{ $timeUntilLastTerm['expired'] ? 'Expired' : $timeUntilLastTerm['formatted'] }}
                                </p>
                                @if($lease->last_term_expiration_date)
                                    <p class="text-xs text-black-500">{{ $lease->last_term_expiration_date->format('M d, Y') }}</p>
                                @endif
                            </div>
                        @endif

                        @if($leaseToSalesRatio)
                            <div class="p-4 {{ $leaseToSalesRatio > 0.15 ? 'bg-orange-100' : ($leaseToSalesRatio > 0.10 ? 'bg-orange-100' : 'bg-orange-100') }} rounded-lg">
                                <h3 class="text-sm font-medium {{ $leaseToSalesRatio > 0.15 ? 'text-black-900' : ($leaseToSalesRatio > 0.10 ? 'text-black-900' : 'text-black-900') }}">Lease to Sales Ratio</h3>
                                <p class="text-lg font-semibold {{ $leaseToSalesRatio > 0.15 ? 'text-black-700' : ($leaseToSalesRatio > 0.10 ? 'text-black-700' : 'text-black-700') }}">
                                    {{ number_format($leaseToSalesRatio * 100, 2) }}%
                                </p>
                                <p class="text-xs {{ $leaseToSalesRatio > 0.15 ? 'text-black-500' : ($leaseToSalesRatio > 0.10 ? 'text-black-500' : 'text-black-500') }}">
                                    Annual Rent: ${{ number_format($lease->total_rent * 12, 0) }}<br>
                                    Annual Sales: ${{ number_format($lease->aws * 4, 0) }}
                                </p>
                            </div>
                        @endif

                        @if($timeUntilFranchiseExpires)
                            <div class="p-4 bg-orange-100 rounded-lg">
                                <h3 class="text-sm font-medium text-black-900">Franchise Agreement</h3>
                                <p class="text-lg font-semibold {{ $timeUntilFranchiseExpires['expired'] ? 'text-red-700' : 'text-black-700' }}">
                                    {{ $timeUntilFranchiseExpires['expired'] ? 'Expired' : $timeUntilFranchiseExpires['formatted'] . ' remaining' }}
                                </p>
                                @if($lease->franchise_agreement_expiration_date)
                                    <p class="text-xs text-black-600">Until: {{ $lease->franchise_agreement_expiration_date->format('M d, Y') }}</p>
                                @endif
                            </div>
                        @endif

                        <!-- NEW: Renewal Status Card -->
                        @if($lease->renewal_date)
                            <div class="p-4 {{ $lease->is_renewal_overdue ? 'bg-red-100 border border-red-200' : 'bg-blue-100 border border-blue-200' }} rounded-lg">
                                <h3 class="text-sm font-medium {{ $lease->is_renewal_overdue ? 'text-red-900' : 'text-blue-900' }}">
                                    {{ $lease->is_renewal_overdue ? '🚨 Renewal Overdue' : '🔔 Renewal Scheduled' }}
                                </h3>
                                <p class="text-lg font-semibold {{ $lease->is_renewal_overdue ? 'text-red-700' : 'text-blue-700' }}">
                                    @if($lease->days_until_renewal !== null)
                                        @if($lease->days_until_renewal < 0)
                                            {{ abs($lease->days_until_renewal) }} days overdue
                                        @elseif($lease->days_until_renewal == 0)
                                            Due today
                                        @else
                                            {{ $lease->days_until_renewal }} days left
                                        @endif
                                    @else
                                        Renewal scheduled
                                    @endif
                                </p>
                                <p class="text-xs {{ $lease->is_renewal_overdue ? 'text-red-600' : 'text-blue-600' }}">
                                    Date: {{ $lease->formatted_renewal_date }}
                                </p>
                            </div>
                        @endif
                    </div>
                </div>

                <!-- Additional Details -->
                <div class="bg-orange-50 shadow-sm ring-1 ring-orange-900/5 rounded-lg p-6">
                    <h2 class="text-lg font-semibold text-black-900 mb-4">Additional Details</h2>
                    <dl class="space-y-4">
                        <div>
                            <dt class="text-sm font-medium text-black-500">% Increase Per Year</dt>
                            <dd class="mt-1 text-sm text-black-900">{{ $lease->percent_increase_per_year ? $lease->percent_increase_per_year . '%' : 'N/A' }}</dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-black-500">Security Deposit</dt>
                            <dd class="mt-1 text-sm text-black-900">${{ number_format($lease->security_deposit ?? 0, 2) }}</dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-black-500">Initial Lease Expiration</dt>
                            <dd class="mt-1 text-sm text-black-900">{{ $lease->initial_lease_expiration_date?->format('M d, Y') ?? 'N/A' }}</dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-black-500">Franchise Agreement Expiration</dt>
                            <dd class="mt-1 text-sm text-black-900">{{ $lease->franchise_agreement_expiration_date?->format('M d, Y') ?? 'N/A' }}</dd>
                        </div>
                    </dl>
                </div>

                <!-- NEW: Quick Actions -->
                @if($lease->renewal_date)
                    <div class="bg-blue-50 shadow-sm ring-1 ring-blue-900/5 rounded-lg p-6">
                        <h2 class="text-lg font-semibold text-black-900 mb-4 flex items-center">
                            <svg class="w-5 h-5 mr-2 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                            </svg>
                            Quick Actions
                        </h2>
                        <div class="space-y-3">
                            @if($lease->renewal_status === 'pending')
                                <button onclick="sendRenewalReminder({{ $lease->id }})"
                                        class="w-full inline-flex justify-center items-center px-4 py-2 border border-blue-300 text-sm font-medium rounded-lg text-blue-700 bg-blue-100 hover:bg-blue-200">
                                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-5 5v-5z" />
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7H4a2 2 0 00-2 2v9a2 2 0 002 2h9a2 2 0 002-2V8a2 2 0 00-2-2" />
                                    </svg>
                                    Send Reminder
                                </button>
                                <button onclick="completeRenewal({{ $lease->id }})"
                                        class="w-full inline-flex justify-center items-center px-4 py-2 border border-green-300 text-sm font-medium rounded-lg text-green-700 bg-green-100 hover:bg-green-200">
                                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                    </svg>
                                    Mark as Completed
                                </button>
                            @endif
                            <a href="{{ route('leases.edit', $lease) }}"
                               class="w-full inline-flex justify-center items-center px-4 py-2 border border-orange-300 text-sm font-medium rounded-lg text-orange-700 bg-orange-100 hover:bg-orange-200">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                </svg>
                                Edit Renewal Details
                            </a>
                        </div>
                    </div>
                @endif

                <!-- Delete Action -->
                <div class="bg-orange-50 shadow-sm ring-1 ring-red-200 rounded-lg p-6 border-red-200">
                    <h2 class="text-lg font-semibold text-red-900 mb-4 flex items-center">
                        <svg class="w-5 h-5 mr-2 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.34 16.5c-.77.833.192 2.5 1.732 2.5z" />
                        </svg>
                        Danger Zone
                    </h2>
                    <p class="text-sm text-red-700 mb-4">This action cannot be undone. This will permanently delete the lease record.</p>
                    <form action="{{ route('leases.destroy', $lease) }}"
                          method="POST"
                          onsubmit="return confirm('Are you sure you want to delete this lease? This action cannot be undone.')">
                        @csrf
                        @method('DELETE')
                        <button type="submit"
                                class="w-full inline-flex justify-center items-center px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 transition-colors duration-200 font-medium">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                            </svg>
                            Delete Lease
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Set Renewal Date Modal -->
    <div id="setRenewalModal" class="hidden fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
        <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
            <div class="mt-3">
                <h3 class="text-lg font-medium text-gray-900 mb-4">Set Renewal Date</h3>
                <form id="setRenewalForm">
                    <input type="hidden" id="renewal_lease_id" />
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Renewal Date</label>
                        <input type="date" id="renewal_date_input"
                               class="block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                               min="{{ date('Y-m-d') }}">
                    </div>
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Notes</label>
                        <textarea id="renewal_notes_input" rows="3"
                                  class="block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                  placeholder="Add notes about the renewal..."></textarea>
                    </div>
                    <div class="flex justify-end space-x-3">
                        <button type="button" onclick="closeRenewalModal()"
                                class="px-4 py-2 border border-gray-300 rounded-md text-gray-700 hover:bg-gray-50">
                            Cancel
                        </button>
                        <button type="submit"
                                class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700">
                            Set Renewal Date
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Complete Renewal Modal -->
    <div id="completeRenewalModal" class="hidden fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
        <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
            <div class="mt-3">
                <h3 class="text-lg font-medium text-gray-900 mb-4">Complete Renewal</h3>
                <form id="completeRenewalForm">
                    <input type="hidden" id="complete_lease_id" />
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Completion Notes</label>
                        <textarea id="completion_notes" rows="3"
                                  class="block w-full rounded-md border-gray-300 shadow-sm focus:border-green-500 focus:ring-green-500"
                                  placeholder="Add notes about the completed renewal..."></textarea>
                    </div>
                    <div class="flex justify-end space-x-3">
                        <button type="button" onclick="closeCompleteModal()"
                                class="px-4 py-2 border border-gray-300 rounded-md text-gray-700 hover:bg-gray-50">
                            Cancel
                        </button>
                        <button type="submit"
                                class="px-4 py-2 bg-green-600 text-white rounded-md hover:bg-green-700">
                            Mark as Completed
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Enhanced JavaScript with Renewal Functions -->
    <script>
        // Renewal Management Functions
        function setRenewalDate(leaseId) {
            document.getElementById('renewal_lease_id').value = leaseId;
            document.getElementById('setRenewalModal').classList.remove('hidden');
        }

        function closeRenewalModal() {
            document.getElementById('setRenewalModal').classList.add('hidden');
            document.getElementById('setRenewalForm').reset();
        }

        function completeRenewal(leaseId) {
            document.getElementById('complete_lease_id').value = leaseId;
            document.getElementById('completeRenewalModal').classList.remove('hidden');
        }

        function closeCompleteModal() {
            document.getElementById('completeRenewalModal').classList.add('hidden');
            document.getElementById('completeRenewalForm').reset();
        }

        async function sendRenewalReminder(leaseId) {
            try {
                const response = await fetch(`/leases/${leaseId}/send-renewal-reminder`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    }
                });

                const data = await response.json();

                if (data.success) {
                    showNotification('✅ Renewal Reminder Sent', data.message, 'success');
                } else {
                    showNotification('❌ Error', data.error, 'error');
                }
            } catch (error) {
                showNotification('❌ Network Error', 'Failed to send reminder', 'error');
            }
        }

        // Handle Set Renewal Date Form
        document.getElementById('setRenewalForm').addEventListener('submit', async function(e) {
            e.preventDefault();

            const leaseId = document.getElementById('renewal_lease_id').value;
            const renewalDate = document.getElementById('renewal_date_input').value;
            const renewalNotes = document.getElementById('renewal_notes_input').value;

            try {
                const response = await fetch(`/leases/${leaseId}`, {
                    method: 'PATCH',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: JSON.stringify({
                        renewal_date: renewalDate,
                        renewal_notes: renewalNotes,
                        renewal_status: 'pending'
                    })
                });

                if (response.ok) {
                    showNotification('✅ Renewal Date Set', 'Automatic reminders have been created', 'success');
                    closeRenewalModal();
                    setTimeout(() => location.reload(), 2000);
                } else {
                    showNotification('❌ Error', 'Failed to set renewal date', 'error');
                }
            } catch (error) {
                showNotification('❌ Network Error', 'Failed to set renewal date', 'error');
            }
        });

        // Handle Complete Renewal Form
        document.getElementById('completeRenewalForm').addEventListener('submit', async function(e) {
            e.preventDefault();

            const leaseId = document.getElementById('complete_lease_id').value;
            const completionNotes = document.getElementById('completion_notes').value;

            try {
                const response = await fetch(`/leases/${leaseId}/complete-renewal`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: JSON.stringify({
                        completion_notes: completionNotes
                    })
                });

                if (response.ok) {
                    showNotification('✅ Renewal Completed', 'Lease renewal has been marked as completed', 'success');
                    closeCompleteModal();
                    setTimeout(() => location.reload(), 2000);
                } else {
                    showNotification('❌ Error', 'Failed to complete renewal', 'error');
                }
            } catch (error) {
                showNotification('❌ Network Error', 'Failed to complete renewal', 'error');
            }
        });

        // Notification Function
        function showNotification(title, message, type = 'info') {
            // Remove existing notifications
            const existing = document.querySelectorAll('.lease-notification');
            existing.forEach(n => n.remove());

            const colors = {
                success: 'bg-green-100 border-green-500 text-green-800',
                error: 'bg-red-100 border-red-500 text-red-800',
                warning: 'bg-yellow-100 border-yellow-500 text-yellow-800',
                info: 'bg-blue-100 border-blue-500 text-blue-800'
            };

            const notification = document.createElement('div');
            notification.className = `lease-notification fixed top-4 right-4 max-w-sm ${colors[type]} border-l-4 p-4 rounded-lg shadow-lg z-50`;
            notification.innerHTML = `
                <div class="flex justify-between items-start">
                    <div>
                        <h4 class="font-semibold text-sm">${title}</h4>
                        <p class="text-sm mt-1">${message}</p>
                    </div>
                    <button onclick="this.parentElement.parentElement.remove()" class="text-lg font-bold ml-4">&times;</button>
                </div>
            `;

            document.body.appendChild(notification);

            // Auto remove after 5 seconds
            setTimeout(() => {
                if (notification.parentElement) {
                    notification.remove();
                }
            }, 5000);
        }

        // Initialize on page load
        document.addEventListener('DOMContentLoaded', function() {
            console.log('🔔 Lease details with renewal system initialized');
        });
    </script>
@endsection
