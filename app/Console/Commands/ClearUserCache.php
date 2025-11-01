<?php

namespace App\Console\Commands;

use App\Services\CacheService;
use Illuminate\Console\Command;

/**
 * Команда для очищення кешу користувача або всього застосунку
 */
class ClearUserCache extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'cache:clear-user 
                            {user_id? : ID користувача для очищення кешу}
                            {--type= : Тип кешу (transactions, categories, budgets, stats, all)}
                            {--flush : Очистити весь кеш застосунку}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Очистити кеш користувача або всього застосунку';

    /**
     * Execute the console command.
     */
    public function handle(CacheService $cacheService): int
    {
        if ($this->option('flush')) {
            $this->warn('Очищення всього кешу застосунку...');
            $cacheService->flush();
            $this->info('✓ Весь кеш очищено!');
            return self::SUCCESS;
        }

        $userIdInput = $this->argument('user_id');
        $type = $this->option('type');

        if (!$userIdInput) {
            $this->error('Необхідно вказати user_id або використати --flush');
            return self::FAILURE;
        }

        $userId = (int) $userIdInput;

        if ($type && !in_array($type, ['transactions', 'categories', 'budgets', 'stats', 'all'])) {
            $this->error('Невірний тип. Доступні: transactions, categories, budgets, stats, all');
            return self::FAILURE;
        }

        $this->info("Очищення кешу користувача #{$userId}...");

        switch ($type) {
            case 'transactions':
                $cacheService->forgetUserTransactions($userId);
                $this->info('✓ Кеш транзакцій очищено');
                break;

            case 'categories':
                $cacheService->forgetUserCategories($userId);
                $this->info('✓ Кеш категорій очищено');
                break;

            case 'budgets':
                $cacheService->forgetUserBudgets($userId);
                $this->info('✓ Кеш бюджетів очищено');
                break;

            case 'stats':
                $cacheService->forgetUserType('stats', $userId);
                $this->info('✓ Кеш статистики очищено');
                break;

            case 'all':
            default:
                $cacheService->forgetUser($userId);
                $this->info('✓ Весь кеш користувача очищено');
                break;
        }

        return self::SUCCESS;
    }
}
