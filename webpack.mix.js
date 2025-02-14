const mix = require('laravel-mix');

mix.postCss('resources/css/app.css', 'public/css', [
    require('tailwindcss'),
    require('@tailwindcss/typography'),
    require('@tailwindcss/aspect-ratio'),

]);
