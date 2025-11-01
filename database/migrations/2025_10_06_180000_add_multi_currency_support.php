<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Додаємо підтримку валют до транзакцій
        Schema::table('transactions', function (Blueprint $table) {
            $table->string('currency', 3)->default('UAH')->after('amount');
            $table->index('currency');
        });

        // Додаємо базову валюту для користувачів
        Schema::table('users', function (Blueprint $table) {
            $table->string('default_currency', 3)->default('UAH')->after('email');
        });

        // Створюємо таблицю для зберігання курсів валют
        Schema::create('exchange_rates', function (Blueprint $table) {
            $table->id();
            $table->string('base_currency', 3);      // UAH
            $table->string('target_currency', 3);    // USD, PLN
            $table->decimal('rate', 12, 6);          // 0.025000 (UAH -> USD)
            $table->date('date');                    // Дата курсу
            $table->timestamps();

            // Унікальність: одна пара валют на день
            $table->unique(['base_currency', 'target_currency', 'date'], 'unique_rate_per_day');
            $table->index(['base_currency', 'date']);
            $table->index(['target_currency', 'date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('exchange_rates');
        
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('default_currency');
        });
        
        Schema::table('transactions', function (Blueprint $table) {
            $table->dropIndex(['currency']);
            $table->dropColumn('currency');
        });
    }
};
