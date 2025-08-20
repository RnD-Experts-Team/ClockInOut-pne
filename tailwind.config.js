import defaultTheme from 'tailwindcss/defaultTheme';
import forms from '@tailwindcss/forms';


/** @type {import('tailwindcss').Config} */
module.exports = {
    content: ['./vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './storage/framework/views/*.php',
        './resources/views/**/*.blade.php',],
        theme: {
          extend: {
            fontFamily: {
              sans: [
                'Inter',
                'ui-sans-serif',
                'system-ui',
                '-apple-system',
                'BlinkMacSystemFont',
                'Segoe UI',
                'Roboto',
                'Helvetica Neue',
                'Arial',
                'Noto Sans',
                'sans-serif',
                'Apple Color Emoji',
                'Segoe UI Emoji',
                'Segoe UI Symbol',
                'Noto Color Emoji'
              ],
              serif: [
                'Merriweather',
                'ui-serif',
                'Georgia',
                'Cambria',
                'Times New Roman',
                'Times',
                'serif'
              ],
              mono: [
                'JetBrains Mono',
                'ui-monospace',
                'SFMono-Regular',
                'Menlo',
                'Monaco',
                'Consolas',
                'Liberation Mono',
                'Courier New',
                'monospace'
              ],
            },
            colors: {
              'primary': '#ff671b',
              'primary-dark': '#2563EB',
            },
            spacing: {
              '128': '32rem',
              '144': '36rem',
            },
            borderRadius: {
              '4xl': '2rem',
            },
          },
        },
        plugins: [],
  }
