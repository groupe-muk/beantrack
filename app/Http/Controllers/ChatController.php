<?php

namespace App\Http\Controllers;

use App\Events\MessageSent;
use App\Models\Message;
use App\Models\Supplier;
use App\Models\User;
use App\Models\Wholesaler;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ChatController extends Controller
{
    public function __construct()
    {
        // Auth middleware is applied in routes
    }

    /**
     * Show the inbox with user lists
     */
    public function index()
    {
        $user = Auth::user();
        Log::info('Current authenticated user:', ['user' => $user]);
   
        
        // Get suppliers and vendors for admins, or appropriate contacts for other roles
        $suppliers = [];
        $vendors = [];
       
        
        if ($user->role === 'admin') {

            // Eager load user relationships
            $suppliers = Supplier::with('user')->get();
            
            Log::info('Suppliers query result:', ['count' => count($suppliers), 'suppliers' => $suppliers->toArray()]);
            $vendors = Wholesaler::with('user')->get();
            Log::info("Vendors query result: ", ['count' => count($vendors), 'vendors' => $vendors]);
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
        
        // Get most recent messages with each user for previews
        $recentMessages = DB::select("
            SELECT m.*, u.name as sender_name, u.role as sender_role 
            FROM (
                SELECT 
                    CASE 
                        WHEN sender_id = ? THEN receiver_id
                        ELSE sender_id
                    END as contact_id,
                    MAX(created_at) as latest_message_time
                FROM messages
                WHERE sender_id = ? OR receiver_id = ?
                GROUP BY contact_id
            ) as latest
            JOIN messages m ON (
                (m.sender_id = latest.contact_id AND m.receiver_id = ?) OR
                (m.sender_id = ? AND m.receiver_id = latest.contact_id)
            )
            JOIN users u ON u.id = latest.contact_id
            WHERE m.created_at = latest.latest_message_time
            ORDER BY latest_message_time DESC
        ", [$user->id, $user->id, $user->id, $user->id, $user->id]);
        
        return view('chat.inbox', [
            'user' => $user,
            'suppliers' => $suppliers,
            'vendors' => $vendors,
            'admins' => $admins ?? [],
            'recentMessages' => collect($recentMessages),
            'currentUser' => $user
        ]);
    }
    
    /**
     * Show chat room with a specific user
     */
    public function chatRoom($userId)
    {
        $currentUser = Auth::user();
        
        try {
            $otherUser = User::findOrFail($userId);
            
            // If this is a supplier or wholesaler ID rather than a user ID, redirect to the correct user ID
            if (strtoupper(substr($userId, 0, 1)) === 'S') {
                // This is likely a supplier ID
                $supplier = Supplier::with('user')->find($userId);
                if ($supplier && $supplier->user) {
                    return redirect()->route('chat.room', $supplier->user->id);
                }
            } elseif (strtoupper(substr($userId, 0, 1)) === 'W') {
                // This is likely a wholesaler ID
                $wholesaler = Wholesaler::with('user')->find($userId);
                if ($wholesaler && $wholesaler->user) {
                    return redirect()->route('chat.room', $wholesaler->user->id);
                }
            }
            
            // Get all messages between these two users
            $messages = Message::where(function($query) use ($currentUser, $userId) {
                    $query->where('sender_id', $currentUser->id)
                        ->where('receiver_id', $userId);
                })
                ->orWhere(function($query) use ($currentUser, $userId) {
                    $query->where('sender_id', $userId)
                        ->where('receiver_id', $currentUser->id);
                })
                ->orderBy('created_at', 'asc')
                ->with(['sender', 'receiver'])
                ->get();
                
            // Mark all messages from the other user as read
            Message::where('sender_id', $userId)
                ->where('receiver_id', $currentUser->id)
                ->where('is_read', 0)
                ->update(['is_read' => 1]);
                
            // Log that messages have been marked as read
            Log::info('Messages marked as read', [
                'user_id' => $currentUser->id,
                'other_user_id' => $userId,
                'count' => $messages->where('sender_id', $userId)->where('is_read', 0)->count()
            ]);
            
            return view('chat.chat-room', [
                'messages' => $messages,
                'otherUser' => $otherUser,
                'currentUser' => $currentUser
            ]);
        } catch (\Exception $e) {
            return back()->with('error', 'User not found. Please try again.');
        }
    }

    /**
     * Send a message to another user
     */
    public function send(Request $request) {
        $message = $request->input('message');
        $receiverId = $request->input('receiver_id');
        $user = Auth::user();
        
        // Log request data for debugging
        Log::info('Chat message attempt', [
            'message' => $message,
            'receiver_id' => $receiverId,
            'user_id' => $user ? $user->id : null,
        ]);
        
        if (!$user) {
            Log::error('Message send failed: User not authenticated');
            return response()->json(['error' => 'User not authenticated'], 401);
        }
        
        try {
            // Store the message in the database using the Message model with automatic timestamps
            $messageModel = Message::create([
                'sender_id' => $user->id,
                'receiver_id' => $receiverId,
                'content' => $message
            ]);
            
            Log::info('Message saved to database', ['message_id' => $messageModel->id]);

            try {
                // Try broadcasting immediately without queuing
                $event = new MessageSent($message, $user, $receiverId, $messageModel->id);
                
                // Use broadcast()->toOthers() but ensure it's not queued
                broadcast($event)->toOthers();
                
                Log::info('Message broadcast successfully', [
                    'channel' => 'chat.' . $receiverId,
                    'event' => 'message.sent',
                    'sender_id' => $user->id,
                    'receiver_id' => $receiverId,
                    'message_id' => $messageModel->id,
                    'broadcast_data' => [
                        'message' => $message,
                        'user' => $user->toArray(),
                        'receiverId' => $receiverId,
                        'messageId' => $messageModel->id
                    ]
                ]);
            } catch (\Exception $broadcastError) {
                // Log the broadcast error but don't fail the request
                Log::warning('Broadcasting error (non-critical): ' . $broadcastError->getMessage(), [
                    'exception' => $broadcastError,
                    'trace' => $broadcastError->getTraceAsString()
                ]);
            }

            // Return the message as a right chat bubble for sender
            return response()->view('components.chat.right-chat-bubble', [
                'message' => $message,
                'messageId' => $messageModel->id,
                'timestamp' => now()->format('h:i A')
            ])->header('Content-Type', 'text/html');
            
        } catch (\Exception $e) {
            Log::error('Message send error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json(['error' => 'Failed to send message: '.$e->getMessage()], 500);
        }

    }

    public function fetchSuppliers() {
        $suppliers = Supplier::all();
        return response()->json($suppliers);
    }
    
    /**
     * Get recent messages for each contact
     */
    private function getRecentMessages($user)
    {
        // Get the most recent message between the current user and each other user
        $recentMessages = Message::where('sender_id', $user->id)
            ->orWhere('receiver_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->get()
            ->groupBy(function ($message) use ($user) {
                // Group by the other user's ID (whether sender or receiver)
                return $message->sender_id == $user->id ? $message->receiver_id : $message->sender_id;
            })
            ->map(function ($messages) {
                // Get the most recent message for each user
                return $messages->first();
            });
            
        return $recentMessages;
    }

    /**
     * Get count of unread messages for current user
     */
    public function getUnreadCount()
    {
        $user = Auth::user();
        if (!$user) {
            return response()->json(['count' => 0]);
        }
        
        $count = Message::getUnreadCount($user->id);
        return response()->json(['count' => $count]);
    }

    /**
     * Mark messages from a specific sender as read
     */
    public function markAsRead(Request $request)
    {
        $senderId = $request->input('sender_id');
        $currentUser = Auth::user();
        
        if (!$currentUser || !$senderId) {
            return response()->json(['error' => 'Invalid request'], 400);
        }
        
        try {
            // Mark all messages from the sender to the current user as read
            $count = Message::where('sender_id', $senderId)
                ->where('receiver_id', $currentUser->id)
                ->where('is_read', 0)
                ->update(['is_read' => 1]);
                
            Log::info('Messages marked as read via AJAX', [
                'user_id' => $currentUser->id,
                'sender_id' => $senderId,
                'count' => $count
            ]);
                
            return response()->json(['success' => true, 'count' => $count]);
        } catch (\Exception $e) {
            Log::error('Error marking messages as read', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
}
