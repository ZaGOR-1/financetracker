<?php

namespace App\Console\Commands;

use App\Services\CurrencyService;
use Illuminate\Console\Command;

class UpdateCurrencyRates extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'currency:update-rates';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Оновити курси валют з ExchangeRate-API';

    /**
     * Execute the console command.
     */
    public function handle(CurrencyService $currencyService): int
    {
        $this->info('🔄 Оновлення курсів валют...');
        $this->newLine();

        try {
            $results = $currencyService->updateAllRates();

            $success = 0;
            $failed = 0;

            foreach ($results as $pair => $result) {
                if ($result['success']) {
                    $this->line("✅ {$pair}: ".number_format($result['rate'], 6));
                    $success++;
                } else {
                    $this->error("❌ {$pair}: ".$result['error']);
                    $failed++;
                }
            }

            $this->newLine();
            $this->info("📊 Успішно оновлено: {$success}");

            if ($failed > 0) {
                $this->warn("⚠️  Помилки: {$failed}");
            }

            $this->newLine();
            $this->info('✅ Оновлення завершено!');

            return Command::SUCCESS;

        } catch (\Exception $e) {
            $this->error('❌ Помилка: '.$e->getMessage());

            return Command::FAILURE;
        }
    }
}
