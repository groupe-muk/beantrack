@extends('layouts.main-view')
@section('content')
<div class="container mx-auto px-4 py-6">
    <div class="bg-white rounded-lg shadow-md flex flex-col h-[calc(100vh-200px)]">
        <!-- Chat Header -->
        <div class="border-b p-4 flex items-center justify-between">
            <div class="flex items-center">
                <a href="{{ route('chat.index') }}" class="text-gray-500 mr-3">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                    </svg>
                </a>
                <div class="flex items-center">
                    <div class="w-10 h-10 rounded-full bg-blue-100 flex items-center justify-center text-blue-600 font-semibold">
                        {{ strtoupper(substr($otherUser->name, 0, 1)) }}
                    </div>
                    <div class="ml-3">
                        <h3 class="font-semibold">{{ $otherUser->name }}</h3>
                        <p class="text-xs text-gray-500">{{ ucfirst($otherUser->role) }}</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Chat Messages -->
        <div id="chat-messages" class="flex-1 overflow-y-auto p-4 space-y-4">
            @foreach($messages as $message)
            @if($message->sender_id === $currentUser->id)
            <x-chat.right-chat-bubble :message="$message->content" :messageId="$message->id" :timestamp="$message->created_at->format('h:i A')" />
            @else
            <x-chat.left-chat-bubble :message="$message->content" :messageId="$message->id" :user="$message->sender" :timestamp="$message->created_at->format('h:i A')" />
            @endif
            @endforeach
        </div> <!-- Chat Input -->
        <div class="border-t p-4">
            <form id="chat-form" action="javascript:void(0);" method="POST">
                @csrf
                <input type="hidden" name="receiver_id" id="receiver_id" value="{{ $otherUser->id }}">
                <div class="flex flex-row items-center rounded-xl bg-white w-full px-4">
                    <div class="flex-grow">
                        <div class="relative w-full">
                            <input type="text" id="message" name="message" class="flex w-full border rounded-xl focus:outline-none focus:border-light-brown pl-4 h-10" placeholder="Type your message..." autofocus />
                        </div>
                    </div>
                    <div class="ml-4">
                        <button type="submit" class="flex items-center justify-center bg-coffee-brown hover:bg-light-brown rounded-xl text-white px-4 py-1 flex-shrink-0">
                            <span>Send</span>
                            <span class="ml-2">
                                <svg class="w-4 h-4 transform rotate-45 -mt-px" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"></path>
                                </svg>
                            </span>
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>


<script>
    document.addEventListener('DOMContentLoaded', function() {
        console.log("Inner script running");
        const chatForm = document.getElementById('chat-form');
        const messageInput = document.getElementById('message');
        const messagesContainer = document.getElementById('chat-messages');
        
        // Add debugging info
        console.log('Chat room initialized');
        const currentUserId = {!! json_encode($currentUser->id) !!};
        const otherUserId = {!! json_encode($otherUser->id) !!};
        console.log('Current user ID:', currentUserId);
        console.log('Other user ID:', otherUserId);
        
        // Function to append message to chat
        function appendMessage(html) {
            // Log the received HTML for debugging
            console.log('Received HTML response to append:', html);

            // Create a temporary container to parse the HTML
            const tempContainer = document.createElement('div');
            tempContainer.innerHTML = html.trim();

            // Find the correct chat bubble element - could be right or left bubble
            // Direct check for the main div
            if (tempContainer.firstElementChild) {
                console.log('Found first element child to append:', tempContainer.firstElementChild.tagName, tempContainer.firstElementChild.className);
                messagesContainer.appendChild(tempContainer.firstElementChild);
            } else {
                console.log('No element child found, using insertAdjacentHTML');
                // Fallback to the old method if we can't find a proper element
                messagesContainer.insertAdjacentHTML('beforeend', html);
            }            // Scroll to bottom
            messagesContainer.scrollTop = messagesContainer.scrollHeight;
        }
        
        // Listen for messages from Echo on a private channel
        try {
            if (window.Echo && typeof window.Echo.private === 'function') {
                // Add debug info to help troubleshoot                console.log('Setting up Echo private channel:', `chat.${currentUserId}`);
                const channel = window.Echo.private(`chat.${currentUserId}`);
                
                // Add debugging for channel events
                channel.subscribed(() => {
                    console.log('Successfully subscribed to channel:', `chat.${currentUserId}`);
                });
                
                channel.error((error) => {
                    console.error('Channel subscription error:', error);
                });
                
                channel.listen('message.sent', function(data) {// Log incoming message data
                console.log('Received message via Echo:', data);
                
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
                            console.error('Error receiving message:', error);                        });
                }
            });
            
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
        // Ensure the form doesn't submit traditionally
        e.preventDefault();

        const message = messageInput.value;
        const receiverId = document.getElementById('receiver_id').value;

        if (!message.trim()) {
            console.log('Empty message, not sending');
            return;
        }
        console.log('Sending message:', message, 'to receiver:', receiverId);

        // Use URLSearchParams instead of FormData for x-www-form-urlencoded format
        const formData = new URLSearchParams();
        formData.append('message', message);
        formData.append('receiver_id', receiverId);
        formData.append('_token', document.querySelector('meta[name="csrf-token"]').content);
        
        console.log('Submitting form data:', {
            message,
            receiver_id: receiverId,
            csrf: document.querySelector('meta[name="csrf-token"]').content
        });
        
        fetch('{{ route('chat.send') }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                        'Accept': 'text/html',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: formData
                }).then(response => {
                console.log('Response received:', response.status, response.statusText);
                if (!response.ok) {
                    throw new Error('Network response was not ok: ' + response.status);
                }
                return response.text();
            })
            .then(html => {
                console.log('Message sent successfully, received HTML response');
                // Check if the response is empty
                if (!html || html.trim() === '') {
                    console.error('Empty response received from server');
                    throw new Error('Empty response from server');
                }

                appendMessage(html);
                messageInput.value = '';
            }).catch(error => {
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
</script>
@endsection
