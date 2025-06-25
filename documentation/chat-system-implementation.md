# BeanTrack Chat System Implementation

This document provides a comprehensive guide to the chat system implemented in the BeanTrack application. The chat system enables real-time communication between suppliers, wholesalers, and administrators in the coffee supply chain management platform.

## Table of Contents

1. [Overview](#overview)
2. [Database Structure](#database-structure)
3. [Models](#models)
4. [Controllers](#controllers)
5. [Views](#views)
6. [Events and Broadcasting](#events-and-broadcasting)
7. [Routes](#routes)
8. [JavaScript Implementation](#javascript-implementation)
9. [Notification System](#notification-system)
10. [Troubleshooting](#troubleshooting)

## Overview

The chat system in BeanTrack facilitates direct communication between various users within the platform. Key features include:

- Private messaging between users (suppliers, wholesalers, administrators)
- Real-time message delivery using Laravel Echo and Pusher
- Unread message notifications
- Message history and chat persistence
- Responsive UI for desktop and mobile devices

## Database Structure

### Messages Table

The chat system relies on a `messages` table in the database, which stores all conversation data between users.

**Migration: 2025_06_16_000013_create_messages_table.php**
```php
Schema::create('messages', function (Blueprint $table) {
    $table->string('id', 6)->primary(); // Message ID with format M00001
    $table->string('sender_id', 6);     // User ID of message sender
    $table->string('receiver_id', 6);   // User ID of message recipient
    $table->text('content');            // Message content
    $table->timestamp('created_at')->useCurrent(); // When message was sent
    $table->timestamp('updated_at')->useCurrent()->useCurrentOnUpdate(); // Last update timestamp
    $table->foreign('sender_id')->references('id')->on('users')->onDelete('cascade');
    $table->foreign('receiver_id')->references('id')->on('users')->onDelete('cascade');
});
```

The migration also creates a MySQL trigger that automatically generates the message ID:

```sql
CREATE TRIGGER before_messages_insert 
BEFORE INSERT ON messages 
FOR EACH ROW 
BEGIN 
    DECLARE last_id INT; 
    SELECT CAST(SUBSTRING(id, 2) AS UNSIGNED) 
    INTO last_id 
    FROM messages 
    ORDER BY id DESC LIMIT 1; 
    SET NEW.id = CONCAT('M', LPAD(COALESCE(last_id + 1, 1), 5, '0')); 
END
```

### Additional Migrations

**Migration: 2024_06_20_add_updated_at_to_messages_table.php**
This migration adds the `updated_at` column to support Laravel's automatic timestamp management:

```php
Schema::table('messages', function (Blueprint $table) {
    $table->timestamp('updated_at')->useCurrent()->useCurrentOnUpdate();
});
```

**Migration: 2025_06_24_add_is_read_to_messages_table.php**
This migration adds an `is_read` column to track read status of messages:

```php
Schema::table('messages', function (Blueprint $table) {
    $table->boolean('is_read')->default(0)->after('content');
});
```

### Jobs Table

The chat system uses Laravel's queue system for broadcasting messages. The `jobs` table supports this functionality:

**Migration: 2024_06_25_create_jobs_table.php**
```php
Schema::create('jobs', function (Blueprint $table) {
    $table->bigIncrements('id');
    $table->string('queue')->index();
    $table->longText('payload');
    $table->unsignedTinyInteger('attempts');
    $table->unsignedInteger('reserved_at')->nullable();
    $table->unsignedInteger('available_at');
    $table->unsignedInteger('created_at');
});
```

## Models

### Message Model

The `Message` model handles database interactions for chat messages.

**app/Models/Message.php**
```php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Message extends Model
{
    protected $table = 'messages';
    protected $keyType = 'string';       // ID is a string, not integer
    public $incrementing = false;        // ID is not auto-incrementing (handled by trigger)
    protected $fillable = [
        'id', 'sender_id', 'receiver_id', 'content', 'is_read'
    ];
    
    /**
     * Indicates if the model should be timestamped.
     */
    public $timestamps = true;

    /**
     * Relationship with the sender user
     */
    public function sender()
    {
        return $this->belongsTo(User::class, 'sender_id');
    }

    /**
     * Relationship with the receiver user
     */
    public function receiver()
    {
        return $this->belongsTo(User::class, 'receiver_id');
    }

    /**
     * Get count of unread messages for a user
     */
    public static function getUnreadCount($userId)
    {
        return self::where('receiver_id', $userId)
                    ->where('is_read', 0)
                    ->count();
    }
}
```

## Controllers

### ChatController

The `ChatController` handles the chat functionality including displaying message history, sending messages, and managing read status.

**Key Methods:**

#### `index()`
Displays the inbox with a list of users and their most recent messages.

```php
public function index()
{
    $user = Auth::user();
    
    // Get suppliers and vendors based on user role
    if ($user->role === 'admin') {
        $suppliers = Supplier::with('user')->get();
        $vendors = Wholesaler::with('user')->get();
    } elseif ($user->role === 'supplier') {
        $vendors = User::where('role', 'vendor')->get();
        $admins = User::where('role', 'admin')->get();
        $suppliers = User::where('role', 'supplier')
                         ->where('id', '!=', $user->id)
                         ->get();
    } elseif ($user->role === 'vendor') {
        $suppliers = User::where('role', 'supplier')->get();
        $admins = User::where('role', 'admin')->get();
        $vendors = User::where('role', 'vendor')
                       ->where('id', '!=', $user->id)
                       ->get();
    }
    
    // Get most recent messages for each contact
    $recentMessages = DB::select(/* SQL query for recent messages */);
    
    return view('chat.inbox', [
        'user' => $user,
        'suppliers' => $suppliers,
        'vendors' => $vendors,
        'admins' => $admins ?? [],
        'recentMessages' => collect($recentMessages),
        'currentUser' => $user
    ]);
}
```

#### `chatRoom($userId)`
Displays the chat room with a specific user and marks incoming messages as read.

```php
public function chatRoom($userId)
{
    $currentUser = Auth::user();
    
    try {
        $otherUser = User::findOrFail($userId);
        
        // Handle redirects if necessary when using entity IDs instead of user IDs
        if (strtoupper(substr($userId, 0, 1)) === 'S') {
            // Redirect from supplier ID to associated user ID
            $supplier = Supplier::with('user')->find($userId);
            if ($supplier && $supplier->user) {
                return redirect()->route('chat.room', $supplier->user->id);
            }
        } elseif (strtoupper(substr($userId, 0, 1)) === 'W') {
            // Redirect from wholesaler ID to associated user ID
            $wholesaler = Wholesaler::with('user')->find($userId);
            if ($wholesaler && $wholesaler->user) {
                return redirect()->route('chat.room', $wholesaler->user->id);
            }
        }
        
        // Get message history between the two users
        $messages = Message::where(/* Query for messages between users */)->get();
        
        // Mark all messages from the other user as read
        Message::where('sender_id', $userId)
            ->where('receiver_id', $currentUser->id)
            ->where('is_read', 0)
            ->update(['is_read' => 1]);
        
        return view('chat.chat-room', [
            'messages' => $messages,
            'otherUser' => $otherUser,
            'currentUser' => $currentUser
        ]);
    } catch (\Exception $e) {
        return back()->with('error', 'User not found. Please try again.');
    }
}
```

#### `send(Request $request)`
Handles sending messages and broadcasting them for real-time delivery.

```php
public function send(Request $request) {
    $message = $request->input('message');
    $receiverId = $request->input('receiver_id');
    $user = Auth::user();
    
    // Log request data for debugging
    Log::info('Chat message attempt', [/* Log data */]);
    
    try {
        // Store the message in the database
        $messageModel = Message::create([
            'sender_id' => $user->id,
            'receiver_id' => $receiverId,
            'content' => $message
        ]);
        
        // Broadcast the message to the receiver
        try {
            broadcast(new MessageSent($message, $user, $receiverId, $messageModel->id))
                ->toOthers();
        } catch (\Exception $broadcastError) {
            // Log but don't fail if broadcasting has an issue
            Log::warning('Broadcasting error (non-critical): ' . $broadcastError->getMessage());
        }

        // Return HTML for the sender's view
        return response()->view('components.chat.right-chat-bubble', [
            'message' => $message,
            'messageId' => $messageModel->id,
            'timestamp' => now()->format('h:i A')
        ])->header('Content-Type', 'text/html');
        
    } catch (\Exception $e) {
        Log::error('Message send error', [/* Error data */]);
        return response()->json(['error' => 'Failed to send message: '.$e->getMessage()], 500);
    }
}
```

#### `getUnreadCount()`
Returns the count of unread messages for the current user.

```php
public function getUnreadCount()
{
    $user = Auth::user();
    if (!$user) {
        return response()->json(['count' => 0]);
    }
    
    $count = Message::getUnreadCount($user->id);
    return response()->json(['count' => $count]);
}
```

#### `markAsRead(Request $request)`
Marks messages from a specific sender as read.

```php
public function markAsRead(Request $request)
{
    $senderId = $request->input('sender_id');
    $currentUser = Auth::user();
    
    if (!$currentUser || !$senderId) {
        return response()->json(['error' => 'Invalid request'], 400);
    }
    
    try {
        // Mark messages as read
        $count = Message::where('sender_id', $senderId)
            ->where('receiver_id', $currentUser->id)
            ->where('is_read', 0)
            ->update(['is_read' => 1]);
            
        return response()->json(['success' => true, 'count' => $count]);
    } catch (\Exception $e) {
        Log::error('Error marking messages as read', [/* Error data */]);
        return response()->json(['error' => $e->getMessage()], 500);
    }
}
```

## Views

### Chat Room View
**resources/views/chat/chat-room.blade.php**

The chat room view displays the conversation between two users and provides the message input interface.

Key components:
1. **Chat Header**: Shows the other user's name and role
2. **Messages Container**: Displays the message history
3. **Chat Input**: Form for sending new messages
4. **JavaScript**: Handles real-time updates and message sending

```blade
@extends('layouts.main-view')
@section('content')
<div class="container mx-auto px-4 py-6">
    <!-- Chat Header -->
    <div class="border-b p-4 flex items-center justify-between">
        <!-- User info and back button -->
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
                    <input type="text" id="message" name="message" class="flex w-full border rounded-xl focus:outline-none focus:border-indigo-300 pl-4 h-10" placeholder="Type your message..." autofocus />
                </div>
                <div class="ml-4">
                    <button type="submit" class="bg-indigo-500 hover:bg-indigo-600 rounded-xl text-white px-4 py-1 flex-shrink-0">
                        <span>Send</span>
                        <span class="ml-2">
                            <!-- Send icon -->
                        </span>
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const chatForm = document.getElementById('chat-form');
        const messageInput = document.getElementById('message');
        const messagesContainer = document.getElementById('chat-messages');
        
        // Function to append message to chat
        function appendMessage(html) {
            // Create temp container and extract the chat bubble
            const tempContainer = document.createElement('div');
            tempContainer.innerHTML = html.trim();
            
            const chatBubble = tempContainer.querySelector('.flex');
            
            if (chatBubble) {
                messagesContainer.appendChild(chatBubble);
            } else {
                messagesContainer.insertAdjacentHTML('beforeend', html);
            }
            
            // Scroll to bottom
            messagesContainer.scrollTop = messagesContainer.scrollHeight;
        }
        
        // Listen for messages via Echo/Pusher
        try {
            if (window.Echo && typeof window.Echo.private === 'function') {
                const channel = window.Echo.private(`chat.${{{ $currentUser->id }}}`);
                
                channel.listen('.message.sent', function(data) {
                    // Trigger unread count update
                    window.dispatchEvent(new CustomEvent('message-received'));
                    
                    if (data.user.id == {{ $otherUser->id }}) {
                        // Fetch HTML for the message bubble
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
                            
                            // Mark message as read since we're in the chat
                            fetch('/chat/mark-read', {
                                method: 'POST',
                                headers: {
                                    'Content-Type': 'application/json',
                                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                                },
                                body: JSON.stringify({
                                    sender_id: data.user.id
                                })
                            }).catch(err => console.error('Error marking as read:', err));
                        })
                        .catch(error => {
                            console.error('Error receiving message:', error);
                        });
                    }
                });
                
                console.log('Real-time chat connected successfully');
            } else {
                console.warn('Echo not properly configured');
            }
        } catch (error) {
            console.error('Error setting up realtime connection:', error);
        }

        // Form submission handler
        chatForm.addEventListener('submit', function(e) {
            e.preventDefault();
            
            const message = messageInput.value;
            const receiverId = document.getElementById('receiver_id').value;
            
            if(!message.trim()) return;
            
            fetch('{{ route('chat.send') }}', {
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
            })
            .then(response => {
                if (!response.ok) {
                    throw new Error('Network response was not ok: ' + response.status);
                }
                return response.text();
            })
            .then(html => {
                appendMessage(html);
                messageInput.value = '';
            })
            .catch(error => {
                console.error('Error sending message:', error);
                // Display error toast message
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
                
                // Auto-remove after 5 seconds
                setTimeout(() => {
                    errorMessage.remove();
                }, 5000);
            })
        });
    });
</script>
@endsection
```

### Chat Components

#### Right Chat Bubble
**resources/views/components/chat/right-chat-bubble.blade.php**

Displays messages sent by the current user.

#### Left Chat Bubble
**resources/views/components/chat/left-chat-bubble.blade.php**

Displays messages received from other users.

### Inbox View
**resources/views/chat/inbox.blade.php**

Displays the list of users with whom the current user can chat.

## Events and Broadcasting

### MessageSent Event
**app/Events/MessageSent.php**

This event is dispatched when a message is sent. Laravel Echo and Pusher use this event for real-time broadcasting.

```php
namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class MessageSent implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;
    public $message;
    public $user;
    public $receiverId;
    public $timestamp;
    public $messageId;

    /**
     * Create a new event instance.
     */
    public function __construct($message, $user, $receiverId, $messageId = null)
    {
        $this->message = $message;
        $this->user = $user;
        $this->receiverId = $receiverId;
        $this->messageId = $messageId ?? uniqid();
        $this->timestamp = now()->format(('h:i A'));
    }

    /**
     * Get the channels the event should broadcast on.
     */
    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('chat.' . $this->receiverId),
        ];
    }

    /**
     * The event name to broadcast.
     */
    public function broadcastAs(): string 
    {
        return 'message.sent';
    }

    /**
     * Get the data to broadcast.
     */
    public function broadcastWith(): array
    {
        return [
            'message' => $this->message,
            'user' => $this->user,
            'receiverId' => $this->receiverId,
            'timestamp' => $this->timestamp,
            'messageId' => $this->messageId,
            'unread' => true
        ];
    }
}
```

### Broadcasting Configuration

#### Echo Setup
**resources/js/echo.js**

```javascript
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
```

#### Channel Authorization
**routes/channels.php**

```php
// Private chat channel authorization
Broadcast::channel('chat.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});
```

## Routes

**routes/web.php**

```php
// Chat Routes
Route::get('/chat', [ChatController::class, 'index'])->name('chat.index');
Route::get('/chat/unread', [ChatController::class, 'getUnreadCount'])->name('chat.unread');
Route::post('/chat/mark-read', [ChatController::class, 'markAsRead'])->name('chat.mark-read');
Route::get('/chat/{userId}', [ChatController::class, 'chatRoom'])->name('chat.room');
Route::post('/chat/send',[ChatController::class, 'send'])->name('chat.send');

// Receive message component (for displaying incoming messages)
Route::post('/chat/receive', function (Request $request) {
    try {
        // Get the input data
        $message = $request->input('message');
        $userData = $request->input('user');
        $timestamp = $request->input('timestamp');
        $messageId = $request->input('messageId', uniqid());
        
        // Create a user object from the data
        // The user data comes as an array from JavaScript, but the component expects an object
        $user = (object) $userData;
        
        // Return only the chat bubble component, not a full layout
        return response()->view('components.chat.left-chat-bubble', [
            'message' => $message,
            'user' => $user,
            'timestamp' => $timestamp,
            'messageId' => $messageId
        ])->header('Content-Type', 'text/html');
        
    } catch (\Exception $e) {
        \Log::error('Chat receive error', [
            'error' => $e->getMessage(),
            'request_data' => $request->all(),
            'trace' => $e->getTraceAsString()
        ]);
        
        return response('Error loading message', 500);
    }
})->name('chat.receive');
```

## Notification System

### Unread Message Indicator

The chat system includes an unread message notification badge in the sidebar:

**app/Helpers/MenuHelper.php**
```php
[
    'href' => '/chat',
    'icon' => '/* SVG icon code */',
    'label' => 'Inbox',
    'badge' => '<span id="unread-message-count" class="hidden ml-2 bg-red-600 text-white text-xs font-bold px-2.5 py-1 rounded-full animate-pulse">0</span>'
]
```

**resources/views/layouts/main-view.blade.php**
```javascript
// Function to update unread message count
function updateUnreadCount() {
    fetch('{{ route('chat.unread') }}')
        .then(response => response.json())
        .then(data => {
            const badge = document.getElementById('unread-message-count');
            if (badge) {
                if (data.count > 0) {
                    badge.textContent = data.count;
                    badge.classList.remove('hidden');
                } else {
                    badge.classList.add('hidden');
                }
            }
        })
        .catch(error => console.error('Error fetching unread count:', error));
}

// Update on page load and periodically
document.addEventListener('DOMContentLoaded', function() {
    updateUnreadCount();
    // Update every 30 seconds
    setInterval(updateUnreadCount, 30000);
});

// Listen for message events
window.addEventListener('message-received', function() {
    updateUnreadCount();
});
```

## Troubleshooting

### Common Issues

1. **Messages not appearing in real-time**
   - **Cause**: Usually caused by issues with Pusher configuration or the `jobs` table missing
   - **Solution**: Ensure Pusher keys are properly set in the environment, and the `jobs` table has been created via migration

2. **"Table 'jobs' doesn't exist" error**
   - **Cause**: Missing migration for the jobs table required by Laravel's queue system
   - **Solution**: Run the migration for creating the jobs table: `php artisan migrate`

3. **Messages appearing only after page refresh**
   - **Cause**: JavaScript errors or broadcasting issues
   - **Solution**: Check browser console for errors, verify Pusher connection, and check Laravel logs

4. **Unread message count not updating**
   - **Cause**: JavaScript errors or route issues
   - **Solution**: Check browser console and ensure the 'chat.unread' route is accessible

5. **"HTTP 500: Internal Server Error" on `/chat/receive` endpoint**
   - **Cause**: JavaScript sends user data as an object, but Blade component expects it as a model with `->` syntax
   - **Solution**: Convert the user array to an object in the route: `$user = (object) $userData;`
   - **Symptoms**: Real-time messaging works (messages save to database) but incoming messages don't display
   - **Debug**: Check Laravel logs for detailed error information

### System Requirements

- PHP 8.x
- Laravel 10.x
- MySQL 8.x
- Pusher account and credentials (for real-time functionality)
- Laravel Echo and Pusher-js libraries

### Performance Considerations

1. **Database Indexing**
   - Consider adding indices on `sender_id`, `receiver_id`, and `is_read` columns for better query performance

2. **Pagination**
   - For users with large message histories, implement pagination

3. **Message Archiving**
   - Consider archiving older messages to improve performance for active conversations

### Important Implementation Notes

#### Data Type Consistency
When working with JavaScript-to-PHP data transmission in real-time chat:

**Issue**: JavaScript objects vs PHP object access
```javascript
// JavaScript sends user data as an object
body: JSON.stringify({
    message: data.message,
    user: data.user, // This is a JavaScript object: {id: 'U00013', name: 'John'}
    timestamp: data.timestamp
})
```

```php
// PHP route receives it as an array, but Blade expects object notation
$userData = $request->input('user'); // This is now an array
$user = (object) $userData; // Convert to object for Blade component
```

**Solution**: Always convert JavaScript objects to PHP objects when passing to Blade components:
```php
Route::post('/chat/receive', function (Request $request) {
    try {
        $message = $request->input('message');
        $userData = $request->input('user');
        $user = (object) $userData; // Convert array to object
        
        return response()->view('components.chat.left-chat-bubble', [
            'user' => $user, // Now accessible as $user->name in Blade
        ]);
    } catch (\Exception $e) {
        \Log::error('Chat receive error', ['error' => $e->getMessage()]);
        return response('Error loading message', 500);
    }
});
```

## Conclusion

The BeanTrack chat system provides a robust real-time communication platform for users within the coffee supply chain management ecosystem. The implementation uses Laravel's event broadcasting system with Pusher to deliver messages in real-time, while also providing fallback mechanisms to ensure messages are always saved even if real-time delivery fails.

The notification system ensures users are informed of new messages, and the read/unread tracking provides clear visibility into which messages need attention.
