@extends('layouts.app')

@section('content')
    <div class="min-h-screen bg-orange-50 py-12">
        <div class="max-w-2xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="bg-orange-50 rounded-2xl ring-1 ring-orange-900/5 overflow-hidden">
                <!-- Header -->
                <div class="px-6 py-4 bg-gradient-to-r from-orange-600 to-orange-700">
                    <h1 class="text-2xl font-semibold text-white">Edit User</h1>
                    <p class="mt-1 text-black-100">Update user information</p>
                </div>

                <!-- Error Messages -->
                @if($errors->any())
                    <div class="px-6 py-4 bg-orange-50 border-l-4 border-orange-500">
                        <div class="flex">
                            <div class="flex-shrink-0">
                                <svg class="h-5 w-5 text-black-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                            </div>
                            <div class="ml-3">
                                <h3 class="text-sm font-medium text-black-800">There were errors with your submission</h3>
                                <div class="mt-2 text-sm text-black-700">
                                    <ul class="list-disc list-inside space-y-1">
                                        @foreach($errors->all() as $error)
                                            <li>{{ $error }}</li>
                                        @endforeach
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                @endif

                <!-- Form -->
                <form action="{{ route('users.update', $user->id) }}" method="POST" class="p-6">
                    @csrf
                    @method('PUT')
                    <div class="space-y-6">
                        <!-- Name -->
                        <div>
                            <label for="name" class="block text-sm font-medium text-black-700">Name</label>
                            <div class="mt-1 relative rounded-md ring-1 ring-orange-900/5">
                                <input type="text" id="name" name="name" value="{{ old('name', $user->name) }}"
                                       class="block w-full pl-10 pr-3 py-3 text-base border-orange-200 focus:outline-none focus:ring-orange-500 focus:border-orange-500 rounded-md transition-shadow"
                                       placeholder="Enter user name" required>
                            </div>
                        </div>

                        <!-- Role Selection -->
                        <div>
                            <label for="role" class="block text-sm font-medium text-black-700">Role</label>
                            <div class="mt-1 relative rounded-md ring-1 ring-orange-900/5">
                                <select id="role" name="role"
                                        class="block w-full pl-3 pr-10 py-3 text-base border-orange-200 focus:outline-none focus:ring-orange-500 focus:border-orange-500 rounded-md">
                                    <option value="user" {{ old('role', $user->role) == 'user' ? 'selected' : '' }}>User</option>
                                    <option value="admin" {{ old('role', $user->role) == 'admin' ? 'selected' : '' }}>Admin</option>
                                    <option value="store_manager" {{ old('role', $user->role) == 'store_manager' ? 'selected' : '' }}>Store Manager</option>
                                </select>
                            </div>
                        </div>

                        <!-- Managed Stores (for store_manager role) -->
                        <div id="managed-stores-section" style="display: {{ old('role', $user->role) == 'store_manager' ? 'block' : 'none' }}">
                                    <label for="managed_stores" class="block text-sm font-medium text-black-700">Managed Stores</label>
                                    @if(auth()->user() && auth()->user()->role === 'admin')
                                        <div class="mt-2 flex items-center space-x-2">
                                            <button type="button" id="select-all-stores-btn" class="px-3 py-1 bg-orange-600 text-white text-sm rounded-md hover:bg-orange-700">Select All</button>
                                            <button type="button" id="clear-all-stores-btn" class="px-3 py-1 bg-white border border-orange-200 text-orange-700 text-sm rounded-md hover:bg-orange-50">Clear</button>
                                        </div>
                                    @endif
                                    <p class="text-xs text-gray-500 mb-2">Select stores this user can manage (hold Ctrl/Cmd to select multiple)</p>
                            <div class="mt-1 relative rounded-md ring-1 ring-orange-900/5">
                                <select id="managed_stores" name="managed_stores[]" multiple size="8"
                                        class="block w-full pl-3 pr-10 py-2 text-base border-orange-200 focus:outline-none focus:ring-orange-500 focus:border-orange-500 rounded-md">
                                    @foreach($stores as $store)
                                        <option value="{{ $store->id }}" 
                                            {{ in_array($store->id, old('managed_stores', $userManagedStoreIds ?? [])) ? 'selected' : '' }}>
                                            {{ $store->store_number }} - {{ $store->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <p class="text-xs text-gray-500 mt-1">Hold Ctrl (Cmd on Mac) to select multiple stores</p>
                        </div>

                        <!-- Global Store Manager -->
                        <div id="global-manager-section" style="display: {{ old('role', $user->role) == 'store_manager' ? 'block' : 'none' }}">
                            <div class="flex items-center">
                                <input type="checkbox" id="is_global_store_manager" name="is_global_store_manager" value="1"
                                    {{ old('is_global_store_manager', $user->is_global_store_manager) ? 'checked' : '' }}
                                    class="h-4 w-4 text-orange-600 focus:ring-orange-500 border-gray-300 rounded">
                                <label for="is_global_store_manager" class="ml-2 block text-sm text-gray-900">
                                    Global Store Manager
                                </label>
                            </div>
                            <p class="text-xs text-gray-500 mt-1 ml-6">When enabled, this user can create and view tickets for ALL stores (ignores store assignments)</p>
                        </div>

                        <!-- Hourly Pay -->
                        <div>
                            <label for="hourly_pay" class="block text-sm font-medium text-black-700">Hourly Pay</label>
                            <div class="mt-1 relative rounded-md ring-1 ring-orange-900/5">
                                <input type="number" step="0.01" id="hourly_pay" name="hourly_pay"
                                       value="{{ old('hourly_pay', $user->hourly_pay) }}"
                                       class="block w-full pl-10 pr-3 py-3 text-base border-orange-200 focus:outline-none focus:ring-orange-500 focus:border-orange-500 rounded-md transition-shadow"
                                       placeholder="Enter hourly pay rate" required>
                            </div>
                        </div>

                        <!-- Optional Password Change -->
                        <div>
                            <label for="password" class="block text-sm font-medium text-black-700">New Password (leave blank to keep current)</label>
                            <div class="mt-1 relative rounded-md ring-1 ring-orange-900/5">
                                <input type="password" id="password" name="password"
                                       class="block w-full pl-10 pr-3 py-3 text-base border-orange-200 focus:outline-none focus:ring-orange-500 focus:border-orange-500 rounded-md transition-shadow"
                                       placeholder="Enter new password">
                            </div>
                        </div>
                    </div>

                    <!-- Form Actions -->
                    <div class="flex items-center justify-end pt-6">
                        <a href="{{ route('users.index') }}" class="mr-4 text-sm font-medium text-black-600 hover:text-black-500">
                            Cancel
                        </a>
                        <button type="submit" class="px-6 py-3 bg-orange-600 text-white text-sm font-semibold rounded-lg ring-1 ring-orange-900/5 hover:bg-orange-700 focus:outline-none focus:ring-2 focus:ring-orange-500 focus:ring-offset-2 transition-colors">
                            Update User
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        // Show/hide managed stores section based on role selection
        const roleSelect = document.getElementById('role');
        const managedStoresSection = document.getElementById('managed-stores-section');
        const globalManagerSection = document.getElementById('global-manager-section');

        function toggleManagedStoresSection(value) {
            if (!managedStoresSection || !globalManagerSection) return;
            if (value === 'store_manager') {
                managedStoresSection.style.display = 'block';
                globalManagerSection.style.display = 'block';
            } else {
                managedStoresSection.style.display = 'none';
                globalManagerSection.style.display = 'none';
            }
        }

        if (roleSelect) {
            roleSelect.addEventListener('change', function() {
                toggleManagedStoresSection(this.value);
            });
            // initialize on page load in case of old value
            toggleManagedStoresSection(roleSelect.value || '{{ old("role", $user->role) }}');
        }

        // Select All / Clear buttons (visible to admins)
        const selectAllBtn = document.getElementById('select-all-stores-btn');
        const clearAllBtn = document.getElementById('clear-all-stores-btn');
        const managedSelect = document.getElementById('managed_stores');

        if (selectAllBtn && managedSelect) {
            selectAllBtn.addEventListener('click', function() {
                for (let i = 0; i < managedSelect.options.length; i++) {
                    managedSelect.options[i].selected = true;
                }
                managedSelect.dispatchEvent(new Event('change'));
            });
        }

        if (clearAllBtn && managedSelect) {
            clearAllBtn.addEventListener('click', function() {
                for (let i = 0; i < managedSelect.options.length; i++) {
                    managedSelect.options[i].selected = false;
                }
                managedSelect.dispatchEvent(new Event('change'));
            });
        }
    });
    </script>
