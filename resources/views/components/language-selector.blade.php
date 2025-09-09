<div id="languageModal" class="fixed inset-0 z-50 flex items-center justify-center hidden" role="dialog" aria-labelledby="language-modal-title" aria-modal="true">
    <div class="fixed inset-0 bg-black bg-opacity-50 transition-opacity"></div>
    <div class="relative bg-white rounded-lg shadow-xl max-w-md w-full mx-4 transform transition-all">
        <div class="bg-orange-50 px-6 py-4 border-b border-orange-200">
            <h3 class="text-lg font-semibold text-gray-900" id="language-modal-title">
                {{ __('messages.select_language') }}
            </h3>
        </div>
        
        <div class="px-6 py-4">
            <p class="text-sm text-gray-600 mb-6">
                {{ __('messages.language_preference') }}
            </p>
            
            <div class="space-y-3">
                <button onclick="selectLanguage('en')" 
                        class="w-full flex items-center justify-between p-4 border-2 border-gray-200 rounded-lg hover:border-orange-500 hover:bg-orange-50 transition-all duration-200 language-option" 
                        data-lang="en">
                    <div class="flex items-center">
                        <span class="text-2xl mr-3">🇺🇸</span>
                        <span class="font-medium text-gray-900">{{ __('messages.english') }}</span>
                    </div>
                    <div class="w-5 h-5 border-2 border-gray-300 rounded-full flex items-center justify-center">
                        <div class="w-3 h-3 bg-orange-500 rounded-full hidden selected-indicator"></div>
                    </div>
                </button>
                
                <button onclick="selectLanguage('ar')" 
                        class="w-full flex items-center justify-between p-4 border-2 border-gray-200 rounded-lg hover:border-orange-500 hover:bg-orange-50 transition-all duration-200 language-option" 
                        data-lang="ar">
                    <div class="flex items-center">
                        <span class="text-2xl mr-3">🇸🇦</span>
                        <span class="font-medium text-gray-900">{{ __('messages.arabic') }}</span>
                    </div>
                    <div class="w-5 h-5 border-2 border-gray-300 rounded-full flex items-center justify-center">
                        <div class="w-3 h-3 bg-orange-500 rounded-full hidden selected-indicator"></div>
                    </div>
                </button>
                
                <button onclick="selectLanguage('es')" 
                        class="w-full flex items-center justify-between p-4 border-2 border-gray-200 rounded-lg hover:border-orange-500 hover:bg-orange-50 transition-all duration-200 language-option" 
                        data-lang="es">
                    <div class="flex items-center">
                        <span class="text-2xl mr-3">🇪🇸</span>
                        <span class="font-medium text-gray-900">{{ __('messages.spanish') }}</span>
                    </div>
                    <div class="w-5 h-5 border-2 border-gray-300 rounded-full flex items-center justify-center">
                        <div class="w-3 h-3 bg-orange-500 rounded-full hidden selected-indicator"></div>
                    </div>
                </button>
            </div>
        </div>
        
        <div class="bg-gray-50 px-6 py-4 flex justify-end space-x-3">
            <button onclick="saveLanguagePreference()" 
                    id="saveLanguageBtn"
                    class="px-6 py-2 bg-orange-600 text-white rounded-lg hover:bg-orange-700 focus:outline-none focus:ring-2 focus:ring-orange-500 focus:ring-offset-2 transition-colors duration-200 disabled:opacity-50 disabled:cursor-not-allowed" 
                    disabled>
                {{ __('messages.save_language') }}
            </button>
        </div>
    </div>
</div>

<script>
let selectedLanguage = null;

// Check if user has language preference stored
function checkLanguagePreference() {
    const storedLang = localStorage.getItem('preferred_language');
    if (!storedLang) {
        // Show language selection modal for first-time visitors
        showLanguageModal();
    } else {
        // Set the language based on stored preference
        setApplicationLanguage(storedLang);
    }
}

// Show language selection modal
function showLanguageModal() {
    const modal = document.getElementById('languageModal');
    modal.classList.remove('hidden');
    setTimeout(() => {
        modal.classList.add('opacity-100');
    }, 50);
}

// Hide language selection modal
function hideLanguageModal() {
    const modal = document.getElementById('languageModal');
    modal.classList.remove('opacity-100');
    setTimeout(() => {
        modal.classList.add('hidden');
    }, 300);
}

// Select language option
function selectLanguage(lang) {
    selectedLanguage = lang;
    
    // Update UI to show selection
    document.querySelectorAll('.language-option').forEach(option => {
        const indicator = option.querySelector('.selected-indicator');
        const border = option;
        
        if (option.dataset.lang === lang) {
            indicator.classList.remove('hidden');
            border.classList.add('border-orange-500', 'bg-orange-50');
            border.classList.remove('border-gray-200');
        } else {
            indicator.classList.add('hidden');
            border.classList.remove('border-orange-500', 'bg-orange-50');
            border.classList.add('border-gray-200');
        }
    });
    
    // Enable save button
    document.getElementById('saveLanguageBtn').disabled = false;
}

// Save language preference and apply it
function saveLanguagePreference() {
    if (selectedLanguage) {
        // Store in localStorage
        localStorage.setItem('preferred_language', selectedLanguage);
        
        // Apply language immediately
        setApplicationLanguage(selectedLanguage);
        
        // Hide modal
        hideLanguageModal();
        
        // Reload page to apply language changes
        window.location.reload();
    }
}

// Set application language and direction
function setApplicationLanguage(lang) {
    // Set document direction
    document.documentElement.dir = lang === 'ar' ? 'rtl' : 'ltr';
    document.documentElement.lang = lang;
    
    // Add language class to body for CSS targeting
    document.body.classList.remove('lang-en', 'lang-ar', 'lang-es');
    document.body.classList.add(`lang-${lang}`);
    
    // Store current language for other scripts
    window.currentLanguage = lang;
    
    // Send language preference to server (with error handling)
    const csrfToken = document.querySelector('meta[name="csrf-token"]');
    if (csrfToken) {
        fetch('/language/switch', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken.getAttribute('content')
            },
            body: JSON.stringify({ language: lang })
        }).catch(error => {
            console.log('Language preference saved locally, server update failed:', error);
        });
    } else {
        console.log('CSRF token not found, language preference saved locally only');
    }
}

// Initialize language system when DOM is loaded
document.addEventListener('DOMContentLoaded', function() {
    checkLanguagePreference();
});

// Language toggle function for manual switching
function toggleLanguage() {
    const currentLang = localStorage.getItem('preferred_language') || 'ar';
    let newLang;
    
    // Cycle through languages: ar -> en -> es -> ar
    if (currentLang === 'ar') {
        newLang = 'en';
    } else if (currentLang === 'en') {
        newLang = 'es';
    } else {
        newLang = 'ar';
    }
    
    localStorage.setItem('preferred_language', newLang);
    setApplicationLanguage(newLang);
    window.location.reload();
}

// Open language modal function
// Add the missing openLanguageModal function
function openLanguageModal() {
    showLanguageModal();
}
</script>

<style>
/* Language-specific styles */
.lang-ar {
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
}

.lang-en {
    font-family: 'Inter', 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
}

.lang-es {
    font-family: 'Inter', 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
}

/* RTL specific adjustments */
[dir="rtl"] .flex-row-reverse {
    flex-direction: row-reverse;
}

[dir="rtl"] .space-x-3 > * + * {
    margin-left: 0;
    margin-right: 0.75rem;
}

[dir="rtl"] .space-x-4 > * + * {
    margin-left: 0;
    margin-right: 1rem;
}

[dir="rtl"] .mr-2 {
    margin-right: 0;
    margin-left: 0.5rem;
}

[dir="rtl"] .mr-3 {
    margin-right: 0;
    margin-left: 0.75rem;
}

[dir="rtl"] .ml-2 {
    margin-left: 0;
    margin-right: 0.5rem;
}

[dir="rtl"] .ml-3 {
    margin-left: 0;
    margin-right: 0.75rem;
}

/* Animation classes */
.opacity-100 {
    opacity: 1;
}

.transition-opacity {
    transition-property: opacity;
    transition-timing-function: cubic-bezier(0.4, 0, 0.2, 1);
    transition-duration: 300ms;
}
</style>