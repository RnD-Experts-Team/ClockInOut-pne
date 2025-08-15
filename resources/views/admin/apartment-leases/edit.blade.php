@extends('layouts.app')

@section('content')
    <div class="container mx-auto px-4 py-8">
        <div class="max-w-4xl mx-auto">
            <div class="bg-white shadow-lg rounded-lg overflow-hidden">
                <div class="bg-blue-600 px-6 py-4">
                    <h1 class="text-2xl font-bold text-white">Edit Apartment Lease</h1>
                </div>

                <form action="{{ route('admin.apartment-leases.update', $apartmentLease) }}" method="POST" class="p-6">
                    @csrf
                    @method('PUT')

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- Store Number -->
                        <div>
                            <label for="store_number" class="block text-sm font-medium text-gray-700 mb-2">Store Number</label>
                            <input type="number" name="store_number" id="store_number"
                                   class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm @error('store_number') border-red-300 @enderror"
                                   value="{{ old('store_number', $apartmentLease->store_number ?? '') }}"
                                   placeholder="1" min="1">
                            @error('store_number')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Apartment Address -->
                        <div>
                            <label for="apartment_address" class="block text-sm font-medium text-gray-700 mb-2">Apartment Address <span class="text-red-500">*</span></label>
                            <textarea name="apartment_address" id="apartment_address" rows="3" required
                                      class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm @error('apartment_address') border-red-300 @enderror"
                                      placeholder="Enter full apartment address">{{ old('apartment_address', $apartmentLease->apartment_address ?? '') }}</textarea>
                            @error('apartment_address')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Rent -->
                        <div>
                            <label for="rent" class="block text-sm font-medium text-gray-700 mb-2">Rent <span class="text-red-500">*</span></label>
                            <input type="number" name="rent" id="rent" step="0.01" min="0" required
                                   class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm @error('rent') border-red-300 @enderror"
                                   value="{{ old('rent', $apartmentLease->rent ?? '') }}"
                                   placeholder="1200.00">
                            @error('rent')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Utilities -->
                        <div>
                            <label for="utilities" class="block text-sm font-medium text-gray-700 mb-2">Utilities</label>
                            <input type="number" name="utilities" id="utilities" step="0.01" min="0"
                                   class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm @error('utilities') border-red-300 @enderror"
                                   value="{{ old('utilities', $apartmentLease->utilities ?? '') }}"
                                   placeholder="150.00">
                            @error('utilities')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Number of AT -->
                        <div>
                            <label for="number_of_AT" class="block text-sm font-medium text-gray-700 mb-2">Number of AT</label>
                            <input type="number" name="number_of_AT" id="number_of_AT" min="1"
                                   class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm @error('number_of_AT') border-red-300 @enderror"
                                   value="{{ old('number_of_AT', $apartmentLease->number_of_AT ?? 1) }}"
                                   placeholder="1">
                            @error('number_of_AT')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Number of Cars - FIXED TO INTEGER INPUT -->
                        <div>
                            <label for="has_car" class="block text-sm font-medium text-gray-700 mb-2">Number of Cars</label>
                            <input type="number" name="has_car" id="has_car" min="0" max="10"
                                   class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm @error('has_car') border-red-300 @enderror"
                                   value="{{ old('has_car', $apartmentLease->has_car ?? 0) }}"
                                   placeholder="0">
                            <p class="mt-1 text-xs text-gray-500">Enter the number of cars (0 if no cars)</p>
                            @error('has_car')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Is Family -->
                        <div>
                            <label for="is_family" class="block text-sm font-medium text-gray-700 mb-2">Is Family <span class="text-red-500">*</span></label>
                            <select name="is_family" id="is_family" required
                                    class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm @error('is_family') border-red-300 @enderror">
                                <option value="">Select...</option>
                                <option value="yes" {{ old('is_family', $apartmentLease->is_family ?? '') == 'yes' ? 'selected' : '' }}>Yes</option>
                                <option value="no" {{ old('is_family', $apartmentLease->is_family ?? '') == 'no' ? 'selected' : '' }}>No</option>
                            </select>
                            @error('is_family')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Expiration Date - FIXED DATE FORMATTING -->
                        <div>
                            <label for="expiration_date" class="block text-sm font-medium text-gray-700 mb-2">Expiration Date</label>
                            <input type="date" name="expiration_date" id="expiration_date"
                                   class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm @error('expiration_date') border-red-300 @enderror"
                                   value="{{ old('expiration_date', $apartmentLease->expiration_date ? \Carbon\Carbon::parse($apartmentLease->expiration_date)->format('Y-m-d') : '') }}">
                            @error('expiration_date')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Drive Time -->
                        <div>
                            <label for="drive_time" class="block text-sm font-medium text-gray-700 mb-2">Drive Time</label>
                            <input type="text" name="drive_time" id="drive_time"
                                   class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm @error('drive_time') border-red-300 @enderror"
                                   value="{{ old('drive_time', $apartmentLease->drive_time ?? '') }}"
                                   placeholder="e.g., 15 minutes">
                            @error('drive_time')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Lease Holder -->
                        <div class="md:col-span-2">
                            <label for="lease_holder" class="block text-sm font-medium text-gray-700 mb-2">Lease Holder <span class="text-red-500">*</span></label>
                            <textarea name="lease_holder" id="lease_holder" rows="2" required
                                      class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm @error('lease_holder') border-red-300 @enderror"
                                      placeholder="Enter lease holder name">{{ old('lease_holder', $apartmentLease->lease_holder ?? '') }}</textarea>
                            @error('lease_holder')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Notes -->
                        <div class="md:col-span-2">
                            <label for="notes" class="block text-sm font-medium text-gray-700 mb-2">Notes</label>
                            <textarea name="notes" id="notes" rows="4"
                                      class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm @error('notes') border-red-300 @enderror"
                                      placeholder="Additional notes or comments">{{ old('notes', $apartmentLease->notes ?? '') }}</textarea>
                            @error('notes')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <!-- Form Actions -->
                    <div class="flex justify-end space-x-4 mt-8 pt-6 border-t border-gray-200">
                        <a href="{{ route('admin.apartment-leases.show', $apartmentLease) }}"
                           class="px-6 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                            Cancel
                        </a>
                        <button type="submit"
                                class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                            Update Lease
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
