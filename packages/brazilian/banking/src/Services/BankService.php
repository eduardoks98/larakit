<?php

namespace Eduardoks98\Banking\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Eduardoks98\Banking\Exceptions\BankingException;

/**
 * Brazilian Bank Codes and Information Service
 */
class BankService
{
    /**
     * Cache key for bank list
     */
    protected const CACHE_KEY = 'banking_banks_list';

    /**
     * Get all banks
     */
    public function getAll(): array
    {
        if (config('banking.cache.enabled')) {
            return Cache::remember(
                config('banking.cache.prefix') . self::CACHE_KEY,
                config('banking.cache.ttl'),
                fn () => $this->fetchBanks()
            );
        }

        return $this->fetchBanks();
    }

    /**
     * Find bank by code
     */
    public function findByCode(string $code): ?array
    {
        $code = str_pad($code, 3, '0', STR_PAD_LEFT);
        $banks = $this->getAll();

        return $banks[$code] ?? null;
    }

    /**
     * Find bank by ISPB
     */
    public function findByIspb(string $ispb): ?array
    {
        $ispb = str_pad($ispb, 8, '0', STR_PAD_LEFT);
        $banks = $this->getAll();

        foreach ($banks as $code => $bank) {
            if (($bank['ispb'] ?? '') === $ispb) {
                return array_merge($bank, ['code' => $code]);
            }
        }

        return null;
    }

    /**
     * Find bank by name (partial match)
     */
    public function findByName(string $name): array
    {
        $name = strtolower($name);
        $banks = $this->getAll();
        $results = [];

        foreach ($banks as $code => $bank) {
            if (
                str_contains(strtolower($bank['name']), $name) ||
                str_contains(strtolower($bank['short_name'] ?? ''), $name)
            ) {
                $results[$code] = $bank;
            }
        }

        return $results;
    }

    /**
     * Check if bank code exists
     */
    public function exists(string $code): bool
    {
        return $this->findByCode($code) !== null;
    }

    /**
     * Get bank name by code
     */
    public function getName(string $code): ?string
    {
        $bank = $this->findByCode($code);
        return $bank['name'] ?? null;
    }

    /**
     * Get bank short name by code
     */
    public function getShortName(string $code): ?string
    {
        $bank = $this->findByCode($code);
        return $bank['short_name'] ?? null;
    }

    /**
     * Get bank ISPB by code
     */
    public function getIspb(string $code): ?string
    {
        $bank = $this->findByCode($code);
        return $bank['ispb'] ?? null;
    }

    /**
     * Get major banks (commonly used)
     */
    public function getMajorBanks(): array
    {
        $majorCodes = ['001', '033', '077', '104', '237', '260', '341', '336', '380'];
        $banks = $this->getAll();

        return array_filter($banks, fn ($bank, $code) => in_array($code, $majorCodes), ARRAY_FILTER_USE_BOTH);
    }

    /**
     * Get digital banks
     */
    public function getDigitalBanks(): array
    {
        $digitalCodes = ['077', '260', '290', '323', '336', '380', '735'];
        $banks = $this->getAll();

        return array_filter($banks, fn ($bank, $code) => in_array($code, $digitalCodes), ARRAY_FILTER_USE_BOTH);
    }

    /**
     * Format bank code
     */
    public function formatCode(string $code): string
    {
        return str_pad($code, 3, '0', STR_PAD_LEFT);
    }

    /**
     * Format ISPB code
     */
    public function formatIspb(string $ispb): string
    {
        return str_pad($ispb, 8, '0', STR_PAD_LEFT);
    }

    /**
     * Search banks
     */
    public function search(string $query): array
    {
        $query = strtolower(trim($query));

        // If numeric, search by code or ISPB
        if (is_numeric($query)) {
            if (strlen($query) <= 3) {
                $bank = $this->findByCode($query);
                return $bank ? [$this->formatCode($query) => $bank] : [];
            } else {
                $bank = $this->findByIspb($query);
                return $bank ? [$bank['code'] => $bank] : [];
            }
        }

        return $this->findByName($query);
    }

    /**
     * Refresh cache
     */
    public function refreshCache(): void
    {
        Cache::forget(config('banking.cache.prefix') . self::CACHE_KEY);
        $this->getAll();
    }

    /**
     * Fetch banks from source
     */
    protected function fetchBanks(): array
    {
        if (config('banking.bank_list_source') === 'api') {
            return $this->fetchFromApi();
        }

        return config('banking.banks', []);
    }

    /**
     * Fetch banks from Brasil API
     */
    protected function fetchFromApi(): array
    {
        try {
            $response = Http::timeout(config('banking.bacen.timeout', 10))
                ->get(config('banking.bacen.base_url'));

            if (!$response->successful()) {
                // Fallback to static list
                return config('banking.banks', []);
            }

            $banks = [];
            foreach ($response->json() as $bank) {
                if (!empty($bank['code'])) {
                    $code = str_pad($bank['code'], 3, '0', STR_PAD_LEFT);
                    $banks[$code] = [
                        'name' => $bank['fullName'] ?? $bank['name'] ?? '',
                        'short_name' => $bank['name'] ?? '',
                        'ispb' => str_pad($bank['ispb'] ?? '', 8, '0', STR_PAD_LEFT),
                    ];
                }
            }

            return empty($banks) ? config('banking.banks', []) : $banks;
        } catch (\Exception $e) {
            // Fallback to static list
            return config('banking.banks', []);
        }
    }
}
