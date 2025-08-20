@extends('layouts.app')

@section('title', 'Company Details')

@section('content')
    <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <!-- Header -->
        <div class="bg-orange-50 shadow-lg rounded-xl overflow-hidden mb-8">
            <div class="px-6 py-4 border-b border-orange-200 bg-orange-100">
                <div class="flex justify-between items-center">
                    <div>
                        <h2 class="text-2xl font-bold text-black-900">{{ $company->name }}</h2>
                        <p class="text-sm text-black-700 mt-1">Company Details & Payment History</p>
                    </div>
                    <div class="flex space-x-3">
                        <a href="{{ route('companies.edit', $company) }}"
                           class="inline-flex items-center px-4 py-2 border border-transparent rounded-lg text-sm font-medium text-white bg-orange-600 hover:bg-orange-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-orange-500">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                            </svg>
                            Edit Company
                        </a>
                        <a href="{{ route('companies.index') }}"
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
                <!-- Company Information Grid -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Basic Information -->
                    <div class="bg-orange-100 rounded-lg p-4">
                        <h3 class="text-lg font-semibold text-black-900 mb-4">Company Information</h3>
                        <dl class="space-y-3">
                            <div>
                                <dt class="text-sm font-medium text-black-700">Company Name</dt>
                                <dd class="text-sm text-black-900">{{ $company->name }}</dd>
                            </div>
                            @if($company->contact_person)
                                <div>
                                    <dt class="text-sm font-medium text-black-700">Contact Person</dt>
                                    <dd class="text-sm text-black-900">{{ $company->contact_person }}</dd>
                                </div>
                            @endif
                            @if($company->phone)
                                <div>
                                    <dt class="text-sm font-medium text-black-700">Phone</dt>
                                    <dd class="text-sm text-black-900">{{ $company->phone }}</dd>
                                </div>
                            @endif
                            @if($company->email)
                                <div>
                                    <dt class="text-sm font-medium text-black-700">Email</dt>
                                    <dd class="text-sm text-black-600">{{ $company->email }}</dd>
                                </div>
                            @endif
                            @if($company->address)
                                <div>
                                    <dt class="text-sm font-medium text-black-700">Address</dt>
                                    <dd class="text-sm text-black-900">{{ $company->address }}</dd>
                                </div>
                            @endif
                        </dl>
                    </div>

                    <!-- Payment Statistics -->
                    <div class="bg-orange-100 rounded-lg p-4">
                        <h3 class="text-lg font-semibold text-black-900 mb-4">Payment Statistics</h3>
                        <dl class="space-y-3">
                            <div>
                                <dt class="text-sm font-medium text-black-700">Total Payments</dt>
                                <dd class="text-lg font-bold text-black-600">{{ $stats['total_payments'] }}</dd>
                            </div>
                            <div>
                                <dt class="text-sm font-medium text-black-700">Total Amount</dt>
                                <dd class="text-lg font-bold text-green-600">${{ number_format($stats['total_amount'], 2) }}</dd>
                            </div>
                            <div>
                                <dt class="text-sm font-medium text-black-700">Paid Amount</dt>
                                <dd class="text-sm text-green-600">${{ number_format($stats['paid_amount'], 2) }}</dd>
                            </div>
                            <div>
                                <dt class="text-sm font-medium text-black-700">Unpaid Amount</dt>
                                <dd class="text-sm text-red-600">${{ number_format($stats['unpaid_amount'], 2) }}</dd>
                            </div>
                            <div>
                                <dt class="text-sm font-medium text-black-700">Average Payment</dt>
                                <dd class="text-sm text-black-900">${{ number_format($stats['avg_payment'], 2) }}</dd>
                            </div>
                        </dl>
                    </div>
                </div>
            </div>
        </div>

        <!-- Payment History -->
        <div class="bg-orange-50 shadow-lg rounded-xl overflow-hidden">
            <div class="px-6 py-4 border-b border-orange-200">
                <div class="flex justify-between items-center">
                    <h3 class="text-lg font-semibold text-black-900">Payment History</h3>
                    @if($company->payments->count() > 0)
                        <a href="{{ route('payments.create', ['company_id' => $company->id]) }}"
                           class="inline-flex items-center px-3 py-2 border border-transparent text-sm font-medium rounded-lg text-white bg-green-600 hover:bg-green-700">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                            </svg>
                            Add Payment
                        </a>
                    @endif
                </div>
            </div>

            @if($company->payments->count() > 0)
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-orange-200">
                        <thead class="bg-orange-100">
                        <tr>
                            <th class="px-6 py-3.5 text-left text-xs font-medium text-black-700 uppercase tracking-wide">Date</th>
                            <th class="px-6 py-3.5 text-left text-xs font-medium text-black-700 uppercase tracking-wide">Store</th>
                            <th class="px-6 py-3.5 text-left text-xs font-medium text-black-700 uppercase tracking-wide">Service</th>
                            <th class="px-6 py-3.5 text-left text-xs font-medium text-black-700 uppercase tracking-wide">Amount</th>
                            <th class="px-6 py-3.5 text-left text-xs font-medium text-black-700 uppercase tracking-wide">Status</th>
                            <th class="px-6 py-3.5 text-left text-xs font-medium text-black-700 uppercase tracking-wide">Actions</th>
                        </tr>
                        </thead>
                        <tbody class="divide-y divide-orange-200 bg-orange-50">
                        @foreach($company->payments as $payment)
                            <tr class="hover:bg-orange-100">
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-black-900">
                                    {{ $payment->date->format('M d, Y') }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-black-900">
                                    {{ $payment->store }}
                                </td>
                                <td class="px-6 py-4 text-sm text-black-900">
                                    <div>{{ $payment->what_got_fixed ?: 'N/A' }}</div>
                                    @if($payment->maintenance_type)
                                        <div class="text-xs text-black-700">{{ $payment->maintenance_type }}</div>
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-semibold text-green-600">
                                    ${{ number_format($payment->cost, 2) }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    @if($payment->paid)
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                        Paid
                                    </span>
                                    @else
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                        Unpaid
                                    </span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                    <div class="flex space-x-3">
                                        <a href="{{ route('payments.show', $payment) }}" class="text-black-600 hover:text-black-700">View</a>
                                        <a href="{{ route('payments.edit', $payment) }}" class="text-black-600 hover:text-black-700">Edit</a>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="text-center py-12">
                    <svg class="mx-auto h-12 w-12 text-black-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                    <h3 class="mt-2 text-sm font-medium text-black-900">No payments yet</h3>
                    <p class="mt-1 text-sm text-black-700">This company hasn't had any payments recorded yet.</p>
                    <div class="mt-6">
                        <a href="{{ route('payments.create', ['company_id' => $company->id]) }}"
                           class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-lg text-white bg-orange-600 hover:bg-orange-700">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                            </svg>
                            Add First Payment
                        </a>
                    </div>
                </div>
            @endif
        </div>

        <!-- Company Actions -->
        @if($company->payments->count() == 0)
            <div class="mt-8 bg-red-50 border border-red-200 rounded-lg p-4">
                <div class="flex justify-between items-center">
                    <div>
                        <h4 class="text-sm font-medium text-red-800">Delete Company</h4>
                        <p class="text-sm text-red-700 mt-1">This company has no payment records and can be safely deleted.</p>
                    </div>
                    <form action="{{ route('companies.destroy', $company) }}" method="POST" class="inline"
                          onsubmit="return confirm('Are you sure you want to delete this company? This action cannot be undone.')">
                        @csrf
                        @method('DELETE')
                        <button type="submit"
                                class="inline-flex items-center px-4 py-2 border border-red-300 rounded-lg text-sm font-medium text-red-700 bg-red-50 hover:bg-red-100 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                            </svg>
                            Delete Company
                        </button>
                    </form>
                </div>
            </div>
        @endif
    </div>
@endsection
