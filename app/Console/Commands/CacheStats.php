<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\File;

class CacheStats extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'cache:stats';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Показати статистику кешу (розмір, кількість ключів, hit rate)';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('📊 Cache Statistics');
        $this->newLine();

        // Driver info
        $driver = config('cache.default');
        $this->line("🔧 <fg=cyan>Driver:</> {$driver}");
        $this->newLine();

        // Отримуємо статистику залежно від драйвера
        switch ($driver) {
            case 'file':
                $this->showFileDriverStats();
                break;
            case 'redis':
                $this->showRedisDriverStats();
                break;
            case 'memcached':
                $this->showMemcachedDriverStats();
                break;
            case 'array':
                $this->showArrayDriverStats();
                break;
            default:
                $this->warn("⚠️  Statistics not available for '{$driver}' driver");
                break;
        }

        return self::SUCCESS;
    }

    /**
     * Показати статистику для file driver
     */
    private function showFileDriverStats(): void
    {
        $cachePath = storage_path('framework/cache/data');

        if (! File::exists($cachePath)) {
            $this->warn('⚠️  Cache directory not found');

            return;
        }

        // Підрахунок файлів
        $files = File::allFiles($cachePath);
        $totalSize = 0;
        $totalKeys = 0;
        $expiredKeys = 0;
        $validKeys = 0;

        foreach ($files as $file) {
            $totalSize += $file->getSize();
            $totalKeys++;

            // Перевірка чи файл валідний
            try {
                $content = File::get($file->getPathname());
                $data = unserialize($content);

                if (is_array($data) && isset($data[0])) {
                    $expiration = $data[0];
                    if ($expiration === 0 || $expiration >= time()) {
                        $validKeys++;
                    } else {
                        $expiredKeys++;
                    }
                }
            } catch (\Exception $e) {
                // Ігноруємо помилки читання
            }
        }

        // Форматуємо розмір
        $sizeFormatted = $this->formatBytes($totalSize);

        // Виводимо статистику
        $this->table(
            ['Metric', 'Value'],
            [
                ['Total Keys', number_format($totalKeys)],
                ['Valid Keys', "<fg=green>{$validKeys}</>"],
                ['Expired Keys', $expiredKeys > 0 ? "<fg=red>{$expiredKeys}</>" : $expiredKeys],
                ['Total Size', $sizeFormatted],
                ['Average Key Size', $totalKeys > 0 ? $this->formatBytes($totalSize / $totalKeys) : '0 B'],
            ]
        );

        // Hit rate (симульовано - для file driver складно відстежити)
        if ($validKeys > 0) {
            $hitRate = round(($validKeys / $totalKeys) * 100, 1);
            $this->newLine();
            $this->line("📈 <fg=cyan>Estimated Hit Rate:</> <fg=green>{$hitRate}%</>");
        }

        // Рекомендації
        if ($expiredKeys > 10) {
            $this->newLine();
            $this->warn("💡 Tip: Run 'php artisan cache:clear' to remove {$expiredKeys} expired keys");
        }

        if ($totalSize > 50 * 1024 * 1024) { // > 50MB
            $this->newLine();
            $this->warn("⚠️  Cache size is large ({$sizeFormatted}). Consider clearing old entries.");
        }
    }

    /**
     * Показати статистику для Redis driver
     */
    private function showRedisDriverStats(): void
    {
        try {
            $store = Cache::getStore();

            // Перевірка чи store підтримує Redis
            if (! method_exists($store, 'connection')) {
                $this->warn('⚠️  Redis connection method not available');

                return;
            }

            $redis = $store->connection();
            $info = $redis->info();

            $this->table(
                ['Metric', 'Value'],
                [
                    ['Connected Clients', $info['connected_clients'] ?? 'N/A'],
                    ['Used Memory', $this->formatBytes((int) ($info['used_memory'] ?? 0))],
                    ['Total Keys', $redis->dbSize()],
                    ['Hit Rate', isset($info['keyspace_hits'], $info['keyspace_misses'])
                        ? $this->calculateHitRate((int) $info['keyspace_hits'], (int) $info['keyspace_misses'])
                        : 'N/A',
                    ],
                ]
            );
        } catch (\Exception $e) {
            $this->error("❌ Failed to get Redis stats: {$e->getMessage()}");
        }
    }

    /**
     * Показати статистику для Memcached driver
     */
    private function showMemcachedDriverStats(): void
    {
        try {
            $store = Cache::getStore();

            // Перевірка чи store підтримує Memcached
            if (! method_exists($store, 'getMemcached')) {
                $this->warn('⚠️  Memcached connection method not available');

                return;
            }

            $memcached = $store->getMemcached();
            $stats = $memcached->getStats();

            if (empty($stats) || ! is_array($stats)) {
                $this->warn('⚠️  No Memcached stats available');

                return;
            }

            $firstServer = array_values($stats)[0] ?? [];

            $this->table(
                ['Metric', 'Value'],
                [
                    ['Total Items', $firstServer['curr_items'] ?? 'N/A'],
                    ['Memory Used', $this->formatBytes((int) ($firstServer['bytes'] ?? 0))],
                    ['Memory Available', $this->formatBytes((int) ($firstServer['limit_maxbytes'] ?? 0))],
                    ['Hit Rate', isset($firstServer['get_hits'], $firstServer['get_misses'])
                        ? $this->calculateHitRate((int) $firstServer['get_hits'], (int) $firstServer['get_misses'])
                        : 'N/A',
                    ],
                ]
            );
        } catch (\Exception $e) {
            $this->error("❌ Failed to get Memcached stats: {$e->getMessage()}");
        }
    }

    /**
     * Показати статистику для Array driver
     */
    private function showArrayDriverStats(): void
    {
        $this->warn('⚠️  Array driver is in-memory only (used for testing)');
        $this->line('No persistent statistics available.');
    }

    /**
     * Форматувати байти в читабельний формат
     */
    private function formatBytes(int $bytes, int $precision = 2): string
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];

        for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
            $bytes /= 1024;
        }

        return round($bytes, $precision).' '.$units[$i];
    }

    /**
     * Розрахувати hit rate
     */
    private function calculateHitRate(int $hits, int $misses): string
    {
        $total = $hits + $misses;
        if ($total === 0) {
            return '0%';
        }

        $hitRate = round(($hits / $total) * 100, 1);
        $color = $hitRate >= 80 ? 'green' : ($hitRate >= 50 ? 'yellow' : 'red');

        return "<fg={$color}>{$hitRate}%</>";
    }
}
