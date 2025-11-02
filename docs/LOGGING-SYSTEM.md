# 📊 Система логування Finance Tracker

## Огляд

Повна система логування для відстеження всіх подій, помилок, SQL запитів, HTTP запитів та користувацьких дій.

---

## 📁 Структура логів

### 1. **Канали логування**

| Канал | Файл | Опис | Період зберігання |
|-------|------|------|-------------------|
| **daily** | `logs/laravel-YYYY-MM-DD.log` | Основний лог усіх подій | 14 днів |
| **errors** | `logs/errors-YYYY-MM-DD.log` | Тільки помилки (error level) | 30 днів |
| **queries** | `logs/queries-YYYY-MM-DD.log` | Всі SQL запити (dev mode) | 7 днів |
| **slow_queries** | `logs/slow-queries-YYYY-MM-DD.log` | Повільні запити (>100ms) | 14 днів |
| **requests** | `logs/requests-YYYY-MM-DD.log` | HTTP запити та відповіді | 7 днів |
| **performance** | `logs/performance-YYYY-MM-DD.log` | Метрики продуктивності | 7 днів |
| **transactions** | `logs/transactions-YYYY-MM-DD.log` | Фінансові транзакції | 30 днів |
| **security** | `logs/security-YYYY-MM-DD.log` | Безпека та аутентифікація | 90 днів |

---

## 🔧 Конфігурація

### **.env параметри**

```env
LOG_CHANNEL=stack
LOG_STACK=daily,errors
LOG_LEVEL=debug
```

### **Налаштування в `config/logging.php`**

Всі канали вже налаштовані з daily rotation та автоматичним видаленням старих файлів.

---

## 🎯 Що логується

### 1. **HTTP запити** (middleware `LogRequests`)

**Логується:**
- Метод, URL, IP, User Agent
- User ID та email (якщо авторизований)
- Параметри запиту (без паролів)
- Статус відповіді
- Час виконання (ms)

**Автоматично:**
- ❗ Error log якщо status >= 400
- ⚠️ Warning якщо запит > 1000ms

**Приклад:**
```json
{
  "method": "POST",
  "url": "http://example.com/transactions",
  "status": 201,
  "duration_ms": 156.23,
  "user_id": 1
}
```

---

### 2. **SQL запити** (AppServiceProvider)

**Логується:**
- Всі SQL запити (тільки в debug mode)
- Час виконання
- Bindings (параметри)

**Автоматично:**
- ⚠️ Warning для повільних запитів (>100ms)

**Приклад:**
```json
{
  "sql": "SELECT * FROM transactions WHERE user_id = 1",
  "time_ms": 45.67,
  "bindings": [1]
}
```

---

### 3. **Винятки** (Exception Handler)

**Логується:**
- Тип винятку
- Повідомлення та код
- Файл та рядок
- Stack trace
- URL, метод, IP
- User ID та input даних

**Спеціальні категорії:**
- Database помилки → `queries.log`
- Auth помилки → `security.log`

**Приклад:**
```json
{
  "exception": "QueryException",
  "message": "SQLSTATE[23000]: Integrity constraint violation",
  "file": "app/Services/TransactionService.php",
  "line": 45,
  "user_id": 1
}
```

---

### 4. **Фінансові транзакції** (TransactionService)

**Логується:**
- Створення транзакції
- Валідаційні помилки
- Успішне завершення

**Приклад:**
```
[INFO] Creating transaction | user_id=1 type=expense amount=100.50
[INFO] Transaction created successfully | transaction_id=123
```

---

### 5. **Frontend помилки** (JavaScript logger)

**Логується:**
- Uncaught JavaScript errors
- Promise rejections
- API помилки
- Користувацькі події

**Відправляється на:**
`POST /api/log` → `errors.log`

**Приклад використання:**
```javascript
// Автоматично
throw new Error('Something went wrong'); // → логується

// Вручну
logger.error('Payment failed', { orderId: 123 });
logger.warn('Connection slow', { ping: 500 });
logger.event('Button clicked', { button: 'checkout' });
```

---

## 📖 Команди для роботи з логами

### **1. Перегляд логів**

```bash
# Останні 50 рядків основного логу
php artisan log:view

# Перегляд помилок
php artisan log:view errors

# Повільні запити
php artisan log:view slow-queries

# Останні 100 рядків
php artisan log:view --lines=100

# Режим tail (постійний перегляд)
php artisan log:view errors --tail

# Пошук по тексту
php artisan log:view --search="QueryException"
```

**Доступні типи:**
- `laravel` (за замовчуванням)
- `errors`
- `queries`
- `slow-queries`
- `requests`
- `performance`
- `transactions`
- `security`

---

### **2. Аналіз логів**

```bash
# Аналіз помилок за останній день
php artisan log:analyze errors

# Топ 10 помилок за 7 днів
php artisan log:analyze errors --days=7

# Аналіз повільних запитів
php artisan log:analyze slow-queries

# Статистика HTTP запитів
php artisan log:analyze requests

# Події безпеки
php artisan log:analyze security
```

**Виводить:**
- Топ найчастіших помилок
- Повільні SQL запити
- HTTP статистику (методи, статус коди)
- Події безпеки

---

## 🚀 Використання в коді

### **PHP (Backend)**

```php
use Illuminate\Support\Facades\Log;

// Основний лог
Log::info('User logged in', ['user_id' => $user->id]);

// Помилки
Log::error('Payment failed', [
    'user_id' => $user->id,
    'amount' => $amount,
    'error' => $exception->getMessage()
]);

// Окремі канали
Log::channel('transactions')->info('Transaction created', [
    'transaction_id' => $transaction->id,
    'amount' => $transaction->amount
]);

Log::channel('security')->warning('Failed login attempt', [
    'email' => $email,
    'ip' => $request->ip()
]);

Log::channel('performance')->warning('Slow operation', [
    'operation' => 'report_generation',
    'duration_ms' => 1500
]);
```

---

### **JavaScript (Frontend)**

```javascript
import { logger } from './utils/logger.js';

// Помилки
logger.error('API request failed', {
    endpoint: '/api/transactions',
    status: 500
});

// Попередження
logger.warn('Slow response', { duration: 2000 });

// Події
logger.event('Report generated', { type: 'monthly' });

// API запити
try {
    const response = await fetch('/api/data');
    logger.apiRequest('GET', '/api/data');
} catch (error) {
    logger.apiError('GET', '/api/data', error);
}

// Debug (тільки development)
logger.debug('Cache hit', { key: 'user_stats' });
```

**Глобальний доступ:**
```javascript
window.logger.error('Something went wrong');
```

---

## 🔍 Приклади діагностики

### **Знайти всі помилки користувача**

```bash
php artisan log:view errors --search="user_id\":1"
```

### **Повільні запити за сьогодні**

```bash
php artisan log:view slow-queries --lines=100
```

### **Аналіз найчастіших помилок**

```bash
php artisan log:analyze errors --days=7
```

### **Моніторинг в реальному часі**

```bash
php artisan log:view errors --tail
```

---

## 📊 Метрики та KPI

### **Автоматичне відстеження:**

✅ **HTTP запити:**
- Загальна кількість
- Розподіл за методами (GET/POST/PUT/DELETE)
- Розподіл за статусами (200/400/500)
- Повільні запити (>1s)

✅ **SQL запити:**
- Всі запити (dev mode)
- Повільні запити (>100ms)
- Середній час виконання

✅ **Помилки:**
- Загальна кількість
- Топ найчастіших
- Розподіл за типами

✅ **Безпека:**
- Невдалі спроби входу
- Аутентифікаційні помилки
- Підозрілі дії

---

## ⚙️ Production налаштування

### **Рекомендації:**

1. **Вимкніть SQL логування:**
```env
APP_DEBUG=false
```

2. **Змініть LOG_LEVEL:**
```env
LOG_LEVEL=error
```

3. **Налаштуйте ротацію:**
```php
'days' => 30, // в config/logging.php
```

4. **Моніторинг логів:**
```bash
# Cron job для аналізу щодня
0 9 * * * php /path/to/artisan log:analyze errors --days=1
```

---

## 🛡️ Безпека

### **Автоматичне приховування чутливих даних:**

Наступні поля автоматично маскуються:
- `password`
- `password_confirmation`
- `current_password`
- `token`
- `_token`
- `secret`
- `key`
- `card`

**Приклад:**
```json
{
  "email": "user@example.com",
  "password": "***REDACTED***"
}
```

---

## 📚 Додаткові ресурси

- [Laravel Logging Documentation](https://laravel.com/docs/10.x/logging)
- [Monolog Documentation](https://github.com/Seldaek/monolog)
- Логи зберігаються в `storage/logs/`

---

## ✅ Чеклист для розробника

- [ ] Логуйте всі критичні операції
- [ ] Використовуйте правильний рівень (debug/info/warning/error)
- [ ] Додавайте контекст (user_id, дані запиту)
- [ ] Не логуйте паролі та чутливі дані
- [ ] Регулярно аналізуйте логи помилок
- [ ] Моніторте повільні запити
- [ ] Налаштуйте алерти для критичних помилок

---

**Дата оновлення:** 2 листопада 2025  
**Версія:** 1.0
