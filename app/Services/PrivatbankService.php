<?php

namespace App\Services;

use App\Models\Church;
use App\Models\PrivatbankTransaction;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Crypt;
use Carbon\Carbon;

class PrivatbankService
{
    protected const API_URL = 'https://api.privatbank.ua/p24api/rest_fiz';

    protected ?string $merchantId = null;
    protected ?string $password = null;
    protected ?string $cardNumber = null;
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

        if ($church->privatbank_merchant_id) {
            try {
                $this->merchantId = Crypt::decryptString($church->privatbank_merchant_id);
            } catch (\Exception $e) {
                $this->merchantId = null;
            }
        }

        if ($church->privatbank_password) {
            try {
                $this->password = Crypt::decryptString($church->privatbank_password);
            } catch (\Exception $e) {
                $this->password = null;
            }
        }

        if ($church->privatbank_card_number) {
            try {
                $this->cardNumber = Crypt::decryptString($church->privatbank_card_number);
            } catch (\Exception $e) {
                // Fallback for plain text (legacy data)
                $this->cardNumber = $church->privatbank_card_number;
            }
        }

        return $this;
    }

    /**
     * Set credentials directly (for validation)
     */
    public function setCredentials(string $merchantId, string $password, string $cardNumber): self
    {
        $this->merchantId = $merchantId;
        $this->password = $password;
        $this->cardNumber = $cardNumber;
        return $this;
    }

    /**
     * Check if PrivatBank is configured
     */
    public function isConfigured(): bool
    {
        return !empty($this->merchantId) && !empty($this->password) && !empty($this->cardNumber);
    }

    /**
     * Generate signature for API request
     * Signature = sha1(md5(data + password))
     */
    protected function generateSignature(string $dataXml): string
    {
        $data = $dataXml . $this->password;
        return sha1(md5($data));
    }

    /**
     * Build XML request for getting statements
     */
    protected function buildStatementsRequest(string $startDate, string $endDate): string
    {
        $dataXml = '<oper>cmt</oper>' .
            '<wait>0</wait>' .
            '<test>0</test>' .
            '<payment id="">' .
            '<prop name="sd" value="' . $startDate . '" />' .
            '<prop name="ed" value="' . $endDate . '" />' .
            '<prop name="card" value="' . $this->cardNumber . '" />' .
            '</payment>';

        $signature = $this->generateSignature($dataXml);

        return '<?xml version="1.0" encoding="UTF-8"?>' .
            '<request version="1.0">' .
            '<merchant>' .
            '<id>' . $this->merchantId . '</id>' .
            '<signature>' . $signature . '</signature>' .
            '</merchant>' .
            '<data>' . $dataXml . '</data>' .
            '</request>';
    }

    /**
     * Build XML request for card balance
     */
    protected function buildBalanceRequest(): string
    {
        $dataXml = '<oper>cmt</oper>' .
            '<wait>0</wait>' .
            '<test>0</test>' .
            '<payment id="">' .
            '<prop name="cardnum" value="' . $this->cardNumber . '" />' .
            '<prop name="country" value="UA" />' .
            '</payment>';

        $signature = $this->generateSignature($dataXml);

        return '<?xml version="1.0" encoding="UTF-8"?>' .
            '<request version="1.0">' .
            '<merchant>' .
            '<id>' . $this->merchantId . '</id>' .
            '<signature>' . $signature . '</signature>' .
            '</merchant>' .
            '<data>' . $dataXml . '</data>' .
            '</request>';
    }

    /**
     * Get statements (transactions) for card
     *
     * @param Carbon $startDate
     * @param Carbon $endDate
     * @return array|null
     */
    public function getStatements(Carbon $startDate, Carbon $endDate): ?array
    {
        if (!$this->isConfigured()) {
            return null;
        }

        $xml = $this->buildStatementsRequest(
            $startDate->format('d.m.Y'),
            $endDate->format('d.m.Y')
        );

        try {
            $response = Http::withHeaders([
                'Content-Type' => 'application/xml',
            ])->withBody($xml, 'application/xml')
                ->post(self::API_URL);

            if (!$response->successful()) {
                Log::warning('PrivatBank API error', [
                    'status' => $response->status(),
                ]);
                return null;
            }

            return $this->parseStatementsResponse($response->body());
        } catch (\Exception $e) {
            Log::error('PrivatBank API exception', ['error' => $e->getMessage()]);
            return null;
        }
    }

    /**
     * Parse statements XML response
     */
    protected function parseStatementsResponse(string $xmlBody): ?array
    {
        try {
            $xml = simplexml_load_string($xmlBody);

            if (!$xml) {
                Log::warning('PrivatBank: Failed to parse XML response');
                return null;
            }

            // Check for error
            if (isset($xml->data->error)) {
                Log::warning('PrivatBank API error', [
                    'message' => (string) $xml->data->error['message'],
                ]);
                return null;
            }

            $statements = [];

            // Navigate to statements
            if (isset($xml->data->info->statements->statement)) {
                foreach ($xml->data->info->statements->statement as $stmt) {
                    $statements[] = [
                        'tranId' => (string) ($stmt['appcode'] ?? ''),
                        'appcode' => (string) ($stmt['appcode'] ?? ''),
                        'trandate' => (string) ($stmt['trandate'] ?? ''),
                        'trantime' => (string) ($stmt['trantime'] ?? ''),
                        'cardamount' => (string) ($stmt['cardamount'] ?? ''),
                        'amount' => (string) ($stmt['amount'] ?? ''),
                        'rest' => (string) ($stmt['rest'] ?? ''),
                        'terminal' => (string) ($stmt['terminal'] ?? ''),
                        'description' => (string) ($stmt['description'] ?? ''),
                    ];
                }
            }

            return $statements;
        } catch (\Exception $e) {
            Log::error('PrivatBank: Error parsing response', ['error' => $e->getMessage()]);
            return null;
        }
    }

    /**
     * Get card balance
     */
    public function getBalance(): ?array
    {
        if (!$this->isConfigured()) {
            return null;
        }

        $xml = $this->buildBalanceRequest();

        try {
            $response = Http::withHeaders([
                'Content-Type' => 'application/xml',
            ])->withBody($xml, 'application/xml')
                ->post('https://api.privatbank.ua/p24api/balance');

            if (!$response->successful()) {
                Log::warning('PrivatBank balance API error', [
                    'status' => $response->status(),
                ]);
                return null;
            }

            return $this->parseBalanceResponse($response->body());
        } catch (\Exception $e) {
            Log::error('PrivatBank balance API exception', ['error' => $e->getMessage()]);
            return null;
        }
    }

    /**
     * Parse balance XML response
     */
    protected function parseBalanceResponse(string $xmlBody): ?array
    {
        try {
            $xml = simplexml_load_string($xmlBody);

            if (!$xml || isset($xml->data->error)) {
                return null;
            }

            if (isset($xml->data->info->cardbalance)) {
                $balance = $xml->data->info->cardbalance;
                return [
                    'balance' => (string) ($balance->av_balance ?? '0'),
                    'balance_date' => (string) ($balance->bal_date ?? ''),
                    'fin_limit' => (string) ($balance->fin_limit ?? '0'),
                ];
            }

            return null;
        } catch (\Exception $e) {
            Log::error('PrivatBank: Error parsing balance response', ['error' => $e->getMessage()]);
            return null;
        }
    }

    /**
     * Sync transactions for church
     *
     * @param int $days Number of days to sync (max 90 for PrivatBank)
     * @return array ['imported' => int, 'skipped' => int, 'error' => string|null]
     */
    public function syncTransactions(int $days = 7): array
    {
        if (!$this->church) {
            return ['imported' => 0, 'skipped' => 0, 'error' => 'Church not set'];
        }

        if (!$this->isConfigured()) {
            return ['imported' => 0, 'skipped' => 0, 'error' => 'PrivatBank not configured'];
        }

        $startDate = Carbon::now()->subDays(min($days, 90));
        $endDate = Carbon::now();

        $statements = $this->getStatements($startDate, $endDate);

        if ($statements === null) {
            return ['imported' => 0, 'skipped' => 0, 'error' => 'Не вдалося отримати виписку. Спробуйте пізніше.'];
        }

        $imported = 0;
        $skipped = 0;

        foreach ($statements as $statement) {
            // Generate unique ID
            $tranId = $statement['appcode'] ?: md5(json_encode($statement));

            // Check if already exists
            $exists = PrivatbankTransaction::where('privat_id', $tranId)->where('church_id', $this->church->id)->exists();

            if ($exists) {
                $skipped++;
                continue;
            }

            PrivatbankTransaction::createFromPrivatData($this->church->id, $statement);
            $imported++;
        }

        // Update last sync time
        $this->church->update(['privatbank_last_sync' => now()]);

        return ['imported' => $imported, 'skipped' => $skipped, 'error' => null];
    }

    /**
     * Validate credentials by trying to get balance or statements
     */
    public function validateCredentials(): ?array
    {
        if (!$this->isConfigured()) {
            return null;
        }

        // Try to get statements for today
        $statements = $this->getStatements(Carbon::now()->subDays(1), Carbon::now());

        if ($statements === null) {
            return null;
        }

        return [
            'card' => substr($this->cardNumber, 0, 4) . ' **** **** ' . substr($this->cardNumber, -4),
            'transactions_count' => count($statements),
        ];
    }

    /**
     * Save encrypted credentials to church
     */
    public function saveCredentials(Church $church, string $merchantId, string $password, string $cardNumber): bool
    {
        try {
            $church->update([
                'privatbank_merchant_id' => Crypt::encryptString($merchantId),
                'privatbank_password' => Crypt::encryptString($password),
                'privatbank_card_number' => Crypt::encryptString($cardNumber),
            ]);
            return true;
        } catch (\Exception $e) {
            Log::error('Failed to save PrivatBank credentials', ['error' => $e->getMessage()]);
            return false;
        }
    }

    /**
     * Remove PrivatBank integration
     */
    public function disconnect(Church $church): bool
    {
        return $church->update([
            'privatbank_merchant_id' => null,
            'privatbank_password' => null,
            'privatbank_card_number' => null,
            'privatbank_auto_sync' => false,
            'privatbank_last_sync' => null,
        ]);
    }
}
