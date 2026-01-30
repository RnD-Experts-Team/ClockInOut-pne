@extends('layouts.app')

@section('content')
<div class="max-w-2xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <!-- Header -->
    <div class="mb-6">
        <a href="{{ route('invoice.email-recipients.index') }}" class="inline-flex items-center text-orange-600 hover:text-orange-700 mb-4">
            <svg class="h-5 w-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
            </svg>
            Back to Recipients
        </a>
        <h1 class="text-3xl font-bold text-black-900">Add Email Recipient</h1>
        <p class="mt-2 text-sm text-black-600">Add a new invoice email recipient</p>
    </div>

    <!-- Error Messages -->
    @if($errors->any())
    <div class="mb-6 bg-red-50 border border-red-200 text-red-800 px-4 py-3 rounded-lg">
        <ul class="list-disc list-inside">
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
    @endif

    <!-- Form -->
    <div class="bg-white shadow-md rounded-lg p-6">
        <form action="{{ route('invoice.email-recipients.store') }}" method="POST">
            @csrf

            <!-- Store Selection -->
            <div class="mb-6">
                <label for="store_id" class="block text-sm font-medium text-gray-700 mb-2">
                    Store <span class="text-red-500">*</span>
                </label>
                <select id="store_id" 
                        name="store_id" 
                        required
                        class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-orange-500 focus:ring-orange-500">
                    <option value="">Select a store...</option>
                    @foreach($stores as $store)
                        <option value="{{ $store->id }}" {{ old('store_id') == $store->id ? 'selected' : '' }}>
                            {{ $store->store_number }} - {{ $store->name }}
                        </option>
                    @endforeach
                </select>
                <p class="mt-1 text-xs text-gray-500">Select the store this recipient is for</p>
            </div>

            <!-- Recipient Name -->
            <div class="mb-6">
                <label for="name" class="block text-sm font-medium text-gray-700 mb-2">
                    Recipient Name <span class="text-red-500">*</span>
                </label>
                <input type="text" 
                       id="name" 
                       name="name" 
                       value="{{ old('name') }}"
                       required
                       class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-orange-500 focus:ring-orange-500"
                       placeholder="e.g., John Smith">
                <p class="mt-1 text-xs text-gray-500">Full name of the recipient</p>
            </div>

            <!-- Email Address -->
            <div class="mb-6">
                <label for="email" class="block text-sm font-medium text-gray-700 mb-2">
                    Email Address <span class="text-red-500">*</span>
                </label>
                <input type="email" 
                       id="email" 
                       name="email" 
                       value="{{ old('email') }}"
                       required
                       class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-orange-500 focus:ring-orange-500"
                       placeholder="john@example.com">
                <p class="mt-1 text-xs text-gray-500">Email address where invoices will be sent</p>
            </div>

            <!-- Set as Default -->
            <div class="mb-6">
                <div class="flex items-center">
                    <input type="checkbox" 
                           id="is_default" 
                           name="is_default" 
                           value="1"
                           {{ old('is_default') ? 'checked' : '' }}
                           class="h-4 w-4 text-orange-600 focus:ring-orange-500 border-gray-300 rounded">
                    <label for="is_default" class="ml-2 block text-sm text-gray-700">
                        Set as default recipient for this store
                    </label>
                </div>
                <p class="mt-1 ml-6 text-xs text-gray-500">
                    The default recipient will be pre-selected when sending invoices for this store
                </p>
            </div>

            <!-- Buttons -->
            <div class="flex justify-end gap-3">
                <a href="{{ route('invoice.email-recipients.index') }}" 
                   class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-lg text-sm font-medium text-gray-700 bg-white hover:bg-gray-50">
                    Cancel
                </a>
                <button type="submit" 
                        class="inline-flex items-center px-4 py-2 border border-transparent rounded-lg text-sm font-medium text-white bg-orange-600 hover:bg-orange-700">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                    </svg>
                    Add Recipient
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
