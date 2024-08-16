<?php

namespace App\Http\Controllers;

use App\Models\Message;
use BotMan\BotMan\BotMan;
use Illuminate\Http\Request;
use BotMan\BotMan\BotManFactory;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;
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
                $replyMessage = 'Halo! Saya Chatbot TCUC! Silahkan gunakan kata kunci Berita untuk mengetahui berita terbaru!';
            } elseif (strtolower($messageText) === 'news' || strtolower($messageText) === 'berita') {
                $replyMessage = $this->fetchLatestNews();
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

    protected function fetchLatestNews()
    {
        $apiKey = config('services.newsapi.api_key');
        $response = Http::withHeaders([
            'x-api-key' => $apiKey,
        ])->get('https://newsapi.org/v2/top-headlines', [
            'country' => 'id',
        ]);

        Log::info('News API Response: ', ['response' => $response->json()]);

        if ($response->successful() && $response->json('status') === 'ok') {
            $articles = $response->json('articles');
            if (!empty($articles)) {
                $replyMessage = "<p>Berikut adalah 5 berita terbaru:</p><ul>";

                foreach (array_slice($articles, 0, 5) as $index => $article) {
                    $title = $article['title'];
                    $url = $article['url'];
                    $source = $article['source']['name'];
                    $replyMessage .= "<li><strong>" . ($index + 1) . ". " . $title . "</strong><br />";
                    $replyMessage .= "<em>Sumber: " . $source . "</em><br />";
                    $replyMessage .= "<a href='" . $url . "' target='_blank' style='text-decoration: none;'>Baca selengkapnya</a></li><br />";
                }
                $replyMessage .= "</ul>";

                return $replyMessage;
            } else {
                return "Maaf, tidak ada berita yang dapat ditampilkan saat ini.";
            }
        } else {
            return "Maaf, tidak ada berita yang dapat ditampilkan saat ini.";
        }
    }
}
