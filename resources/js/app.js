import './echo';
import ApexCharts from 'apexcharts';
window.ApexCharts = ApexCharts;

var themeToggleDarkIcon = document.getElementById('theme-toggle-dark-icon');
var themeToggleLightIcon = document.getElementById('theme-toggle-light-icon');

// Change the icons inside the button based on previous settings
if (localStorage.getItem('color-theme') === 'dark' || (!('color-theme' in localStorage) && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
    themeToggleLightIcon.classList.remove('hidden');
} else {
    themeToggleDarkIcon.classList.remove('hidden');
}

var themeToggleBtn = document.getElementById('theme-toggle');

// Mobile Sidebar Toggle Functionality
document.addEventListener('DOMContentLoaded', function() {
    const toggleSidebarMobileEl = document.getElementById('toggleSidebarMobile');
    const sidebarEl = document.getElementById('sidebar');
    const toggleSidebarMobileHamburger = document.getElementById('toggleSidebarMobileHamburger');
    const toggleSidebarMobileClose = document.getElementById('toggleSidebarMobileClose');

    if (toggleSidebarMobileEl) {
        toggleSidebarMobileEl.addEventListener('click', function() {
            // Toggle sidebar
            if (sidebarEl.classList.contains('hidden')) {
                sidebarEl.classList.remove('hidden');
                sidebarEl.classList.add('flex');
            } else {
                sidebarEl.classList.remove('flex');
                sidebarEl.classList.add('hidden');
            }

            // Toggle hamburger/close icons
            toggleSidebarMobileHamburger.classList.toggle('hidden');
            toggleSidebarMobileClose.classList.toggle('hidden');
        });
    }
});

themeToggleBtn.addEventListener('click', function() {

    // toggle icons inside button
    themeToggleDarkIcon.classList.toggle('hidden');
    themeToggleLightIcon.classList.toggle('hidden');

    // if set via local storage previously
    if (localStorage.getItem('color-theme')) {
        if (localStorage.getItem('color-theme') === 'light') {
            document.documentElement.classList.add('dark');
            localStorage.setItem('color-theme', 'dark');
        } else {
            document.documentElement.classList.remove('dark');
            localStorage.setItem('color-theme', 'light');
        }

    // if NOT set via local storage previously
    } else {
        if (document.documentElement.classList.contains('dark')) {
            document.documentElement.classList.remove('dark');
            localStorage.setItem('color-theme', 'light');
        } else {
            document.documentElement.classList.add('dark');
            localStorage.setItem('color-theme', 'dark');
        }
    }
    
});