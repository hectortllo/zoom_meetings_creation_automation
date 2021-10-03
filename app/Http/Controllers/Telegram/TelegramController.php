<?php

namespace App\Http\Controllers\Telegram;

use App\Http\Controllers\Controller;

class TelegramController extends Controller
{
    const URL_API = "https://api.telegram.org/bot";
    public static function sendMessage($chatId, $message)
    {
        $token = env('TELEGRAM_BOT_API_TOKEN', '');

        $data = [
            'chat_id' => $chatId,
            'text' => $message
        ];

        $url = self::URL_API . $token . "/sendMessage?";
        $response = file_get_contents($url . http_build_query($data));

        return $response;
    }
}
