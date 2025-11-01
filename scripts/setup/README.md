# 🛠️ Setup Scripts

Скрипти для початкового налаштування системи та створення тестових даних.

## 📋 Список скриптів

### `create-test-transactions.php`
Створити тестові транзакції для демонстрації та тестування.

```bash
php setup/create-test-transactions.php
```

**Створює:**
- 5-10 транзакцій різних типів
- Доходи та витрати
- Різні категорії
- Поточний місяць

**Використання:**
- Тестування нових функцій
- Демонстрація системи
- Заповнення порожньої БД

### `create-multicurrency-transactions.php`
Створити мультивалютні тестові транзакції.

```bash
php setup/create-multicurrency-transactions.php
```

**Створює транзакції в:**
- 🇺🇦 UAH (гривні)
- 🇺🇸 USD (долари)
- 🇵🇱 PLN (злоті)

**Приклади:**
- Зарплата в PLN
- Фріланс у USD
- Комунальні в UAH
- Покупки в різних валютах

**Коли використовувати:**
- Тестування мультивалютності
- Перевірка конвертації
- Демонстрація валютних можливостей

### `run-migration.php`
Запустити міграції бази даних (альтернатива `php artisan migrate`).

```bash
php setup/run-migration.php
```

**Використання:**
- Коли `artisan` недоступний
- Кастомна логіка перед/після міграцій
- Автоматизація deployment

## 🎯 Типові сценарії

### Перше налаштування проекту
```bash
# 1. Встановити залежності
composer install
npm install

# 2. Налаштувати .env
cp .env.example .env
php artisan key:generate

# 3. Створити БД
touch database/database.sqlite

# 4. Запустити міграції
php artisan migrate

# 5. Створити тестові дані
php setup/create-test-transactions.php
php setup/create-multicurrency-transactions.php

# 6. Оновити курси валют
php ../currency/update-rates.php
```

### Демонстрація системи клієнту
```bash
# 1. Очистити існуючі дані
php artisan migrate:fresh

# 2. Створити користувача
php artisan db:seed --class=UserSeeder

# 3. Додати красиві демо-дані
php setup/create-test-transactions.php
php setup/create-multicurrency-transactions.php

# 4. Оновити курси
php artisan currency:update-rates

# 5. Запустити сервер
php artisan serve
```

### Тестування нової функції
```bash
# 1. Створити окрему тестову БД
cp database/database.sqlite database/test.sqlite

# 2. В .env змінити
DB_DATABASE=database/test.sqlite

# 3. Створити тестові дані
php setup/create-multicurrency-transactions.php

# 4. Тестувати функцію

# 5. Повернути основну БД
DB_DATABASE=database/database.sqlite
```

### Reset системи
```bash
# Повне очищення та перезапуск
php artisan migrate:fresh --seed
php setup/create-test-transactions.php
php artisan currency:update-rates
php artisan cache:clear
```

## 🔧 Створення власного setup скрипту

```php
<?php

require __DIR__ . '/../../vendor/autoload.php';

$app = require_once __DIR__ . '/../../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\User;
use App\Models\Transaction;
use App\Models\Category;

echo "🛠️ Setup: Creating custom data...\n";

// Знайти або створити користувача
$user = User::first() ?? User::factory()->create();

// Створити категорію
$category = Category::firstOrCreate([
    'user_id' => $user->id,
    'name' => 'Custom Category',
    'type' => 'income',
]);

// Створити транзакції
Transaction::create([
    'user_id' => $user->id,
    'category_id' => $category->id,
    'amount' => 1000,
    'currency' => 'UAH',
    'description' => 'Test transaction',
    'transaction_date' => now(),
]);

echo "✅ Done!\n";
```

## ⚠️ Важливо

### Перед запуском на production:
- ❌ **НЕ запускати** create-test-transactions на реальних даних!
- ❌ **НЕ запускати** migrate:fresh на production БД!
- ✅ Зробити backup перед будь-якими змінами
- ✅ Тестувати на копії БД

### Backup перед setup:
```bash
# Створити backup
php ../backup.sh

# Або вручну
cp database/database.sqlite database/database.backup.sqlite
```

## 📚 Документація

Детальні інструкції по налаштуванню: `README.md` (корінь проекту)

Міграції: `docs/deployment.md`
