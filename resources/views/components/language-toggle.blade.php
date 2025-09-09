<div class="relative inline-block text-left">
    <button onclick="toggleLanguage()" 
            class="inline-flex items-center px-3 py-2 border border-orange-200 rounded-md shadow-sm bg-white text-sm font-medium text-gray-700 hover:bg-orange-50 hover:border-orange-300 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-orange-500 transition-all duration-200"
            title="{{ __('messages.select_language') }}">
        <span class="text-lg mr-2">🌐</span>
        <span class="hidden sm:inline">
            @if(app()->getLocale() === 'ar')
                العربية
            @elseif(app()->getLocale() === 'es')
                Español
            @else
                English
            @endif
        </span>
        <span class="sm:hidden">
            @if(app()->getLocale() === 'ar')
                ع
            @elseif(app()->getLocale() === 'es')
                ES
            @else
                EN
            @endif
        </span>
        <svg class="ml-2 -mr-1 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 9l4-4 4 4m0 6l-4 4-4-4"></path>
        </svg>
    </button>
</div>