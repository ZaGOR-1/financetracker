<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Додаємо додаткові індекси для оптимізації запитів
     * Покращує продуктивність фільтрації, сортування та аналітики
     */
    public function up(): void
    {
        // Додаткові індекси для таблиці transactions
        Schema::table('transactions', function (Blueprint $table) {
            // Індекс для фільтрації по типу (income/expense)
            // Використовується в фільтрах дашборду
            $table->index('type', 'transactions_type_index');

            // Композитний індекс для запитів статистики за період
            // Використовується: WHERE user_id = ? AND transaction_date BETWEEN ? AND ?
            $table->index(['user_id', 'transaction_date'], 'transactions_user_date_index');

            // Композитний індекс для фільтрації за користувачем і типом
            // Використовується: WHERE user_id = ? AND type = ?
            $table->index(['user_id', 'type'], 'transactions_user_type_index');

            // Композитний індекс для аналітики по категоріях
            // Використовується: WHERE category_id = ? AND transaction_date BETWEEN ? AND ?
            $table->index(['category_id', 'transaction_date'], 'transactions_category_date_index');
        });

        // Додаткові індекси для таблиці categories
        Schema::table('categories', function (Blueprint $table) {
            // Індекс для швидкого пошуку активних категорій
            // Використовується: WHERE is_active = 1
            $table->index('is_active', 'categories_is_active_index');

            // Індекс для пошуку по типу
            $table->index('type', 'categories_type_index');

            // Композитний індекс для фільтрації активних категорій користувача за типом
            // Використовується: WHERE user_id = ? AND type = ? AND is_active = 1
            $table->index(['user_id', 'type', 'is_active'], 'categories_user_type_active_index');
        });

        // Додаткові індекси для таблиці budgets
        Schema::table('budgets', function (Blueprint $table) {
            // Індекс для пошуку активних бюджетів
            $table->index('is_active', 'budgets_is_active_index');

            // Індекс для періоду бюджету
            $table->index('period', 'budgets_period_index');

            // Композитний індекс для пошуку активних бюджетів користувача
            // Використовується: WHERE user_id = ? AND is_active = 1
            $table->index(['user_id', 'is_active'], 'budgets_user_active_index');

            // Композитний індекс для пошуку бюджетів за датами
            // Використовується для перевірки перекриття періодів
            $table->index(['start_date', 'end_date'], 'budgets_dates_index');
        });

        // Індекси для таблиці users (якщо потрібно)
        Schema::table('users', function (Blueprint $table) {
            // Індекс для валюти користувача
            $table->index('default_currency', 'users_default_currency_index');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Видаляємо індекси у зворотному порядку
        Schema::table('users', function (Blueprint $table) {
            $table->dropIndex('users_default_currency_index');
        });

        Schema::table('budgets', function (Blueprint $table) {
            $table->dropIndex('budgets_dates_index');
            $table->dropIndex('budgets_user_active_index');
            $table->dropIndex('budgets_period_index');
            $table->dropIndex('budgets_is_active_index');
        });

        Schema::table('categories', function (Blueprint $table) {
            $table->dropIndex('categories_user_type_active_index');
            $table->dropIndex('categories_type_index');
            $table->dropIndex('categories_is_active_index');
        });

        Schema::table('transactions', function (Blueprint $table) {
            $table->dropIndex('transactions_category_date_index');
            $table->dropIndex('transactions_user_type_index');
            $table->dropIndex('transactions_user_date_index');
            $table->dropIndex('transactions_type_index');
        });
    }
};
