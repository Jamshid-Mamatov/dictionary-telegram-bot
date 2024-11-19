<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;

class TelegramSetupCommand extends Command
{
    protected $signature = 'telegram:setup';
    protected $description = 'Setup and verify Telegram webhook';

    public function handle()
    {
        $this->info('Starting Telegram webhook setup...');

        // Get token from the correct config path
        $token = config('telegram.bots.mybot.token');

        if (empty($token) || $token === 'YOUR-BOT-TOKEN') {
            $this->error('TELEGRAM_BOT_TOKEN is not set properly in .env');
            return 1;
        }

        // Get ngrok URL
        $ngrokUrl = $this->ask('Enter your ngrok URL (without trailing slash):');
        $webhookUrl = "{$ngrokUrl}/api/telegram/webhook";

        // Update config
        config(['telegram.bots.mybot.webhook_url' => $webhookUrl]);

        $baseUrl = "https://api.telegram.org/bot{$token}";

        try {
            // Check current webhook
            $this->info('Checking current webhook status...');
            $currentWebhook = Http::get("{$baseUrl}/getWebhookInfo")->json();
            $this->info('Current webhook info:');
            $this->info(json_encode($currentWebhook, JSON_PRETTY_PRINT));

            // Set new webhook
            $this->info('Setting new webhook URL: ' . $webhookUrl);
            $response = Http::get("{$baseUrl}/setWebhook", [
                'url' => $webhookUrl,
                'allowed_updates' => ['message', 'callback_query'],
                'drop_pending_updates' => true
            ]);

            if ($response->successful()) {
                $this->info('✅ Webhook set successfully!');
                $this->info('Response: ' . json_encode($response->json(), JSON_PRETTY_PRINT));

                // Update .env file
                $this->updateEnvFile('TELEGRAM_WEBHOOK_URL', $webhookUrl);

                // Verify setup
                $verifyResponse = Http::get("{$baseUrl}/getWebhookInfo")->json();

                if ($verifyResponse['url'] === $webhookUrl) {
                    $this->info('✅ Webhook verification successful!');
                } else {
                    $this->error('❌ Webhook verification failed!');
                }
            } else {
                $this->error('❌ Failed to set webhook');
                $this->error('Response: ' . $response->body());
            }

        } catch (\Exception $e) {
            $this->error('❌ Error: ' . $e->getMessage());
            return 1;
        }

        $this->info("\n📝 Next steps:");
        $this->info("1. Try sending a message to your bot");
        $this->info("2. Check Laravel logs: tail -f storage/logs/laravel.log");
        $this->info("3. Monitor ngrok dashboard: http://127.0.0.1:4040");

        return 0;
    }

    /**
     * Update the .env file
     */
    protected function updateEnvFile($key, $value)
    {
        $path = base_path('.env');

        if (file_exists($path)) {
            $content = file_get_contents($path);

            // If the key exists, replace it
            if (strpos($content, $key) !== false) {
                $content = preg_replace(
                    "/^{$key}=.*/m",
                    "{$key}={$value}",
                    $content
                );
            } else {
                // If the key doesn't exist, add it
                $content .= "\n{$key}={$value}\n";
            }

            file_put_contents($path, $content);
        }
    }
}
