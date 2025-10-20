@extends('layouts.app')

@section('title', 'Apartment Lease Details')

@section('content')
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <div class="flex justify-between items-center mb-6">
            <div>
                <h1 class="text-3xl font-bold text-black-900">
                    Apartment Lease Details
                    @if($apartmentLease->store_number)
                        - Store #{{ $apartmentLease->store_number }}
                    @endif
                </h1>
                <p class="mt-2 text-sm text-black-700">{{ Str::limit($apartmentLease->apartment_address, 80) }}</p>

                <!-- NEW: Renewal Status Badge in Header -->
                @if($apartmentLease->renewal_date)
                    @php $renewalStatusInfo = $apartmentLease->renewal_status_info; @endphp
                    <div class="mt-2">
                        <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium {{ $renewalStatusInfo['class'] }}">
                            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            🔔 {{ $renewalStatusInfo['message'] }}
                        </span>
                    </div>
                @endif
            </div>
            <div class="flex space-x-3">
                <a href="{{ route('admin.apartment-leases.edit', $apartmentLease) }}"
                   class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-lg text-white bg-orange-600 hover:bg-orange-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-orange-500 transition-all duration-200">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                    </svg>
                    Edit Lease
                </a>
                <a href="{{ route('admin.apartment-leases.index') }}"
                   class="inline-flex items-center px-4 py-2 border border-orange-200 text-sm font-medium rounded-lg text-black-700 bg-orange-50 hover:bg-orange-100 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-orange-500 transition-all duration-200">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                    </svg>
                    Back to List
                </a>
            </div>
        </div>

        <!-- NEW: Renewal Alert Banner -->
        @if($apartmentLease->renewal_date)
            @php
                $renewalStatusInfo = $apartmentLease->renewal_status_info;
                $isOverdue = $apartmentLease->is_renewal_overdue;
                $isUrgent = $apartmentLease->days_until_renewal !== null && $apartmentLease->days_until_renewal <= 7 && $apartmentLease->days_until_renewal >= 0;
            @endphp

            @if($isOverdue || $isUrgent || $apartmentLease->renewal_status === 'pending')
                <div class="mb-6 {{ $isOverdue ? 'bg-red-50 border-l-4 border-red-400' : ($isUrgent ? 'bg-orange-50 border-l-4 border-orange-400' : 'bg-blue-50 border-l-4 border-blue-400') }} p-4 rounded-lg shadow-sm">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <svg class="h-5 w-5 {{ $isOverdue ? 'text-red-400' : ($isUrgent ? 'text-orange-400' : 'text-blue-400') }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    @if($isOverdue)
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                                    @else
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    @endif
                                </svg>
                            </div>
                            <div class="ml-3">
                                <h3 class="text-sm font-medium {{ $isOverdue ? 'text-red-800' : ($isUrgent ? 'text-orange-800' : 'text-blue-800') }}">
                                    @if($isOverdue)
                                        🚨 Apartment Lease Renewal Overdue
                                    @elseif($isUrgent)
                                        ⚡ Apartment Lease Renewal Due Soon
                                    @else
                                        🔔 Apartment Lease Renewal Scheduled
                                    @endif
                                </h3>
                                <div class="mt-2 text-sm {{ $isOverdue ? 'text-red-700' : ($isUrgent ? 'text-orange-700' : 'text-blue-700') }}">
                                    <p>
                                        <strong>Due Date:</strong> {{ $apartmentLease->formatted_renewal_date }}
                                        @if($apartmentLease->days_until_renewal !== null)
                                            - {{ abs($apartmentLease->days_until_renewal) }} days {{ $apartmentLease->days_until_renewal < 0 ? 'overdue' : 'remaining' }}
                                        @endif
                                    </p>
                                    @if($apartmentLease->renewalCreatedBy)
                                        <p class="mt-1"><strong>Set by:</strong> {{ $apartmentLease->renewalCreatedBy->name }}</p>
                                    @endif
                                </div>
                            </div>
                        </div>
                        <div class="flex space-x-2">
                            @if($apartmentLease->renewal_status === 'pending')
                                <button onclick="sendApartmentRenewalReminder({{ $apartmentLease->id }})"
                                        class="inline-flex items-center px-3 py-1 border border-blue-300 text-xs font-medium rounded text-blue-700 bg-blue-100 hover:bg-blue-200">
                                    🔔 Send Reminder
                                </button>
                                <button onclick="completeApartmentRenewal({{ $apartmentLease->id }})"
                                        class="inline-flex items-center px-3 py-1 border border-green-300 text-xs font-medium rounded text-green-700 bg-green-100 hover:bg-green-200">
                                    ✅ Mark Complete
                                </button>
                            @endif
                        </div>
                    </div>
                </div>
            @endif
        @endif

        <!-- Status Alert -->
        @if($apartmentLease->expiration_date)
            @php
                $expirationDate = \Carbon\Carbon::parse($apartmentLease->expiration_date);
                $now = \Carbon\Carbon::now();
                $isExpired = $expirationDate->isPast();
                $showAlert = $isExpired || $now->diffInDays($expirationDate, false) <= 30;
            @endphp

            @if($showAlert)
                <div class="mb-6 p-4 rounded-lg {{ $isExpired ? 'bg-red-100 text-red-800' : 'bg-orange-100 text-orange-800' }}">
                    <div class="flex items-center">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <span class="font-medium">
                            @php
                                $interval = $now->diff($expirationDate);
                                $timeString = '';

                                if ($interval->y > 0) {
                                    $timeString .= $interval->y . ' year' . ($interval->y > 1 ? 's' : '') . ' ';
                                }
                                if ($interval->m > 0) {
                                    $timeString .= $interval->m . ' month' . ($interval->m > 1 ? 's' : '') . ' ';
                                }
                                if ($interval->d > 0) {
                                    $timeString .= $interval->d . ' day' . ($interval->d > 1 ? 's' : '') . ' ';
                                }
                            @endphp

                            @if($isExpired)
                                📅 Lease Expired {{ trim($timeString) }} ago
                            @else
                                ⏰ Lease Expires in {{ trim($timeString) }}
                            @endif
                        </span>
                    </div>
                </div>
            @endif
        @endif

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <!-- Main Information -->
            <div class="lg:col-span-2 space-y-6">
                <!-- NEW: Renewal Management Section -->
                @if($apartmentLease->renewal_date)
                    <div class="bg-blue-50 shadow-sm ring-1 ring-blue-900/5 rounded-lg p-6">
                        <h2 class="text-lg font-semibold text-black-900 mb-4 flex items-center">
                            <svg class="w-5 h-5 mr-2 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            🔔 Apartment Renewal Management
                        </h2>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <h3 class="text-sm font-medium text-blue-700">Renewal Date</h3>
                                <p class="mt-1 text-lg font-semibold text-blue-900">{{ $apartmentLease->formatted_renewal_date }}</p>
                                @if($apartmentLease->days_until_renewal !== null)
                                    <p class="text-sm text-blue-600">
                                        {{ $apartmentLease->days_until_renewal < 0 ? abs($apartmentLease->days_until_renewal) . ' days overdue' : $apartmentLease->days_until_renewal . ' days remaining' }}
                                    </p>
                                @endif
                            </div>
                            <div>
                                <h3 class="text-sm font-medium text-blue-700">Current Status</h3>
                                <div class="mt-1">
                                    <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium {{ $renewalStatusInfo['class'] }}">
                                        {{ $renewalStatusInfo['message'] }}
                                    </span>
                                </div>
                            </div>
                            @if($apartmentLease->renewalCreatedBy)
                                <div>
                                    <h3 class="text-sm font-medium text-blue-700">Set By</h3>
                                    <p class="mt-1 text-sm text-blue-900">{{ $apartmentLease->renewalCreatedBy->name }}</p>
                                </div>
                            @endif
                            @if($apartmentLease->renewal_completed_at)
                                <div>
                                    <h3 class="text-sm font-medium text-blue-700">Completed On</h3>
                                    <p class="mt-1 text-sm text-blue-900">{{ $apartmentLease->renewal_completed_at->format('M j, Y g:i A') }}</p>
                                </div>
                            @endif
                        </div>

                        @if($apartmentLease->renewal_notes)
                            <div class="mt-6">
                                <h3 class="text-sm font-medium text-blue-700">Renewal Notes</h3>
                                <div class="mt-2 bg-blue-100 rounded-lg p-3">
                                    <p class="text-sm text-blue-900 whitespace-pre-wrap">{{ $apartmentLease->renewal_notes }}</p>
                                </div>
                            </div>
                        @endif

                        @if($apartmentLease->renewal_reminder_sent_at)
                            <div class="mt-4 text-xs text-blue-600">
                                <strong>Last reminder sent:</strong> {{ $apartmentLease->renewal_reminder_sent_at->format('M j, Y g:i A') }}
                            </div>
                        @endif
                    </div>
                @endif

                <!-- Basic Apartment Information -->
                <div class="bg-orange-50 shadow-sm ring-1 ring-orange-900/5 rounded-lg p-6">
                    <h2 class="text-lg font-semibold text-black-900 mb-4 flex items-center">
                        <svg class="w-5 h-5 mr-2 text-black-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-2m-14 0h2m0 0V9a2 2 0 012-2h2a2 2 0 012 2v10" />
                        </svg>
                        Apartment Information
                    </h2>
                    <dl class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        @if($apartmentLease->store_number)
                            <div>
                                <dt class="text-sm font-medium text-black-500">Store Number</dt>
                                <dd class="mt-1 text-sm text-black-900">{{ $apartmentLease->store_number }}</dd>
                            </div>
                        @endif
                        <div>
                            <dt class="text-sm font-medium text-black-500">Drive Time</dt>
                            <dd class="mt-1 text-sm text-black-900">{{ $apartmentLease->drive_time ?? 'N/A' }}</dd>
                        </div>
                        <div class="md:col-span-2">
                            <dt class="text-sm font-medium text-black-500">Apartment Address</dt>
                            <dd class="mt-1 text-sm text-black-900">{{ $apartmentLease->apartment_address }}</dd>
                        </div>
                    </dl>
                </div>

                <!-- Financial Information -->
                <div class="bg-orange-50 shadow-sm ring-1 ring-orange-900/5 rounded-lg p-6">
                    <h2 class="text-lg font-semibold text-black-900 mb-4 flex items-center">
                        <svg class="w-5 h-5 mr-2 text-black-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1" />
                        </svg>
                        Financial Details
                    </h2>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div class="text-center p-4 bg-orange-100 rounded-lg">
                            <dt class="text-sm font-medium text-black-500">Monthly Rent</dt>
                            <dd class="mt-1 text-lg font-semibold text-black-600">${{ number_format($apartmentLease->rent, 2) }}</dd>
                        </div>
                        @if($apartmentLease->utilities)
                            <div class="text-center p-4 bg-orange-100 rounded-lg">
                                <dt class="text-sm font-medium text-black-500">Monthly Utilities</dt>
                                <dd class="mt-1 text-lg font-semibold text-black-600">${{ number_format($apartmentLease->utilities, 2) }}</dd>
                            </div>
                        @endif
                    </div>
                    <div class="mt-6 p-4 bg-orange-100 rounded-lg">
                        <div class="flex justify-between items-center">
                            <span class="text-lg font-medium text-black-900">Total Monthly Cost:</span>
                            <span class="text-xl font-bold text-black-600">${{ number_format($apartmentLease->total_rent, 2) }}</span>
                        </div>
                    </div>
                    <div class="mt-4 p-4 bg-orange-100 rounded-lg">
                        <div class="flex justify-between items-center">
                            <span class="text-lg font-medium text-black-900">Annual Cost:</span>
                            <span class="text-xl font-bold text-black-600">${{ number_format($apartmentLease->total_rent * 12, 2) }}</span>
                        </div>
                    </div>
                </div>

                <!-- Resident Information -->
                <div class="bg-orange-50 shadow-sm ring-1 ring-orange-900/5 rounded-lg p-6">
                    <h2 class="text-lg font-semibold text-black-900 mb-4 flex items-center">
                        <svg class="w-5 h-5 mr-2 text-black-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                        </svg>
                        Resident & Property Details
                    </h2>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                        <div class="text-center p-4 bg-orange-100 rounded-lg">
                            <dt class="text-sm font-medium text-black-500">Number of AT</dt>
                            <dd class="mt-1 text-lg font-semibold text-black-600">{{ $apartmentLease->number_of_AT }}</dd>
                        </div>
                        <div class="text-center p-4 bg-orange-100 rounded-lg">
                            <dt class="text-sm font-medium text-black-500">Number of Cars</dt>
                            <dd class="mt-1 text-lg font-semibold text-black-600">{{ $apartmentLease->has_car }}</dd>
                        </div>
                        <div class="text-center p-4 {{ $apartmentLease->is_family === 'Yes' || $apartmentLease->is_family === 'yes' ? 'bg-green-100' : 'bg-orange-100' }} rounded-lg">
                            <dt class="text-sm font-medium text-black-500">Family Unit</dt>
                            <dd class="mt-1">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $apartmentLease->is_family === 'Yes' || $apartmentLease->is_family === 'yes' ? 'bg-green-200 text-green-800' : 'bg-gray-200 text-gray-800' }}">
                                {{ ($apartmentLease->is_family === 'Yes' || $apartmentLease->is_family === 'yes') ? 'Yes' : 'No' }}
                            </span>
                            </dd>
                        </div>
                    </div>
                </div>

                <!-- Lease Holder Information -->
                <div class="bg-orange-50 shadow-sm ring-1 ring-orange-900/5 rounded-lg p-6">
                    <h2 class="text-lg font-semibold text-black-900 mb-4 flex items-center">
                        <svg class="w-5 h-5 mr-2 text-black-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                        </svg>
                        Lease Holder Information
                    </h2>
                    <dl class="grid grid-cols-1 gap-6">
                        <div>
                            <dt class="text-sm font-medium text-black-500">Lease Holder(s)</dt>
                            <dd class="mt-1 text-sm text-black-900">{{ $apartmentLease->lease_holder }}</dd>
                        </div>
                        @if($apartmentLease->expiration_date)
                            <div>
                                <dt class="text-sm font-medium text-black-500">Lease Expiration Date</dt>
                                <dd class="mt-1 text-sm text-black-900">
                                    @php
                                        $expirationDate = \Carbon\Carbon::parse($apartmentLease->expiration_date);
                                        $now = \Carbon\Carbon::now();

                                        if ($expirationDate->isPast()) {
                                            $interval = $now->diff($expirationDate);
                                            $timeAgo = '';

                                            if ($interval->y > 0) {
                                                $timeAgo .= $interval->y . ' year' . ($interval->y > 1 ? 's' : '') . ' ';
                                            }
                                            if ($interval->m > 0) {
                                                $timeAgo .= $interval->m . ' month' . ($interval->m > 1 ? 's' : '') . ' ';
                                            }
                                            if ($interval->d > 0) {
                                                $timeAgo .= $interval->d . ' day' . ($interval->d > 1 ? 's' : '') . ' ';
                                            }

                                            $formattedDate = $expirationDate->format('F j, Y') . ' (' . trim($timeAgo) . ' ago)';
                                        } else {
                                            $interval = $now->diff($expirationDate);
                                            $timeFromNow = '';

                                            if ($interval->y > 0) {
                                                $timeFromNow .= $interval->y . ' year' . ($interval->y > 1 ? 's' : '') . ' ';
                                            }
                                            if ($interval->m > 0) {
                                                $timeFromNow .= $interval->m . ' month' . ($interval->m > 1 ? 's' : '') . ' ';
                                            }
                                            if ($interval->d > 0) {
                                                $timeFromNow .= $interval->d . ' day' . ($interval->d > 1 ? 's' : '') . ' ';
                                            }

                                            $formattedDate = $expirationDate->format('F j, Y') . ' (in ' . trim($timeFromNow) . ')';
                                        }
                                    @endphp
                                    {{ $formattedDate }}
                                </dd>
                            </div>
                        @endif
                    </dl>
                </div>

                <!-- Notes -->
                @if($apartmentLease->notes)
                    <div class="bg-orange-50 shadow-sm ring-1 ring-orange-900/5 rounded-lg p-6">
                        <h2 class="text-lg font-semibold text-black-900 mb-4 flex items-center">
                            <svg class="w-5 h-5 mr-2 text-black-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 8h10M7 12h4m1 8l-4-4H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-1.586z" />
                            </svg>
                            Notes
                        </h2>
                        <div class="bg-orange-100 rounded-lg p-4">
                            <p class="text-sm text-black-900 whitespace-pre-wrap">{{ $apartmentLease->notes }}</p>
                        </div>
                    </div>
                @endif
            </div>

            <!-- Sidebar with Key Metrics -->
            <div class="space-y-6">
                <!-- NEW: Renewal Quick Actions -->
                @if($apartmentLease->renewal_date && $apartmentLease->renewal_status !== 'completed')
                    <div class="bg-blue-50 shadow-sm ring-1 ring-blue-900/5 rounded-lg p-6">
                        <h2 class="text-lg font-semibold text-black-900 mb-4 flex items-center">
                            <svg class="w-5 h-5 mr-2 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            🔔 Renewal Actions
                        </h2>
                        <div class="space-y-3">
                            @if($apartmentLease->renewal_status === 'pending')
                                <button onclick="completeApartmentRenewal({{ $apartmentLease->id }})"
                                        class="w-full inline-flex items-center justify-center px-4 py-2 border border-transparent text-sm font-medium rounded-lg text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 transition-all duration-200">
                                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                    </svg>
                                    ✅ Complete Renewal
                                </button>
                                <button onclick="sendApartmentRenewalReminder({{ $apartmentLease->id }})"
                                        class="w-full inline-flex items-center justify-center px-4 py-2 border border-blue-200 text-sm font-medium rounded-lg text-blue-700 bg-blue-100 hover:bg-blue-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-all duration-200">
                                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-5 5v-5zM4 19h9" />
                                    </svg>
                                    🔔 Send Reminder
                                </button>
                            @endif
                            <a href="{{ route('admin.apartment-leases.edit', $apartmentLease) }}"
                               class="w-full inline-flex items-center justify-center px-4 py-2 border border-blue-200 text-sm font-medium rounded-lg text-blue-700 bg-blue-100 hover:bg-blue-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-all duration-200">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                </svg>
                                📝 Edit Renewal
                            </a>
                        </div>
                    </div>
                @endif

                <!-- Current Status -->
                <div class="bg-orange-50 shadow-sm ring-1 ring-orange-900/5 rounded-lg p-6">
                    <h2 class="text-lg font-semibold text-black-900 mb-4">Current Status</h2>
                    <div class="space-y-4">
                        @if($apartmentLease->expiration_date)
                            @php
                                $expirationDate = \Carbon\Carbon::parse($apartmentLease->expiration_date);
                                $now = \Carbon\Carbon::now();
                                $isExpired = $expirationDate->isPast();
                                $interval = $now->diff($expirationDate);
                                $timeString = '';

                                if ($interval->y > 0) {
                                    $timeString .= $interval->y . ' year' . ($interval->y > 1 ? 's' : '') . ' ';
                                }
                                if ($interval->m > 0) {
                                    $timeString .= $interval->m . ' month' . ($interval->m > 1 ? 's' : '') . ' ';
                                }
                                if ($interval->d > 0) {
                                    $timeString .= $interval->d . ' day' . ($interval->d > 1 ? 's' : '') . ' ';
                                }
                            @endphp

                            @if($isExpired)
                                <div class="p-4 bg-red-100 rounded-lg">
                                    <h3 class="text-sm font-medium text-red-900">Lease Status</h3>
                                    <p class="text-lg font-semibold text-red-700">
                                        Expired {{ trim($timeString) }} ago
                                    </p>
                                    <p class="text-xs text-red-600">
                                        Expiration: {{ $expirationDate->format('M d, Y') }}
                                    </p>
                                </div>
                            @elseif($now->diffInDays($expirationDate, false) <= 30)
                                <div class="p-4 bg-orange-100 rounded-lg">
                                    <h3 class="text-sm font-medium text-orange-900">Lease Status</h3>
                                    <p class="text-lg font-semibold text-orange-700">
                                        Expires in {{ trim($timeString) }}
                                    </p>
                                    <p class="text-xs text-orange-600">
                                        Expiration: {{ $expirationDate->format('M d, Y') }}
                                    </p>
                                </div>
                            @else
                                <div class="p-4 bg-green-100 rounded-lg">
                                    <h3 class="text-sm font-medium text-green-900">Lease Status</h3>
                                    <p class="text-lg font-semibold text-green-700">Active</p>
                                    <p class="text-xs text-green-600">
                                        Expires: {{ $expirationDate->format('M d, Y') }}
                                    </p>
                                </div>
                            @endif
                        @else
                            <div class="p-4 bg-gray-100 rounded-lg">
                                <h3 class="text-sm font-medium text-gray-900">Lease Status</h3>
                                <p class="text-lg font-semibold text-gray-700">No expiration date set</p>
                            </div>
                        @endif

                        <!-- Monthly Rent Per AT -->
                        <div class="p-4 bg-orange-100 rounded-lg">
                            <h3 class="text-sm font-medium text-orange-900">Monthly Rent per AT</h3>
                            <p class="text-lg font-semibold text-orange-700">
                                ${{ number_format($apartmentLease->total_rent / max($apartmentLease->number_of_AT, 1), 2) }}
                            </p>
                            <p class="text-xs text-orange-600">Per AT per month</p>
                        </div>
                    </div>
                </div>

                <!-- Quick Actions -->
                <div class="bg-orange-50 shadow-sm ring-1 ring-orange-900/5 rounded-lg p-6">
                    <h2 class="text-lg font-semibold text-black-900 mb-4">Quick Actions</h2>
                    <div class="space-y-3">
                        <a href="{{ route('admin.apartment-leases.edit', $apartmentLease) }}"
                           class="w-full inline-flex items-center justify-center px-4 py-2 border border-transparent text-sm font-medium rounded-lg text-white bg-orange-600 hover:bg-orange-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-orange-500 transition-all duration-200">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                            </svg>
                            Edit Lease
                        </a>
                        <a href="{{ route('admin.apartment-leases.index') }}"
                           class="w-full inline-flex items-center justify-center px-4 py-2 border border-orange-200 text-sm font-medium rounded-lg text-black-700 bg-orange-50 hover:bg-orange-100 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-orange-500 transition-all duration-200">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                            </svg>
                            View All Leases
                        </a>
                    </div>
                </div>

                <!-- Record Information -->
                <div class="bg-orange-50 shadow-sm ring-1 ring-orange-900/5 rounded-lg p-6">
                    <h2 class="text-lg font-semibold text-black-900 mb-4">Record Information</h2>
                    <dl class="space-y-4">
                        <div>
                            <dt class="text-sm font-medium text-black-500">Created By</dt>
                            <dd class="mt-1 text-sm text-black-900">
                                {{ $apartmentLease->createdBy->name ?? 'System' }}
                            </dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-black-500">Created</dt>
                            <dd class="mt-1 text-sm text-black-900">{{ $apartmentLease->created_at->format('M j, Y g:i A') }}</dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-black-500">Last Updated</dt>
                            <dd class="mt-1 text-sm text-black-900">{{ $apartmentLease->updated_at->format('M j, Y g:i A') }}</dd>
                        </div>
                        @if($apartmentLease->created_at != $apartmentLease->updated_at)
                            <div>
                                <dt class="text-sm font-medium text-black-500">Last Modified</dt>
                                <dd class="mt-1 text-sm text-black-900">{{ $apartmentLease->updated_at->diffForHumans() }}</dd>
                            </div>
                        @endif
                    </dl>
                </div>

                <!-- Delete Action -->
                <div class="bg-red-50 shadow-sm ring-1 ring-red-200 rounded-lg p-6 border-red-200">
                    <h2 class="text-lg font-semibold text-red-900 mb-4 flex items-center">
                        <svg class="w-5 h-5 mr-2 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.34 16.5c-.77.833.192 2.5 1.732 2.5z" />
                        </svg>
                        Danger Zone
                    </h2>
                    <p class="text-sm text-red-700 mb-4">This action cannot be undone. This will permanently delete the apartment lease record and all associated renewal information.</p>
                    <form action="{{ route('admin.apartment-leases.destroy', $apartmentLease) }}"
                          method="POST"
                          onsubmit="return confirm('Are you sure you want to delete this apartment lease? This will also remove any renewal schedules and reminders. This action cannot be undone.')">
                        @csrf
                        @method('DELETE')
                        <button type="submit"
                                class="w-full inline-flex justify-center items-center px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 transition-colors duration-200 font-medium">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                            </svg>
                            Delete Apartment Lease
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- NEW: Apartment Renewal Action Modals and JavaScript -->
    <script>
        // NEW: Apartment renewal action functions
        async function sendApartmentRenewalReminder(leaseId) {
            try {
                const response = await fetch(`/admin/apartment-leases/${leaseId}/send-renewal-reminder`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    }
                });

                const data = await response.json();

                if (data.success) {
                    showApartmentNotification('✅ Apartment Renewal Reminder Sent', data.message, 'success');
                } else {
                    showApartmentNotification('❌ Error', data.error, 'error');
                }
            } catch (error) {
                showApartmentNotification('❌ Network Error', 'Failed to send apartment renewal reminder', 'error');
            }
        }

        async function completeApartmentRenewal(leaseId) {
            if (confirm('Are you sure you want to mark this apartment renewal as completed?')) {
                try {
                    const response = await fetch(`/admin/apartment-leases/${leaseId}/complete-renewal`, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                        }
                    });

                    if (response.ok) {
                        showApartmentNotification('✅ Apartment Renewal Completed', 'Apartment lease renewal has been marked as completed', 'success');
                        setTimeout(() => location.reload(), 2000);
                    } else {
                        showApartmentNotification('❌ Error', 'Failed to complete apartment renewal', 'error');
                    }
                } catch (error) {
                    showApartmentNotification('❌ Network Error', 'Failed to complete apartment renewal', 'error');
                }
            }
        }

        // NEW: Apartment notification function
        function showApartmentNotification(title, message, type = 'info') {
            // Remove existing notifications
            const existing = document.querySelectorAll('.apartment-lease-notification');
            existing.forEach(n => n.remove());

            const colors = {
                success: 'bg-green-100 border-green-500 text-green-800',
                error: 'bg-red-100 border-red-500 text-red-800',
                warning: 'bg-yellow-100 border-yellow-500 text-yellow-800',
                info: 'bg-blue-100 border-blue-500 text-blue-800'
            };

            const notification = document.createElement('div');
            notification.className = `apartment-lease-notification fixed top-4 right-4 max-w-sm ${colors[type]} border-l-4 p-4 rounded-lg shadow-lg z-50`;
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

        console.log('🏢 Apartment lease details with renewal management loaded');
    </script>
@endsection
