@if (session('success'))
<div id="toast-notification" class="fixed top-4 right-4 z-50 animate-slide-in-right">
    <div class="rounded-lg shadow-lg overflow-hidden">
        <div class="flex items-center p-4 {{ str_contains(strtolower(session('success')), 'clock-in') ? 'bg-green-50' : 'bg-blue-50' }} min-w-[320px]">
            <!-- Icon -->
            @if(str_contains(strtolower(session('success')), 'clock-in'))
            <div class="flex-shrink-0">
                <svg class="h-6 w-6 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1"></path>
                </svg>
            </div>
            @else
            <div class="flex-shrink-0">
                <svg class="h-6 w-6 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path>
                </svg>
            </div>
            @endif

            <!-- Content -->
            <div class="ml-3 w-0 flex-1">
                <p class="text-sm font-medium {{ str_contains(strtolower(session('success')), 'clock-in') ? 'text-green-800' : 'text-blue-800' }}">
                    Success!
                </p>
                <p class="mt-1 text-sm {{ str_contains(strtolower(session('success')), 'clock-in') ? 'text-green-700' : 'text-blue-700' }}">
                    {{ session('success') }}
                </p>
            </div>

            <!-- Close button -->
            <div class="ml-4 flex-shrink-0 flex">
                <button type="button" onclick="closeToast()" class="inline-flex {{ str_contains(strtolower(session('success')), 'clock-in') ? 'text-green-400 hover:text-green-500' : 'text-blue-400 hover:text-blue-500' }}">
                    <span class="sr-only">Close</span>
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>
        </div>

        <!-- Progress bar -->
        <div class="relative h-1 w-full">
            <div id="toast-progress-bar" class="{{ str_contains(strtolower(session('success')), 'clock-in') ? 'bg-green-500' : 'bg-blue-500' }} h-full w-full animate-progress"></div>
        </div>
    </div>
</div>

<style>
    @keyframes slideInRight {
        from {
            transform: translateX(100%);
            opacity: 0;
        }
        to {
            transform: translateX(0);
            opacity: 1;
        }
    }

    @keyframes slideOutRight {
        from {
            transform: translateX(0);
            opacity: 1;
        }
        to {
            transform: translateX(100%);
            opacity: 0;
        }
    }

    @keyframes progress {
        from {
            width: 100%;
        }
        to {
            width: 0%;
        }
    }

    .animate-slide-in-right {
        animation: slideInRight 0.3s ease-out;
    }

    .animate-slide-out-right {
        animation: slideOutRight 0.3s ease-out;
    }

    .animate-progress {
        animation: progress 5s linear;
    }
</style>

<script>
    // Auto-dismiss toast after 5 seconds
    if (document.getElementById('toast-notification')) {
        setTimeout(() => {
            closeToast();
        }, 5000);
    }

    function closeToast() {
        const toast = document.getElementById('toast-notification');
        if (toast) {
            toast.classList.add('animate-slide-out-right');
            setTimeout(() => {
                toast.remove();
            }, 300);
        }
    }
</script>
@endif