<?php

namespace App\Http\Controllers;

use App\Models\Message;
use BotMan\BotMan\BotMan;
use Illuminate\Http\Request;
use BotMan\BotMan\BotManFactory;
use BotMan\BotMan\Drivers\DriverManager;

class BotManController extends Controller
{
    public function handle(Request $request)
    {
        DriverManager::loadDriver(\BotMan\Drivers\Web\WebDriver::class);

        $botman = BotManFactory::create([]);

        $messageText = trim($request->input('message', ''));

        Message::create([
            'sender' => 'user',
            'message' => $messageText,
        ]);

        $botman->hears('.*', function (BotMan $bot) use ($messageText) {
            if (strtolower($messageText) === 'hi' || strtolower($messageText) === 'hello') {
                $replyMessage = 'Halo! Saya Chatbot TCUC!';
            } else {
                $replyMessage = 'Maaf saya tidak memahami itu.';
            }

            Message::create([
                'sender' => 'bot',
                'message' => $replyMessage,
            ]);

            $bot->reply($replyMessage);
        });

        $botman->listen();
    }
}
