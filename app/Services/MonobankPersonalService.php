<?php

namespace App\Services;

use App\Models\Church;
use App\Models\MonobankTransaction;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Crypt;
use Carbon\Carbon;

class MonobankPersonalService
{
    protected const API_BASE = 'https://api.monobank.ua';

    protected ?string $token = null;
    protected ?Church $church = null;

    public function __construct(?Church $church = null)
    {
        if ($church) {
            $this->setChurch($church);
        }
    }

    public function setChurch(Church $church): self
    {
        $this->church = $church;
        if ($church->monobank_token) {
            try {
                $this->token = Crypt::decryptString($church->monobank_token);
            } catch (\Exception $e) {
                $this->token = null;
            }
        }
        return $this;
    }

    public function setToken(string $token): self
    {
        $this->token = $token;
        return $this;
    }

    /**
     * Check if Monobank is configured
     */
    public function isConfigured(): bool
    {
        return !empty($this->token);
    }

    /**
     * Get client info (accounts list)
     */
    public function getClientInfo(): ?array
    {
        if (!$this->token) {
            return null;
        }

        try {
            $response = Http::withHeaders([
                'X-Token' => $this->token,
            ])->get(self::API_BASE . '/personal/client-info');

            if ($response->successful()) {
                return $response->json();
            }

            Log::warning('Monobank API error', [
                'status' => $response->status(),
            ]);

            return null;
        } catch (\Exception $e) {
            Log::error('Monobank API exception', ['error' => $e->getMessage()]);
            return null;
        }
    }

    /**
     * Get accounts list
     */
    public function getAccounts(): array
    {
        $info = $this->getClientInfo();
        if (!$info || !isset($info['accounts'])) {
            return [];
        }

        return collect($info['accounts'])->map(function ($account) {
            return [
                'id' => $account['id'],
                'iban' => $account['iban'] ?? null,
                'currency_code' => $account['currencyCode'],
                'currency' => $this->getCurrencyName($account['currencyCode']),
                'balance' => $account['balance'] / 100,
                'credit_limit' => ($account['creditLimit'] ?? 0) / 100,
                'type' => $account['type'] ?? 'unknown',
                'masked_pan' => $account['maskedPan'][0] ?? null,
            ];
        })->toArray();
    }

    /**
     * Get UAH accounts only
     */
    public function getUahAccounts(): array
    {
        return collect($this->getAccounts())
            ->filter(fn($acc) => $acc['currency_code'] == 980)
            ->values()
            ->toArray();
    }

    /**
     * Get statements (transactions) for account
     *
     * @param string $accountId Account ID or "0" for default
     * @param int $from Unix timestamp
     * @param int|null $to Unix timestamp (default: now)
     */
    public function getStatements(string $accountId, int $from, ?int $to = null): ?array
    {
        if (!$this->token) {
            return null;
        }

        $to = $to ?? time();

        // Monobank allows max 31 days + 1 hour per request
        $maxPeriod = 31 * 24 * 60 * 60;
        if (($to - $from) > $maxPeriod) {
            $from = $to - $maxPeriod;
        }

        try {
            $response = Http::withHeaders([
                'X-Token' => $this->token,
            ])->get(self::API_BASE . "/personal/statement/{$accountId}/{$from}/{$to}");

            if ($response->successful()) {
                return $response->json();
            }

            // Rate limit - need to wait
            if ($response->status() === 429) {
                Log::warning('Monobank rate limit exceeded');
                return null;
            }

            Log::warning('Monobank statements error', [
                'status' => $response->status(),
            ]);

            return null;
        } catch (\Exception $e) {
            Log::error('Monobank statements exception', ['error' => $e->getMessage()]);
            return null;
        }
    }

    /**
     * Sync transactions for church
     *
     * @param int $days Number of days to sync (max 31)
     * @return array ['imported' => int, 'skipped' => int, 'error' => string|null]
     */
    public function syncTransactions(int $days = 7): array
    {
        if (!$this->church) {
            return ['imported' => 0, 'skipped' => 0, 'error' => 'Church not set'];
        }

        if (!$this->isConfigured()) {
            return ['imported' => 0, 'skipped' => 0, 'error' => 'Monobank not configured'];
        }

        $accountId = $this->church->monobank_account_id ?? '0';
        $from = Carbon::now()->subDays(min($days, 31))->timestamp;

        $statements = $this->getStatements($accountId, $from);

        if ($statements === null) {
            return ['imported' => 0, 'skipped' => 0, 'error' => 'Не вдалося отримати виписку. Спробуйте пізніше.'];
        }

        $imported = 0;
        $skipped = 0;

        foreach ($statements as $statement) {
            // Check if already exists
            $exists = MonobankTransaction::where('mono_id', $statement['id'])->where('church_id', $this->church->id)->exists();

            if ($exists) {
                $skipped++;
                continue;
            }

            MonobankTransaction::createFromMonoData($this->church->id, $statement);
            $imported++;
        }

        // Update last sync time
        $this->church->update(['monobank_last_sync' => now()]);

        return ['imported' => $imported, 'skipped' => $skipped, 'error' => null];
    }

    /**
     * Validate token by trying to get client info
     */
    public function validateToken(string $token): ?array
    {
        $this->setToken($token);
        $info = $this->getClientInfo();

        if (!$info) {
            return null;
        }

        return [
            'name' => $info['name'] ?? 'Unknown',
            'accounts' => $this->getUahAccounts(),
        ];
    }

    /**
     * Get currency name by ISO code
     */
    protected function getCurrencyName(int $code): string
    {
        return match ($code) {
            980 => 'UAH',
            840 => 'USD',
            978 => 'EUR',
            985 => 'PLN',
            default => (string) $code,
        };
    }

    /**
     * Save encrypted token to church
     */
    public function saveToken(Church $church, string $token, ?string $accountId = null): bool
    {
        try {
            $church->update([
                'monobank_token' => Crypt::encryptString($token),
                'monobank_account_id' => $accountId,
            ]);
            return true;
        } catch (\Exception $e) {
            Log::error('Failed to save Monobank token', ['error' => $e->getMessage()]);
            return false;
        }
    }

    /**
     * Remove Monobank integration
     */
    public function disconnect(Church $church): bool
    {
        return $church->update([
            'monobank_token' => null,
            'monobank_account_id' => null,
            'monobank_auto_sync' => false,
            'monobank_last_sync' => null,
            'monobank_webhook_secret' => null,
        ]);
    }

    /**
     * Set webhook URL for real-time notifications
     */
    public function setWebhook(string $webhookUrl): bool
    {
        if (!$this->token) {
            return false;
        }

        try {
            $response = Http::withHeaders([
                'X-Token' => $this->token,
            ])->post(self::API_BASE . '/personal/webhook', [
                'webHookUrl' => $webhookUrl,
            ]);

            if ($response->successful()) {
                return true;
            }

            Log::warning('Monobank webhook setup failed', [
                'status' => $response->status(),
            ]);

            return false;
        } catch (\Exception $e) {
            Log::error('Monobank webhook exception', ['error' => $e->getMessage()]);
            return false;
        }
    }
}
