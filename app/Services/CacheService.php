<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Carbon\Carbon;

/**
 * Централізований сервіс управління кешем
 * 
 * Надає єдиний інтерфейс для кешування даних у застосунку
 * з автоматичною генерацією ключів та управлінням TTL
 */
class CacheService
{
    /**
     * TTL для різних типів даних (у хвилинах)
     */
    private const TTL = [
        'stats' => 5,           // Статистика - 5 хвилин
        'transactions' => 10,   // Транзакції - 10 хвилин
        'categories' => 60,     // Категорії - 1 година (змінюються рідко)
        'budgets' => 15,        // Бюджети - 15 хвилин
        'currency' => 1440,     // Курси валют - 24 години
        'user' => 30,           // Дані користувача - 30 хвилин
    ];

    /**
     * Префікс для всіх ключів
     */
    private string $prefix;

    public function __construct()
    {
        $this->prefix = config('cache.prefix', 'finance_tracker');
    }

    /**
     * Отримати або зберегти дані в кеші
     * 
     * @param string $type Тип даних (stats, transactions, categories, etc.)
     * @param string $key Унікальний ключ
     * @param callable $callback Функція для отримання даних якщо кеш порожній
     * @param int|null $ttl Час життя в хвилинах (null = використати default)
     * @return mixed
     */
    public function remember(string $type, string $key, callable $callback, ?int $ttl = null): mixed
    {
        $cacheKey = $this->generateKey($type, $key);
        $ttlMinutes = $ttl ?? (self::TTL[$type] ?? 60);
        
        return Cache::remember($cacheKey, now()->addMinutes($ttlMinutes), $callback);
    }

    /**
     * Отримати дані з кешу
     * 
     * @param string $type
     * @param string $key
     * @param mixed $default Значення за замовчуванням
     * @return mixed
     */
    public function get(string $type, string $key, mixed $default = null): mixed
    {
        $cacheKey = $this->generateKey($type, $key);
        return Cache::get($cacheKey, $default);
    }

    /**
     * Зберегти дані в кеші
     * 
     * @param string $type
     * @param string $key
     * @param mixed $value
     * @param int|null $ttl
     * @return bool
     */
    public function put(string $type, string $key, mixed $value, ?int $ttl = null): bool
    {
        $cacheKey = $this->generateKey($type, $key);
        $ttlMinutes = $ttl ?? (self::TTL[$type] ?? 60);
        
        return Cache::put($cacheKey, $value, now()->addMinutes($ttlMinutes));
    }

    /**
     * Видалити конкретний ключ з кешу
     * 
     * @param string $type
     * @param string $key
     * @return bool
     */
    public function forget(string $type, string $key): bool
    {
        $cacheKey = $this->generateKey($type, $key);
        return Cache::forget($cacheKey);
    }

    /**
     * Очистити весь кеш для користувача
     * 
     * @param int $userId
     * @return void
     */
    public function forgetUser(int $userId): void
    {
        $types = ['stats', 'transactions', 'categories', 'budgets', 'user'];
        
        foreach ($types as $type) {
            $pattern = $this->generateKey($type, "user_{$userId}_*");
            $this->forgetByPattern($pattern);
        }
    }

    /**
     * Очистити весь кеш певного типу для користувача
     * 
     * @param string $type
     * @param int $userId
     * @return void
     */
    public function forgetUserType(string $type, int $userId): void
    {
        $pattern = $this->generateKey($type, "user_{$userId}_*");
        $this->forgetByPattern($pattern);
    }

    /**
     * Очистити кеш транзакцій користувача
     * 
     * @param int $userId
     * @return void
     */
    public function forgetUserTransactions(int $userId): void
    {
        // ТИМЧАСОВЕ РІШЕННЯ: Повне очищення кешу при зміні транзакцій
        // TODO: Реалізувати правильне видалення за патерном для file driver
        // або перейти на Redis/Memcached з підтримкою tags
        Cache::flush();
    }

    /**
     * Очистити кеш категорій користувача
     * 
     * @param int $userId
     * @return void
     */
    public function forgetUserCategories(int $userId): void
    {
        // ТИМЧАСОВЕ РІШЕННЯ: Повне очищення кешу при зміні категорій
        // TODO: Реалізувати правильне видалення за патерном для file driver
        // або перейти на Redis/Memcached з підтримкою tags
        Cache::flush();
    }

    /**
     * Очистити кеш бюджетів користувача
     * 
     * @param int $userId
     * @return void
     */
    public function forgetUserBudgets(int $userId): void
    {
        $this->forgetUserType('budgets', $userId);
    }

    /**
     * Згенерувати ключ кешу
     * 
     * @param string $type
     * @param string $key
     * @return string
     */
    private function generateKey(string $type, string $key): string
    {
        return "{$this->prefix}:{$type}:{$key}";
    }

    /**
     * Видалити ключі за шаблоном (для file driver)
     * 
     * @param string $pattern
     * @return void
     */
    private function forgetByPattern(string $pattern): void
    {
        // Для file driver треба видалити всі можливі комбінації ключів
        if (config('cache.default') === 'file') {
            // Витягуємо тип та userId з паттерну
            // Паттерн: "finance_tracker:categories:user_{userId}_*"
            if (preg_match('/:(\w+):user_(\d+)_/', $pattern, $matches)) {
                $type = $matches[1]; // categories, transactions, stats і т.д.
                $userId = (int) $matches[2];
                
                // Список усіх можливих хешів для фільтрів (включно з 'all')
                // Замість хеша генеруємо всі можливі варіанти
                $possibleFilters = [
                    [],
                    ['type' => 'income'],
                    ['type' => 'expense'],
                    ['is_active' => true],
                    ['is_active' => false],
                    ['type' => 'income', 'is_active' => true],
                    ['type' => 'income', 'is_active' => false],
                    ['type' => 'expense', 'is_active' => true],
                    ['type' => 'expense', 'is_active' => false],
                ];
                
                foreach ($possibleFilters as $filters) {
                    $key = match($type) {
                        'categories' => $this->categoriesKey($userId, $filters),
                        'transactions' => $this->transactionsKey($userId, $filters),
                        'stats' => $this->statsKey($userId),
                        default => "user_{$userId}_{$type}",
                    };
                    
                    $fullKey = $this->generateKey($type, $key);
                    Cache::forget($fullKey);
                }
            }
        } else {
            // Для Redis можна використати SCAN або tags
            // TODO: В продакшені краще використати Cache::tags()
            Cache::forget($pattern);
        }
    }

    /**
     * Очистити весь кеш застосунку
     * 
     * @return bool
     */
    public function flush(): bool
    {
        return Cache::flush();
    }

    /**
     * Перевірити чи існує ключ в кеші
     * 
     * @param string $type
     * @param string $key
     * @return bool
     */
    public function has(string $type, string $key): bool
    {
        $cacheKey = $this->generateKey($type, $key);
        return Cache::has($cacheKey);
    }

    /**
     * Отримати TTL для типу даних
     * 
     * @param string $type
     * @return int
     */
    public function getTTL(string $type): int
    {
        return self::TTL[$type] ?? 60;
    }

    /**
     * Згенерувати ключ для статистики користувача
     * 
     * @param int $userId
     * @param string|null $fromDate
     * @param string|null $toDate
     * @param array $additionalParams
     * @return string
     */
    public function statsKey(int $userId, ?string $fromDate = null, ?string $toDate = null, array $additionalParams = []): string
    {
        $params = array_merge([
            'from' => $fromDate ?? 'null',
            'to' => $toDate ?? 'null',
        ], $additionalParams);
        
        $hash = md5(json_encode($params));
        return "user_{$userId}_stats_{$hash}";
    }

    /**
     * Згенерувати ключ для списку категорій
     * 
     * @param int $userId
     * @param array $filters
     * @return string
     */
    public function categoriesKey(int $userId, array $filters = []): string
    {
        $hash = empty($filters) ? 'all' : md5(json_encode($filters));
        return "user_{$userId}_categories_{$hash}";
    }

    /**
     * Згенерувати ключ для транзакцій
     * 
     * @param int $userId
     * @param array $filters
     * @param int $page
     * @return string
     */
    public function transactionsKey(int $userId, array $filters = [], int $page = 1): string
    {
        $hash = md5(json_encode($filters));
        return "user_{$userId}_transactions_{$hash}_page_{$page}";
    }

    /**
     * Згенерувати ключ для бюджетів
     * 
     * @param int $userId
     * @param array $filters
     * @return string
     */
    public function budgetsKey(int $userId, array $filters = []): string
    {
        $hash = empty($filters) ? 'all' : md5(json_encode($filters));
        return "user_{$userId}_budgets_{$hash}";
    }
}
