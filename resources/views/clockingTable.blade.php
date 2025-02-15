@extends('layouts.app')

@section('content')


<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <!-- Header -->
    <div class="sm:flex sm:items-center">
        <div class="sm:flex-auto">
            <h1 class="text-2xl font-semibold text-gray-900">Clocking Records</h1>
            <p class="mt-2 text-sm text-gray-700">
                A list of all employee clocking records including their check-in and check-out times, mileage, date, total miles, total hours, and earnings.
            </p>
        </div>
        <!-- Export CSV Button -->
        <div class="mt-4 sm:mt-0 sm:ml-16 sm:flex-none">
            <form method="GET" action="{{ route('admin.clocking.export') }}" class="inline-block" id="export-form">
                <input type="hidden" name="start_date" value="{{ request('start_date') }}">
                <input type="hidden" name="end_date" value="{{ request('end_date') }}">
                <!-- Add other filters if needed -->
                <button type="submit" class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500">
                    <svg class="-ml-1 mr-2 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                    Export CSV
                </button>
            </form>
        </div>
    </div>

    <!-- Filter Section -->
    <div class="mt-8 bg-white shadow-sm ring-1 ring-gray-900/5 sm:rounded-lg p-6">
        <form method="GET" action="{{ route('admin.clocking') }}" id="filter-form" class="space-y-8 divide-y divide-gray-200">
            <div class="grid grid-cols-1 gap-y-6 sm:grid-cols-4 sm:gap-x-8">
                <!-- Start Date -->
                <div>
                    <label for="start_date" class="block text-sm font-medium text-gray-700">Start Date</label>
                    <div class="mt-1">
                        <input type="date" name="start_date" id="start_date" value="{{ request('start_date') }}"
                               class="block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm">
                    </div>
                </div>
                <!-- End Date -->
                <div>
                    <label for="end_date" class="block text-sm font-medium text-gray-700">End Date</label>
                    <div class="mt-1">
                        <input type="date" name="end_date" id="end_date" value="{{ request('end_date') }}"
                               class="block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm">
                    </div>
                </div>
                <!-- Add other filters (employee, times, etc.) if needed -->
            </div>
        </form>
    </div>

    <!-- Records Table -->
    @if(isset($clockings) && $clockings->count() > 0)
    <div class="mt-8 flex flex-col">
        <div class="-mx-4 -my-2 overflow-x-auto sm:-mx-6 lg:-mx-8">
            <div class="inline-block min-w-full py-2 align-middle">
                <div class="overflow-hidden shadow-sm ring-1 ring-black ring-opacity-5 rounded-lg">
                    <table class="min-w-full divide-y divide-gray-300">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="py-3.5 pl-4 pr-3 text-left text-sm font-semibold text-gray-900 sm:pl-6 lg:pl-8">Name</th>
                                <th class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">Date</th>
                                <th class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">Clock In</th>
                                <th class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">Clock Out</th>
                                <th class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">Miles In</th>
                                <th class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">Miles Out</th>
                                <th class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">Total Miles</th>
                                <th class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">Total Hours</th>
                                <th class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">Earnings</th>
                                <th class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">Images</th>
                                <th class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 bg-white">
                            @foreach($clockings as $clocking)
                                <tr>
                                    <!-- Name -->
                                    <td class="whitespace-nowrap py-4 pl-4 pr-3 text-sm font-medium text-gray-900 sm:pl-6 lg:pl-8">
                                        {{ $clocking->user->name ?? 'N/A' }}
                                    </td>
                                    <!-- Date (from clock_in) -->
                                    <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-500">
                                        @if($clocking->clock_in)
                                            {{ \Carbon\Carbon::parse($clocking->clock_in)->format('Y-m-d') }}
                                        @else
                                            -
                                        @endif
                                    </td>
                                    <!-- Clock In (only time) -->
                                    <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-500">
                                        @if($clocking->clock_in)
                                            {{ \Carbon\Carbon::parse($clocking->clock_in)->format('H:i') }}
                                        @else
                                            -
                                        @endif
                                    </td>
                                    <!-- Clock Out (only time) -->
                                    <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-500">
                                        @if($clocking->clock_out)
                                            {{ \Carbon\Carbon::parse($clocking->clock_out)->format('H:i') }}
                                        @else
                                            -
                                        @endif
                                    </td>
                                    <!-- Miles In -->
                                    <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-500">
                                        {{ !is_null($clocking->miles_in) ? $clocking->miles_in : '-' }}
                                    </td>
                                    <!-- Miles Out -->
                                    <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-500">
                                        {{ !is_null($clocking->miles_out) ? $clocking->miles_out : '-' }}
                                    </td>
                                    <!-- Total Miles -->
                                    <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-500">
                                        {{ $clocking->total_miles }}
                                    </td>
                                    <!-- Total Hours -->
                                    <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-500">
                                        {{ $clocking->total_hours }}
                                    </td>
                                    <!-- Earnings -->
                                    <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-500">
                                        @if(is_numeric($clocking->earnings))
                                            {{ '$' . number_format($clocking->earnings, 2) }}
                                        @else
                                            $0.00
                                        @endif
                                    </td>
                                    <!-- Images (displayed in one column vertically) -->
                                    <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-500">
                                        <div class="flex flex-row space-x-2">
                                            @if(!empty($clocking->image_in))
                                                <a href="{{ asset('storage/' . $clocking->image_in) }}" target="_blank" class="group transition-opacity duration-500 delay-200">
                                                    <img src="{{ asset('storage/' . $clocking->image_in) }}" 
                                                         alt="Clock In Image" 
                                                         class="h-10 w-10 rounded-lg object-cover ring-1 ring-gray-200 transition-all group-hover:ring-2 group-hover:ring-blue-500">
                                                    <span class="sr-only">View Clock In Image</span>
                                                </a>
                                            @endif
                                    
                                            @if(!empty($clocking->image_out))
                                                <a href="{{ asset('storage/' . $clocking->image_out) }}" target="_blank" class="group transition-opacity duration-500 delay-400">
                                                    <img src="{{ asset('storage/' . $clocking->image_out) }}" 
                                                         alt="Clock Out Image" 
                                                         class="h-10 w-10 rounded-lg object-cover ring-1 ring-gray-200 transition-all group-hover:ring-2 group-hover:ring-blue-500">
                                                    <span class="sr-only">View Clock Out Image</span>
                                                </a>
                                            @endif
                                        </div>
                                    </td>
                                    
                                    <!-- Actions (Edit) -->
                                    <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-500">
                                        <button type="button"
                                                class="edit-btn inline-flex items-center px-3 py-1 border border-transparent text-sm leading-4 font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
                                                data-clocking-id="{{ $clocking->id }}"
                                                data-clock-in="{{ $clocking->clock_in ? \Carbon\Carbon::parse($clocking->clock_in)->format('Y-m-d\TH:i') : '' }}"
                                                data-clock-out="{{ $clocking->clock_out ? \Carbon\Carbon::parse($clocking->clock_out)->format('Y-m-d\TH:i') : '' }}"
                                                data-miles-in="{{ $clocking->miles_in }}"
                                                data-miles-out="{{ $clocking->miles_out }}">
                                            Edit
                                        </button>
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
                                <!-- Summation of Earnings -->
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
            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2H9m0 0a2 2 0 012-2h2a2 2 0 012 2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
            </svg>
            <h3 class="mt-2 text-sm font-semibold text-gray-900">No records found</h3>
            <p class="mt-1 text-sm text-gray-500">No clocking records found for the selected date range.</p>
        </div>
    @endif
</div>

<!-- Modal Popup for Updating Clocking Record -->
<div id="editModal" class="fixed inset-0 flex items-center justify-center bg-gray-800 bg-opacity-50 hidden z-50">
    <div class="bg-white rounded-lg shadow-lg w-full max-w-md">
        <div class="px-4 py-3 border-b flex justify-between items-center">
            <h3 class="text-lg font-medium text-gray-900">Update Clocking Record</h3>
            <button type="button" id="closeModal" class="text-gray-400 hover:text-gray-500">
                <span class="sr-only">Close</span>
                <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>
        <form action="{{ route('admin.clocking.update') }}" method="POST" id="editForm">
            @csrf
            <input type="hidden" name="clocking_id" id="clocking_id">
            <div class="px-4 py-3">
                <div class="grid grid-cols-2 gap-4">
                    <div class="col-span-2 sm:col-span-1">
                        <label for="clock_in" class="block text-sm font-medium text-gray-700">Clock In</label>
                        <input type="datetime-local" name="clock_in" id="clock_in"
                               class="mt-1 block w-full rounded-md border-gray-300 shadow-sm
                                      focus:border-blue-500 focus:ring-blue-500 sm:text-sm">
                    </div>
                    <div class="col-span-2 sm:col-span-1">
                        <label for="clock_out" class="block text-sm font-medium text-gray-700">Clock Out</label>
                        <input type="datetime-local" name="clock_out" id="clock_out"
                               class="mt-1 block w-full rounded-md border-gray-300 shadow-sm
                                      focus:border-blue-500 focus:ring-blue-500 sm:text-sm">
                    </div>
                    <div class="col-span-2 sm:col-span-1">
                        <label for="miles_in" class="block text-sm font-medium text-gray-700">Miles In</label>
                        <input type="number" name="miles_in" id="miles_in"
                               class="mt-1 block w-full rounded-md border-gray-300 shadow-sm
                                      focus:border-blue-500 focus:ring-blue-500 sm:text-sm">
                    </div>
                    <div class="col-span-2 sm:col-span-1">
                        <label for="miles_out" class="block text-sm font-medium text-gray-700">Miles Out</label>
                        <input type="number" name="miles_out" id="miles_out"
                               class="mt-1 block w-full rounded-md border-gray-300 shadow-sm
                                      focus:border-blue-500 focus:ring-blue-500 sm:text-sm">
                    </div>
                </div>
            </div>
            <div class="px-4 py-3 border-t flex justify-end space-x-3">
                <button type="button" id="cancelEdit"
                        class="px-4 py-2 rounded-md border border-gray-300 text-gray-700
                               hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2
                               focus:ring-blue-500">
                    Cancel
                </button>
                <button type="submit"
                        class="px-4 py-2 rounded-md bg-blue-600 text-white hover:bg-blue-700
                               focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                    Save Changes
                </button>
            </div>
        </form>
    </div>
</div>

<script>
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

    editButtons.forEach(button => {
        button.addEventListener('click', function() {
            // Retrieve data from the button's data attributes
            const clockingId = this.getAttribute('data-clocking-id');
            const clockIn = this.getAttribute('data-clock-in');
            const clockOut = this.getAttribute('data-clock-out');
            const milesIn = this.getAttribute('data-miles-in');
            const milesOut = this.getAttribute('data-miles-out');

            // Populate the form fields
            document.getElementById('clocking_id').value = clockingId;
            document.getElementById('clock_in').value = clockIn;
            document.getElementById('clock_out').value = clockOut;
            document.getElementById('miles_in').value = milesIn;
            document.getElementById('miles_out').value = milesOut;

            // Show the modal
            modal.classList.remove('hidden');
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
