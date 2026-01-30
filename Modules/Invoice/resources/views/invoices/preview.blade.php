@extends('layouts.app')

@section('title', 'Invoice Preview')

@section('content')
<div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <!-- Header -->
    <div class="mb-6">
        <a href="{{ route('invoice.generate.form') }}" class="inline-flex items-center text-orange-600 hover:text-orange-700 mb-4">
            <svg class="h-5 w-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
            </svg>
            Back to Form
        </a>
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-3xl font-bold text-black-900">Invoice Preview</h1>
                <p class="mt-2 text-sm text-black-600">Review invoice details before generating</p>
            </div>
            <div class="text-right">
                <p class="text-sm text-black-600">Invoice Number</p>
                <p class="text-xl font-bold text-orange-600">{{ $invoiceNumber }}</p>
            </div>
        </div>
    </div>

    <!-- Invoice Preview Card -->
    <div class="bg-white shadow-xl rounded-xl overflow-hidden mb-6">
        <!-- Store & Period Info -->
        <div class="px-6 py-4 bg-gradient-to-r from-orange-50 to-orange-100 border-b border-orange-200">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <p class="text-sm font-medium text-black-600">Store</p>
                    <p class="text-lg font-bold text-black-900">{{ $invoiceData['store']['number'] }} - {{ $invoiceData['store']['name'] }}</p>
                    @if($invoiceData['store']['address'])
                    <p class="text-sm text-black-600">{{ $invoiceData['store']['address'] }}</p>
                    @endif
                </div>
                <div class="text-right">
                    <p class="text-sm font-medium text-black-600">Period</p>
                    <p class="text-lg font-bold text-black-900">{{ $invoiceData['period']['from'] }} - {{ $invoiceData['period']['to'] }}</p>
                    <p class="text-sm text-black-600">{{ $invoiceData['period']['days'] }} days</p>
                </div>
            </div>
        </div>

        <!-- Invoice Body -->
        <div class="p-6 space-y-6">
            <!-- 1. Labor Costs -->
            <div>
                <h3 class="text-lg font-bold text-black-900 mb-3 flex items-center">
                    <span class="flex items-center justify-center w-8 h-8 rounded-full bg-blue-100 text-blue-600 text-sm font-bold mr-2">1</span>
                    Labor Costs
                </h3>
                <div class="bg-blue-50 rounded-lg p-4 border border-blue-200">
                    @forelse($invoiceData['labor']['by_technician'] as $tech)
                    <div class="flex justify-between items-center py-2 {{ !$loop->last ? 'border-b border-blue-200' : '' }}">
                        <div>
                            <p class="font-semibold text-black-900">{{ $tech['name'] }}</p>
                            <p class="text-sm text-black-600">{{ number_format($tech['total_hours'], 2) }} hours @ ${{ number_format($tech['hourly_rate'], 2) }}/hr</p>
                        </div>
                        <p class="text-lg font-bold text-blue-600">${{ number_format($tech['total_cost'], 2) }}</p>
                    </div>
                    @empty
                    <p class="text-sm text-black-500 text-center py-2">No labor costs for this period</p>
                    @endforelse
                    <div class="flex justify-between items-center pt-3 mt-3 border-t-2 border-blue-300">
                        <p class="font-bold text-black-900">Labor Subtotal</p>
                        <p class="text-xl font-bold text-blue-600">${{ number_format($invoiceData['labor']['total'], 2) }}</p>
                    </div>
                </div>
            </div>

            <!-- 2. Technician Materials -->
            <div>
                <h3 class="text-lg font-bold text-black-900 mb-3 flex items-center">
                    <span class="flex items-center justify-center w-8 h-8 rounded-full bg-green-100 text-green-600 text-sm font-bold mr-2">2</span>
                    Technician Materials
                </h3>
                <div class="bg-green-50 rounded-lg p-4 border border-green-200">
                    @forelse($invoiceData['materials']['items'] as $material)
                    <div class="flex justify-between items-center py-2 {{ !$loop->last ? 'border-b border-green-200' : '' }}">
                        <p class="text-black-900">{{ $material['item_name'] }}</p>
                        <p class="font-semibold text-green-600">${{ number_format($material['cost'], 2) }}</p>
                    </div>
                    @empty
                    <p class="text-sm text-black-500 text-center py-2">No materials purchased for this period</p>
                    @endforelse
                    <div class="flex justify-between items-center pt-3 mt-3 border-t-2 border-green-300">
                        <p class="font-bold text-black-900">Materials Subtotal</p>
                        <p class="text-xl font-bold text-green-600">${{ number_format($invoiceData['materials']['total'], 2) }}</p>
                    </div>
                </div>
            </div>

            <!-- 3. Admin Equipment -->
            <div>
                <div class="flex items-center justify-between mb-3">
                    <h3 class="text-lg font-bold text-black-900 flex items-center">
                        <span class="flex items-center justify-center w-8 h-8 rounded-full bg-purple-100 text-purple-600 text-sm font-bold mr-2">3</span>
                        Admin Equipment Purchases
                    </h3>
                    <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-bold bg-purple-100 text-purple-800 border border-purple-300">
                        <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path>
                        </svg>
                        Admin Purchase
                    </span>
                </div>
                <div class="bg-gradient-to-br from-purple-50 to-white rounded-lg p-4 border-2 border-purple-200">
                    @forelse($invoiceData['equipment']['items'] as $equipment)
                    <div class="py-3 {{ !$loop->last ? 'border-b border-purple-200' : '' }}">
                        <div class="flex justify-between items-start mb-1">
                            <div class="flex-1">
                                <p class="font-semibold text-black-900">{{ $equipment['item_name'] }}</p>
                                <div class="flex items-center gap-3 mt-1">
                                    <p class="text-xs text-black-600">
                                        <span class="font-medium">Qty:</span> {{ $equipment['quantity'] }}
                                    </p>
                                    <p class="text-xs text-black-600">
                                        <span class="font-medium">Unit:</span> ${{ number_format($equipment['unit_cost'], 2) }}
                                    </p>
                                    <p class="text-xs text-purple-700 font-medium">
                                        {{ $equipment['company'] }}
                                    </p>
                                </div>
                                <p class="text-xs text-black-500 mt-1">
                                    Payment Date: {{ $equipment['payment_date'] }} | Payment #{{ $equipment['payment_id'] }}
                                </p>
                            </div>
                            <p class="text-lg font-bold text-purple-600 ml-4">${{ number_format($equipment['total_cost'], 2) }}</p>
                        </div>
                    </div>
                    @empty
                    <p class="text-sm text-black-500 text-center py-4">No equipment purchases for this period</p>
                    @endforelse
                    <div class="flex justify-between items-center pt-4 mt-4 border-t-2 border-purple-300">
                        <p class="font-bold text-black-900">Equipment Subtotal</p>
                        <p class="text-xl font-bold text-purple-600">${{ number_format($invoiceData['equipment']['total'], 2) }}</p>
                    </div>
                </div>
            </div>

            <!-- 4. Mileage -->
            <div>
                <h3 class="text-lg font-bold text-black-900 mb-3 flex items-center">
                    <span class="flex items-center justify-center w-8 h-8 rounded-full bg-orange-100 text-orange-600 text-sm font-bold mr-2">4</span>
                    Mileage
                </h3>
                <div class="bg-orange-50 rounded-lg p-4 border border-orange-200">
                    <div class="flex justify-between items-center">
                        <div>
                            <p class="text-black-900">Total Miles Driven</p>
                            <p class="text-sm text-black-600">{{ number_format($invoiceData['mileage']['total_miles'], 2) }} miles</p>
                        </div>
                        <p class="text-xl font-bold text-orange-600">${{ number_format($invoiceData['mileage']['payment'], 2) }}</p>
                    </div>
                </div>
            </div>

            <!-- Totals -->
            <div class="bg-gradient-to-r from-orange-50 to-orange-100 rounded-lg p-6 border-2 border-orange-300">
                <div class="space-y-3">
                    <div class="flex justify-between text-lg">
                        <span class="text-black-700">Subtotal</span>
                        <span class="font-semibold text-black-900">${{ number_format($invoiceData['totals']['subtotal'], 2) }}</span>
                    </div>
                    <div class="flex justify-between text-lg">
                        <span class="text-black-700">Tax ({{ $invoiceData['totals']['tax_rate'] }}%)</span>
                        <span class="font-semibold text-black-900">${{ number_format($invoiceData['totals']['tax'], 2) }}</span>
                    </div>
                    <div class="flex justify-between text-2xl pt-3 border-t-2 border-orange-400">
                        <span class="font-bold text-black-900">Grand Total</span>
                        <span class="font-bold text-orange-600">${{ number_format($invoiceData['totals']['grand_total'], 2) }}</span>
                    </div>
                </div>
            </div>

            <!-- Summary Stats -->
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4 pt-4">
                <div class="text-center p-3 bg-gray-50 rounded-lg">
                    <p class="text-2xl font-bold text-black-900">{{ $invoiceData['invoice_cards_count'] }}</p>
                    <p class="text-xs text-black-600">Invoice Cards</p>
                </div>
                <div class="text-center p-3 bg-gray-50 rounded-lg">
                    <p class="text-2xl font-bold text-black-900">{{ count($invoiceData['labor']['by_technician']) }}</p>
                    <p class="text-xs text-black-600">Technicians</p>
                </div>
                <div class="text-center p-3 bg-gray-50 rounded-lg">
                    <p class="text-2xl font-bold text-black-900">{{ count($invoiceData['materials']['items']) }}</p>
                    <p class="text-xs text-black-600">Materials</p>
                </div>
                <div class="text-center p-3 bg-gray-50 rounded-lg">
                    <p class="text-2xl font-bold text-black-900">{{ count($invoiceData['equipment']['items']) }}</p>
                    <p class="text-xs text-black-600">Equipment</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Action Buttons -->
    <div class="flex gap-4">
        <a href="{{ route('invoice.generate.form') }}" 
           class="flex-1 inline-flex justify-center items-center px-6 py-3 border-2 border-orange-200 rounded-lg text-base font-medium text-orange-700 bg-white hover:bg-orange-50 transition-all">
            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 17l-5-5m0 0l5-5m-5 5h12"></path>
            </svg>
            Edit Details
        </a>
        <form action="{{ route('invoice.generate') }}" method="POST" class="flex-1">
            @csrf
            <input type="hidden" name="store_id" value="{{ $invoiceData['store']['id'] }}">
            <input type="hidden" name="date_from" value="{{ request('date_from') }}">
            <input type="hidden" name="date_to" value="{{ request('date_to') }}">
            <button type="submit" 
                    class="w-full inline-flex justify-center items-center px-6 py-3 border border-transparent rounded-lg text-base font-medium text-white bg-green-600 hover:bg-green-700 shadow-lg hover:shadow-xl transition-all">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                </svg>
                Generate Invoice
            </button>
        </form>
    </div>

    <!-- Info -->
    <div class="mt-6 text-center text-sm text-black-500">
        <p>Generated at: {{ $invoiceData['generated_at'] }}</p>
    </div>
</div>
@endsection
