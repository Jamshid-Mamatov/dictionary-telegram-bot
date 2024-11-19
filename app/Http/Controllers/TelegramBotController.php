<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Telegram\Bot\Laravel\Facades\Telegram;

class TelegramBotController extends Controller
{
    //

    public function webhook(Request $request)
    {
        Log::info('Telegram webhook hit', [
            'payload' => $request->all()
        ]);

        $update = Telegram::commandsHandler(true);

        return response()->json(['status' => 'success']);
    }

    public function debug(Request $request)
    {
        return response()->json([
            'webhook_url' => config('telegram.bots.mybot.webhook_url'),
            'bot_token' => 'configured: ' . (! empty(config('telegram.bots.mybot.token'))),
        ]);
    }
}
