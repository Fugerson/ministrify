<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class MonitorBackups extends Command
{
    protected $signature = 'backup:monitor';
    protected $description = 'Check backup health and alert if issues found';

    private const MIN_BACKUP_SIZE = 50 * 1024; // 50KB minimum
    private const MAX_BACKUP_AGE_HOURS = 12;

    public function handle(): int
    {
        $backupDir = '/var/www/ministrify/backups';
        $issues = [];

        // Find latest backup
        $pattern = $backupDir . '/ministrify_*.sql.gz';
        $files = glob($pattern);

        if (empty($files)) {
            $issues[] = "No backup files found in {$backupDir}";
        } else {
            // Sort by modification time, newest first
            usort($files, fn($a, $b) => filemtime($b) - filemtime($a));
            $latestBackup = $files[0];
            $backupSize = filesize($latestBackup);
            $backupAge = (time() - filemtime($latestBackup)) / 3600; // hours

            $this->info("Latest backup: " . basename($latestBackup));
            $this->info("Size: " . number_format($backupSize / 1024, 2) . " KB");
            $this->info("Age: " . number_format($backupAge, 1) . " hours");

            // Check size
            if ($backupSize < self::MIN_BACKUP_SIZE) {
                $issues[] = "Backup too small: " . number_format($backupSize / 1024, 2) . " KB (min: " . (self::MIN_BACKUP_SIZE / 1024) . " KB)";
            }

            // Check age
            if ($backupAge > self::MAX_BACKUP_AGE_HOURS) {
                $issues[] = "Backup too old: " . number_format($backupAge, 1) . " hours (max: " . self::MAX_BACKUP_AGE_HOURS . " hours)";
            }
        }

        if (!empty($issues)) {
            $this->error("Backup issues found:");
            foreach ($issues as $issue) {
                $this->error("  - {$issue}");
            }

            $this->sendTelegramAlert($issues);
            return Command::FAILURE;
        }

        $this->info("Backup health: OK");
        return Command::SUCCESS;
    }

    private function sendTelegramAlert(array $issues): void
    {
        $botToken = config('services.telegram.alert_bot_token');
        $chatId = config('services.telegram.alert_chat_id');

        if (!$botToken || !$chatId) {
            Log::warning('Telegram alert credentials not configured');
            return;
        }

        $message = "🚨 *Ministrify Backup Alert*\n\n";
        $message .= "Server: `49.12.100.17`\n";
        $message .= "Time: " . now()->format('Y-m-d H:i:s') . "\n\n";
        $message .= "*Issues:*\n";
        foreach ($issues as $issue) {
            $message .= "• {$issue}\n";
        }

        try {
            Http::post("https://api.telegram.org/bot{$botToken}/sendMessage", [
                'chat_id' => $chatId,
                'text' => $message,
                'parse_mode' => 'Markdown',
            ]);
            $this->info("Telegram alert sent");
        } catch (\Exception $e) {
            Log::error('Failed to send Telegram alert', ['error' => $e->getMessage()]);
        }
    }
}
