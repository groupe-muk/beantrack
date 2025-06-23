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
        </div>
        
        <!-- Chat Input -->
        <div class="border-t p-4">
            <form id="chat-form" action="" method="POST">
                @csrf
                <input type="hidden" name="receiver_id" id="receiver_id" value="{{ $otherUser->id }}">
                <div class="flex flex-row items-center rounded-xl bg-white w-full px-4">
                    <div class="flex-grow">
                        <div class="relative w-full">
                            <input type="text" id="message" name="message" class="flex w-full border rounded-xl focus:outline-none focus:border-indigo-300 pl-4 h-10" placeholder="Type your message..." />
                        </div>
                    </div>
                    <div class="ml-4">
                        <button type="submit" class="flex items-center justify-center bg-indigo-500 hover:bg-indigo-600 rounded-xl text-white px-4 py-1 flex-shrink-0">
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
        const chatForm = document.getElementById('chat-form');
        const messageInput = document.getElementById('message');
        const messagesContainer = document.getElementById('chat-messages');        //append message to chat
        function appendMessage(html) {
            // Create a temporary container to parse the HTML
            const tempContainer = document.createElement('div');
            tempContainer.innerHTML = html;
            
            // Find the actual chat bubble element (first child with flex class)
            const chatBubble = tempContainer.querySelector('.flex');
            
            if (chatBubble) {
                // Only append the chat bubble element, not the entire HTML
                messagesContainer.appendChild(chatBubble);
            } else {
                // Fallback to the old method if we can't find a proper chat bubble
                messagesContainer.insertAdjacentHTML('beforeend', html);
            }
            
            // Scroll to bottom
            messagesContainer.scrollTop = messagesContainer.scrollHeight;
        }        // Listen for messages from Echo on a private channel
        window.Echo.private(`chat.${{{ $currentUser->id }}}`)
            .listen('.message.sent', function(data) {
                // Only process messages from the current chat partner
                if (data.user.id == {{ $otherUser->id }}) {
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
                    });
                }
            });        //Handle form submission
        chatForm.addEventListener('submit', function(e) {
            e.preventDefault();

            const message = messageInput.value;
            if(!message.trim()) return;
            
            fetch('/chat/send', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                    'Accept': 'text/html',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                },
                body: new URLSearchParams({
                    'message': message,
                    'receiver_id': receiverId,
                    '_token': document.querySelector('meta[name="csrf-token"]').content
                })
            }).then(response => response.text())
            .then(html => {
                appendMessage(html);
                messageInput.value = '';
            })
        })
    });

</script>
@endsection
