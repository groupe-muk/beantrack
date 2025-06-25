document.addEventListener('DOMContentLoaded', function() {
    console.log("Chat script loaded");
    
    const chatForm = document.getElementById('chat-form');
    const messageInput = document.getElementById('message');
    const messagesContainer = document.getElementById('chat-messages');
    
    // Get user IDs from the page data attributes
    const currentUserId = document.querySelector('[data-current-user]').dataset.currentUser;
    const otherUserId = document.querySelector('[data-other-user]').dataset.otherUser;
    
    console.log('Chat room initialized');
    console.log('Current user ID:', currentUserId);
    console.log('Other user ID:', otherUserId);
    
    // Function to append message to chat
    function appendMessage(html) {
        console.log('Received HTML response to append:', html);

        const tempContainer = document.createElement('div');
        tempContainer.innerHTML = html.trim();

        if (tempContainer.firstElementChild) {
            console.log('Found first element child to append:', tempContainer.firstElementChild.tagName, tempContainer.firstElementChild.className);
            messagesContainer.appendChild(tempContainer.firstElementChild);
        } else {
            console.log('No element child found, using insertAdjacentHTML');
            messagesContainer.insertAdjacentHTML('beforeend', html);
        }
        
        // Scroll to bottom
        messagesContainer.scrollTop = messagesContainer.scrollHeight;
    }    // Listen for messages from Echo on a channel
    try {
        if (window.Echo) {
            console.log('Setting up Echo channel:', `chat.${currentUserId}`);
            // Using public channel for debugging
            const channel = window.Echo.channel(`chat.${currentUserId}`);
            
            // Add debugging for channel events
            channel.subscribed(() => {
                console.log('Successfully subscribed to channel:', `chat.${currentUserId}`);
                console.log('🧪 Testing channel by listening for any events...');
            });
            
            channel.error((error) => {
                console.error('Channel subscription error:', error);
            });
            
            // Use direct Pusher binding since Echo.listen() isn't working
            if (window.Echo.connector && window.Echo.connector.pusher) {
                const pusherChannel = window.Echo.connector.pusher.channel(`chat.${currentUserId}`);
                if (pusherChannel) {
                    console.log('🔄 Setting up direct Pusher listener for message.sent...');
                    pusherChannel.bind('message.sent', function(data) {
                        console.log('🚨 DIRECT PUSHER LISTENER TRIGGERED - Event received!');
                        console.log('🔔 Received message via direct Pusher:', data);
                        console.log('📍 Message from user ID:', data.user.id);
                        console.log('📍 Expected from user ID:', otherUserId);
                        console.log('📍 Current user ID:', currentUserId);
                        console.log('📍 Channel we are listening on:', `chat.${currentUserId}`);
                        console.log('📍 Full message data:', JSON.stringify(data, null, 2));
                    
                        // Dispatch event for unread count update
                        window.dispatchEvent(new CustomEvent('message-received'));
                    
                        // Only process messages from the current chat partner
                        if (data.user.id === otherUserId) {
                            fetch('/chat/receive', {
                                method: 'POST',
                                headers: {
                                    'Content-Type': 'application/json',
                                    'Accept': 'text/html',
                                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                                },
                                body: JSON.stringify({
                                    message: data.message,
                                    user: data.user,
                                    timestamp: data.timestamp
                                })
                            })
                            .then(response => response.text())
                            .then(html => {
                                appendMessage(html);

                                // Mark the message as read since we're in the chat room
                                fetch('/chat/mark-read', {
                                    method: 'POST',
                                    headers: {
                                        'Content-Type': 'application/json',
                                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                                    },
                                    body: JSON.stringify({
                                        sender_id: data.user.id
                                    })
                                }).catch(error => console.error('Error marking as read:', error));
                            })
                            .catch(error => {
                                console.error('Error receiving message:', error);
                            });
                        }
                    });
                } else {
                    console.error('Could not access Pusher channel directly');
                }
            } else {
                console.error('Could not access Pusher instance directly');
            }
            
            // Show connected status
            console.log('Real-time chat connected successfully');
        } else {
            console.warn('Echo is not properly initialized. Real-time messaging disabled.');
        }
    } catch (error) {
        console.error('Error setting up real-time messaging:', error);
    }

    // Handle form submission
    chatForm.addEventListener('submit', function(e) {
        console.log('Form submit triggered');
        e.preventDefault();

        const message = messageInput.value;
        const receiverId = document.getElementById('receiver_id').value;

        if (!message.trim()) {
            console.log('Empty message, not sending');
            return;
        }
        
        console.log('Sending message:', message, 'to receiver:', receiverId);

        const formData = new URLSearchParams();
        formData.append('message', message);
        formData.append('receiver_id', receiverId);
        formData.append('_token', document.querySelector('meta[name="csrf-token"]').content);
        
        console.log('Submitting form data:', {
            message,
            receiver_id: receiverId,
            csrf: document.querySelector('meta[name="csrf-token"]').content
        });
        
        // Get the chat send route from a data attribute
        const chatSendUrl = document.querySelector('[data-chat-send-url]').dataset.chatSendUrl;
        
        fetch(chatSendUrl, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
                'Accept': 'text/html',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            },
            body: formData
        })
        .then(response => {
            console.log('Response received:', response.status, response.statusText);
            if (!response.ok) {
                throw new Error('Network response was not ok: ' + response.status);
            }
            return response.text();
        })
        .then(html => {
            console.log('Message sent successfully, received HTML response');
            if (!html || html.trim() === '') {
                console.error('Empty response received from server');
                throw new Error('Empty response from server');
            }

            appendMessage(html);
            messageInput.value = '';
        })
        .catch(error => {
            console.error('Error sending message:', error);
            console.error('Error details:', error.message);
            
            // Display a toast message instead of an alert
            const errorMessage = document.createElement('div');
            errorMessage.className = 'bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded fixed bottom-4 right-4 shadow-md';
            errorMessage.innerHTML = `
                <div class="flex items-center">
                    <div class="py-1">
                        <svg class="w-6 h-6 mr-4" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z"></path>
                        </svg>
                    </div>
                    <div>
                        <p>Failed to send message. The message was saved but real-time updates may not work.</p>
                        <p class="text-sm">Try refreshing the page.</p>
                    </div>
                </div>
            `;
            document.body.appendChild(errorMessage);

            // Remove the error message after 5 seconds
            setTimeout(() => {
                errorMessage.remove();
            }, 5000);
        });
    });
});
