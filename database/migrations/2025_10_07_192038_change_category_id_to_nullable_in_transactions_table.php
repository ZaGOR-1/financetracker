<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Для SQLite потрібно пересоздати таблицю, тому що ALTER COLUMN не підтримується
        if (Schema::getConnection()->getDriverName() === 'sqlite') {
            // Видаляємо старий foreign key constraint та додаємо новий з SET NULL
            Schema::disableForeignKeyConstraints();

            // Створюємо тимчасову таблицю з точною структурою
            Schema::create('transactions_temp', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->constrained()->onDelete('cascade');
                $table->foreignId('category_id')->nullable()->constrained()->onDelete('set null');
                $table->enum('type', ['income', 'expense']);
                $table->decimal('amount', 15, 2);
                $table->text('description')->nullable();
                $table->date('transaction_date');
                $table->timestamps();
                $table->string('currency', 3)->default('UAH');

                $table->index(['user_id', 'category_id', 'transaction_date']);
                $table->index(['user_id', 'type', 'transaction_date']);
                $table->index('transaction_date');
            });

            // Копіюємо дані з правильним порядком полів
            DB::statement('INSERT INTO transactions_temp (id, user_id, category_id, type, amount, description, transaction_date, created_at, updated_at, currency) SELECT id, user_id, category_id, type, amount, description, transaction_date, created_at, updated_at, currency FROM transactions');

            // Видаляємо стару таблицю
            Schema::drop('transactions');

            // Перейменовуємо нову таблицю
            Schema::rename('transactions_temp', 'transactions');

            Schema::enableForeignKeyConstraints();
        } else {
            // Для MySQL/PostgreSQL
            Schema::table('transactions', function (Blueprint $table) {
                $table->dropForeign(['category_id']);
                $table->foreignId('category_id')->nullable()->change();
                $table->foreign('category_id')->references('id')->on('categories')->onDelete('set null');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::getConnection()->getDriverName() === 'sqlite') {
            Schema::disableForeignKeyConstraints();

            Schema::create('transactions_temp', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->constrained()->onDelete('cascade');
                $table->foreignId('category_id')->constrained()->onDelete('restrict');
                $table->enum('type', ['income', 'expense']);
                $table->decimal('amount', 15, 2);
                $table->text('description')->nullable();
                $table->date('transaction_date');
                $table->timestamps();
                $table->string('currency', 3)->default('UAH');

                $table->index(['user_id', 'category_id', 'transaction_date']);
                $table->index(['user_id', 'type', 'transaction_date']);
                $table->index('transaction_date');
            });

            DB::statement('INSERT INTO transactions_temp (id, user_id, category_id, type, amount, description, transaction_date, created_at, updated_at, currency) SELECT id, user_id, category_id, type, amount, description, transaction_date, created_at, updated_at, currency FROM transactions');
            Schema::drop('transactions');
            Schema::rename('transactions_temp', 'transactions');

            Schema::enableForeignKeyConstraints();
        } else {
            Schema::table('transactions', function (Blueprint $table) {
                $table->dropForeign(['category_id']);
                $table->foreignId('category_id')->nullable(false)->change();
                $table->foreign('category_id')->references('id')->on('categories')->onDelete('restrict');
            });
        }
    }
};
