/** @type {import('tailwindcss').Config} */
export default {
    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './storage/framework/views/*.php',
        './resources/views/**/*.blade.php',
        './resources/js/**/*.js',
    ],
    theme: {
        extend: {
            colors: {
                // PT Pelayanan Energi Batam brand colors
                primary: {
                    50:  '#e8f7f9',
                    100: '#c5ecf1',
                    200: '#90d9e5',
                    300: '#54c4d6',
                    400: '#29afc4',
                    500: '#158b9f',  // main brand color
                    600: '#117a8b',
                    700: '#0d6373',
                    800: '#094e5a',
                    900: '#063b45',
                    950: '#032028',
                },
                surface: {
                    50:  '#f4f6f9',  // page background
                    100: '#e9ecf0',
                    200: '#d8dce5',
                    300: '#c4cad4',  // border & subtle dividers
                },
                charcoal: {
                    DEFAULT: '#333333',
                    light:   '#363636',
                    medium:  '#555555',
                    muted:   '#888888',
                },
            },
            fontFamily: {
                sans: ['Inter', 'system-ui', '-apple-system', 'sans-serif'],
            },
            boxShadow: {
                'card': '0 1px 3px 0 rgba(0,0,0,0.08), 0 1px 2px -1px rgba(0,0,0,0.06)',
                'card-md': '0 4px 6px -1px rgba(0,0,0,0.07), 0 2px 4px -2px rgba(0,0,0,0.05)',
            },
            borderRadius: {
                'xl': '0.75rem',
            },
        },
    },
    plugins: [
        require('@tailwindcss/forms'),
        require('@tailwindcss/typography'),
    ],
}
