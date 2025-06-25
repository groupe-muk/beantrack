import Echo from 'laravel-echo';
import Pusher from 'pusher-js';

window.Pusher = Pusher;

// Only initialize Echo if Pusher keys are available
const pusherKey = import.meta.env.VITE_PUSHER_APP_KEY;
const pusherCluster = import.meta.env.VITE_PUSHER_APP_CLUSTER;

if (pusherKey && pusherCluster) {
    window.Echo = new Echo({
        broadcaster: 'pusher',
        key: pusherKey,
        cluster: pusherCluster,
        forceTLS: true,
        csrfToken: document.querySelector('meta[name="csrf-token"]')?.content,
        authEndpoint: '/broadcasting/auth',
        // Enable debug mode for easier troubleshooting
        enabledTransports: ['ws', 'wss'],
    });
    
    // Add global error handler for Echo
    window.Echo.connector.pusher.connection.bind('error', function(err) {
        console.error('Pusher connection error:', err);
    });
    
    // Add connection success handler for debugging
    window.Echo.connector.pusher.connection.bind('connected', function() {
        console.log('Pusher connected successfully!');
    });
    
    // Subscribe to connection state change events
    window.Echo.connector.pusher.connection.bind('state_change', function(states) {
        console.log(`Pusher connection: ${states.previous} -> ${states.current}`);
    });
    
    // Add debugging for auth failures
    window.Echo.connector.pusher.connection.bind('auth_failed', function(err) {
        console.error('Pusher auth failed:', err);
    });
    
    // Make Pusher instance globally accessible for direct binding
    window.pusher = window.Echo.connector.pusher;
    
    console.log('✅ Echo and Pusher initialized successfully');
} else {
    console.warn('Pusher configuration missing. Real-time messaging disabled.');
    // Create a dummy Echo object to prevent errors
    window.Echo = {
        private: () => ({
            listen: () => {}
        }),
        channel: () => ({
            listen: () => {}
        })
    };
}