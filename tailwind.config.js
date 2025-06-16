/** @type {import('tailwindcss').Config} */
export default {
    content: [
        "./resources/**/*.blade.php",
        "./resources/**/*.js", 
        "./node_modules/flowbite/**/*.js" 
    ],
    theme: {
        extend: {
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