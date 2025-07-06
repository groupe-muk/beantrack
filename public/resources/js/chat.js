/**
 * BeanTrack Chat System - External JavaScript
 * 
 * This file handles all chat functionality including:
 * - Real-time message sending and receiving
 * - Direct Pusher WebSocket connections
 * - Message UI management
 * - Error handling and fallback mechanisms
 */

document.addEventListener('DOMContentLoaded', function() {
    console.log('🚀 Chat system initializing...');
    
    // Get DOM elements
    const chatForm = document.getElementById('chat-form');
    const messageInput = document.getElementById('message');
    const messagesContainer = document.getElementById('chat-messages');
    
    if (!chatForm || !messageInput || !messagesContainer) {
        console.error('❌ Required chat elements not found');
        return;
    }
    
    // Get data from form attributes
    const currentUserId = chatForm.dataset.currentUserId;
    const receiverId = chatForm.dataset.receiverId;
    const csrfToken = chatForm.dataset.csrfToken;
    
    console.log('📋 Chat initialization data:', {
        currentUserId,
        receiverId,
        csrfToken: csrfToken ? 'present' : 'missing'
    });
    
    // Enable debug mode if localStorage flag is set
    const debugMode = localStorage.getItem('chat_debug') === 'true';
    if (debugMode) {
        console.log('🐛 Chat debug mode enabled');
    }
    
    /**
     * Append a message to the chat container
     */
    function appendMessage(html) {
        try {
            const tempContainer = document.createElement('div');
            tempContainer.innerHTML = html.trim();
            
            const chatBubble = tempContainer.querySelector('.flex');
            
            if (chatBubble) {
                messagesContainer.appendChild(chatBubble);
                if (debugMode) console.log('✅ Message bubble appended successfully');
            } else {
                messagesContainer.insertAdjacentHTML('beforeend', html);
                if (debugMode) console.log('✅ Message HTML inserted directly');
            }
            
            // Scroll to bottom smoothly
            messagesContainer.scrollTop = messagesContainer.scrollHeight;
            
        } catch (error) {
            console.error('❌ Error appending message:', error);
        }
    }
    
    /**
     * Show user-friendly error notification
     */
    function showErrorNotification(message, details = null) {
        const errorDiv = document.createElement('div');
        errorDiv.className = 'fixed bottom-4 right-4 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded shadow-md z-50';
        errorDiv.innerHTML = `
            <div class="flex items-start">
                <svg class="w-5 h-5 mr-2 mt-0.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path>
                </svg>
                <div>
                    <p class="font-semibold">${message}</p>
                    ${details ? `<p class="text-sm mt-1">${details}</p>` : ''}
                </div>
            </div>
        `;
        
        document.body.appendChild(errorDiv);
        
        // Auto-remove after 5 seconds
        setTimeout(() => {
            if (errorDiv.parentNode) {
                errorDiv.remove();
            }
        }, 5000);
    }
    
    /**
     * Send message via AJAX
     */
    async function sendMessage(message, receiverId) {
        console.log('📤 Sending message:', { message: message.substring(0, 50) + '...', receiverId });
        
        try {
            const response = await fetch('/chat/send', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                    'Accept': 'text/html',
                    'X-CSRF-TOKEN': csrfToken
                },
                body: new URLSearchParams({
                    'message': message,
                    'receiver_id': receiverId,
                    '_token': csrfToken
                })
            });
            
            if (!response.ok) {
                const errorText = await response.text();
                throw new Error(`HTTP ${response.status}: ${response.statusText} - ${errorText}`);
            }
            
            const html = await response.text();
            console.log('✅ Message sent successfully');
            
            return html;
            
        } catch (error) {
            console.error('❌ Failed to send message:', error);
            throw error;
        }
    }
    
    /**
     * Handle incoming messages from Pusher
     */
    function handleIncomingMessage(data) {
        console.log('📥 Processing incoming message:', {
            messageId: data.messageId,
            senderId: data.user?.id,
            receiverId: data.receiverId,
            timestamp: data.timestamp
        });
        
        // Only process messages from the current chat partner
        if (data.user && data.user.id == receiverId) {
            console.log('✅ Message is from current chat partner, displaying...');
            
            // Fetch HTML for the message bubble
            fetch('/chat/receive', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'text/html',
                    'X-CSRF-TOKEN': csrfToken
                },
                body: JSON.stringify({
                    message: data.message,
                    user: data.user,
                    timestamp: data.timestamp,
                    messageId: data.messageId
                })
            })
            .then(response => {
                if (!response.ok) {
                    throw new Error(`HTTP ${response.status}: ${response.statusText}`);
                }
                return response.text();
            })
            .then(html => {
                appendMessage(html);
                console.log('✅ Incoming message displayed successfully');
                
                // Mark message as read since we're in the chat
                markMessagesAsRead(data.user.id);
                
                // Trigger unread count update for other pages
                window.dispatchEvent(new CustomEvent('message-received'));
            })
            .catch(error => {
                console.error('❌ Error displaying incoming message:', error);
                showErrorNotification('Failed to display incoming message', error.message);
            });
        } else {
            console.log('ℹ️ Message not from current chat partner, ignoring display but updating counts');
            // Still trigger unread count update
            window.dispatchEvent(new CustomEvent('message-received'));
        }
    }
    
    /**
     * Mark messages as read
     */
    function markMessagesAsRead(senderId) {
        if (!senderId) return;
        
        fetch('/chat/mark-read', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken
            },
            body: JSON.stringify({
                sender_id: senderId
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                console.log(`✅ Marked ${data.count} messages as read`);
            }
        })
        .catch(error => {
            console.error('❌ Error marking messages as read:', error);
        });
    }
    
    /**
     * Setup real-time connection using direct Pusher binding
     * This approach is more reliable than Laravel Echo .listen() method
     */
    function setupRealTimeConnection() {
        console.log('🔌 Setting up real-time connection...');
        
        try {
            // Method 1: Try Laravel Echo first
            if (window.Echo && typeof window.Echo.private === 'function') {
                console.log('📡 Attempting Laravel Echo connection...');
                
                const channel = window.Echo.private(`chat.${currentUserId}`);
                
                // Use .notification() method for better reliability
                channel.notification((notification) => {
                    console.log('🔔 Echo notification received:', notification);
                    if (notification.type === 'App\\Events\\MessageSent') {
                        handleIncomingMessage(notification);
                    }
                });
                
                // Also try .listen() as fallback
                channel.listen('.message.sent', function(data) {
                    console.log('👂 Echo listen triggered:', data);
                    handleIncomingMessage(data);
                });
                
                console.log('✅ Laravel Echo connection established');
            }
            
            // Method 2: Direct Pusher binding (more reliable)
            if (window.Pusher && window.pusher) {
                console.log('📡 Setting up direct Pusher connection...');
                
                const channelName = `private-chat.${currentUserId}`;
                const channel = window.pusher.subscribe(channelName);
                
                // Bind to the specific event
                channel.bind('App\\Events\\MessageSent', function(data) {
                    console.log('📨 Direct Pusher message received:', data);
                    handleIncomingMessage(data);
                });
                
                // Also bind to alternative event names
                channel.bind('message.sent', function(data) {
                    console.log('📨 Direct Pusher message.sent received:', data);
                    handleIncomingMessage(data);
                });
                
                // Check if channel is successfully subscribed
                channel.bind('pusher:subscription_succeeded', function(members) {
                    console.log('✅ Direct Pusher subscription successful for:', channelName);
                });
                
                channel.bind('pusher:subscription_error', function(error) {
                    console.error('❌ Direct Pusher subscription error:', error);
                });
                
                console.log('✅ Direct Pusher binding established');
            }
            
            // Check if any real-time method is available
            if (!window.Echo && !window.pusher) {
                console.warn('⚠️ No real-time connection available (Echo or Pusher missing)');
                showErrorNotification(
                    'Real-time messaging unavailable', 
                    'Messages will still be saved, but you may need to refresh to see new messages.'
                );
            }
            
        } catch (error) {
            console.error('❌ Error setting up real-time connection:', error);
            showErrorNotification('Real-time connection failed', error.message);
        }
    }
    
    /**
     * Handle form submission
     */
    chatForm.addEventListener('submit', async function(e) {
        e.preventDefault();
        
        const message = messageInput.value.trim();
        
        if (!message) {
            console.log('ℹ️ Empty message, ignoring submit');
            return;
        }
        
        if (message.length > 1000) {
            showErrorNotification('Message too long', 'Please keep messages under 1000 characters.');
            return;
        }
        
        // Disable input while sending
        const submitButton = chatForm.querySelector('button[type="submit"]');
        const originalButtonText = submitButton.innerHTML;
        
        messageInput.disabled = true;
        submitButton.disabled = true;
        submitButton.innerHTML = '<span>Sending...</span>';
        
        try {
            const html = await sendMessage(message, receiverId);
            appendMessage(html);
            messageInput.value = '';
            console.log('✅ Message sent and displayed');
            
        } catch (error) {
            console.error('❌ Message send failed:', error);
            showErrorNotification(
                'Failed to send message',
                'The message was not sent. Please try again.'
            );
        } finally {
            // Re-enable input
            messageInput.disabled = false;
            submitButton.disabled = false;
            submitButton.innerHTML = originalButtonText;
            messageInput.focus();
        }
    });
    
    /**
     * Handle Enter key for sending messages
     */
    messageInput.addEventListener('keypress', function(e) {
        if (e.key === 'Enter' && !e.shiftKey) {
            e.preventDefault();
            chatForm.dispatchEvent(new Event('submit'));
        }
    });
    
    /**
     * Initialize everything
     */
    function initializeChat() {
        console.log('🎯 Initializing chat system...');
        
        // Focus message input
        messageInput.focus();
        
        // Scroll to bottom initially
        messagesContainer.scrollTop = messagesContainer.scrollHeight;
        
        // Setup real-time connection
        setupRealTimeConnection();
        
        // Mark existing messages as read
        if (receiverId) {
            markMessagesAsRead(receiverId);
        }
        
        console.log('✅ Chat system initialized successfully');
    }
    
    // Start the chat system
    initializeChat();
    
    // Global error handler for unhandled promises
    window.addEventListener('unhandledrejection', function(event) {
        console.error('🚫 Unhandled promise rejection in chat:', event.reason);
    });
});

/**
 * Global chat utilities
 */
window.ChatUtils = {
    enableDebug: () => {
        localStorage.setItem('chat_debug', 'true');
        console.log('🐛 Chat debug mode enabled. Refresh the page to see debug logs.');
    },
    
    disableDebug: () => {
        localStorage.removeItem('chat_debug');
        console.log('🔇 Chat debug mode disabled.');
    },
    
    checkConnection: () => {
        if (window.pusher) {
            console.log('Pusher connection state:', window.pusher.connection.state);
            console.log('Pusher channels:', Object.keys(window.pusher.channels.channels));
        }
        if (window.Echo) {
            console.log('Echo available:', !!window.Echo);
        }
    }
};