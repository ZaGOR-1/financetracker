<?php

namespace App\Console\Commands;

use App\Models\Budget;
use App\Notifications\BudgetExceededNotification;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;

class CheckBudgetsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'budgets:check
                            {--force : Force notification even if already sent today}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check budgets and send notifications for exceeded or warning thresholds';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('Checking budgets...');

        $budgets = Budget::query()
            ->with(['user', 'category'])
            ->where('is_active', true)
            ->whereDate('start_date', '<=', now())
            ->whereDate('end_date', '>=', now())
            ->get();

        if ($budgets->isEmpty()) {
            $this->info('No active budgets found.');

            return Command::SUCCESS;
        }

        $notificationsSent = 0;
        $force = $this->option('force');

        foreach ($budgets as $budget) {
            $percentage = $budget->percentage;
            $alertType = null;

            // Визначаємо тип сповіщення
            if ($percentage > 100) {
                $alertType = 'exceeded';
            } elseif ($percentage >= $budget->alert_threshold) {
                $alertType = 'warning';
            }

            if (! $alertType) {
                continue; // Бюджет в нормі
            }

            // Перевіряємо, чи вже відправляли сповіщення сьогодні
            $cacheKey = "budget_alert_{$budget->id}_{$alertType}_".now()->format('Y-m-d');

            if (! $force && Cache::has($cacheKey)) {
                $this->line("  Skipping budget #{$budget->id} - notification already sent today");

                continue;
            }

            // Відправляємо нотифікацію
            try {
                $budget->user->notify(new BudgetExceededNotification($budget, $alertType));

                // Зберігаємо мітку, що сповіщення відправлено (на 24 години)
                Cache::put($cacheKey, true, now()->endOfDay());

                $categoryName = $budget->category?->name ?? 'Загальний бюджет';
                $icon = $alertType === 'exceeded' ? '🚨' : '⚠️';

                $this->line("  {$icon} Sent {$alertType} notification for '{$categoryName}' to {$budget->user->email} ({$percentage}%)");
                $notificationsSent++;
            } catch (\Exception $e) {
                $this->error("  Failed to send notification for budget #{$budget->id}: {$e->getMessage()}");
            }
        }

        $this->newLine();
        $this->info("Budget check completed. {$notificationsSent} notification(s) sent.");

        return Command::SUCCESS;
    }
}
