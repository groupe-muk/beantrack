<?php

namespace App\Http\Controllers;

use App\Events\PusherBroadcast;
use Illuminate\Broadcasting\Broadcasters\PusherBroadcaster;
use Illuminate\Http\Request;

class PusherController extends Controller
{
    public function index () {
        return view('chat.chat-room');
    }
    public function broadcast (Request $request) {
        broadcast(new PusherBroadcast($request->get('message')))->toOthers();

        return view('broadcast', [
            'message' => $request->get('message')
        ]);
    }
    public function receive (Request $request) {
        return view('receive', [
            'message' => $request->get('message')
        ]);
    }
}
