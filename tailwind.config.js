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
                'dashboard-text-light': '#ECE6DF',
                'dashboard-light': '#47301F',
                'hover-brown': '#5A3D29',
                'status-text-red': '#991B1B',
                'status-background-red': '#FEE2E2',
                'status-text-green': '#166534',
                'status-background-green': '#DCFCE7',
                'status-text-orange': '#92400E',
                'status-background-orange': '#FEF3C7',
                'status-text-blue': '#1E40AF',
                'status-background-blue': '#D8EAFE',
                'status-text-gray': '#1F2937',
                'status-background-gray': '#F3F4F6',
                'progress-bar-green': '#22C55E',
                'progress-bar-orange': '#F59E08',
                'progress-bar-red': '#EF4444',
                'pale-brown': '#EFEBE7',


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