<?php

namespace App\Http\Controllers;

use App\Models\Message;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class ChatTestController extends Controller
{
    public function testSend(Request $request)
    {
        try {
            // Get current user
            $user = Auth::user();
            
            if (!$user) {
                return response()->json(['error' => 'Not authenticated'], 401);
            }
            
            // Get all users except the current one
            $users = User::where('id', '!=', $user->id)->get(['id', 'name', 'email']);
            
            // If a message was submitted, send it
            $sent = false;
            $error = null;
            
            if ($request->isMethod('post')) {
                $message = $request->input('message');
                $receiverId = $request->input('receiver_id');
                
                Log::info('Test message attempt', [
                    'message' => $message,
                    'receiver_id' => $receiverId,
                    'user_id' => $user->id,
                ]);
                
                try {                    // Store the message in the database, using Laravel's automatic timestamps
                    $messageModel = new Message();
                    $messageModel->sender_id = $user->id;
                    $messageModel->receiver_id = $receiverId;
                    $messageModel->content = $message;
                    $messageModel->save();
                    
                    Log::info('Test message saved', ['message_id' => $messageModel->id]);
                    
                    $sent = true;
                } catch (\Exception $e) {
                    $error = $e->getMessage();
                    Log::error('Test message error', [
                        'error' => $e->getMessage(),
                        'trace' => $e->getTraceAsString()
                    ]);
                }
            }
            
            return view('chat.test', [
                'users' => $users,
                'sent' => $sent,
                'error' => $error,
                'currentUser' => $user
            ]);
        } catch (\Exception $e) {
            Log::error('Test controller error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
}
