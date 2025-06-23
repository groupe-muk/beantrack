<?php

namespace App\Http\Controllers;

use App\Events\MessageSent;
use App\Models\Message;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class MessageAttachmentController extends Controller
{
    /**
     * Store a new file attachment for a message
     */
    public function store(Request $request)
    {
        $request->validate([
            'file' => 'required|file|max:10240', // 10MB max
            'receiver_id' => 'required|exists:users,id'
        ]);

        $user = Auth::user();
        $receiverId = $request->input('receiver_id');
        
        // Store the file
        $path = $request->file('file')->store('message_attachments', 'public');
        $fileName = basename($path);
        $fileType = $request->file('file')->getMimeType();
        
        // Create a message with the file info
        $message = Message::create([
            'sender_id' => $user->id,
            'receiver_id' => $receiverId,
            'content' => json_encode([
                'type' => 'attachment',
                'file_name' => $fileName,
                'file_path' => $path,
                'file_type' => $fileType
            ])
        ]);
        
        // Broadcast the message
        broadcast(new MessageSent($message->content, $user, $receiverId, $message->id))->toOthers();
        
        return response()->json([
            'success' => true,
            'file_url' => Storage::url($path),
            'message_id' => $message->id
        ]);
    }
    
    /**
     * Get an attachment
     */
    public function show($fileName)
    {
        $path = 'message_attachments/' . $fileName;
        
        if (!Storage::disk('public')->exists($path)) {
            abort(404);
        }
        
        return response()->file(Storage::disk('public')->path($path));
    }
}
