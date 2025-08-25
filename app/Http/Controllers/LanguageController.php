<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;

class LanguageController extends Controller
{
    /**
     * Switch the application language
     */
    public function switch(Request $request)
    {
        $language = $request->input('language', 'ar');
        
        // Validate language
        if (!in_array($language, ['ar', 'en'])) {
            $language = 'ar';
        }
        
        // Store in session
        Session::put('locale', $language);
        
        return response()->json([
            'success' => true,
            'language' => $language,
            'direction' => $language === 'ar' ? 'rtl' : 'ltr'
        ]);
    }
}