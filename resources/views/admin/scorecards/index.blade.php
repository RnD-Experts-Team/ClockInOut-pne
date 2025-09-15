@extends('layouts.app')

@section('title', 'Employee Scorecards')

@section('content')
    <div class="max-w-full mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <!-- Header Section -->
        <div class="sm:flex sm:items-center sm:justify-between mb-6">
            <div>
                <h1 class="text-3xl font-bold text-gray-900">Employee Scorecards</h1>
                <p class="mt-2 text-sm text-gray-600">Track employee hours, payments, and totals</p>
            </div>
            <div class="mt-4 sm:mt-0 flex space-x-3">
                <!-- Export Image Button -->
                <button type="button" id="openScorecardModal"
                        class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-lg text-white bg-orange-600 hover:bg-orange-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-orange-500 transition-all duration-300 hover:shadow-lg">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                    </svg>
                    Export as Image
                </button>
            </div>
        </div>

        <!-- Filters Section -->
        <div class="bg-white shadow-lg rounded-xl p-6 mb-8 border border-gray-200">
            <form method="GET" action="{{ route('admin.scorecards.index') }}" class="space-y-6" id="filterForm">
                <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
                    <!-- Date Range Filter -->
                    <div>
                        <label for="date_range" class="block text-sm font-semibold text-gray-800 mb-2">Date Range</label>
                        <select name="date_range" id="date_range" class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-orange-500 focus:ring-2 focus:ring-orange-500/20 sm:text-sm transition-all duration-200 hover:border-gray-400 py-3 px-4">
                            <option value="all" {{ request('date_range') === 'all' || !request('date_range') ? 'selected' : '' }}>All Time</option>
                            <option value="this_week" {{ request('date_range') === 'this_week' ? 'selected' : '' }}>This Week</option>
                            <option value="this_month" {{ request('date_range') === 'this_month' ? 'selected' : '' }}>This Month</option>
                            <option value="last_week" {{ request('date_range') === 'last_week' ? 'selected' : '' }}>Last Week</option>
                            <option value="last_month" {{ request('date_range') === 'last_month' ? 'selected' : '' }}>Last Month</option>
                            <option value="custom" {{ request('date_range') === 'custom' ? 'selected' : '' }}>Custom</option>
                        </select>
                    </div>

                    <!-- User Filter -->
                    <div>
                        <label for="user_id" class="block text-sm font-semibold text-gray-800 mb-2">Employee</label>
                        <select name="user_id" class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-orange-500 focus:ring-2 focus:ring-orange-500/20 sm:text-sm transition-all duration-200 hover:border-gray-400 py-3 px-4">
                            <option value="all">All Employees</option>
                            @foreach($allUsers as $user)
                                <option value="{{ $user->id }}" {{ request('user_id') == $user->id ? 'selected' : '' }}>
                                    {{ $user->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Search Field -->
                    <div>
                        <label for="search" class="block text-sm font-semibold text-gray-800 mb-2">Search</label>
                        <input type="text" name="search" id="search"
                               class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-orange-500 focus:ring-2 focus:ring-orange-500/20 sm:text-sm transition-all duration-200 hover:border-gray-400 py-3 px-4"
                               placeholder="Employee name or email..."
                               value="{{ request('search') }}">
                    </div>
                </div>

                <!-- Custom Date Fields -->
                <div id="customDateFields" style="display: {{ request('date_range') === 'custom' ? 'block' : 'none' }};" class="grid grid-cols-1 md:grid-cols-2 gap-6 mt-6">
                    <div>
                        <label for="start_date" class="block text-sm font-semibold text-gray-800 mb-2">Start Date</label>
                        <input type="date" name="start_date" id="start_date" class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-orange-500 focus:ring-2 focus:ring-orange-500/20 sm:text-sm transition-all duration-200 hover:border-gray-400 py-3 px-4" value="{{ request('start_date') }}">
                    </div>
                    <div>
                        <label for="end_date" class="block text-sm font-semibold text-gray-800 mb-2">End Date</label>
                        <input type="date" name="end_date" id="end_date" class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-orange-500 focus:ring-2 focus:ring-orange-500/20 sm:text-sm transition-all duration-200 hover:border-gray-400 py-3 px-4" value="{{ request('end_date') }}">
                    </div>
                </div>

                <!-- Filter Actions -->
                <div class="flex justify-end space-x-4">
                    <a href="{{ route('admin.scorecards.index') }}"
                       class="inline-flex items-center px-5 py-3 border border-gray-300 text-sm font-medium rounded-lg text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-orange-500 transition-all duration-200">
                        Reset
                    </a>
                    <button type="submit"
                            class="inline-flex items-center px-5 py-3 border border-transparent text-sm font-medium rounded-lg text-white bg-orange-600 hover:bg-orange-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-orange-500 transition-all duration-200">
                        Apply Filter
                    </button>
                </div>
            </form>
        </div>

        <!-- Scorecards Table -->
        <div class="bg-white shadow-xl rounded-xl overflow-hidden border border-gray-200">
            @if($scorecards->count() > 0)
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gradient-to-r from-gray-50 to-gray-100">
                        <tr>
                            <th class="px-6 py-4 text-left text-xs font-semibold text-gray-900 uppercase tracking-wider">Name</th>
                            <th class="px-6 py-4 text-center text-xs font-semibold text-gray-900 uppercase tracking-wider">Hourly Rate</th>
                            <th class="px-6 py-4 text-center text-xs font-semibold text-gray-900 uppercase tracking-wider">Total Hours</th>
                            <th class="px-6 py-4 text-center text-xs font-semibold text-gray-900 uppercase tracking-wider">Total Hourly Pay</th>
                            <th class="px-6 py-4 text-center text-xs font-semibold text-gray-900 uppercase tracking-wider">Payments Made to Company</th>
                            <th class="px-6 py-4 text-center text-xs font-semibold text-gray-900 uppercase tracking-wider">Fuel Cost</th>
                            <th class="px-6 py-4 text-center text-xs font-semibold text-gray-900 uppercase tracking-wider bg-orange-100">Total</th>
                        </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                        @foreach($scorecards as $index => $scorecard)
                            <tr class="{{ $index % 2 == 0 ? 'bg-white' : 'bg-gray-50' }} hover:bg-orange-50 transition-colors duration-150">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center">
                                        <div class="w-10 h-10 bg-orange-500 rounded-full flex items-center justify-center mr-4">
                                            <span class="text-sm font-medium text-white">
                                                {{ substr($scorecard->user->name, 0, 2) }}
                                            </span>
                                        </div>
                                        <div>
                                            <div class="text-sm font-medium text-gray-900">{{ $scorecard->user->name }}</div>
                                            <div class="text-sm text-gray-500">{{ $scorecard->user->email }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-center text-sm text-gray-900">
                                    ${{ number_format($scorecard->hourly_rate, 2) }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-center text-sm text-gray-900">
                                    {{ $scorecard->total_hours }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-center text-sm text-gray-900">
                                    ${{ number_format($scorecard->total_hourly_pay, 2) }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-center text-sm text-gray-900">
                                    ${{ number_format($scorecard->payments_to_company, 2) }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-center text-sm text-gray-900">
                                    ${{ number_format($scorecard->fuel_cost, 2) }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-center text-sm font-bold text-orange-600 bg-orange-50">
                                    ${{ number_format($scorecard->total, 2) }}
                                </td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="text-center py-16">
                    <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                    </svg>
                    <h3 class="mt-4 text-lg font-medium text-gray-900">No scorecard data found</h3>
                    <p class="mt-2 text-sm text-gray-500">No employee data matches your current filters.</p>
                </div>
            @endif
        </div>

        <!-- Modal for Scorecard Export -->
        <div id="scorecardModal" class="fixed inset-0 bg-opacity-50 hidden z-50" onclick="closeModal('scorecardModal')">
            <div class="flex items-center justify-center min-h-screen">
                <div class="bg-white rounded-lg shadow-lg p-6 w-full max-w-6xl max-h-[80vh] overflow-y-auto relative" onclick="event.stopPropagation()">
                    <div class="modal-content">
                        <div id="scorecardContent" class="p-4">
                            <p>Loading...</p>
                        </div>
                    </div>
                    <div class="mt-4 text-right">
                        <button onclick="generateScreenshot('scorecardModal', 'scorecard')"
                                class="inline-flex items-center px-4 py-2 bg-red-600 text-white text-sm font-medium rounded hover:bg-red-700 mr-2">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                            </svg>
                            Screenshot
                        </button>
                        <button onclick="closeModal('scorecardModal')"
                                class="inline-flex items-center px-4 py-2 bg-gray-600 text-white text-sm font-medium rounded hover:bg-gray-700">
                            Close
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Date range functionality
            const dateRangeSelect = document.getElementById('date_range');
            const customDateFields = document.getElementById('customDateFields');

            if (dateRangeSelect && customDateFields) {
                dateRangeSelect.addEventListener('change', function() {
                    customDateFields.style.display = this.value === 'custom' ? 'block' : 'none';
                });
            }

            // Modal functionality
            const openScorecardModal = document.getElementById('openScorecardModal');
            if (openScorecardModal) {
                openScorecardModal.addEventListener('click', function() {
                    openModalWithContent();
                });
            }

            function openModalWithContent() {
                const modal = document.getElementById('scorecardModal');
                if (modal) {
                    modal.classList.remove('hidden');
                    loadScorecardContent();
                    document.body.style.overflow = 'hidden';
                }
            }

            function loadScorecardContent() {
                const params = new URLSearchParams(window.location.search);

                fetch(`{{ route('admin.scorecards.export') }}?${params.toString()}`)
                    .then(response => response.text())
                    .then(html => {
                        document.getElementById('scorecardContent').innerHTML = html;
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        document.getElementById('scorecardContent').innerHTML = '<p>Error loading content</p>';
                    });
            }

            window.loadScorecardContent = loadScorecardContent;
        });

        // Modal close function
        function closeModal(modalId) {
            const modal = document.getElementById(modalId);
            if (modal) {
                modal.classList.add('hidden');
                document.body.style.overflow = 'auto';
            }
        }

        // Screenshot functionality
        function generateScreenshot(modalId, type) {
            showLanguageSelection(modalId, type);
        }

        // Language selection modal
        function showLanguageSelection(modalId, type) {
            const languageModal = document.createElement('div');
            languageModal.id = 'languageSelectionModal';
            languageModal.className = 'fixed inset-0 bg-opacity-50 z-[110] flex items-center justify-center';
            languageModal.innerHTML = `
            <div class="bg-white rounded-lg shadow-xl p-6 max-w-md w-full mx-4">
                <div class="text-center mb-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-2">Select Language for Screenshot</h3>
                    <p class="text-sm text-gray-600">Choose the language for your screenshot export</p>
                </div>

                <div class="space-y-3">
                    <button onclick="proceedWithScreenshot('${modalId}', '${type}', 'en')"
                            class="w-full flex items-center justify-center px-4 py-3 border border-gray-300 rounded-lg text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-orange-500 focus:border-orange-500 transition-colors">
                        <span class="text-2xl mr-3">🇺🇸</span>
                        <span class="font-medium">English</span>
                    </button>

                    <button onclick="proceedWithScreenshot('${modalId}', '${type}', 'ar')"
                            class="w-full flex items-center justify-center px-4 py-3 border border-gray-300 rounded-lg text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-orange-500 focus:border-orange-500 transition-colors">
                        <span class="text-2xl mr-3">🇸🇦</span>
                        <span class="font-medium">العربية (Arabic)</span>
                    </button>
                </div>

                <div class="mt-6 text-center">
                    <button onclick="hideLanguageSelection()"
                            class="px-4 py-2 text-sm text-gray-500 hover:text-gray-700 focus:outline-none">
                        Cancel
                    </button>
                </div>
            </div>
        `;

            document.body.appendChild(languageModal);
        }

        // Hide language selection modal
        function hideLanguageSelection() {
            const languageModal = document.getElementById('languageSelectionModal');
            if (languageModal) {
                languageModal.remove();
            }
        }

        // Main screenshot processing function
        function proceedWithScreenshot(modalId, type, language) {
            hideLanguageSelection();

            // Apply language-specific styling before screenshot
            applyLanguageStyles(language);

            showScreenshotLoading(modalId);

            // Load html2canvas and proceed with screenshot
            loadHtml2Canvas().then(html2canvas => {
                const modal = document.getElementById(modalId);
                const contentElement = modal.querySelector('.modal-content');

                const options = {
                    backgroundColor: '#ffffff',
                    scale: 2,
                    useCORS: true,
                    allowTaint: true,
                    scrollX: 0,
                    scrollY: 0,
                    width: contentElement.scrollWidth,
                    height: contentElement.scrollHeight,
                    logging: false
                };

                html2canvas(contentElement, options).then(canvas => {
                    canvas.toBlob(blob => {
                        const url = URL.createObjectURL(blob);
                        const now = new Date();
                        const dateStr = now.toISOString().split('T')[0];
                        const timeStr = now.toTimeString().split(' ')[0].replace(/:/g, '-');
                        const languageSuffix = language === 'ar' ? '-arabic' : '-english';
                        const filename = `${type}${languageSuffix}-${dateStr}-${timeStr}.png`;

                        hideScreenshotLoading(modalId);

                        // Auto download
                        const link = document.createElement('a');
                        link.href = url;
                        link.download = filename;
                        document.body.appendChild(link);
                        link.click();
                        document.body.removeChild(link);

                        // Show success message
                        showSuccessNotification('Screenshot downloaded successfully!');

                        // Reset language styles after screenshot
                        resetLanguageStyles();

                        setTimeout(() => {
                            URL.revokeObjectURL(url);
                        }, 1000);
                    }, 'image/png', 0.95);
                }).catch(error => {
                    console.error('html2canvas error:', error);
                    hideScreenshotLoading(modalId);
                    showErrorNotification('Failed to capture screenshot: ' + error.message);
                    resetLanguageStyles();
                });
            }).catch(error => {
                console.error('Failed to load html2canvas:', error);
                hideScreenshotLoading(modalId);
                showErrorNotification('Failed to load screenshot library');
            });
        }

        // Apply Arabic language styles
        function applyLanguageStyles(language) {
            const contentElement = document.querySelector('#scorecardContent');
            if (!contentElement) return;

            if (language === 'ar') {
                // Apply Arabic styling
                contentElement.style.direction = 'rtl';
                contentElement.style.textAlign = 'right';

                // Update header text to Arabic
                const header = contentElement.querySelector('h1');
                if (header) {
                    header.setAttribute('data-original-text', header.textContent);
                    header.textContent = 'بطاقة النتائج الأسبوع 9';
                }

                // Arabic translations for scorecard terms
                const elementsToTranslate = {
                    'Week 9 Score Card': 'بطاقة النتائج الأسبوع 9',
                    'Employee Scorecards': 'بطاقات نتائج الموظفين',
                    'Name': 'الاسم',
                    'Hourly Rate': 'الأجر بالساعة',
                    'Total Hours': 'إجمالي الساعات',
                    'Total Hourly Pay': 'إجمالي الأجر بالساعة',
                    'Payments Made to the Company': 'المدفوعات للشركة',
                    'Fuel Cost': 'تكلفة الوقود',
                    'Additional Payment': 'دفعة إضافية',
                    'Total': 'الإجمالي',
                    'TOTAL': 'المجموع',
                    'Generated on': 'تم الإنشاء في',
                    'No scorecard data available': 'لا توجد بيانات بطاقة نتائج متاحة',
                    'No Scorecard Data Found': 'لم يتم العثور على بيانات بطاقة النتائج',
                    'for the selected period': 'للفترة المحددة'
                };

                // Translate table headers and common elements
                Object.keys(elementsToTranslate).forEach(englishText => {
                    const elements = contentElement.querySelectorAll('th, td, p, span, h1, h2, h3');
                    elements.forEach(el => {
                        if (el.textContent && el.textContent.trim() === englishText) {
                            el.setAttribute('data-original-text', el.textContent);
                            el.textContent = elementsToTranslate[englishText];
                        } else if (el.textContent && el.textContent.includes(englishText)) {
                            el.setAttribute('data-original-text', el.textContent);
                            el.textContent = el.textContent.replace(englishText, elementsToTranslate[englishText]);
                        }
                    });
                });

                // Update date format to Arabic
                const dateElements = contentElement.querySelectorAll('p');
                dateElements.forEach(el => {
                    if (el.textContent && el.textContent.includes('Generated on')) {
                        el.setAttribute('data-original-text', el.textContent);
                        const now = new Date();
                        const arabicDate = now.toLocaleDateString('ar-SA', {
                            year: 'numeric',
                            month: 'long',
                            day: 'numeric',
                            hour: '2-digit',
                            minute: '2-digit'
                        });
                        el.textContent = `تم الإنشاء في ${arabicDate}`;
                    }
                });

                // Style tables for RTL
                const tables = contentElement.querySelectorAll('table');
                tables.forEach(table => {
                    table.style.direction = 'rtl';
                });

            } else {
                // English is default, reset any previous Arabic styling
                resetLanguageStyles();
            }
        }

        // Reset language styles back to English
        function resetLanguageStyles() {
            const contentElement = document.querySelector('#scorecardContent');
            if (!contentElement) return;

            // Reset direction and text alignment
            contentElement.style.direction = '';
            contentElement.style.textAlign = '';

            // Reset table direction
            const tables = contentElement.querySelectorAll('table');
            tables.forEach(table => {
                table.style.direction = '';
            });

            // Restore original text content
            contentElement.querySelectorAll('[data-original-text]').forEach(element => {
                const originalText = element.getAttribute('data-original-text');
                if (originalText) {
                    element.textContent = originalText;
                    element.removeAttribute('data-original-text');
                }
            });
        }

        // Show loading state during screenshot generation
        function showScreenshotLoading(modalId) {
            const modal = document.getElementById(modalId);
            const screenshotButton = modal.querySelector('button[onclick*="generateScreenshot"]');

            if (screenshotButton) {
                screenshotButton.innerHTML = `
                <svg class="animate-spin w-4 h-4 mr-2" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                Generating...
            `;
                screenshotButton.disabled = true;
            }
        }

        // Hide loading state after screenshot completion
        function hideScreenshotLoading(modalId) {
            const modal = document.getElementById(modalId);
            const screenshotButton = modal.querySelector('button[onclick*="generateScreenshot"]');

            if (screenshotButton) {
                screenshotButton.innerHTML = `
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                </svg>
                Screenshot
            `;
                screenshotButton.disabled = false;
            }
        }

        // Load html2canvas library dynamically
        function loadHtml2Canvas() {
            return new Promise((resolve, reject) => {
                if (window.html2canvas) {
                    resolve(window.html2canvas);
                    return;
                }
                const script = document.createElement('script');
                // Use the correct html2canvas-pro CDN URL
                script.src = 'https://cdn.jsdelivr.net/npm/html2canvas-pro@latest/dist/html2canvas-pro.min.js';
                script.onload = () => resolve(window.html2canvas);
                script.onerror = reject;
                document.head.appendChild(script);
            });
        }

        // Show success notification
        function showSuccessNotification(message) {
            const notification = document.createElement('div');
            notification.className = 'fixed top-4 right-4 bg-green-600 text-white px-6 py-4 rounded-lg shadow-xl z-[100] max-w-sm transform translate-x-0 transition-transform duration-300';
            notification.innerHTML = `
            <div class="flex items-center">
                <svg class="w-6 h-6 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                </svg>
                <div>
                    <p class="font-semibold">Success!</p>
                    <p class="text-sm opacity-90">${message}</p>
                </div>
            </div>
        `;

            document.body.appendChild(notification);

            setTimeout(() => {
                notification.style.transform = 'translateX(100%)';
                setTimeout(() => {
                    if (document.body.contains(notification)) {
                        document.body.removeChild(notification);
                    }
                }, 300);
            }, 3000);
        }

        // Show error notification
        function showErrorNotification(message) {
            const notification = document.createElement('div');
            notification.className = 'fixed top-4 right-4 bg-red-600 text-white px-6 py-4 rounded-lg shadow-xl z-[100] max-w-sm transform translate-x-0 transition-transform duration-300';
            notification.innerHTML = `
            <div class="flex items-center">
                <svg class="w-6 h-6 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
                <div>
                    <p class="font-semibold">Error!</p>
                    <p class="text-sm opacity-90">${message}</p>
                </div>
            </div>
        `;

            document.body.appendChild(notification);

            setTimeout(() => {
                notification.style.transform = 'translateX(100%)';
                setTimeout(() => {
                    if (document.body.contains(notification)) {
                        document.body.removeChild(notification);
                    }
                }, 300);
            }, 5000);
        }

        // Close modal when clicking outside
        window.onclick = function(event) {
            const modal = document.getElementById('scorecardModal');
            if (event.target === modal) {
                closeModal('scorecardModal');
            }
        }
    </script>
@endsection
