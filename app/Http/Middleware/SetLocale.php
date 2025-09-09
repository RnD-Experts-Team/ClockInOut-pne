<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Session;

class SetLocale
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        // Check for language in session first
        $locale = Session::get('locale');
        
        // If not in session, check if it's being set via request
        if (!$locale && $request->has('lang')) {
            $locale = $request->get('lang');
            Session::put('locale', $locale);
        }
        
        // If still no locale, default to Arabic
        if (!$locale) {
            $locale = 'ar';
            Session::put('locale', $locale);
        }
        
        // Validate locale
        if (!in_array($locale, ['en', 'ar', 'es'])) {
            $locale = 'ar';
        }
        
        // Set the application locale
        App::setLocale($locale);
        
        return $next($request);
    }
}