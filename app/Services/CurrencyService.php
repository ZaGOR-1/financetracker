<?php

namespace App\Services;

use DateTime;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class CurrencyService
{
    private array $supportedCurrencies;
    private int $cacheDuration;

    public function __construct()
    {
        $this->supportedCurrencies = config('currencies.supported', []);
        $this->cacheDuration = config('currencies.exchange_api.cache_duration', 3600);
    }

    /**
     * Отримати список підтримуваних валют
     */
    public function getSupportedCurrencies(): array
    {
        return $this->supportedCurrencies;
    }

    /**
     * Перевірити чи валюта підтримується
     */
    public function isSupported(string $currency): bool
    {
        return isset($this->supportedCurrencies[$currency]);
    }

    /**
     * Форматувати суму з символом валюти
     * 
     * @param float|string $amount Сума для форматування
     * @param string $currency Код валюти (UAH, USD, PLN)
     * @return string Відформатована сума з символом
     */
    public function format(float|string $amount, string $currency): string
    {
        $config = $this->supportedCurrencies[$currency] ?? null;

        if (!$config) {
            throw new \InvalidArgumentException("Непідтримувана валюта: {$currency}");
        }

        $amountFloat = (float) $amount;

        $formatted = number_format(
            abs($amountFloat),
            $config['decimal_places'],
            '.',
            ' '
        );

        $sign = $amountFloat < 0 ? '-' : '';

        return "{$sign}{$formatted} {$config['symbol']}";
    }

    /**
     * Отримати символ валюти
     */
    public function getSymbol(string $currency): string
    {
        return $this->supportedCurrencies[$currency]['symbol'] ?? $currency;
    }

    /**
     * Отримати назву валюти
     */
    public function getName(string $currency): string
    {
        return $this->supportedCurrencies[$currency]['name'] ?? $currency;
    }

    /**
     * Конвертувати суму з однієї валюти в іншу
     * 
     * @param float $amount Сума для конвертації
     * @param string $fromCurrency Вихідна валюта
     * @param string $toCurrency Цільова валюта
     * @param DateTime|null $date Дата курсу (за замовчуванням - сьогодні)
     * @return float Конвертована сума
     */
    public function convert(
        float $amount,
        string $fromCurrency,
        string $toCurrency,
        ?DateTime $date = null
    ): float {
        // Якщо валюти однакові - повертаємо суму без змін
        if ($fromCurrency === $toCurrency) {
            return $amount;
        }

        // Перевіряємо чи валюти підтримуються
        if (!$this->isSupported($fromCurrency) || !$this->isSupported($toCurrency)) {
            throw new \InvalidArgumentException(
                "Непідтримувана валюта: {$fromCurrency} або {$toCurrency}"
            );
        }

        $date = $date ?? new DateTime();
        $rate = $this->getExchangeRate($fromCurrency, $toCurrency, $date);

        return round($amount * $rate, 2);
    }

    /**
     * Отримати курс обміну між двома валютами
     * 
     * @param string $from Вихідна валюта
     * @param string $to Цільова валюта
     * @param DateTime $date Дата курсу
     * @return float Курс обміну
     */
    private function getExchangeRate(string $from, string $to, DateTime $date): float
    {
        $dateStr = $date->format('Y-m-d');
        $cacheKey = "exchange_rate:{$from}:{$to}:{$dateStr}";

        return Cache::remember($cacheKey, $this->cacheDuration, function () use ($from, $to, $date, $dateStr) {
            // Спочатку шукаємо в базі даних
            $rate = DB::table('exchange_rates')
                ->where('base_currency', $from)
                ->where('target_currency', $to)
                ->where('date', $dateStr)
                ->value('rate');

            if ($rate !== null) {
                return (float) $rate;
            }

            // Якщо немає в БД - запитуємо з API
            try {
                return $this->fetchExchangeRateFromAPI($from, $to, $date);
            } catch (\Exception $e) {
                Log::error("Помилка отримання курсу {$from}->{$to}: " . $e->getMessage());
                
                // Повертаємо курс 1:1 як fallback (краще мати дані ніж помилку)
                return 1.0;
            }
        });
    }

    /**
     * Запит курсу з зовнішнього API
     */
    private function fetchExchangeRateFromAPI(string $from, string $to, DateTime $date): float
    {
        $provider = config('currencies.exchange_api.provider', 'nbu');

        return match ($provider) {
            'exchangerate-api' => $this->fetchFromExchangeRateAPI($from, $to, $date),
            'nbu' => $this->fetchFromNBU($from, $to, $date),
            default => throw new \Exception("Невідомий провайдер курсів: {$provider}"),
        };
    }

    /**
     * Отримання курсу з ExchangeRate-API.com
     * API документація: https://www.exchangerate-api.com/docs/overview
     */
    private function fetchFromExchangeRateAPI(string $from, string $to, DateTime $date): float
    {
        $apiKey = config('currencies.exchange_api.exchangerate_api_key');

        if (!$apiKey) {
            throw new \Exception('ExchangeRate-API ключ не налаштований');
        }

        // ExchangeRate-API підтримує тільки актуальні курси, не історичні
        // Для історичних даних потрібен платний план
        $url = "https://v6.exchangerate-api.com/v6/{$apiKey}/pair/{$from}/{$to}";

        try {
            $response = Http::timeout(10)
                ->withOptions(['verify' => false]) // Тимчасово для Windows SSL проблем
                ->get($url);

            if (!$response->successful()) {
                throw new \Exception("Помилка API: " . $response->status());
            }

            $data = $response->json();

            if ($data['result'] !== 'success') {
                throw new \Exception("API помилка: " . ($data['error-type'] ?? 'unknown'));
            }

            $rate = (float) $data['conversion_rate'];

            // Зберігаємо в БД
            $this->saveExchangeRate($from, $to, $rate, $date);

            return $rate;

        } catch (\Exception $e) {
            Log::error("ExchangeRate-API помилка {$from}->{$to}: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Отримання курсу з API Національного банку України
     * API документація: https://bank.gov.ua/ua/open-data/api-dev
     */
    private function fetchFromNBU(string $from, string $to, DateTime $date): float
    {
        // Якщо обидві валюти не UAH - конвертуємо через UAH
        // Наприклад: USD -> PLN = (USD -> UAH) * (UAH -> PLN)
        if ($from !== 'UAH' && $to !== 'UAH') {
            $fromToUah = $this->fetchFromNBU($from, 'UAH', $date);
            $uahToTarget = $this->fetchFromNBU('UAH', $to, $date);
            
            $rate = $fromToUah * $uahToTarget;
            
            // Зберігаємо обчислений курс
            $this->saveExchangeRate($from, $to, $rate, $date);
            
            return $rate;
        }

        // Визначаємо яку валюту запитувати (НБУ дає курси до гривні)
        $currencyCode = $from === 'UAH' ? $to : $from;
        $dateStr = $date->format('Ymd');

        // Запит до API НБУ
        $response = Http::timeout(10)
            ->withOptions(['verify' => false]) // Тимчасово для Windows SSL проблем
            ->get('https://bank.gov.ua/NBUStatService/v1/statdirectory/exchange', [
                'valcode' => $currencyCode,
                'date' => $dateStr,
                'json' => '',
            ]);

        if (!$response->successful()) {
            throw new \Exception("Помилка API НБУ: " . $response->status());
        }

        $data = $response->json();

        if (empty($data)) {
            throw new \Exception("НБУ не повернув курс для {$currencyCode} на дату {$dateStr}");
        }

        // НБУ повертає курс іноземної валюти до гривні
        $nbuRate = (float) $data[0]['rate'];

        // Якщо конвертуємо з UAH в іншу валюту - інвертуємо курс
        $rate = $from === 'UAH' ? (1 / $nbuRate) : $nbuRate;

        // Зберігаємо в БД
        $this->saveExchangeRate($from, $to, $rate, $date);

        return $rate;
    }

    /**
     * Зберегти курс обміну в базу даних
     */
    private function saveExchangeRate(string $from, string $to, float $rate, DateTime $date): void
    {
        try {
            DB::table('exchange_rates')->updateOrInsert(
                [
                    'base_currency' => $from,
                    'target_currency' => $to,
                    'date' => $date->format('Y-m-d'),
                ],
                [
                    'rate' => $rate,
                    'updated_at' => now(),
                    'created_at' => now(),
                ]
            );
        } catch (\Exception $e) {
            Log::error("Помилка збереження курсу: " . $e->getMessage());
        }
    }

    /**
     * Отримати актуальні курси для всіх пар валют
     */
    public function updateAllRates(?DateTime $date = null): array
    {
        $date = $date ?? new DateTime();
        $currencies = array_keys($this->supportedCurrencies);
        $results = [];

        foreach ($currencies as $from) {
            foreach ($currencies as $to) {
                if ($from !== $to) {
                    try {
                        $rate = $this->convert(1, $from, $to, $date);
                        $results["{$from}->{$to}"] = [
                            'success' => true,
                            'rate' => $rate,
                        ];
                    } catch (\Exception $e) {
                        $results["{$from}->{$to}"] = [
                            'success' => false,
                            'error' => $e->getMessage(),
                        ];
                    }
                }
            }
        }

        return $results;
    }
}
