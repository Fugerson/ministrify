<?php

namespace App\Console\Commands;

use App\Services\WebPushService;
use Illuminate\Console\Command;

class GenerateVapidKeys extends Command
{
    protected $signature = 'vapid:generate';
    protected $description = 'Generate VAPID keys for Web Push notifications';

    public function handle(): int
    {
        $this->info('Generating VAPID keys...');

        try {
            $keys = WebPushService::generateVapidKeys();

            $this->newLine();
            $this->info('Add these keys to your .env file:');
            $this->newLine();
            $this->line("VAPID_PUBLIC_KEY={$keys['public_key']}");
            $this->line("VAPID_PRIVATE_KEY={$keys['private_key']}");
            $this->newLine();
            $this->warn('Keep the private key secret!');

            return Command::SUCCESS;
        } catch (\Exception $e) {
            $this->error('Failed to generate VAPID keys: ' . $e->getMessage());
            return Command::FAILURE;
        }
    }
}
