@extends('layouts.app')

@section('title', 'Submit New Maintenance Request')

@section('content')
<div class="max-w-4xl mx-auto">
    {{-- Header --}}
    <div class="mb-6">
        <h1 class="text-3xl font-bold text-gray-900">Submit Maintenance Request</h1>
        <p class="mt-2 text-sm text-gray-600">Report equipment issues and request maintenance support</p>
    </div>

    {{-- Form Card --}}
    <div class="bg-white shadow-xl rounded-xl overflow-hidden border border-gray-200">
        <form action="{{ route('native.requests.store') }}" method="POST" enctype="multipart/form-data" class="p-6 space-y-6">
            @csrf

            {{-- Store Selection --}}
            <div>
                <label for="store_id" class="block text-sm font-semibold text-gray-800 mb-2">
                    Store <span class="text-red-500">*</span>
                </label>
                <select name="store_id" id="store_id" required
                    class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-orange-500 focus:ring-2 focus:ring-orange-500/20 sm:text-sm transition-all duration-200 py-3 px-4">
                    <option value="">Select a store...</option>
                    @foreach($stores as $store)
                        <option value="{{ $store->id }}" {{ old('store_id') == $store->id ? 'selected' : '' }}>
                            {{ $store->store_number }} - {{ $store->name }}
                        </option>
                    @endforeach
                </select>
                @error('store_id')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            {{-- Equipment with Issue --}}
            <div>
                <label for="equipment_with_issue" class="block text-sm font-semibold text-gray-800 mb-2">
                    Equipment with Issue <span class="text-red-500">*</span>
                </label>
                <input type="text" name="equipment_with_issue" id="equipment_with_issue" required maxlength="255"
                    value="{{ old('equipment_with_issue') }}"
                    placeholder="e.g., Ice Machine, HVAC System, Refrigerator"
                    class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-orange-500 focus:ring-2 focus:ring-orange-500/20 sm:text-sm transition-all duration-200 py-3 px-4">
                @error('equipment_with_issue')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            {{-- Description --}}
            <div>
                <label for="description_of_issue" class="block text-sm font-semibold text-gray-800 mb-2">
                    Description of Issue <span class="text-red-500">*</span>
                </label>
                <textarea name="description_of_issue" id="description_of_issue" required rows="4"
                    placeholder="Please provide detailed information about the issue..."
                    class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-orange-500 focus:ring-2 focus:ring-orange-500/20 sm:text-sm transition-all duration-200 py-3 px-4">{{ old('description_of_issue') }}</textarea>
                @error('description_of_issue')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            {{-- Urgency Level --}}
            <div>
                <label class="block text-sm font-semibold text-gray-800 mb-3">
                    Urgency Level <span class="text-red-500">*</span>
                </label>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    @foreach($urgencyLevels as $urgency)
                        <label class="relative flex items-center p-4 border-2 rounded-lg cursor-pointer hover:bg-gray-50 transition-colors duration-200
                            {{ old('urgency_level_id') == $urgency->id ? 'border-orange-500 bg-orange-50' : 'border-gray-200' }}">
                            <input type="radio" name="urgency_level_id" value="{{ $urgency->id }}" required
                                {{ old('urgency_level_id') == $urgency->id ? 'checked' : '' }}
                                class="h-4 w-4 text-orange-600 border-gray-300 focus:ring-orange-500">
                            <div class="ml-3 flex-1">
                                <div class="flex items-center">
                                    <span class="w-3 h-3 rounded-full mr-2" style="background-color: {{ $urgency->color }};"></span>
                                    <span class="font-medium text-gray-900">{{ $urgency->name }}</span>
                                </div>
                            </div>
                        </label>
                    @endforeach
                </div>
                @error('urgency_level_id')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            {{-- Basic Troubleshoot --}}
            <div>
                <label class="flex items-start">
                    <div class="flex items-center h-5">
                        <input type="checkbox" name="basic_troubleshoot_done" value="1"
                            {{ old('basic_troubleshoot_done') ? 'checked' : '' }}
                            class="h-4 w-4 text-orange-600 border-gray-300 rounded focus:ring-orange-500">
                    </div>
                    <div class="ml-3">
                        <span class="font-medium text-gray-900">Basic troubleshooting completed</span>
                        <p class="text-sm text-gray-500">Check power supply, reset, check connections, etc.</p>
                    </div>
                </label>
                @error('basic_troubleshoot_done')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            {{-- Photo Attachments --}}
            <div>
                <label for="attachments" class="block text-sm font-semibold text-gray-800 mb-2">
                    Photo Attachments (Optional)
                </label>
                <input type="file" name="attachments[]" id="attachments" multiple accept="image/jpeg,image/png,image/jpg,application/pdf"
                    class="block w-full text-sm text-gray-500
                        file:mr-4 file:py-2 file:px-4
                        file:rounded-lg file:border-0
                        file:text-sm file:font-semibold
                        file:bg-orange-50 file:text-orange-700
                        hover:file:bg-orange-100">
                <p class="mt-1 text-xs text-gray-500">Max 5 files. JPEG, PNG, JPG, PDF.</p>
                @error('attachments')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
                @error('attachments.*')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            {{-- Form Actions --}}
            <div class="flex flex-col sm:flex-row gap-3 pt-4">
                <button type="submit"
                    class="flex-1 inline-flex justify-center items-center px-6 py-3 border border-transparent text-base font-medium rounded-lg text-white bg-gradient-to-r from-orange-500 to-orange-600 hover:from-orange-600 hover:to-orange-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-orange-500 transition-all duration-300 hover:shadow-lg">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                    </svg>
                    Submit Request
                </button>
                <a href="{{ route('native.requests.index') }}"
                    class="flex-1 inline-flex justify-center items-center px-6 py-3 border border-gray-300 text-base font-medium rounded-lg text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-orange-500 transition-all duration-200">
                    Cancel
                </a>
            </div>
        </form>
    </div>
</div>
@endsection
