<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     */
    protected function schedule(Schedule $schedule): void
    {
        // Перевірка бюджетів щодня о 09:00
        $schedule->command('budgets:check')
            ->dailyAt('09:00')
            ->withoutOverlapping()
            ->onOneServer();

        // Оновлення курсів валют щодня о 16:00 (після оновлення на ExchangeRate-API)
        $schedule->command('currency:update-rates')
            ->dailyAt('16:00')
            ->withoutOverlapping()
            ->onOneServer();
    }

    /**
     * Register the commands for the application.
     */
    protected function commands(): void
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}
