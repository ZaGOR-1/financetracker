<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class LogViewer extends Command
{
    protected $signature = 'log:view
                          {type=laravel : Тип логу (laravel|errors|queries|slow-queries|requests|performance|transactions|security)}
                          {--lines=50 : Кількість останніх рядків}
                          {--tail : Режим tail (постійний перегляд)}
                          {--search= : Пошук по тексту}';

    protected $description = 'Переглянути логи застосунку';

    protected $logPaths = [
        'laravel' => 'laravel.log',
        'errors' => 'errors.log',
        'queries' => 'queries.log',
        'slow-queries' => 'slow-queries.log',
        'requests' => 'requests.log',
        'performance' => 'performance.log',
        'transactions' => 'transactions.log',
        'security' => 'security.log',
    ];

    public function handle(): int
    {
        $type = $this->argument('type');
        $lines = $this->option('lines');
        $tail = $this->option('tail');
        $search = $this->option('search');

        if (! isset($this->logPaths[$type])) {
            $this->error("Невідомий тип логу: {$type}");
            $this->info('Доступні типи: '.implode(', ', array_keys($this->logPaths)));

            return 1;
        }

        $logFile = $this->getLatestLogFile($type);

        if (! $logFile || ! File::exists($logFile)) {
            $this->error("Лог файл не знайдено: {$logFile}");

            return 1;
        }

        $this->info("📋 Лог: {$type} ({$logFile})");
        $this->newLine();

        if ($tail) {
            $this->tailLog($logFile, $search);
        } else {
            $this->showLog($logFile, $lines, $search);
        }

        return 0;
    }

    /**
     * Отримати останній лог файл для типу
     */
    protected function getLatestLogFile(string $type): ?string
    {
        $baseName = $this->logPaths[$type];
        $logDir = storage_path('logs');

        // Для daily логів шукаємо останній файл
        $pattern = str_replace('.log', '-*.log', $baseName);
        $files = glob("{$logDir}/{$pattern}");

        if (! empty($files)) {
            // Сортуємо за датою модифікації
            usort($files, fn ($a, $b) => filemtime($b) - filemtime($a));

            return $files[0];
        }

        // Якщо daily файлів немає, перевіряємо базовий файл
        $baseFile = "{$logDir}/{$baseName}";

        return File::exists($baseFile) ? $baseFile : null;
    }

    /**
     * Показати останні рядки логу
     */
    protected function showLog(string $logFile, int $lines, ?string $search): void
    {
        $command = "tail -n {$lines} ".escapeshellarg($logFile);

        if ($search) {
            $command .= ' | grep '.escapeshellarg($search);
        }

        $output = shell_exec($command);

        if ($output) {
            $this->line($this->highlightLog($output));
        } else {
            $this->warn('Лог порожній або не знайдено результатів пошуку.');
        }
    }

    /**
     * Tail режим (постійний перегляд)
     */
    protected function tailLog(string $logFile, ?string $search): void
    {
        $this->info('🔄 Режим tail активовано (Ctrl+C для виходу)');
        $this->newLine();

        $command = 'tail -f '.escapeshellarg($logFile);

        if ($search) {
            $command .= ' | grep --line-buffered '.escapeshellarg($search);
        }

        passthru($command);
    }

    /**
     * Підсвічування логів (кольори)
     */
    protected function highlightLog(string $content): string
    {
        // ERROR - червоний
        $content = preg_replace('/\[(\d{4}-\d{2}-\d{2}[^\]]+)\] local\.ERROR:(.+)/', '<fg=red>[$1] local.ERROR:$2</>', $content);

        // WARNING - жовтий
        $content = preg_replace('/\[(\d{4}-\d{2}-\d{2}[^\]]+)\] local\.WARNING:(.+)/', '<fg=yellow>[$1] local.WARNING:$2</>', $content);

        // INFO - синій
        $content = preg_replace('/\[(\d{4}-\d{2}-\d{2}[^\]]+)\] local\.INFO:(.+)/', '<fg=blue>[$1] local.INFO:$2</>', $content);

        // DEBUG - сірий
        $content = preg_replace('/\[(\d{4}-\d{2}-\d{2}[^\]]+)\] local\.DEBUG:(.+)/', '<fg=gray>[$1] local.DEBUG:$2</>', $content);

        return $content;
    }
}
