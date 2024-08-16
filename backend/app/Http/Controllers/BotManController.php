<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use App\Models\Message;
use BotMan\BotMan\BotMan;
use Illuminate\Http\Request;
use BotMan\BotMan\BotManFactory;
use Illuminate\Support\Facades\Http;
use BotMan\BotMan\Drivers\DriverManager;

class BotManController extends Controller
{
    public function handle(Request $request)
    {
        DriverManager::loadDriver(\BotMan\Drivers\Web\WebDriver::class);

        $botman = BotManFactory::create([]);
        $messageText = trim($request->input('message', ''));

        if ($messageText === 'load_previous_session') {
            $previousMessages = Message::all()->map(function ($message) {
                return [
                    'text' => $message->message,
                    'sender' => $message->sender,
                ];
            });

            return response()->json(['messages' => $previousMessages]);
        }

        if (strtolower($messageText) === 'hapus chat') {
            Message::truncate();
            $replyMessage = "Semua riwayat chat telah dihapus.";
            $botman->say($replyMessage, $botman->getUser()->getId());

            Message::create([
                'sender' => 'bot',
                'message' => $replyMessage,
            ]);

            return;
        }

        Message::create([
            'sender' => 'user',
            'message' => $messageText,
        ]);

        $botman->hears('.*', function (BotMan $bot) use ($messageText) {
            $messageTextLower = strtolower($messageText);
            $replyMessage = '';

            if (in_array($messageTextLower, ['hi', 'hello', 'hai', 'halo', 'helo'])) {
                $replyMessage = 'Halo! Saya Chatbot TCUC!';
            } elseif (in_array($messageTextLower, ['jam berapa sekarang?', 'pukul berapa sekarang?', 'sekarang jam berapa?'])) {
                $replyMessage = 'Sekarang pukul ' . Carbon::now()->format('H:i') . '.';
            } elseif (in_array($messageTextLower, ['hari ini tanggal berapa?', 'tanggal berapa sekarang?'])) {
                $replyMessage = 'Hari ini tanggal ' . Carbon::now()->format('d-m-Y') . '.';
            } elseif (in_array($messageTextLower, ['hari apa sekarang?'])) {
                $replyMessage = 'Hari ini adalah hari ' . Carbon::now()->locale('id')->dayName . '.';
            } elseif (in_array($messageTextLower, ['cuaca hari ini bagaimana?', 'bagaimana cuaca hari ini?'])) {
                $replyMessage = 'Cuaca hari ini cerah dengan suhu sekitar 27°C.';
            } elseif (in_array($messageTextLower, ['dimana saya sekarang?', 'lokasi saya dimana?'])) {
                $replyMessage = 'Maaf, saya tidak bisa mengetahui lokasi Anda saat ini.';
            } elseif (in_array($messageTextLower, ['bagaimana kondisi lalu lintas?', 'lalu lintas sekarang bagaimana?'])) {
                $replyMessage = 'Lalu lintas saat ini lancar.';
            } elseif (in_array($messageTextLower, ['cara menurunkan demam?', 'bagaimana menurunkan demam?'])) {
                $replyMessage = 'Untuk menurunkan demam, Anda bisa minum banyak air, istirahat yang cukup, dan jika perlu, minum obat penurun demam seperti paracetamol.';
            } elseif (in_array($messageTextLower, ['news', 'berita', 'berita terbaru apa hari ini?'])) {
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

    protected function loadPreviousSession()
    {
        $messages = Message::orderBy('created_at', 'asc')->get();
        $responseMessages = [];

        foreach ($messages as $message) {
            $responseMessages[] = [
                'sender' => $message->sender,
                'message' => $message->message,
            ];
        }

        return response()->json(['messages' => $responseMessages]);
    }
}
