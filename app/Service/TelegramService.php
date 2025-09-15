<?php

namespace App\Service;

use Longman\TelegramBot\Telegram;
use Longman\TelegramBot\Request;

class TelegramService
{
    public static function sendMessage(string $text)
    {
        Request::initialize(new Telegram('7620073499:AAEMY6RTvtqSyBf8U4OPpJ4e68jgBYX5JEo', 'MyNoticeBot'));
        // https://api.telegram.org/bot token/getUpdates
        Request::sendMessage([
            'chat_id' => 6110160562,
            'text' => $text,
            'parse_mode' => 'HTML',
        ]);
    }
}
