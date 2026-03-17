<?php
namespace App\Services\Api;

use Illuminate\Support\Facades\Session;

class LanguageService
{
    /**
     * Switch application language
     */
    public function switchLanguage(string $language = 'ar'): array
    {
        $allowedLanguages = ['ar', 'en', 'es'];

        if (!in_array($language, $allowedLanguages)) {
            $language = 'ar';
        }

        Session::put('locale', $language);

        return [
            'success' => true,
            'language' => $language,
            'direction' => $language === 'ar' ? 'rtl' : 'ltr'
        ];
    }
}