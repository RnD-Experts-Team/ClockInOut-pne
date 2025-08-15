@extends('layouts.app')

@section('title', 'Import Apartment Leases')

@section('content')
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <!-- Header -->
        <div class="mb-8">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-3xl font-bold text-gray-900">Import Apartment Leases</h1>
                    <p class="mt-2 text-sm text-gray-600">Upload a CSV or Excel file to import multiple apartment leases</p>
                </div>
                <a href="{{ route('admin.apartment-leases.index') }}"
                   class="inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-lg text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                    </svg>
                    Back to List
                </a>
            </div>
        </div>

        <!-- Import Form -->
        <div class="bg-white shadow-lg rounded-xl overflow-hidden">
            <form action="{{ route('admin.apartment-leases.import') }}" method="POST" enctype="multipart/form-data">
                @csrf

                <div class="px-6 py-8">
                    <!-- File Upload -->
                    <div class="mb-6">
                        <label for="file" class="block text-sm font-medium text-gray-700 mb-2">Select File *</label>
                        <div class="mt-1 flex justify-center px-6 pt-5 pb-6 border-2 border-gray-300 border-dashed rounded-lg hover:border-blue-400 transition-colors">
                            <div class="space-y-1 text-center">
                                <svg class="mx-auto h-12 w-12 text-gray-400" stroke="currentColor" fill="none" viewBox="0 0 48 48">
                                    <path d="M28 8H12a4 4 0 00-4 4v20m32-12v8m0 0v8a4 4 0 01-4 4H12a4 4 0 01-4-4v-4m32-4l-3.172-3.172a4 4 0 00-5.656 0L28 28M8 32l9.172-9.172a4 4 0 015.656 0L28 28m0 0l4 4m4-24h8m-4-4v8m-12 4h.02" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                                </svg>
                                <div class="flex text-sm text-gray-600">
                                    <label for="file" class="relative cursor-pointer bg-white rounded-md font-medium text-blue-600 hover:text-blue-500 focus-within:outline-none focus-within:ring-2 focus-within:ring-offset-2 focus-within:ring-blue-500">
                                        <span>Upload a file</span>
                                        <input id="file" name="file" type="file" accept=".csv,.xlsx,.xls" class="sr-only" required>
                                    </label>
                                    <p class="pl-1">or drag and drop</p>
                                </div>
                                <p class="text-xs text-gray-500">CSV, XLSX, XLS up to 10MB</p>
                            </div>
                        </div>
                        @error('file')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- File Format Information -->
                    <div class="mb-6 p-4 bg-blue-50 border border-blue-200 rounded-lg">
                        <h3 class="text-sm font-medium text-blue-900 mb-2">Required File Format</h3>
                        <p class="text-sm text-blue-700 mb-3">Your file should contain the following columns in this order:</p>
                        <div class="grid grid-cols-2 md:grid-cols-3 gap-2 text-xs text-blue-600">
                            <div>• Store Number</div>
                            <div>• Apartment Address</div>
                            <div>• Rent</div>
                            <div>• Utilities</div>
                            <div>• Number of Residents</div>
                            <div>• Has Car</div>
                            <div>• Is Family</div>
                            <div>• Expiration Date</div>
                            <div>• Drive Time</div>
                            <div>• Notes</div>
                            <div>• Lease Holder</div>
                        </div>
                    </div>

                    <!-- Download Template -->
                    <div class="mb-6 p-4 bg-gray-50 border border-gray-200 rounded-lg">
                        <div class="flex items-center justify-between">
                            <div>
                                <h3 class="text-sm font-medium text-gray-900">Download Template</h3>
                                <p class="text-sm text-gray-600">Download a sample CSV file with the correct format</p>
                            </div>
                            <a href="#" download="apartment-leases-template.csv"
                               class="inline-flex items-center px-3 py-2 border border-transparent text-sm font-medium rounded-lg text-blue-600 bg-blue-100 hover:bg-blue-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                </svg>
                                Download Template
                            </a>
                        </div>
                    </div>

                    <!-- Import Options -->
                    <div class="mb-6">
                        <h3 class="text-lg font-medium text-gray-900 mb-4">Import Options</h3>
                        <div class="space-y-3">
                            <div class="flex items-center">
                                <input id="skip_duplicates" name="skip_duplicates" type="checkbox" class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded" checked>
                                <label for="skip_duplicates" class="ml-2 block text-sm text-gray-900">
                                    Skip duplicate entries (based on apartment address)
                                </label>
                            </div>
                            <div class="flex items-center">
                                <input id="update_existing" name="update_existing" type="checkbox" class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                                <label for="update_existing" class="ml-2 block text-sm text-gray-900">
                                    Update existing records with new data
                                </label>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Form Actions -->
                <div class="px-6 py-4 bg-gray-50 border-t border-gray-200 flex justify-end space-x-3">
                    <a href="{{ route('admin.apartment-leases.index') }}"
                       class="inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-lg text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500">
                        Cancel
                    </a>
                    <button type="submit"
                            class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-lg text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12" />
                        </svg>
                        Import Apartment Leases
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const fileInput = document.getElementById('file');
            const dropZone = fileInput.closest('.border-dashed');

            // Handle file selection
            fileInput.addEventListener('change', function(e) {
                if (e.target.files.length > 0) {
                    const fileName = e.target.files[0].name;
                    const fileSize = (e.target.files[0].size / 1024 / 1024).toFixed(2);
                    dropZone.querySelector('.text-center').innerHTML = `
                <svg class="mx-auto h-12 w-12 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <div class="text-sm text-gray-600">
                    <p class="font-medium text-green-600">${fileName}</p>
                    <p class="text-xs text-gray-500">${fileSize} MB</p>
                </div>
            `;
                    dropZone.classList.remove('border-gray-300');
                    dropZone.classList.add('border-green-300', 'bg-green-50');
                }
            });

            // Handle drag and drop
            dropZone.addEventListener('dragover', function(e) {
                e.preventDefault();
                dropZone.classList.add('border-blue-400', 'bg-blue-50');
            });

            dropZone.addEventListener('dragleave', function(e) {
                e.preventDefault();
                dropZone.classList.remove('border-blue-400', 'bg-blue-50');
            });

            dropZone.addEventListener('drop', function(e) {
                e.preventDefault();
                dropZone.classList.remove('border-blue-400', 'bg-blue-50');

                const files = e.dataTransfer.files;
                if (files.length > 0) {
                    fileInput.files = files;
                    fileInput.dispatchEvent(new Event('change'));
                }
            });
        });
    </script>

@endsection
