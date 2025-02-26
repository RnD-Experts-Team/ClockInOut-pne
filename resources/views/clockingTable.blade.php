@extends('layouts.app')

@section('content')


    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12 bg-gray-50 min-h-screen">
        <!-- Header with improved spacing and background -->
        <div class="bg-white rounded-lg shadow-sm p-6 mb-8">
            <div class="sm:flex sm:items-center sm:justify-between">
                <div class="sm:flex-auto">
                    <h1 class="text-3xl font-bold text-gray-900">Clocking Records</h1>
                    <p class="mt-3 text-base text-gray-600">
                        A list of all employee clocking records including their check-in and check-out times, mileage, date,
                        total miles, total hours, and earnings.
                    </p>
                </div>
                <!-- Export CSV Button with improved styling -->
                <div class="mt-4 sm:mt-0 sm:ml-16 sm:flex-none">
                    <form method="GET" action="{{ route('admin.clocking.export') }}" class="inline-block" id="export-form">
                        <input type="hidden" name="start_date" value="{{ request('start_date') }}">
                        <input type="hidden" name="end_date" value="{{ request('end_date') }}">
                        <input type="hidden" name="user_id" value="{{ request('user_id') }}">
                        <button type="submit"
                            class="group relative inline-flex items-center px-6 py-3 border border-transparent text-sm font-medium rounded-xl text-white bg-gradient-to-br from-emerald-400 via-emerald-500 to-teal-600 hover:from-emerald-500 hover:via-emerald-600 hover:to-teal-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-emerald-500 transition-all duration-300 ease-out transform hover:scale-[1.02] active:scale-[0.98] shadow-lg hover:shadow-emerald-500/25 active:shadow-inner">
                            <span class="absolute inset-y-0 left-0 flex items-center pl-3">
                                <svg class="h-5 w-5 text-emerald-50 group-hover:text-white transition-colors duration-200 transform group-hover:scale-110" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                </svg>
                            </span>
                            <span class="pl-8">Export Records</span>
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <!-- Filter Section -->
        <div class="mt-8 bg-white shadow-sm ring-1 ring-gray-900/5 sm:rounded-lg p-6">
            <div class="flex justify-between items-center">
                <div>
                    <h2 class="text-lg font-medium text-gray-900">Current Gas Payment Rate</h2>
                    <p class="text-sm text-gray-500">${{ number_format($gasPaymentRate, 2) }} per mile</p>
                </div>
                <form action="{{ route('admin.clocking.updateGasRate') }}" method="POST"
                    class="flex items-center space-x-4">
                    @csrf
                    <div>
                        <input type="number" step="0.01" name="gas_payment_rate"
                            class="block w-32 rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm"
                            value="{{ $gasPaymentRate }}" required>
                    </div>
                    <button type="submit"
                        class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700">
                        Update Rate
                    </button>
                </form>
            </div>
        </div>

        <!-- Update the filter section to include user filter -->
        <!-- Filter Section with Enhanced Styling -->
        <div class="mt-8 bg-white shadow-lg rounded-xl p-6 transition-all duration-300 hover:shadow-xl">
            <form method="GET" action="{{ route('admin.clocking') }}" id="filter-form" class="space-y-6">
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4 md:gap-6">
                    <!-- Start Date -->
                    <div class="space-y-2">
                        <label for="start_date" class="block text-sm font-medium text-gray-700">Start Date</label>
                        <div class="relative">
                            <input type="date" name="start_date" id="start_date" value="{{ request('start_date') }}"
                                class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-500/50 sm:text-sm transition-all duration-200 hover:border-blue-400">
                        </div>
                    </div>
                    <!-- End Date -->
                    <div class="space-y-2">
                        <label for="end_date" class="block text-sm font-medium text-gray-700">End Date</label>
                        <div class="relative">
                            <input type="date" name="end_date" id="end_date" value="{{ request('end_date') }}"
                                class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-500/50 sm:text-sm transition-all duration-200 hover:border-blue-400">
                        </div>
                    </div>
                    <!-- User Filter -->
                    <div class="space-y-2">
                        <label for="user_id" class="block text-sm font-medium text-gray-700">Select Employee</label>
                        <div class="relative">
                            <select name="user_id" id="user_id"
                                class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-500/50 sm:text-sm transition-all duration-200 hover:border-blue-400">
                                <option value="">All Employees</option>
                                @foreach ($users as $user)
                                    <option value="{{ $user->id }}"
                                        {{ request('user_id') == $user->id ? 'selected' : '' }}>
                                        {{ $user->name }}
                                    </option>
                                @endforeach
                            </select>

                        </div>
                    </div>
                </div>
                <!-- Filter Button -->
                <div class="flex justify-end mt-6 space-x-4">
                    <a href="{{ route('admin.clocking') }}"
                        class="inline-flex items-center px-4 py-2.5 border border-gray-300 text-sm font-medium rounded-lg text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500 active:bg-gray-100 transition-all duration-200 transform hover:scale-[1.02]">
                        <svg class="-ml-1 mr-2 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                        </svg>
                        Reset Filters
                    </a>
                    <button type="submit"
                        class="inline-flex items-center px-4 py-2.5 border border-transparent text-sm font-medium rounded-lg text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 active:bg-blue-800 transition-all duration-200 transform hover:scale-[1.02]">
                        <svg class="-ml-1 mr-2 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z" />
                        </svg>
                        Apply Filters
                    </button>
                </div>
            </form>
        </div>

        <!-- Records Table -->
        @if (isset($clockings) && $clockings->count() > 0)
            <div class="mt-8 flex flex-col">
                <div class="-mx-4 -my-2 overflow-x-auto sm:-mx-6 lg:-mx-8">
                    <div class="inline-block min-w-full py-2 align-middle">
                        <div class="overflow-hidden shadow-sm ring-1 ring-black ring-opacity-5 rounded-lg">
                            <table class="min-w-full divide-y divide-gray-300">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="py-3.5 pl-4 pr-3 text-left text-sm font-semibold text-gray-900">Name</th>
                                        <th class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">Date</th>
                                        <th class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">Clock In</th>
                                        <th class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">Clock Out
                                        </th>
                                        <th class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">Miles In</th>
                                        <th class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">Miles Out
                                        </th>
                                        <th class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">Total Miles
                                        </th>
                                        <th class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">Gas Payment
                                        </th>
                                        <th class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">Purchase Cost
                                        </th>
                                        <th class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">Total Hours
                                        </th>
                                        <th class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">Total Salary
                                        </th>
                                        <th class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">Images</th>
                                        <th class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">Actions</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-200 bg-white">
                                    @foreach ($clockings as $clocking)
                                        <tr>
                                            <td class="whitespace-nowrap py-4 pl-4 pr-3 text-sm font-medium text-gray-900">
                                                {{ $clocking->user->name ?? 'N/A' }}
                                            </td>
                                            <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-500">
                                                {{ $clocking->clock_in ? Carbon\Carbon::parse($clocking->clock_in)->format('M d, Y') : '-' }}
                                            </td>
                                            <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-500">
                                                {{ $clocking->clock_in ? Carbon\Carbon::parse($clocking->clock_in)->format('g:i A') : '-' }}
                                            </td>
                                            <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-500">
                                                {{ $clocking->clock_out ? Carbon\Carbon::parse($clocking->clock_out)->format('g:i A') : '-' }}
                                            </td>
                                            <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-500">
                                                {{ $clocking->miles_in ?? '-' }}
                                            </td>
                                            <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-500">
                                                {{ $clocking->miles_out ?? '-' }}
                                            </td>
                                            <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-500">
                                                {{ $clocking->total_miles ?? '-' }}
                                            </td>
                                            <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-500">
                                                ${{ number_format($clocking->gas_payment ?? 0, 2) }}
                                            </td>
                                            <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-500">
                                                ${{ number_format($clocking->purchase_cost ?? 0, 2) }}
                                            </td>
                                            <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-500">
                                                {{ $clocking->total_hours ?? '-' }}
                                            </td>
                                            <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-500">
                                                ${{ number_format($clocking->total_salary ?? 0, 2) }}
                                            </td>
                                            <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-500">
                                                <div class="flex flex-row space-x-2">
                                                    @if ($clocking->image_in)
                                                        <a href="{{ asset('storage/' . $clocking->image_in) }}"
                                                            target="_blank" class="group">
                                                            <img src="{{ asset('storage/' . $clocking->image_in) }}"
                                                                alt="Clock In"
                                                                class="h-10 w-10 rounded-lg object-cover ring-1 ring-gray-200 hover:ring-blue-500">
                                                        </a>
                                                    @endif
                                                    @if ($clocking->image_out)
                                                        <a href="{{ asset('storage/' . $clocking->image_out) }}"
                                                            target="_blank" class="group">
                                                            <img src="{{ asset('storage/' . $clocking->image_out) }}"
                                                                alt="Clock Out"
                                                                class="h-10 w-10 rounded-lg object-cover ring-1 ring-gray-200 hover:ring-blue-500">
                                                        </a>
                                                    @endif
                                                    @if ($clocking->purchase_receipt)
                                                        <a href="{{ asset('storage/' . $clocking->purchase_receipt) }}"
                                                            target="_blank" class="group">
                                                            <img src="{{ asset('storage/' . $clocking->purchase_receipt) }}"
                                                                alt="Receipt"
                                                                class="h-10 w-10 rounded-lg object-cover ring-1 ring-gray-200 hover:ring-blue-500">
                                                        </a>
                                                    @endif
                                                </div>
                                            </td>
                                            <!-- Replace the existing Actions cell with this -->
                                            <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-500">
                                                <div class="flex space-x-2">
                                                    <!-- Replace the existing edit button with this -->
                                                    <button type="button"
                                                        class="edit-btn inline-flex items-center px-3 py-1 border border-transparent text-sm leading-4 font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
                                                        data-clocking-id="{{ $clocking->id }}"
                                                        data-clock-in="{{ $clocking->clock_in ? Carbon\Carbon::parse($clocking->clock_in)->format('Y-m-d\TH:i') : '' }}"
                                                        data-clock-out="{{ $clocking->clock_out ? Carbon\Carbon::parse($clocking->clock_out)->format('Y-m-d\TH:i') : '' }}"
                                                        data-miles-in="{{ $clocking->miles_in ?? '' }}"
                                                        data-miles-out="{{ $clocking->miles_out ?? '' }}"
                                                        data-purchase-cost="{{ $clocking->purchase_cost ?? '' }}">
                                                        Edit
                                                    </button>
                                                        <button type="button"
                                                            class="delete-btn inline-flex items-center px-3 py-1 border border-transparent text-sm leading-4 font-medium rounded-md text-white bg-red-600 hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500"
                                                            data-clocking-id="{{ $clocking->id }}">
                                                            Delete
                                                        </button>
                                                    </div>
                                                </td>
                                            </tr>
                                    @endforeach
                                </tbody>

                                <!-- Totals Row -->
                                <tfoot class="bg-gray-50">
                                    <tr>
                                        <!-- Adjust colspan to align "Totals:" properly -->
                                        <th colspan="4" class="py-3.5 text-right text-sm font-semibold text-gray-900">
                                            Totals:
                                        </th>
                                        <!-- Summation of Miles In -->
                                        <th class="px-3 py-3.5 text-sm text-gray-500">
                                            {{ $totalMilesIn }}
                                        </th>
                                        <!-- Summation of Miles Out -->
                                        <th class="px-3 py-3.5 text-sm text-gray-500">
                                            {{ $totalMilesOut }}
                                        </th>
                                        <!-- Summation of Total Miles -->
                                        <th class="px-3 py-3.5 text-sm text-gray-500">
                                            {{ $totalMiles }}
                                        </th>
                                        <!-- Summation of Total Hours -->
                                        <th class="px-3 py-3.5 text-sm text-gray-500">
                                            {{ $totalHoursFormatted }}
                                        </th>
                                        <!-- Summation of Gas Payment -->
                                        <th class="px-3 py-3.5 text-sm text-gray-500">
                                            {{ '$' . number_format($totalGasPayment, 2) }}
                                        </th>
                                        <!-- Summation of Purchase Cost -->
                                        <th class="px-3 py-3.5 text-sm text-gray-500">
                                            {{ '$' . number_format($totalPurchaseCost, 2) }}
                                        </th>
                                        <!-- Summation of Total Earnings -->
                                        <th class="px-3 py-3.5 text-sm text-gray-500">
                                            {{ '$' . number_format($totalEarnings, 2) }}
                                        </th>
                                        <th></th>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>
                </div>
                <!-- Pagination -->
                <div class="mt-8">
                    {{ $clockings->links('vendor.pagination.custom') }}
                </div>
            @else
                <div class="mt-8 text-center">
                    <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor"
                        viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 012-2H9m0 0a2 2 0 012-2h2a2 2 0 012 2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                    </svg>
                    <h3 class="mt-2 text-sm font-semibold text-gray-900">No records found</h3>
                    <p class="mt-1 text-sm text-gray-500">No clocking records found for the selected date range.</p>
                </div>
        @endif
    </div>

    <!-- Delete Confirmation Modal -->
    <div id="deleteModal" class="fixed inset-0 flex items-center justify-center bg-gray-800 bg-opacity-50 hidden z-50">
        <div class="bg-white rounded-lg shadow-lg w-full max-w-md">
            <div class="px-4 py-3 border-b">
                <h3 class="text-lg font-medium text-gray-900">Confirm Delete</h3>
            </div>
            <div class="px-4 py-3">
                <p class="text-sm text-gray-500">Are you sure you want to delete this record? This action cannot be undone.
                </p>
            </div>
            <form id="deleteForm" method="POST" class="px-4 py-3 border-t flex justify-end space-x-3">
                @csrf
                @method('DELETE')
                <button type="button" id="cancelDelete"
                    class="px-4 py-2 rounded-md border border-gray-300 text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                    Cancel
                </button>
                <button type="submit"
                    class="px-4 py-2 rounded-md bg-red-600 text-white hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500">
                    Delete
                </button>
            </form>
        </div>
    </div>

    <!-- Modal Popup for Updating Clocking Record -->
    <div id="editModal" class="fixed inset-0 flex items-center justify-center bg-gray-800 bg-opacity-50 hidden z-50">
        <div class="bg-white rounded-xl shadow-xl w-full max-w-2xl transform transition-all">
            <div class="px-6 py-4 border-b border-gray-200 flex justify-between items-center">
                <h3 class="text-xl font-semibold text-gray-900">Update Clocking Record</h3>
                <button type="button" id="closeModal" class="text-gray-400 hover:text-gray-500 transition-colors">
                    <span class="sr-only">Close</span>
                    <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
            <form action="{{ route('admin.clocking.update') }}" method="POST" id="editForm">
                @csrf
                <input type="hidden" name="clocking_id" id="clocking_id">
                <div class="px-6 py-4">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- Clock In -->
                        <div>
                            <label for="clock_in" class="block text-sm font-medium text-gray-700 mb-1">Clock In
                                Time</label>
                            <input type="datetime-local" name="clock_in" id="clock_in"
                                class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm transition-all duration-200">
                        </div>
                        <!-- Clock Out -->
                        <div>
                            <label for="clock_out" class="block text-sm font-medium text-gray-700 mb-1">Clock Out
                                Time</label>
                            <input type="datetime-local" name="clock_out" id="clock_out"
                                class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm transition-all duration-200">
                        </div>
                        <!-- Miles In -->
                        <div>
                            <label for="miles_in" class="block text-sm font-medium text-gray-700 mb-1">Miles In</label>
                            <input type="number" name="miles_in" id="miles_in"
                                class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm transition-all duration-200">
                        </div>
                        <!-- Miles Out -->
                        <div>
                            <label for="miles_out" class="block text-sm font-medium text-gray-700 mb-1">Miles Out</label>
                            <input type="number" name="miles_out" id="miles_out"
                                class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm transition-all duration-200">
                        </div>
                        <!-- Purchase Cost -->
                        <div class="md:col-span-2">
                            <label for="purchase_cost" class="block text-sm font-medium text-gray-700 mb-1">Purchase Cost
                                ($)</label>
                            <input type="number" name="purchase_cost" id="purchase_cost" step="0.01" min="0"
                                class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm transition-all duration-200">
                        </div>
                    </div>
                </div>
                <div class="px-6 py-4 bg-gray-50 rounded-b-xl border-t border-gray-200 flex justify-end space-x-3">
                    <button type="button" id="cancelEdit"
                        class="px-4 py-2 rounded-lg border border-gray-300 text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-all duration-200">
                        Cancel
                    </button>
                    <button type="submit"
                        class="px-4 py-2 rounded-lg bg-blue-600 text-white hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-all duration-200">
                        Save Changes
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        // Add this to the existing script section
        const deleteButtons = document.querySelectorAll('.delete-btn');
        const deleteModal = document.getElementById('deleteModal');
        const cancelDelete = document.getElementById('cancelDelete');
        const deleteForm = document.getElementById('deleteForm');

        deleteButtons.forEach(button => {
            button.addEventListener('click', function() {
                const clockingId = this.getAttribute('data-clocking-id');
                deleteForm.action = `/admin/clocking/${clockingId}`;
                deleteModal.classList.remove('hidden');
            });
        });

        // Hide delete modal when cancel button is clicked
        cancelDelete.addEventListener('click', hideDeleteModal);

        function hideDeleteModal() {
            deleteModal.classList.add('hidden');
        }

        // Hide delete modal when clicking outside
        window.addEventListener('click', function(e) {
            if (e.target === deleteModal) {
                hideDeleteModal();
            }
        });
        // Auto-submit filter form on date changes
        document.getElementById('start_date').addEventListener('change', submitForm);
        document.getElementById('end_date').addEventListener('change', submitForm);

        function submitForm() {
            document.getElementById('filter-form').submit();
        }

        // Modal handling
        const editButtons = document.querySelectorAll('.edit-btn');
        const modal = document.getElementById('editModal');
        const closeModal = document.getElementById('closeModal');
        const cancelEdit = document.getElementById('cancelEdit');

        // Update the edit button click handler
       // Add this to your existing script section
document.querySelectorAll('.edit-btn').forEach(button => {
    button.addEventListener('click', function() {
        const clockingId = this.getAttribute('data-clocking-id');
        const clockIn = this.getAttribute('data-clock-in');
        const clockOut = this.getAttribute('data-clock-out');
        const milesIn = this.getAttribute('data-miles-in');
        const milesOut = this.getAttribute('data-miles-out');
        const purchaseCost = this.getAttribute('data-purchase-cost');

        // Clear previous values
        document.getElementById('editForm').reset();

        // Set new values
        document.getElementById('clocking_id').value = clockingId;
        document.getElementById('clock_in').value = clockIn || '';
        document.getElementById('clock_out').value = clockOut || '';
        document.getElementById('miles_in').value = milesIn || '';
        document.getElementById('miles_out').value = milesOut || '';
        document.getElementById('purchase_cost').value = purchaseCost || '';

        document.getElementById('editModal').classList.remove('hidden');
    });
});

        // Hide modal when close button or cancel button is clicked
        closeModal.addEventListener('click', hideModal);
        cancelEdit.addEventListener('click', hideModal);

        function hideModal() {
            modal.classList.add('hidden');
        }

        // Optionally hide modal when clicking outside the modal content
        window.addEventListener('click', function(e) {
            if (e.target === modal) {
                hideModal();
            }
        });
    </script>
@endsection
