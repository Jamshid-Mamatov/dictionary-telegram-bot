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


            if (str_starts_with($text, '/')) {
                $this->handleCommand($chatId, $text);
            } else {
                // Handle regular word
                $this->handleWord($chatId, $text);
            }

            return response('OK', 200);
        } catch (\Exception $e) {
            Log::error('Telegram Webhook Error: ' . $e->getMessage());
            return response('Error processing request', 500);
        }
    }

    private function handleCommand($chatId, $command){
        switch ($command) {
            case '/start':
                $message = "Welcome to the Dictionary Bot! 🎉\n\nSend me a word, and I'll give you its definition.";
                break;

            case '/help':
                $message = "Here are the commands you can use:\n" .
                    "/start - Start interacting with the bot\n" .
                    "/help - Get a list of available commands\n" .
                    "Just type any word to know its meaning!";
                break;

            default:
                $message = "Unknown command. Type /help to see available commands.";
                break;
        }

        $this->telegramService->sendMessage($chatId, $message);
    }


    private function handleWord($chatId, $word){
        // Validate input
        if (empty($word) || strlen($word) < 2) {
            $this->telegramService->sendMessage($chatId, 'Please send a valid word.');
            return;
        }

        // Search dictionary
        $definitions = $this->dictionaryService->searchWord($word);

        // Prepare response
        if ($definitions->isEmpty()) {
            $this->telegramService->sendMessage($chatId, "No definition found for '{$word}'.");
            return;
        }

        // Send definitions
        $responseText = "Definitions for '{$word}':\n\n";
        foreach ($definitions as $definition) {
            $responseText .= "• {$definition->definition}\n";
        }

        $this->telegramService->sendMessage($chatId, $responseText);
    }
}
