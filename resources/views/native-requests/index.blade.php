@extends('layouts.app')

@section('title', 'My Maintenance Requests')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    {{-- Enhanced Success Banner --}}
    @if(session('success') && session('ticket_id'))
        <div class="bg-gradient-to-r from-green-50 to-green-100 border-l-4 border-green-500 rounded-xl p-8 mb-8 shadow-lg">
            <div class="flex items-center justify-between">
                <div class="flex items-center flex-1">
                    <svg class="w-16 h-16 text-green-500 mr-6 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    <div>
                        <h3 class="text-3xl font-bold text-green-900 mb-2">✓ Ticket Submitted Successfully!</h3>
                        <p class="text-xl text-green-700">Your request has been created as Ticket <span class="font-mono font-bold text-green-900">#{{ session('ticket_id') }}</span></p>
                    </div>
                </div>
                <a href="{{ route('native.requests.index') }}" 
                   class="px-8 py-4 bg-green-600 text-white text-lg font-semibold rounded-lg hover:bg-green-700 transition-colors shadow-md ml-6">
                    View All My Tickets
                </a>
            </div>
        </div>
    @elseif(session('success'))
        <div class="bg-green-50 border-l-4 border-green-500 p-4 mb-6 rounded-lg">
            <p class="text-green-800">{{ session('success') }}</p>
        </div>
    @endif

    {{-- Header --}}
    <div class="sm:flex sm:items-center sm:justify-between mb-6">
        <div>
            <h1 class="text-3xl font-bold text-gray-900">My Maintenance Requests</h1>
            <p class="mt-2 text-sm text-gray-600">View and track your submitted maintenance requests</p>
        </div>
        <div class="mt-4 sm:mt-0">
            <a href="{{ route('native.requests.create') }}"
                class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-lg text-white bg-gradient-to-r from-orange-500 to-orange-600 hover:from-orange-600 hover:to-orange-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-orange-500 transition-all duration-300 hover:shadow-lg">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                </svg>
                New Request
            </a>
        </div>
    </div>

    {{-- Filters Section --}}
    <div class="bg-white shadow-lg rounded-xl p-6 mb-6 border border-gray-200">
        <form method="GET" action="{{ route('native.requests.index') }}" class="space-y-4">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                {{-- Store Filter --}}
                <div>
                    <label for="store_filter" class="block text-sm font-medium text-gray-700 mb-1">Store</label>
                    <select name="store_filter" id="store_filter" 
                        class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-orange-500 focus:ring-orange-500 sm:text-sm">
                        <option value="all" {{ request('store_filter') === 'all' || !request('store_filter') ? 'selected' : '' }}>
                            All My Stores
                        </option>
                        @foreach($availableStores as $store)
                            <option value="{{ $store->id }}" {{ request('store_filter') == $store->id ? 'selected' : '' }}>
                                {{ $store->store_number }} - {{ $store->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                {{-- Status Filter --}}
                <div>
                    <label for="status" class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                    <select name="status" id="status" 
                        class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-orange-500 focus:ring-orange-500 sm:text-sm">
                        <option value="all" {{ request('status') === 'all' || !request('status') ? 'selected' : '' }}>All Statuses</option>
                        <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>Pending</option>
                        <option value="in_progress" {{ request('status') === 'in_progress' ? 'selected' : '' }}>In Progress</option>
                        <option value="done" {{ request('status') === 'done' ? 'selected' : '' }}>Done</option>
                        <option value="canceled" {{ request('status') === 'canceled' ? 'selected' : '' }}>Canceled</option>
                    </select>
                </div>

                {{-- Search --}}
                <div>
                    <label for="search" class="block text-sm font-medium text-gray-700 mb-1">Search</label>
                    <input type="text" name="search" id="search" value="{{ request('search') }}"
                        placeholder="Search equipment or description..."
                        class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-orange-500 focus:ring-orange-500 sm:text-sm">
                </div>

                {{-- Actions --}}
                <div class="flex items-end gap-2">
                    <button type="submit" class="flex-1 px-4 py-2 bg-orange-600 text-white rounded-lg hover:bg-orange-700 focus:ring-2 focus:ring-orange-500 text-sm font-medium">
                        Apply Filters
                    </button>
                    <a href="{{ route('native.requests.index') }}" class="px-4 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 text-sm font-medium">
                        Reset
                    </a>
                </div>
            </div>
        </form>
    </div>

    {{-- Requests Table --}}
    <div class="bg-white shadow-xl rounded-xl overflow-hidden border border-gray-200">
        @if($requests->count() > 0)
            {{-- Desktop Table --}}
            <div class="hidden md:block overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gradient-to-r from-gray-50 to-gray-100">
                        <tr>
                            <th class="px-6 py-4 text-left text-xs font-semibold text-gray-900 uppercase">Date</th>
                            <th class="px-6 py-4 text-left text-xs font-semibold text-gray-900 uppercase">Store</th>
                            <th class="px-6 py-4 text-left text-xs font-semibold text-gray-900 uppercase">Equipment</th>
                            <th class="px-6 py-4 text-left text-xs font-semibold text-gray-900 uppercase">Urgency</th>
                            <th class="px-6 py-4 text-left text-xs font-semibold text-gray-900 uppercase">Status</th>
                            <th class="px-6 py-4 text-left text-xs font-semibold text-gray-900 uppercase">Assigned To</th>
                            <th class="px-6 py-4 text-left text-xs font-semibold text-gray-900 uppercase">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        @foreach($requests as $index => $request)
                            <tr class="{{ ($index % 2 == 0) ? 'bg-white' : 'bg-gray-50' }} hover:bg-orange-50 transition-colors">
                                <td class="px-6 py-4 text-sm text-gray-600">{{ $request->request_date->format('M d, Y') }}</td>
                                <td class="px-6 py-4 text-sm">
                                    <div class="font-medium text-gray-900">{{ $request->store->store_number }}</div>
                                    <div class="text-xs text-gray-500">{{ Str::limit($request->store->name, 20) }}</div>
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-600">{{ Str::limit($request->equipment_with_issue, 30) }}</td>
                                <td class="px-6 py-4 text-sm">
                                    <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold" 
                                        style="background-color: {{ $request->urgencyLevel->color }}20; color: {{ $request->urgencyLevel->color }};">
                                        {{ $request->urgencyLevel->name }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-sm">
                                    @php
                                        $statusColors = [
                                            'pending' => 'bg-yellow-100 text-yellow-800 border-yellow-200',
                                            'received' => 'bg-purple-100 text-purple-800 border-purple-200',
                                            'in_progress' => 'bg-blue-100 text-blue-800 border-blue-200',
                                            'done' => 'bg-green-100 text-green-800 border-green-200',
                                            'canceled' => 'bg-red-100 text-red-800 border-red-200',
                                        ];
                                    @endphp
                                    <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold border {{ $statusColors[$request->status] ?? 'bg-gray-100 text-gray-800' }}">
                                        {{ ucwords(str_replace('_', ' ', $request->status)) }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-600">
                                    {{ $request->assignedTo?->name ?? 'Not Assigned' }}
                                </td>
                                <td class="px-6 py-4 text-sm">
                                    <a href="{{ route('native.requests.show', $request) }}" class="text-orange-600 hover:text-orange-900 font-medium">
                                        View Details
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            {{-- Mobile Cards --}}
            <div class="md:hidden divide-y divide-gray-200">
                @foreach($requests as $request)
                    <div class="p-4 hover:bg-gray-50">
                        <div class="flex justify-between items-start mb-2">
                            <div class="text-sm font-medium text-gray-900">{{ $request->store->store_number }}</div>
                            <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-semibold" 
                                style="background-color: {{ $request->urgencyLevel->color }}20; color: {{ $request->urgencyLevel->color }};">
                                {{ $request->urgencyLevel->name }}
                            </span>
                        </div>
                        <div class="text-sm text-gray-600 mb-2">{{ $request->equipment_with_issue }}</div>
                        <div class="flex items-center justify-between text-xs text-gray-500">
                            <span>{{ $request->request_date->format('M d, Y') }}</span>
                            <span class="font-medium">{{ ucwords(str_replace('_', ' ', $request->status)) }}</span>
                        </div>
                        <div class="mt-3">
                            <a href="{{ route('native.requests.show', $request) }}" class="text-sm text-orange-600 font-medium">View Details →</a>
                        </div>
                    </div>
                @endforeach
            </div>

            {{-- Pagination with Query String --}}
            <div class="px-6 py-4 border-t border-gray-200 bg-gray-50">
                {{ $requests->links() }}
            </div>
        @else
            <div class="text-center py-12">
                <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                </svg>
                <h3 class="mt-2 text-sm font-medium text-gray-900">No requests found</h3>
                <p class="mt-1 text-sm text-gray-500">Get started by creating a new maintenance request.</p>
                <div class="mt-6">
                    <a href="{{ route('native.requests.create') }}" class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-lg text-white bg-orange-600 hover:bg-orange-700">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                        </svg>
                        New Request
                    </a>
                </div>
            </div>
        @endif
    </div>
</div>
@endsection
