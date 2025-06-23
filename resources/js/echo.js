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
        // Enable debug mode for easier troubleshooting
        enabledTransports: ['ws', 'wss'],
    });
    
    // Add global error handler for Echo
    window.Echo.connector.pusher.connection.bind('error', function(err) {
        console.error('Pusher connection error:', err);
    });
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