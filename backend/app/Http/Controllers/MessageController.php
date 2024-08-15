<?php

namespace App\Http\Controllers;

use App\Models\Message;
use Illuminate\Http\Request;

class MessageController extends Controller
{
    public function index()
    {
        return response()->json(Message::all());
    }

    public function store(Request $request)
    {
        $message = new Message;
        $message->sender = $request->sender;
        $message->message = $request->message;
        $message->save();

        $botReply = new Message;
        $botReply->sender = 'bot';
        $botReply->message = 'Hi, saya chatbot TCUC!';
        $botReply->save();

        return response()->json([
            'userMessage' => $message,
            'botReply' => $botReply
        ]);
    }
}
