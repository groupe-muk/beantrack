/** @type {import('tailwindcss').Config} */
export default {
    content: [
        "./resources/**/*.blade.php",
        "./resources/**/*.js", 
        "./node_modules/flowbite/**/*.js" 
    ],
    theme: {
        extend: {
            colors: {
                
                'white' : '#FFFFFF',
                'light-brown': '#854B10',
                'soft-gray': '#e5e8eb',
                'mild-gray': '#B0A6A6',
                'warm-gray':'#332E2E',
                'dark-background' : '#171717',
                'coffee-brown': '#4A2924',
                'light-gray': '#F5F2F0',
                'brown': '#47301f',
                'soft-brown': '#8F6E56',
                'cream-brown': '#D3BEAC',
                'off-white': '#F1ECE7',

            },
            fontFamily: {
                
                sans: ['Work Sans', 'ui-sans-serif', 'system-ui', '-apple-system', 'BlinkMacSystemFont', '"Segoe UI"', 'Roboto', '"Helvetica Neue"', 'Arial', '"Noto Sans"', 'sans-serif', '"Apple Color Emoji"', '"Segoe UI Emoji"', '"Segoe UI Symbol"', '"Noto Color Emoji"'],
                serif: ['Inter', 'serif'],
            },
        },
    },
    plugins: [
        require('flowbite/plugin'),

    ],
};