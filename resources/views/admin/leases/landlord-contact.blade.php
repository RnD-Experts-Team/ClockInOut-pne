@extends('layouts.app')

@section('title', 'Landlord Contact Directory')

@section('content')
    <div class="min-h-screen bg-gray-50 py-8">
        <div class="max-w-full mx-auto ">
            <!-- Simple Header -->
            <div class="text-center mb-8">
                <h1 class="text-3xl font-bold text-gray-900 mb-2">Landlord Contact Directory</h1>
                <p class="text-gray-600">Generated on {{ now()->format('F j, Y \\a\\t g:i A') }}</p>
            </div>

            <!-- Excel-like Table -->
            <div class="bg-white shadow-lg rounded-lg overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="min-w-full border-collapse" id="landlordDirectoryTable">
                        <thead>
                        <tr class="bg-[#ff671b] text-white">
                            <!-- FIXED: Store # should be sorted as number, not text -->
                            <th class="border border-gray-300 px-4 py-3 text-left text-sm font-semibold cursor-pointer hover:bg-[#e55b17] transition-colors select-none"
                                onclick="sortLandlordTable(0, 'number')" id="landlord-header-0">
                                <div class="flex items-center justify-between">
                                    Store #
                                    <span class="ml-2 text-xs opacity-75" id="landlord-sort-indicator-0">↑</span>
                                </div>
                            </th>
                            <th class="border border-gray-300 px-4 py-3 text-left text-sm font-semibold cursor-pointer hover:bg-[#e55b17] transition-colors select-none"
                                onclick="sortLandlordTable(1, 'text')" id="landlord-header-1">
                                <div class="flex items-center justify-between">
                                    Store Name
                                    <span class="ml-2 text-xs opacity-75" id="landlord-sort-indicator-1">A↓</span>
                                </div>
                            </th>
                            <th class="border border-gray-300 px-4 py-3 text-left text-sm font-semibold cursor-pointer hover:bg-[#e55b17] transition-colors select-none"
                                onclick="sortLandlordTable(2, 'text')" id="landlord-header-2">
                                <div class="flex items-center justify-between">
                                    Landlord Name
                                    <span class="ml-2 text-xs opacity-75" id="landlord-sort-indicator-2">A↓</span>
                                </div>
                            </th>
                            <th class="border border-gray-300 px-4 py-3 text-left text-sm font-semibold cursor-pointer hover:bg-[#e55b17] transition-colors select-none"
                                onclick="sortLandlordTable(3, 'text')" id="landlord-header-3">
                                <div class="flex items-center justify-between">
                                    Email
                                    <span class="ml-2 text-xs opacity-75" id="landlord-sort-indicator-3">A↓</span>
                                </div>
                            </th>
                            <th class="border border-gray-300 px-4 py-3 text-left text-sm font-semibold cursor-pointer hover:bg-[#e55b17] transition-colors select-none"
                                onclick="sortLandlordTable(4, 'text')" id="landlord-header-4">
                                <div class="flex items-center justify-between">
                                    Phone
                                    <span class="ml-2 text-xs opacity-75" id="landlord-sort-indicator-4">A↓</span>
                                </div>
                            </th>
                            <th class="border border-gray-300 px-4 py-3 text-left text-sm font-semibold cursor-pointer hover:bg-[#e55b17] transition-colors select-none"
                                onclick="sortLandlordTable(5, 'text')" id="landlord-header-5">
                                <div class="flex items-center justify-between">
                                    Address
                                    <span class="ml-2 text-xs opacity-75" id="landlord-sort-indicator-5">A↓</span>
                                </div>
                            </th>
                            <th class="border border-gray-300 px-4 py-3 text-right text-sm font-semibold cursor-pointer hover:bg-[#e55b17] transition-colors select-none"
                                onclick="sortLandlordTable(6, 'number')" id="landlord-header-6">
                                <div class="flex items-center justify-end">
                                    AWS
                                    <span class="ml-2 text-xs opacity-75" id="landlord-sort-indicator-6">↑</span>
                                </div>
                            </th>
                            <th class="border border-gray-300 px-4 py-3 text-right text-sm font-semibold cursor-pointer hover:bg-[#e55b17] transition-colors select-none"
                                onclick="sortLandlordTable(7, 'number')" id="landlord-header-7">
                                <div class="flex items-center justify-end">
                                    Total Rent
                                    <span class="ml-2 text-xs opacity-75" id="landlord-sort-indicator-7">↑</span>
                                </div>
                            </th>
                            <th class="border border-gray-300 px-5 py-5 text-left text-sm font-semibold cursor-pointer hover:bg-[#e55b17] transition-colors select-none"
                                onclick="sortLandlordTable(8, 'text')" id="landlord-header-8">
                                <div class="flex items-center justify-between">
                                    Responsibilities
                                    <span class="ml-2 text-xs opacity-75" id="landlord-sort-indicator-8">A↓</span>
                                </div>
                            </th>
                        </tr>
                        </thead>
                        <tbody id="landlordTableBody">
                        @foreach($leases as $index => $lease)
                            <tr class="{{ $index % 2 == 0 ? 'bg-white' : 'bg-gray-50' }} hover:bg-[#fff4ed]">
                                <!-- Store Number -->
                                <td class="border border-gray-300 px-4 py-3 text-sm" data-sort="{{ $lease->store_number ?: 'N/A' }}">
                                    {{ $lease->store_number ?: 'N/A' }}
                                </td>
                                <!-- Store Name -->
                                <td class="border border-gray-300 px-4 py-3 text-sm" data-sort="{{ $lease->name ?: 'N/A' }}">
                                    {{ $lease->name ?: 'N/A' }}
                                </td>
                                <!-- Landlord Name -->
                                <td class="border border-gray-300 px-4 py-3 text-sm truncatable" data-sort="{{ $lease->landlord_name ?: 'N/A' }}">
                                    {{ $lease->landlord_name ?: 'N/A' }}
                                </td>
                                <!-- Email -->
                                <td class="border border-gray-300 px-4 py-3 text-sm truncatable" data-sort="{{ $lease->landlord_email ?: 'N/A' }}">
                                    {{ $lease->landlord_email ?: 'N/A' }}
                                </td>
                                <!-- Phone -->
                                <td class="border border-gray-300 px-4 py-3 text-sm" data-sort="{{ $lease->landlord_phone ?: 'N/A' }}">
                                    {{ $lease->landlord_phone ?: 'N/A' }}
                                </td>
                                <!-- Address -->
                                <td class="border border-gray-300 px-4 py-3 text-sm truncatable" data-sort="{{ $lease->landlord_address ?: 'N/A' }}">
                                    {{ $lease->landlord_address ?: 'N/A' }}
                                </td>
                                <!-- AWS -->
                                <td class="border border-gray-300 px-4 py-3 text-sm text-right" data-sort="{{ $lease->aws ?: 0 }}">
                                    ${{ number_format($lease->aws ?: 0, 0) }}
                                </td>
                                <!-- Total Rent -->
                                <td class="border border-gray-300 px-4 py-3 text-sm text-right" data-sort="{{ $lease->total_rent ?: 0 }}">
                                    ${{ number_format($lease->total_rent ?: 0, 0) }}
                                </td>
                                <!-- Responsibilities -->
                                <td class="border border-gray-300 px-5 py-5 text-sm truncatable" data-sort="{{ $lease->landlord_responsibility ?: 'N/A' }}">
                                    {{ $lease->landlord_responsibility ?: 'N/A' }}
                                </td>
                            </tr>
                        @endforeach
                        </tbody>
                        <!-- Totals Row -->
                        <tfoot>
                        <tr class="bg-[#e55b17] text-white font-semibold">
                            <td class="border border-gray-300 px-4 py-3 text-sm">TOTAL</td>
                            <td class="border border-gray-300 px-4 py-3 text-sm">{{ $leases->count() }} Stores</td>
                            <td class="border border-gray-300 px-4 py-3 text-sm">{{ $leases->whereNotNull('landlord_name')->unique('landlord_name')->count() }} Landlords</td>
                            <td class="border border-gray-300 px-4 py-3 text-sm">-</td>
                            <td class="border border-gray-300 px-4 py-3 text-sm">-</td>
                            <td class="border border-gray-300 px-4 py-3 text-sm">-</td>
                            <td class="border border-gray-300 px-4 py-3 text-sm text-right">${{ number_format($leases->sum('aws'), 0) }}</td>
                            <td class="border border-gray-300 px-4 py-3 text-sm text-right">${{ number_format($leases->sum(fn($lease) => $lease->total_rent), 0) }}</td>
                            <td class="border border-gray-300 px-4 py-3 text-sm">-</td>
                        </tr>
                        </tfoot>
                    </table>
                </div>
            </div>

            <!-- Close Button -->
            <div class="mt-6 text-center">
                <button onclick="closeModal('landlordContactModal')"
                        class="inline-flex items-center px-4 py-2 bg-[#ff671b] text-white text-sm font-medium rounded hover:bg-[#e55b17]">
                    Close
                </button>
            </div>
        </div>
    </div>

    <script>
        // Combined DOMContentLoaded event listener
        document.addEventListener('DOMContentLoaded', function() {

            // Configuration - you can easily change this number later
            const TRUNCATE_LENGTH = 10;

            // Sorting functionality
            let landlordSortDirection = {};

            window.sortLandlordTable = function(columnIndex, type) {
                const tbody = document.getElementById('landlordTableBody');
                if (!tbody) return;

                const rows = Array.from(tbody.querySelectorAll('tr'));
                if (rows.length === 0) return;

                const currentDirection = landlordSortDirection[columnIndex] || 'asc';
                const newDirection = currentDirection === 'asc' ? 'desc' : 'asc';
                landlordSortDirection[columnIndex] = newDirection;

                // Clear all sort indicators
                for (let i = 0; i <= 8; i++) {
                    const indicator = document.getElementById(`landlord-sort-indicator-${i}`);
                    if (indicator) {
                        // Store # (0), AWS (6), Total Rent (7) are numbers
                        if (i === 0 || i === 6 || i === 7) {
                            indicator.textContent = '↑';
                        } else {
                            indicator.textContent = 'A↓';
                        }
                        indicator.style.opacity = '0.5';
                    }
                }

                // Set active sort indicator
                const activeIndicator = document.getElementById(`landlord-sort-indicator-${columnIndex}`);
                if (activeIndicator) {
                    if (type === 'number') {
                        activeIndicator.textContent = newDirection === 'asc' ? '↑' : '↓';
                    } else {
                        activeIndicator.textContent = newDirection === 'asc' ? 'A↓' : 'Z↑';
                    }
                    activeIndicator.style.opacity = '1';
                }

                // Sort rows
                rows.sort((a, b) => {
                    let aValue, bValue;

                    if (type === 'number') {
                        // FIXED: Store numbers need integer parsing
                        if (columnIndex === 0) {
                            aValue = parseInt(a.cells[columnIndex].getAttribute('data-sort')) || 0;
                            bValue = parseInt(b.cells[columnIndex].getAttribute('data-sort')) || 0;
                        } else {
                            aValue = parseFloat(a.cells[columnIndex].getAttribute('data-sort')) || 0;
                            bValue = parseFloat(b.cells[columnIndex].getAttribute('data-sort')) || 0;
                        }
                    } else {
                        aValue = (a.cells[columnIndex].getAttribute('data-sort') || '').toLowerCase();
                        bValue = (b.cells[columnIndex].getAttribute('data-sort') || '').toLowerCase();

                        // Handle N/A values
                        if (aValue === 'n/a') aValue = 'zzzz';
                        if (bValue === 'n/a') bValue = 'zzzz';
                    }

                    if (newDirection === 'asc') {
                        return aValue > bValue ? 1 : (aValue < bValue ? -1 : 0);
                    } else {
                        return aValue < bValue ? 1 : (aValue > bValue ? -1 : 0);
                    }
                });

                // Re-append sorted rows with alternating colors
                rows.forEach((row, index) => {
                    row.className = (index % 2 === 0 ? 'bg-white' : 'bg-gray-50') + ' hover:bg-[#fff4ed]';
                    tbody.appendChild(row);
                });

                // IMPORTANT: Re-initialize truncation after sorting
                initTextTruncation();
            };

            // Text truncation functionality
            function initTextTruncation() {
                // Only target elements with the 'truncatable' class
                const elements = document.querySelectorAll('.truncatable');
                elements.forEach(element => {
                    processTruncation(element);
                });
            }

            // Process individual element for truncation
            function processTruncation(element) {
                const originalText = element.textContent.trim();

                // Skip if text is short enough or already processed
                if (originalText.length <= TRUNCATE_LENGTH || element.hasAttribute('data-truncated')) {
                    return;
                }

                // Mark as processed to avoid double-processing
                element.setAttribute('data-truncated', 'true');

                // Store original text
                element.setAttribute('data-original-text', originalText);

                // Create truncated version
                const truncatedText = originalText.substring(0, TRUNCATE_LENGTH).trim();

                // Create the truncated content with read more link
                const truncatedContent = document.createElement('span');
                truncatedContent.className = 'truncated-content';
                truncatedContent.innerHTML = `
                    <span class="truncated-text">${truncatedText}...</span>
                    <button class="read-more-btn" type="button" style="color: #ff671b; background: none; border: none; text-decoration: underline; cursor: pointer; font-size: inherit; padding: 0; margin-left: 5px;">
                        Read More
                    </button>
                `;

                // Create the full content (initially hidden)
                const fullContent = document.createElement('span');
                fullContent.className = 'full-content';
                fullContent.style.display = 'none';
                fullContent.innerHTML = `
                    <span class="full-text">${originalText}</span>
                    <button class="read-less-btn" type="button" style="color: #ff671b; background: none; border: none; text-decoration: underline; cursor: pointer; font-size: inherit; padding: 0; margin-left: 5px;">
                        Read Less
                    </button>
                `;

                // Clear original content and add new structure
                element.innerHTML = '';
                element.appendChild(truncatedContent);
                element.appendChild(fullContent);

                // Add event listeners for toggle functionality
                const readMoreBtn = truncatedContent.querySelector('.read-more-btn');
                const readLessBtn = fullContent.querySelector('.read-less-btn');

                readMoreBtn.addEventListener('click', function(e) {
                    e.preventDefault();
                    e.stopPropagation();
                    showFullText(element);
                });

                readLessBtn.addEventListener('click', function(e) {
                    e.preventDefault();
                    e.stopPropagation();
                    showTruncatedText(element);
                });
            }

            // Show full text
            function showFullText(element) {
                const truncatedContent = element.querySelector('.truncated-content');
                const fullContent = element.querySelector('.full-content');

                if (truncatedContent && fullContent) {
                    truncatedContent.style.display = 'none';
                    fullContent.style.display = 'inline';
                }
            }

            // Show truncated text
            function showTruncatedText(element) {
                const truncatedContent = element.querySelector('.truncated-content');
                const fullContent = element.querySelector('.full-content');

                if (truncatedContent && fullContent) {
                    truncatedContent.style.display = 'inline';
                    fullContent.style.display = 'none';
                }
            }

            // Initialize truncation on page load
            initTextTruncation();

            // Make functions available globally for modal reinitialization
            window.reinitTextTruncation = initTextTruncation;
        });

        function closeModal(modalId) {
            // Add your modal closing logic here
            window.close(); // or whatever method you use to close the modal
        }
    </script>

    <style>
        /* Read More/Less Button Styling */
        .read-more-btn,
        .read-less-btn {
            color: #ff671b !important;
            background: none !important;
            border: none !important;
            text-decoration: underline !important;
            cursor: pointer !important;
            font-size: inherit !important;
            font-weight: 500 !important;
            padding: 0 !important;
            margin-left: 5px !important;
            transition: color 0.2s ease !important;
        }

        .read-more-btn:hover,
        .read-less-btn:hover {
            color: #e55b17 !important;
            text-decoration: none !important;
        }

        /* Ensure buttons don't break layout */
        .truncated-content,
        .full-content {
            display: inline;
            word-wrap: break-word;
        }

        /* Optional: Add smooth transition */
        .truncated-content,
        .full-content {
            transition: opacity 0.3s ease;
        }
    </style>

@endsection
