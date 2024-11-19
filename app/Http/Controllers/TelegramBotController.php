<?php

namespace App\Http\Controllers;

use App\Services\DictionaryService;
use App\Services\TelegramService;
use Illuminate\Http\Request;
use Telegram\Bot\Laravel\Facades\Telegram;
use Illuminate\Support\Facades\Log;

class TelegramBotController extends Controller
{
    protected $dictionaryService;
    protected $telegramService;

    public function __construct(
        DictionaryService $dictionaryService,
        TelegramService $telegramService
    ) {
        $this->dictionaryService = $dictionaryService;
        $this->telegramService = $telegramService;
    }

    public function webhook(Request $request)
    {
        try {
            $update = Telegram::commandsHandler(true);

            $message = $update->getMessage();
            $chatId = $message->getChat()->getId();
            $text = $message->getText();

            $definitions = $this->dictionaryService->searchWord($text);

            if ($definitions->isEmpty()) {
                $this->telegramService->sendMessage($chatId, "No definition found for '{$text}'.");
            } else {
                $response = "Definitions for '{$text}':\n\n";
                foreach ($definitions as $definition) {
                    $response .= "• {$definition->definition}\n";
                }
                $this->telegramService->sendMessage($chatId, $response);
            }

            return response('OK', 200);
        } catch (\Exception $e) {
            Log::error('Telegram Webhook Error: ' . $e->getMessage());
            return response('Error processing request', 500);
        }
    }
}
