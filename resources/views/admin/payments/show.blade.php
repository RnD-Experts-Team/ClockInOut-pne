@extends('layouts.app')

@section('title', 'Payment Details')

@section('content')
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <div class="bg-orange-50 shadow-lg rounded-xl overflow-hidden">
            <!-- Header -->
            <div class="px-6 py-4 border-b border-orange-200 bg-orange-100">
                <div class="flex justify-between items-center">
                    <div>
                        <h2 class="text-2xl font-bold text-black-900">Payment Details</h2>
                        <p class="text-sm text-black-600 mt-1">{{ $payment->store }} - {{ $payment->date->format('M d, Y') }}</p>
                    </div>
                    <div class="flex space-x-3">
                        <a href="{{ route('payments.edit', $payment) }}"
                           class="inline-flex items-center px-4 py-2 border border-transparent rounded-lg text-sm font-medium text-white bg-orange-600 hover:bg-orange-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-orange-500">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                            </svg>
                            Edit Payment
                        </a>
                        <a href="{{ route('payments.index') }}"
                           class="inline-flex items-center px-4 py-2 border border-orange-300 rounded-lg text-sm font-medium text-black-700 bg-white hover:bg-orange-100 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-orange-500">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                            </svg>
                            Back to List
                        </a>
                    </div>
                </div>
            </div>

            <div class="p-6">
                <!-- Payment Status Badge -->
                <div class="mb-6">
                    @if($payment->paid)
                        <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-green-100 text-green-800">
                            <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                            </svg>
                            Payment Completed
                        </span>
                    @else
                        <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-red-100 text-red-800">
                            <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd" />
                            </svg>
                            Payment Pending
                        </span>
                    @endif
                </div>

                <!-- Payment Information Grid -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
                    <!-- Basic Information -->
                    <div class="bg-orange-100 rounded-lg p-4">
                        <h3 class="text-lg font-semibold text-black-900 mb-4">Basic Information</h3>
                        <dl class="space-y-3">
                            <div>
                                <dt class="text-sm font-medium text-black-700">Store</dt>
                                <dd class="text-sm text-black-900">{{ $payment->store }}</dd>
                            </div>
                            <div>
                                <dt class="text-sm font-medium text-black-700">Date</dt>
                                <dd class="text-sm text-black-900">{{ $payment->date->format('F d, Y') }}</dd>
                            </div>
                            <div>
                                <dt class="text-sm font-medium text-black-700">Company</dt>
                                <dd class="text-sm text-black-900">{{ $payment->company->name }}</dd>
                            </div>
                            <div>
                                <dt class="text-sm font-medium text-black-700">Cost</dt>
                                <dd class="text-lg font-bold text-green-600">${{ number_format($payment->cost, 2) }}</dd>
                            </div>
                        </dl>
                    </div>

                    <!-- Service Information -->
                    <div class="bg-orange-100 rounded-lg p-4">
                        <h3 class="text-lg font-semibold text-black-900 mb-4">Service Information</h3>
                        <dl class="space-y-3">
                            @if($payment->what_got_fixed)
                                <div>
                                    <dt class="text-sm font-medium text-black-700">What Got Fixed</dt>
                                    <dd class="text-sm text-black-900">{{ $payment->what_got_fixed }}</dd>
                                </div>
                            @endif
                            @if($payment->maintenance_type)
                                <div>
                                    <dt class="text-sm font-medium text-black-700">Maintenance Type</dt>
                                    <dd class="text-sm text-black-900">
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-orange-200 text-black-800">
                                            {{ $payment->maintenance_type }}
                                        </span>
                                    </dd>
                                </div>
                            @endif
                            @if($payment->payment_method)
                                <div>
                                    <dt class="text-sm font-medium text-black-700">Payment Method</dt>
                                    <dd class="text-sm text-black-900">{{ $payment->payment_method }}</dd>
                                </div>
                            @endif
                        </dl>
                    </div>
                </div>

                <!-- Time Metrics -->
                <div class="bg-orange-100 rounded-lg p-6 mb-6">
                    <h3 class="text-lg font-semibold text-black-900 mb-4">Time Metrics</h3>
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                        <div class="text-center">
                            <dt class="text-sm font-medium text-black-700">Week</dt>
                            <dd class="text-lg font-bold text-black-900">{{ $payment->week }}</dd>
                        </div>
                        <div class="text-center">
                            <dt class="text-sm font-medium text-black-700">Month</dt>
                            <dd class="text-lg font-bold text-black-900">{{ $payment->month }}</dd>
                        </div>
                        <div class="text-center">
                            <dt class="text-sm font-medium text-black-700">Year</dt>
                            <dd class="text-lg font-bold text-black-900">{{ $payment->year }}</dd>
                        </div>
                        <div class="text-center">
                            <dt class="text-sm font-medium text-black-700">This Month</dt>
                            <dd class="text-sm {{ $payment->this_month === 'This Month' ? 'text-green-600 font-medium' : 'text-black-600' }}">
                                {{ $payment->this_month }}
                            </dd>
                        </div>
                    </div>
                </div>

                <!-- Notes -->
                @if($payment->notes)
                    <div class="bg-orange-100 rounded-lg p-6">
                        <h3 class="text-lg font-semibold text-black-900 mb-4">Notes</h3>
                        <div class="text-sm text-black-700 whitespace-pre-line">{{ $payment->notes }}</div>
                    </div>
                @endif
            </div>

            <!-- Actions Footer -->
            <div class="px-6 py-4 bg-orange-100 border-t border-orange-200">
                <div class="flex justify-between items-center">
                    <div class="text-xs text-black-600">
                        Created: {{ $payment->created_at->format('M d, Y g:i A') }}
                        @if($payment->updated_at->ne($payment->created_at))
                            | Updated: {{ $payment->updated_at->format('M d, Y g:i A') }}
                        @endif
                    </div>
                    <div class="flex space-x-3">
                        <form action="{{ route('payments.destroy', $payment) }}" method="POST" class="inline"
                              onsubmit="return confirm('Are you sure you want to delete this payment record?')">
                            @csrf
                            @method('DELETE')
                            <button type="submit"
                                    class="inline-flex items-center px-3 py-2 border border-red-300 rounded-lg text-sm font-medium text-red-700 bg-red-50 hover:bg-red-100 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                </svg>
                                Delete
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
