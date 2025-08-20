@extends('layouts.app')

@section('title', 'Add Company')

@section('content')
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <div class="bg-orange-50 shadow-lg rounded-xl overflow-hidden">
            <div class="px-6 py-4 border-b border-orange-200">
                <div class="flex justify-between items-center">
                    <h2 class="text-2xl font-bold text-black-900">Add New Company</h2>
                    <a href="{{ route('companies.index') }}"
                       class="inline-flex items-center px-4 py-2 border border-orange-300 rounded-lg text-sm font-medium text-black-700 bg-white hover:bg-orange-100 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-orange-500">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                        </svg>
                        Back to Companies
                    </a>
                </div>
            </div>

            <form action="{{ route('companies.store') }}" method="POST" class="p-6 space-y-6">
                @csrf

                <!-- Company Name -->
                <div>
                    <label for="name" class="block text-sm font-medium text-black-700 mb-2">Company Name <span class="text-red-500">*</span></label>
                    <input type="text" name="name" id="name" required
                           class="block w-full border-orange-300 rounded-lg shadow-sm focus:ring-orange-500 focus:border-orange-500 sm:text-sm @error('name') border-red-300 @enderror"
                           value="{{ old('name') }}"
                           placeholder="Enter company name">
                    @error('name')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Contact Person -->
                    <div>
                        <label for="contact_person" class="block text-sm font-medium text-black-700 mb-2">Contact Person</label>
                        <input type="text" name="contact_person" id="contact_person"
                               class="block w-full border-orange-300 rounded-lg shadow-sm focus:ring-orange-500 focus:border-orange-500 sm:text-sm @error('contact_person') border-red-300 @enderror"
                               value="{{ old('contact_person') }}"
                               placeholder="Primary contact name">
                        @error('contact_person')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Phone -->
                    <div>
                        <label for="phone" class="block text-sm font-medium text-black-700 mb-2">Phone</label>
                        <input type="tel" name="phone" id="phone"
                               class="block w-full border-orange-300 rounded-lg shadow-sm focus:ring-orange-500 focus:border-orange-500 sm:text-sm @error('phone') border-red-300 @enderror"
                               value="{{ old('phone') }}"
                               placeholder="(555) 123-4567">
                        @error('phone')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <!-- Email -->
                <div>
                    <label for="email" class="block text-sm font-medium text-black-700 mb-2">Email</label>
                    <input type="email" name="email" id="email"
                           class="block w-full border-orange-300 rounded-lg shadow-sm focus:ring-orange-500 focus:border-orange-500 sm:text-sm @error('email') border-red-300 @enderror"
                           value="{{ old('email') }}"
                           placeholder="company@example.com">
                    @error('email')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Address -->
                <div>
                    <label for="address" class="block text-sm font-medium text-black-700 mb-2">Address</label>
                    <textarea name="address" id="address" rows="3"
                              class="block w-full border-orange-300 rounded-lg shadow-sm focus:ring-orange-500 focus:border-orange-500 sm:text-sm @error('address') border-red-300 @enderror"
                              placeholder="Company address">{{ old('address') }}</textarea>
                    @error('address')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Website -->
                    <div>
                        <label for="website" class="block text-sm font-medium text-black-700 mb-2">Website</label>
                        <input type="url" name="website" id="website"
                               class="block w-full border-orange-300 rounded-lg shadow-sm focus:ring-orange-500 focus:border-orange-500 sm:text-sm @error('website') border-red-300 @enderror"
                               value="{{ old('website') }}"
                               placeholder="https://www.example.com">
                        @error('website')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Status -->
                    <div>
                        <label for="is_active" class="block text-sm font-medium text-black-700 mb-2">Status</label>
                        <select name="is_active" id="is_active"
                                class="block w-full border-orange-300 rounded-lg shadow-sm focus:ring-orange-500 focus:border-orange-500 sm:text-sm @error('is_active') border-red-300 @enderror">
                            <option value="1" {{ old('is_active', '1') == '1' ? 'selected' : '' }}>Active</option>
                            <option value="0" {{ old('is_active') == '0' ? 'selected' : '' }}>Inactive</option>
                        </select>
                        @error('is_active')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <!-- Notes -->
                <div>
                    <label for="notes" class="block text-sm font-medium text-black-700 mb-2">Notes</label>
                    <textarea name="notes" id="notes" rows="4"
                              class="block w-full border-orange-300 rounded-lg shadow-sm focus:ring-orange-500 focus:border-orange-500 sm:text-sm @error('notes') border-red-300 @enderror"
                              placeholder="Additional notes about the company">{{ old('notes') }}</textarea>
                    @error('notes')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Submit Button -->
                <div class="flex justify-end space-x-3 pt-6 border-t border-orange-200">
                    <a href="{{ route('companies.index') }}"
                       class="inline-flex items-center px-4 py-2 border border-orange-300 rounded-lg text-sm font-medium text-black-700 bg-white hover:bg-orange-100 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-orange-500 transition-colors">
                        Cancel
                    </a>
                    <button type="submit"
                            class="inline-flex items-center px-4 py-2 border border-transparent rounded-lg text-sm font-medium text-white bg-orange-600 hover:bg-orange-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-orange-500 transition-colors">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                        </svg>
                        Create Company
                    </button>
                </div>
            </form>
        </div>
    </div>
@endsection
