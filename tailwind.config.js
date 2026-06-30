import defaultTheme from 'tailwindcss/defaultTheme';
import forms from '@tailwindcss/forms';

/** @type {import('tailwindcss').Config} */
export default {
    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './storage/framework/views/*.php',
        './resources/views/**/*.blade.php',
        './resources/js/**/*.js',
    ],
    safelist: [
        { pattern: /bg-primary-(50|100|200|300|400|500|600|700|800|900)/ },
        { pattern: /text-primary-(50|100|200|300|400|500|600|700|800|900)/ },
        { pattern: /border-primary-(50|100|200|300|400|500|600|700|800|900)/ },
        { pattern: /hover:bg-primary-(50|100|200|300|400|500|600|700|800|900)/, variants: ['hover'] },
        { pattern: /hover:text-primary-(50|100|200|300|400|500|600|700|800|900)/, variants: ['hover'] },
        'bg-primary',
        'text-primary',
    ],

    theme: {
        extend: {
            fontFamily: {
                sans: ['Figtree', ...defaultTheme.fontFamily.sans],
            },
            colors: {
                primary: {
                    DEFAULT: '#1e4d8b',
                    50:  '#eef3fb',
                    100: '#d5e3f4',
                    200: '#adc7e9',
                    300: '#7aaadc',
                    400: '#4d8ecf',
                    500: '#2e72bb',
                    600: '#1e4d8b',
                    700: '#1a4278',
                    800: '#163665',
                    900: '#122b52',
                },
            },
        },
    },

    plugins: [forms],
};
