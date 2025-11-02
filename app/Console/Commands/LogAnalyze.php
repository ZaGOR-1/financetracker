<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class LogAnalyze extends Command
{
    protected $signature = 'log:analyze
                          {type=errors : Тип логу для аналізу}
                          {--days=1 : Кількість днів для аналізу}';

    protected $description = 'Аналіз логів: статистика помилок, повільних запитів тощо';

    public function handle(): int
    {
        $type = $this->argument('type');
        $days = $this->option('days');

        $this->info("📊 Аналіз логів: {$type} (за останні {$days} днів)");
        $this->newLine();

        match ($type) {
            'errors' => $this->analyzeErrors($days),
            'slow-queries' => $this->analyzeSlowQueries($days),
            'requests' => $this->analyzeRequests($days),
            'security' => $this->analyzeSecurity($days),
            default => $this->error("Невідомий тип аналізу: {$type}"),
        };

        return 0;
    }

    /**
     * Аналіз помилок
     */
    protected function analyzeErrors(int $days): void
    {
        $logFiles = $this->getLogFiles('errors', $days);
        $errors = [];
        $totalErrors = 0;

        foreach ($logFiles as $file) {
            $content = File::get($file);
            preg_match_all('/local\.ERROR: (.+?) {/', $content, $matches);

            foreach ($matches[1] as $error) {
                $errors[$error] = ($errors[$error] ?? 0) + 1;
                $totalErrors++;
            }
        }

        $this->table(
            ['Помилка', 'Кількість', 'Відсоток'],
            collect($errors)
                ->sortDesc()
                ->take(10)
                ->map(fn ($count, $error) => [
                    substr($error, 0, 60).'...',
                    $count,
                    round(($count / $totalErrors) * 100, 2).'%',
                ])
                ->values()
                ->toArray()
        );

        $this->info("✅ Загальна кількість помилок: {$totalErrors}");
    }

    /**
     * Аналіз повільних запитів
     */
    protected function analyzeSlowQueries(int $days): void
    {
        $logFiles = $this->getLogFiles('slow-queries', $days);
        $queries = [];
        $totalQueries = 0;

        foreach ($logFiles as $file) {
            $content = File::get($file);
            preg_match_all('/"sql":"(.+?)","time_ms":(\d+)/', $content, $matches, PREG_SET_ORDER);

            foreach ($matches as $match) {
                $sql = substr($match[1], 0, 100);
                $time = (int) $match[2];

                if (! isset($queries[$sql])) {
                    $queries[$sql] = ['count' => 0, 'total_time' => 0, 'max_time' => 0];
                }

                $queries[$sql]['count']++;
                $queries[$sql]['total_time'] += $time;
                $queries[$sql]['max_time'] = max($queries[$sql]['max_time'], $time);
                $totalQueries++;
            }
        }

        $this->table(
            ['SQL (перші 100 символів)', 'Кількість', 'Середній час (ms)', 'Макс. час (ms)'],
            collect($queries)
                ->sortByDesc(fn ($data) => $data['total_time'])
                ->take(10)
                ->map(fn ($data, $sql) => [
                    $sql.'...',
                    $data['count'],
                    round($data['total_time'] / $data['count'], 2),
                    $data['max_time'],
                ])
                ->values()
                ->toArray()
        );

        $this->info("✅ Загальна кількість повільних запитів: {$totalQueries}");
    }

    /**
     * Аналіз HTTP запитів
     */
    protected function analyzeRequests(int $days): void
    {
        $logFiles = $this->getLogFiles('requests', $days);
        $stats = [
            'total' => 0,
            'methods' => [],
            'status_codes' => [],
            'slow_requests' => 0,
        ];

        foreach ($logFiles as $file) {
            $content = File::get($file);
            preg_match_all('/"method":"(.+?)".+"status":(\d+).+"duration_ms":(\d+\.\d+)/', $content, $matches, PREG_SET_ORDER);

            foreach ($matches as $match) {
                $stats['total']++;
                $stats['methods'][$match[1]] = ($stats['methods'][$match[1]] ?? 0) + 1;
                $stats['status_codes'][$match[2]] = ($stats['status_codes'][$match[2]] ?? 0) + 1;

                if ((float) $match[3] > 1000) {
                    $stats['slow_requests']++;
                }
            }
        }

        $this->info("📊 Загальна кількість запитів: {$stats['total']}");
        $this->info("🐌 Повільних запитів (>1s): {$stats['slow_requests']}");
        $this->newLine();

        $this->line('<fg=cyan>HTTP методи:</>');
        foreach ($stats['methods'] as $method => $count) {
            $this->line("  {$method}: {$count}");
        }

        $this->newLine();
        $this->line('<fg=cyan>Статус коди:</>');
        foreach ($stats['status_codes'] as $status => $count) {
            $color = $status >= 400 ? 'red' : ($status >= 300 ? 'yellow' : 'green');
            $this->line("  <fg={$color}>{$status}</>: {$count}");
        }
    }

    /**
     * Аналіз безпеки
     */
    protected function analyzeSecurity(int $days): void
    {
        $logFiles = $this->getLogFiles('security', $days);
        $events = [];

        foreach ($logFiles as $file) {
            $content = File::get($file);
            preg_match_all('/local\.(WARNING|ERROR|INFO): (.+?) {/', $content, $matches, PREG_SET_ORDER);

            foreach ($matches as $match) {
                $level = $match[1];
                $event = $match[2];
                $key = "{$level}: {$event}";
                $events[$key] = ($events[$key] ?? 0) + 1;
            }
        }

        $this->table(
            ['Подія безпеки', 'Кількість'],
            collect($events)
                ->sortDesc()
                ->map(fn ($count, $event) => [$event, $count])
                ->values()
                ->toArray()
        );
    }

    /**
     * Отримати лог файли за період
     */
    protected function getLogFiles(string $type, int $days): array
    {
        $files = [];
        $logDir = storage_path('logs');

        for ($i = 0; $i < $days; $i++) {
            $date = now()->subDays($i)->format('Y-m-d');
            $file = "{$logDir}/{$type}-{$date}.log";

            if (File::exists($file)) {
                $files[] = $file;
            }
        }

        return $files;
    }
}
