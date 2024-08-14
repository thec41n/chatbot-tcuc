<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Models\ChatHistory;

Route::prefix('api')->group(function () {
    Route::post('/chatbot', function (Request $request) {
        $message = $request->input('message');
        $reply = 'Hi! Im Chatbot TCUC!';

        ChatHistory::create([
            'sender' => 'user',
            'message' => $message,
        ]);

        ChatHistory::create([
            'sender' => 'bot',
            'message' => $reply,
        ]);

        return response()->json(['reply' => $reply]);
    });
});