@extends('layouts.app')

@section('content')
    <div class="min-h-screen bg-orange-50 py-12">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <!-- Header -->
            <div class="sm:flex sm:items-center sm:justify-between mb-6">
                <div>
                    <h1 class="text-3xl font-bold text-black-900">Users List</h1>
                    <p class="mt-2 text-sm text-black-700">A list of all users in your account including their name, email, hourly pay, and role.</p>
                </div>
                <div class="relative inline-block text-left">
                    <button type="button" id="clear-notifications-menu" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-lg text-white bg-gradient-to-r from-orange-500 to-orange-600 hover:from-orange-600 hover:to-orange-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-orange-500 transition-all duration-300 hover:shadow-lg transform hover:scale-105" aria-expanded="false" aria-haspopup="true">
                        Clear Notifications
                        <svg class="w-4 h-4 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                        </svg>
                    </button>
                    <div id="clear-notifications-dropdown" class="origin-top-right absolute right-0 mt-2 w-48 rounded-md shadow-lg bg-white ring-1 ring-orange-900/5 focus:outline-none hidden" role="menu" aria-orientation="vertical" aria-labelledby="clear-notifications-menu">
                        <form action="{{ route('notifications.clear') }}" method="POST" class="space-y-1">
                            @csrf
                            @method('DELETE')
                            <button type="submit" name="action" value="clear_30" class="w-full text-left px-4 py-2 text-sm text-black-700 hover:bg-orange-50 hover:text-black-900 focus:outline-none focus:bg-orange-50" role="menuitem">
                                Clear Last 30 Days
                            </button>
                            <button type="submit" name="action" value="clear_15" class="w-full text-left px-4 py-2 text-sm text-black-700 hover:bg-orange-50 hover:text-black-900 focus:outline-none focus:bg-orange-50" role="menuitem">
                                Clear Last 15 Days
                            </button>
                            <button type="submit" name="action" value="clear_week" class="w-full text-left px-4 py-2 text-sm text-black-700 hover:bg-orange-50 hover:text-black-900 focus:outline-none focus:bg-orange-50" role="menuitem">
                                Clear This Week
                            </button>
                            <button type="submit" name="action" value="mark_not_delete" class="w-full text-left px-4 py-2 text-sm text-black-700 hover:bg-orange-50 hover:text-black-900 focus:outline-none focus:bg-orange-50" role="menuitem">
                                Mark as Not Delete
                            </button>
                        </form>
                    </div>
                </div>

                <div class="mt-4 sm:mt-0 flex space-x-3">
                    <a href="{{ route('users.create') }}" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-lg text-white bg-gradient-to-r from-orange-500 to-orange-600 hover:from-orange-600 hover:to-orange-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-orange-500 transition-all duration-300 hover:shadow-lg transform hover:scale-105">
                        Add New User
                    </a>
                </div>
            </div>

            <!-- Success Message -->
            @if(session('success'))
                <div class="mt-6 rounded-md bg-orange-50 p-4" role="alert">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <svg class="h-5 w-5 text-black-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                        <div class="ml-3">
                            <p class="text-sm font-medium text-black-800">
                                {{ session('success') }}
                            </p>
                        </div>
                        <div class="ml-auto pl-3">
                            <div class="-mx-1.5 -my-1.5">
                                <button type="button" class="inline-flex rounded-md bg-orange-50 p-1.5 text-black-500 hover:bg-orange-100 focus:outline-none focus:ring-2 focus:ring-orange-600 focus:ring-offset-2 focus:ring-offset-orange-50">
                                    <span class="sr-only">Dismiss</span>
                                    <svg class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                                        <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd" />
                                    </svg>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            @endif

            <!-- Users Table -->
            <div class="mt-8 flex flex-col">
                <div class="-mx-4 -my-2 overflow-x-auto sm:-mx-6 lg:-mx-8">
                    <div class="inline-block min-w-full py-2 align-middle">
                        <div class="overflow-hidden ring-1 ring-orange-900/5 rounded-lg">
                            <table class="min-w-full divide-y divide-orange-200">
                                <thead class="bg-orange-50">
                                <tr>
                                    <th scope="col" class="py-3.5 pl-4 pr-3 text-left text-sm font-semibold text-black-900 sm:pl-6 lg:pl-8">ID</th>
                                    <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-black-900">Name</th>
                                    <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-black-900">Email</th>
                                    <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-black-900">Hourly Pay</th>
                                    <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-black-900">Role</th>
                                    <th scope="col" class="relative py-3.5 pl-3 pr-4 sm:pr-6 lg:pr-8">
                                        <span class="sr-only">Actions</span>
                                    </th>
                                </tr>
                                </thead>
                                <tbody class="divide-y divide-orange-200 bg-orange-50">
                                @foreach($users as $user)
                                    <tr class="hover:bg-orange-100">
                                        <td class="whitespace-nowrap py-4 pl-4 pr-3 text-sm font-medium text-black-900 sm:pl-6 lg:pl-8">
                                            {{ $user->id }}
                                        </td>
                                        <td class="whitespace-nowrap px-3 py-4 text-sm text-black-500">
                                            {{ $user->name }}
                                        </td>
                                        <td class="whitespace-nowrap px-3 py-4 text-sm text-black-500">
                                            {{ $user->email }}
                                        </td>
                                        <td class="whitespace-nowrap px-3 py-4 text-sm text-black-500">
                                            {{ $user->hourly_pay !== null ? '$' . number_format($user->hourly_pay, 2) : '-' }}
                                        </td>
                                        <td class="whitespace-nowrap px-3 py-4 text-sm">
                                        <span class="inline-flex rounded-full bg-orange-100 px-2 text-xs font-semibold leading-5 text-black-800">
                                            {{ $user->role }}
                                        </span>
                                        </td>
                                        <td class="relative whitespace-nowrap py-4 pl-3 pr-4 text-right text-sm font-medium sm:pr-6 lg:pr-8">
                                            <div class="flex justify-end gap-3">
                                                <a href="{{ route('users.edit', $user->id) }}" class="inline-flex items-center px-3 py-1.5 border border-orange-200 ring-1 ring-orange-900/5 text-sm font-medium rounded-md text-black-700 bg-orange-50 hover:bg-orange-100 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-orange-500 transition-colors">
                                                    <svg class="-ml-0.5 mr-1.5 h-4 w-4 text-black-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                                    </svg>
                                                    Edit
                                                </a>
                                                <form action="{{ route('users.destroy', $user->id) }}" method="POST" class="inline-block">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="inline-flex items-center px-3 py-1.5 border border-transparent text-sm font-medium rounded-md text-black-700 bg-orange-100 hover:bg-orange-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-orange-500 transition-colors" onclick="return confirm('Are you sure you want to delete this user?')">
                                                        <svg class="-ml-0.5 mr-1.5 h-4 w-4 text-black-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                                        </svg>
                                                        Delete
                                                    </button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Dismiss success message
        document.addEventListener('DOMContentLoaded', function() {
            const successAlert = document.querySelector('[role="alert"]');
            const dismissButton = successAlert?.querySelector('button');

            if (dismissButton) {
                dismissButton.addEventListener('click', function() {
                    successAlert.style.display = 'none';
                });
            }
        });

        // Toggle dropdown for clear notifications
        document.addEventListener('DOMContentLoaded', function() {
            const clearButton = document.getElementById('clear-notifications-menu');
            const dropdown = document.getElementById('clear-notifications-dropdown');

            if (clearButton && dropdown) {
                clearButton.addEventListener('click', function(e) {
                    e.preventDefault();
                    const isHidden = dropdown.classList.contains('hidden');
                    dropdown.classList.toggle('hidden');
                    clearButton.setAttribute('aria-expanded', isHidden ? 'true' : 'false');
                });
            }

            // Close dropdown when clicking outside
            document.addEventListener('click', function(e) {
                const isClickInside = clearButton?.contains(e.target) || dropdown?.contains(e.target);
                if (!isClickInside && !dropdown.classList.contains('hidden')) {
                    dropdown.classList.add('hidden');
                    clearButton?.setAttribute('aria-expanded', 'false');
                }
            });
        });
    </script>
@endsection
