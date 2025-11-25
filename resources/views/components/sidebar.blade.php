<div x-cloak>
    <div x-show="mobileOpen" @click="mobileOpen = false" x-transition:enter="transition-opacity ease-linear duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" x-transition:leave="transition-opacity ease-linear duration-300" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0" class="fixed inset-0 bg-gray-600 bg-opacity-75 z-40 lg:hidden"></div>

    <div :class="[ mobileOpen ? 'translate-x-0' : '-translate-x-full', sidebarCollapsed ? 'w-16' : 'w-64' ]" class="fixed inset-y-0 left-0 z-50 bg-orange-50 border-r border-orange-100 transition-all duration-300 ease-in-out lg:translate-x-0 lg:static lg:inset-0 shadow-xl flex flex-col">
        <div class="flex items-center justify-between h-16 bg-orange-100 border-b border-orange-200 px-3">
            <a href="{{ auth()->check() ? (auth()->user()->role === 'admin' ? route('admin.clocking') : route('clocking.index')) : '/' }}" class="flex items-center group" x-show="!sidebarCollapsed || window.innerWidth < 1024">
                <div class="relative">
                    <div class="absolute inset-0 bg-orange-200 rounded-full blur-md transform group-hover:scale-110 transition-transform duration-300"></div>
                    <svg class="w-6 h-6 text-orange-600" :class="sidebarCollapsed ? '' : 'mr-3'" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                    </svg>
                </div>
                <span class="ml-2 text-xl font-bold text-orange-900 group-hover:text-orange-700 transition-colors">PNE Maint.</span>
            </a>

            <button @click="mobileOpen = false" class="lg:hidden flex items-center justify-center w-8 h-8 text-orange-600 hover:bg-orange-200 rounded-lg transition-colors focus:outline-none">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
            </button>

            <button @click="sidebarCollapsed = !sidebarCollapsed" class="hidden lg:flex items-center justify-center w-8 h-8 text-orange-600 hover:bg-orange-200 rounded-lg transition-colors focus:outline-none focus:ring-2 focus:ring-orange-400" :class="sidebarCollapsed ? 'mx-auto' : ''">
                <svg class="w-5 h-5 transition-transform duration-300" :class="sidebarCollapsed ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 19l-7-7 7-7m8 14l-7-7 7-7"></path>
                </svg>
            </button>
        </div>

        <nav class="flex-1 px-2 py-4 space-y-1 overflow-y-auto custom-scrollbar">
            @auth
                @if(auth()->user()->role === 'admin')
                    <div x-data="{ open: false }">
                        <button @click="open = !open" class="flex items-center justify-between w-full px-3 py-3 text-sm font-medium text-orange-900 rounded-lg hover:bg-orange-200 focus:outline-none focus:bg-orange-200 transition-colors" :class="sidebarCollapsed ? 'justify-center' : ''" title="Schedule">
                            <div class="flex items-center" :class="sidebarCollapsed ? 'justify-center w-full' : ''">
                                <svg class="w-6 h-6 text-orange-600" :class="sidebarCollapsed ? '' : 'mr-3'" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3a4 4 0 118 0v4m-4 0l-8 4v12l8-4m0 0l8 4M12 11v8"></path>
                                </svg>
                                <span x-show="!sidebarCollapsed">Schedule</span>
                            </div>
                            <svg x-show="!sidebarCollapsed" :class="open ? 'rotate-180' : ''" class="w-4 h-4 transition-transform duration-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                            </svg>
                        </button>
                        <div x-show="open && !sidebarCollapsed" class="px-4 mt-2 space-y-1">
                            <a href="{{ route('admin.schedules.index') }}" class="block px-4 py-2 text-sm text-gray-700 rounded-lg hover:bg-orange-100 hover:text-orange-900">All Schedules</a>
                            <a href="{{ route('admin.schedules.create') }}" class="block px-4 py-2 text-sm text-gray-700 rounded-lg hover:bg-orange-100 hover:text-orange-900">Create Schedule</a>
                            <a href="{{ route('admin.task-assignments.index') }}" class="block px-4 py-2 text-sm text-gray-700 rounded-lg hover:bg-orange-100 hover:text-orange-900">Task Assignments</a>
                            <a href="{{ route('admin.scorecards.index') }}" class="block px-4 py-2 text-sm text-gray-700 rounded-lg hover:bg-orange-100 hover:text-orange-900">Scorecards</a>
                            <a href="{{ route('calendar.index') }}" class="block px-4 py-2 text-sm text-gray-700 rounded-lg hover:bg-orange-100 hover:text-orange-900">Calendar</a>
                        </div>
                    </div>

                    <a href="{{ route('admin.clocking') }}" class="flex items-center px-3 py-3 text-sm font-medium text-orange-900 rounded-lg hover:bg-orange-200 transition-colors" :class="sidebarCollapsed ? 'justify-center' : ''" title="Clocking Records">
                        <svg class="w-6 h-6 text-orange-600" :class="sidebarCollapsed ? '' : 'mr-3'" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        <span x-show="!sidebarCollapsed">Clocking Records</span>
                    </a>

                    <div x-data="{ open: {{ request()->is('admin/requests*') || request()->is('maintenance-requests*') ? 'true' : 'false' }} }">
                        <button @click="open = !open" class="flex items-center justify-between w-full px-3 py-3 text-sm font-medium text-orange-900 rounded-lg hover:bg-orange-200 focus:outline-none focus:bg-orange-200 transition-colors" :class="sidebarCollapsed ? 'justify-center' : ''" title="Maintenance">
                            <div class="flex items-center" :class="sidebarCollapsed ? 'justify-center w-full' : ''">
                                <svg class="w-6 h-6 text-orange-600" :class="sidebarCollapsed ? '' : 'mr-3'" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                </svg>
                                <span x-show="!sidebarCollapsed">Maintenance</span>
                            </div>
                            <svg x-show="!sidebarCollapsed" :class="open ? 'rotate-180' : ''" class="w-4 h-4 transition-transform duration-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                            </svg>
                        </button>
                        <div x-show="open && !sidebarCollapsed" class="px-4 mt-2 space-y-1">
                            <a href="{{ route('maintenance-requests.index') }}" class="block px-4 py-2 text-sm text-gray-700 rounded-lg hover:bg-orange-100 hover:text-orange-900 {{ request()->is('maintenance-requests*') ? 'bg-orange-100 text-orange-900 font-semibold' : '' }}">Cognito Requests</a>
                            <a href="{{ route('admin.native.index') }}" class="block px-4 py-2 text-sm text-gray-700 rounded-lg hover:bg-orange-100 hover:text-orange-900 {{ request()->is('admin/requests*') ? 'bg-orange-100 text-orange-900 font-semibold' : '' }}">Native Requests</a>
                        </div>
                    </div>

                    <a href="{{ route('workbooks.folders.index') }}" class="flex items-center px-3 py-3 text-sm font-medium text-orange-900 rounded-lg hover:bg-orange-200 transition-colors" :class="sidebarCollapsed ? 'justify-center' : ''" title="Workbook">
                        <svg class="w-6 h-6 text-orange-600" :class="sidebarCollapsed ? '' : 'mr-3'" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7h18M3 12h18M3 17h18M7 7v10"></path>
                        </svg>
                        <span x-show="!sidebarCollapsed">Workbook</span>
                    </a>

                    <div x-data="{ open: false }">
                        <button @click="open = !open" class="flex items-center justify-between w-full px-3 py-3 text-sm font-medium text-orange-900 rounded-lg hover:bg-orange-200 focus:outline-none focus:bg-orange-200 transition-colors" :class="sidebarCollapsed ? 'justify-center' : ''" title="Payments">
                            <div class="flex items-center" :class="sidebarCollapsed ? 'justify-center w-full' : ''">
                                <svg class="w-6 h-6 text-orange-600" :class="sidebarCollapsed ? '' : 'mr-3'" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                                <span x-show="!sidebarCollapsed">Payments</span>
                            </div>
                            <svg x-show="!sidebarCollapsed" :class="open ? 'rotate-180' : ''" class="w-4 h-4 transition-transform duration-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                            </svg>
                        </button>
                        <div x-show="open && !sidebarCollapsed" class="px-4 mt-2 space-y-1">
                            <a href="{{ route('payments.index') }}" class="block px-4 py-2 text-sm text-gray-700 rounded-lg hover:bg-orange-100 hover:text-orange-900">Payment Tracker</a>
                            <a href="{{ route('companies.index') }}" class="block px-4 py-2 text-sm text-gray-700 rounded-lg hover:bg-orange-100 hover:text-orange-900">Companies</a>
                        </div>
                    </div>

                    <div x-data="{ open: false }">
                        <button @click="open = !open" class="flex items-center justify-between w-full px-3 py-3 text-sm font-medium text-orange-900 rounded-lg hover:bg-orange-200 focus:outline-none focus:bg-orange-200 transition-colors" :class="sidebarCollapsed ? 'justify-center' : ''" title="Leases">
                            <div class="flex items-center" :class="sidebarCollapsed ? 'justify-center w-full' : ''">
                                <svg class="w-6 h-6 text-orange-600" :class="sidebarCollapsed ? '' : 'mr-3'" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-2m-14 0h2m0 0V9a2 2 0 012-2h2a2 2 0 012 2v10"></path>
                                </svg>
                                <span x-show="!sidebarCollapsed">Leases</span>
                            </div>
                            <svg x-show="!sidebarCollapsed" :class="open ? 'rotate-180' : ''" class="w-4 h-4 transition-transform duration-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                            </svg>
                        </button>
                        <div x-show="open && !sidebarCollapsed" class="px-4 mt-2 space-y-1">
                            <a href="{{ route('leases.index') }}" class="block px-4 py-2 text-sm text-gray-700 rounded-lg hover:bg-orange-100 hover:text-orange-900">Store Leases</a>
                            <a href="{{ route('admin.apartment-leases.index') }}" class="block px-4 py-2 text-sm text-gray-700 rounded-lg hover:bg-orange-100 hover:text-orange-900">Apartment Leases</a>
                        </div>
                    </div>

                    <div x-data="{ open: false }">
                        <button @click="open = !open" class="flex items-center justify-between w-full px-3 py-3 text-sm font-medium text-orange-900 rounded-lg hover:bg-orange-200 focus:outline-none focus:bg-orange-200 transition-colors" :class="sidebarCollapsed ? 'justify-center' : ''" title="Settings">
                            <div class="flex items-center" :class="sidebarCollapsed ? 'justify-center w-full' : ''">
                                <svg class="w-6 h-6 text-orange-600" :class="sidebarCollapsed ? '' : 'mr-3'" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                </svg>
                                <span x-show="!sidebarCollapsed">Settings</span>
                            </div>
                            <svg x-show="!sidebarCollapsed" :class="open ? 'rotate-180' : ''" class="w-4 h-4 transition-transform duration-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                            </svg>
                        </button>
                        <div x-show="open && !sidebarCollapsed" class="px-4 mt-2 space-y-1">
                            <a href="{{ route('users.index') }}" class="block px-4 py-2 text-sm text-gray-700 rounded-lg hover:bg-orange-100 hover:text-orange-900">User Management</a>
                            <a href="{{ route('stores.index') }}" class="block px-4 py-2 text-sm text-gray-700 rounded-lg hover:bg-orange-100 hover:text-orange-900">Store Management</a>
                        </div>
                    </div>

                @elseif(auth()->user()->role === 'store_manager')
                    <a href="{{ route('native.requests.create') }}" class="flex items-center px-3 py-3 text-sm font-medium text-orange-900 rounded-lg hover:bg-orange-200 transition-colors {{ request()->routeIs('native.create') ? 'bg-orange-200' : '' }}" :class="sidebarCollapsed ? 'justify-center' : ''" title="New Maintenance Request">
                        <svg class="w-6 h-6 text-orange-600" :class="sidebarCollapsed ? '' : 'mr-3'" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                        </svg>
                        <span x-show="!sidebarCollapsed">New Maintenance Request</span>
                    </a>

                    <a href="{{ route('native.requests.index') }}" class="flex items-center px-3 py-3 text-sm font-medium text-orange-900 rounded-lg hover:bg-orange-200 transition-colors {{ request()->routeIs('native.index') ? 'bg-orange-200' : '' }}" :class="sidebarCollapsed ? 'justify-center' : ''" title="My Tickets">
                        <svg class="w-6 h-6 text-orange-600" :class="sidebarCollapsed ? '' : 'mr-3'" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                        </svg>
                        <span x-show="!sidebarCollapsed">My Tickets</span>
                    </a>
                @endif
            @else
                <a href="{{ route('login') }}" class="flex items-center px-3 py-3 text-sm font-medium text-orange-900 rounded-lg hover:bg-orange-200 transition-colors" :class="sidebarCollapsed ? 'justify-center' : ''" title="Log in">
                    <svg class="w-6 h-6 text-orange-600" :class="sidebarCollapsed ? '' : 'mr-3'" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1"></path>
                    </svg>
                    <span x-show="!sidebarCollapsed">Log in</span>
                </a>
            @endauth
        </nav>

        @auth
            <div x-data="{ profileOpen: false }" class="relative border-t border-orange-200 bg-orange-100">
                <div x-show="sidebarCollapsed" class="p-4">
                    <button @click="profileOpen = !profileOpen" class="w-full flex items-center justify-center focus:outline-none group">
                        <div class="w-10 h-10 rounded-full bg-orange-300 flex items-center justify-center text-orange-800 font-bold group-hover:ring-2 group-hover:ring-orange-500 transition-all">
                            {{ substr(auth()->user()->name, 0, 1) }}
                        </div>
                    </button>

                    <div x-show="profileOpen" @click.away="profileOpen = false" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 transform scale-95" x-transition:enter-end="opacity-100 transform scale-100" x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100 transform scale-100" x-transition:leave-end="opacity-0 transform scale-95" class="absolute bottom-full left-0 mb-2 w-64 bg-white rounded-lg shadow-xl border border-orange-200 py-2 z-50">
                        <div class="px-4 py-3 border-b border-gray-200">
                            <p class="text-sm font-medium text-gray-900">{{ auth()->user()->name }}</p>
                            <p class="text-xs text-gray-500 mt-1">{{ auth()->user()->email }}</p>
                            <p class="text-xs text-orange-600 mt-1 font-medium">{{ ucfirst(str_replace('_', ' ', auth()->user()->role)) }}</p>
                        </div>

                        @if(auth()->user()->role !== 'admin' && auth()->user()->role !== 'store_manager')
                            <div class="py-1">
                                <a href="{{ route('user.schedule.index') }}" class="flex items-center px-4 py-2 text-sm text-gray-700 hover:bg-orange-50 transition-colors">
                                    <svg class="w-4 h-4 mr-3 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                    </svg>
                                    My Schedule
                                </a>
                                <a href="{{ route('user.tasks.index') }}" class="flex items-center px-4 py-2 text-sm text-gray-700 hover:bg-orange-50 transition-colors">
                                    <svg class="w-4 h-4 mr-3 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"></path>
                                    </svg>
                                    My Tasks
                                </a>
                                <a href="{{ route('attendance.index') }}" class="flex items-center px-4 py-2 text-sm text-gray-700 hover:bg-orange-50 transition-colors">
                                    <svg class="w-4 h-4 mr-3 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                                    </svg>
                                    Attendance
                                </a>
                                <a href="{{ route('clocking.index') }}" class="flex items-center px-4 py-2 text-sm text-gray-700 hover:bg-orange-50 transition-colors">
                                    <svg class="w-4 h-4 mr-3 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                    </svg>
                                    Clock In/Out
                                </a>
                            </div>
                        @endif

                        <div class="border-t border-gray-200 mt-1 pt-1">
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button type="submit" class="flex items-center w-full px-4 py-2 text-sm text-red-600 hover:bg-red-50 transition-colors">
                                    <svg class="w-4 h-4 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path>
                                    </svg>
                                    Logout
                                </button>
                            </form>
                        </div>
                    </div>
                </div>

                <div x-show="!sidebarCollapsed" class="p-4">
                    <div class="flex items-center mb-3">
                        <div class="flex-shrink-0">
                            <div class="w-10 h-10 rounded-full bg-orange-300 flex items-center justify-center text-orange-800 font-bold">
                                {{ substr(auth()->user()->name, 0, 1) }}
                            </div>
                        </div>
                        <div class="ml-3 flex-1 min-w-0">
                            <p class="text-sm font-medium text-gray-900 truncate">{{ auth()->user()->name }}</p>
                            <p class="text-xs text-gray-500 truncate">{{ ucfirst(str_replace('_', ' ', auth()->user()->role)) }}</p>
                        </div>
                    </div>

                    @if(auth()->user()->role !== 'admin' && auth()->user()->role !== 'store_manager')
                        <div class="space-y-1 mb-3">
                            <a href="{{ route('user.schedule.index') }}" class="flex items-center px-3 py-2 text-xs text-gray-700 hover:bg-orange-200 rounded-lg transition-colors">
                                <svg class="w-4 h-4 mr-2 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                </svg>
                                My Schedule
                            </a>
                            <a href="{{ route('user.tasks.index') }}" class="flex items-center px-3 py-2 text-xs text-gray-700 hover:bg-orange-200 rounded-lg transition-colors">
                                <svg class="w-4 h-4 mr-2 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"></path>
                                </svg>
                                My Tasks
                            </a>
                            <a href="{{ route('attendance.index') }}" class="flex items-center px-3 py-2 text-xs text-gray-700 hover:bg-orange-200 rounded-lg transition-colors">
                                <svg class="w-4 h-4 mr-2 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                                </svg>
                                Attendance
                            </a>
                            <a href="{{ route('clocking.index') }}" class="flex items-center px-3 py-2 text-xs text-gray-700 hover:bg-orange-200 rounded-lg transition-colors">
                                <svg class="w-4 h-4 mr-2 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                                Clock In/Out
                            </a>
                        </div>

                        <div class="flex gap-1 mb-3">
                            <button onclick="switchLanguage('ar')" class="flex-1 px-2 py-1 text-xs bg-orange-200 rounded hover:bg-orange-300 transition-colors">AR</button>
                            <button onclick="switchLanguage('en')" class="flex-1 px-2 py-1 text-xs bg-orange-200 rounded hover:bg-orange-300 transition-colors">EN</button>
                            <button onclick="switchLanguage('es')" class="flex-1 px-2 py-1 text-xs bg-orange-200 rounded hover:bg-orange-300 transition-colors">ES</button>
                        </div>
                    @endif

                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="flex items-center justify-center w-full px-4 py-2 text-sm font-medium text-white bg-orange-600 rounded-lg hover:bg-orange-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-orange-500 transition-colors">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path>
                            </svg>
                            Logout
                        </button>
                    </form>
                </div>
            </div>
        @endauth
    </div>
</div>

<script>
    function switchLanguage(lang) {
        fetch('{{ route('language.switch') }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            },
            body: JSON.stringify({ language: lang })
        })
            .then(response => response.json())
            .then(data => {
                if(data.success) {
                    window.location.reload();
                } else {
                    alert('Failed to switch language');
                }
            })
            .catch(() => alert('Error switching language'));
    }
</script>
