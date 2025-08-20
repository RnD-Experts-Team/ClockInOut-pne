{{-- resources/views/payments/create.blade.php --}}
@extends('layouts.app')

@section('title', 'Add Payment')

@section('content')
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <div class="bg-white shadow-lg rounded-xl overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200">
                <div class="flex justify-between items-center">
                    <h2 class="text-2xl font-bold text-gray-900">Add New Payment</h2>
                    <a href="{{ route('payments.index') }}"
                       class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-lg text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-[#3B82F6]">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                        </svg>
                        Back to Payments
                    </a>
                </div>
            </div>

            <form action="{{ route('payments.store') }}" method="POST" class="p-6 space-y-6">
                @csrf

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Store -->
                    <div>
                        <label for="store" class="block text-sm font-medium text-gray-700 mb-2">Store Number</label>
                        <input type="text" name="store" id="store"
                               class="block w-full border-gray-300 rounded-lg shadow-sm focus:ring-[#3B82F6] focus:border-[#3B82F6] sm:text-sm @error('store') border-red-300 @enderror"
                               value="{{ old('store') }}"
                               placeholder="e.g., Store 024">
                        @error('store')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Date -->
                    <div>
                        <label for="date" class="block text-sm font-medium text-gray-700 mb-2">Date</label>
                        <input type="date" name="date" id="date"
                               class="block w-full border-gray-300 rounded-lg shadow-sm focus:ring-[#3B82F6] focus:border-[#3B82F6] sm:text-sm @error('date') border-red-300 @enderror"
                               value="{{ old('date', date('Y-m-d')) }}">
                        @error('date')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Company -->
                    <div>
                        <label for="company_id" class="block text-sm font-medium text-gray-700 mb-2">Company</label>
                        <select name="company_id" id="company_id"
                                class="block w-full border-gray-300 rounded-lg shadow-sm focus:ring-[#3B82F6] focus:border-[#3B82F6] sm:text-sm @error('company_id') border-red-300 @enderror">
                            <option value="">Select a Company</option>
                            @foreach($companies as $company)
                                <option value="{{ $company->id }}" {{ old('company_id') == $company->id ? 'selected' : '' }}>
                                    {{ $company->name }}
                                </option>
                            @endforeach
                        </select>
                        @error('company_id')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Cost -->
                    <div>
                        <label for="cost" class="block text-sm font-medium text-gray-700 mb-2">Cost</label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <span class="text-gray-500 sm:text-sm">$</span>
                            </div>
                            <input type="number" name="cost" id="cost" step="0.01" min="0"
                                   class="block w-full pl-7 border-gray-300 rounded-lg shadow-sm focus:ring-[#3B82F6] focus:border-[#3B82F6] sm:text-sm @error('cost') border-red-300 @enderror"
                                   value="{{ old('cost') }}"
                                   placeholder="0.00">
                        </div>
                        @error('cost')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Maintenance Type -->
                    <div>
                        <label for="maintenance_type" class="block text-sm font-medium text-gray-700 mb-2">Maintenance Type</label>
                        <input type="text" name="maintenance_type" id="maintenance_type"
                               class="block w-full border-gray-300 rounded-lg shadow-sm focus:ring-[#3B82F6] focus:border-[#3B82F6] sm:text-sm"
                               value="{{ old('maintenance_type') }}"
                               placeholder="e.g., Equipment/Parts">
                    </div>

                    <!-- Payment Method -->
                    <div>
                        <label for="payment_method" class="block text-sm font-medium text-gray-700 mb-2">Payment Method</label>
                        <select name="payment_method" id="payment_method"
                                class="block w-full border-gray-300 rounded-lg shadow-sm focus:ring-[#3B82F6] focus:border-[#3B82F6] sm:text-sm">
                            <option value="">Select Payment Method</option>
                            <option value="CC" {{ old('payment_method') === 'CC' ? 'selected' : '' }}>Credit Card (CC)</option>
                            <option value="Cash" {{ old('payment_method') === 'Cash' ? 'selected' : '' }}>Cash</option>
                            <option value="Check" {{ old('payment_method') === 'Check' ? 'selected' : '' }}>Check</option>
                            <option value="ACH" {{ old('payment_method') === 'ACH' ? 'selected' : '' }}>ACH</option>
                            <option value="Wire" {{ old('payment_method') === 'Wire' ? 'selected' : '' }}>Wire Transfer</option>
                        </select>
                    </div>
                </div>

                <!-- What Got Fixed -->
                <div>
                    <label for="what_got_fixed" class="block text-sm font-medium text-gray-700 mb-2">What Got Fixed</label>
                    <input type="text" name="what_got_fixed" id="what_got_fixed"
                           class="block w-full border-gray-300 rounded-lg shadow-sm focus:ring-[#3B82F6] focus:border-[#3B82F6] sm:text-sm"
                           value="{{ old('what_got_fixed') }}"
                           placeholder="e.g., Make Line (Pizza Dress Station)">
                </div>

                <!-- Notes -->
                <div>
                    <label for="notes" class="block text-sm font-medium text-gray-700 mb-2">Notes</label>
                    <textarea name="notes" id="notes" rows="3"
                              class="block w-full border-gray-300 rounded-lg shadow-sm focus:ring-[#3B82F6] focus:border-[#3B82F6] sm:text-sm"
                              placeholder="Additional notes or invoice links...">{{ old('notes') }}</textarea>
                </div>

                <!-- Payment Status -->
                <div class="flex items-center">
                    <input type="checkbox" name="paid" id="paid" value="1"
                           class="h-4 w-4 text-[#3B82F6] focus:ring-[#3B82F6] border-gray-300 rounded"
                        {{ old('paid') ? 'checked' : '' }}>
                    <label for="paid" class="ml-2 block text-sm text-gray-700">
                        Payment has been made
                    </label>
                </div>

                <!-- Submit Button -->
                <div class="flex justify-end space-x-3">
                    <a href="{{ route('payments.index') }}"
                       class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-lg text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-[#3B82F6]">
                        Cancel
                    </a>
                    <button type="submit"
                            class="inline-flex items-center px-4 py-2 border border-transparent rounded-lg text-sm font-medium text-white bg-[#3B82F6] hover:bg-[#2563EB] focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-[#3B82F6]">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                        </svg>
                        Create Payment
                    </button>
                </div>
            </form>
        </div>
    </div>
@endsection
