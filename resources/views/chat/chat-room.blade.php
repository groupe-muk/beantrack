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
            <form id="chat-form" 
                  action="javascript:void(0);" 
                  method="POST"
                  data-current-user-id="{{ $currentUser->id }}"
                  data-receiver-id="{{ $otherUser->id }}"
                  data-csrf-token="{{ csrf_token() }}">
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

<!-- Load external chat JavaScript -->
<script src="{{ asset('resources/js/chat.js') }}?v={{ time() }}" defer></script>
@endsection
