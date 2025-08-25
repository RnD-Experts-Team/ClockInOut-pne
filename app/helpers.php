<?php

if (!function_exists('getCurrentLocale')) {
    /**
     * Get the current application locale
     */
    function getCurrentLocale()
    {
        return app()->getLocale();
    }
}

if (!function_exists('isRtl')) {
    /**
     * Check if current locale is RTL
     */
    function isRtl()
    {
        return in_array(app()->getLocale(), ['ar', 'he', 'fa']);
    }
}

if (!function_exists('getDirection')) {
    /**
     * Get text direction for current locale
     */
    function getDirection()
    {
        return isRtl() ? 'rtl' : 'ltr';
    }
}