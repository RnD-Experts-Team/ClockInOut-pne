<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\Api\LanguageService;

class LanguageController extends Controller
{
    protected $languageService;

    public function __construct(LanguageService $languageService)
    {
        $this->languageService = $languageService;
    }

    /**
     * Switch application language
     */
    public function switch(Request $request)
    {
        $language = $request->input('language', 'ar');

        $result = $this->languageService->switchLanguage($language);

        return response()->json($result);
    }
}