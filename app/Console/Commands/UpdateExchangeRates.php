<?php

namespace App\Console\Commands;

use App\Services\CurrencyService;
use Illuminate\Console\Command;

class UpdateExchangeRates extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'currency:update-rates {--date= : Дата для оновлення курсів (Y-m-d)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Оновити курси валют з API Національного банку України';

    /**
     * Execute the console command.
     */
    public function handle(CurrencyService $currencyService): int
    {
        $this->info('🔄 Оновлення курсів валют...');
        $this->newLine();

        $date = $this->option('date') ? new \DateTime($this->option('date')) : null;

        try {
            $results = $currencyService->updateAllRates($date);

            $successful = 0;
            $failed = 0;

            foreach ($results as $pair => $result) {
                if ($result['success']) {
                    $this->info("✅ {$pair}: " . number_format($result['rate'], 6));
                    $successful++;
                } else {
                    $this->error("❌ {$pair}: {$result['error']}");
                    $failed++;
                }
            }

            $this->newLine();
            $this->info("📊 Результат:");
            $this->line("  Успішно: {$successful}");
            
            if ($failed > 0) {
                $this->line("  Помилки: {$failed}");
            }

            return self::SUCCESS;
        } catch (\Exception $e) {
            $this->error('❌ Помилка: ' . $e->getMessage());
            return self::FAILURE;
        }
    }
}
