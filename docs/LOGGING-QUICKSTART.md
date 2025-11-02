# 🚀 Швидкий старт - Система логування

## Що вже працює автоматично

✅ **HTTP запити** — логуються автоматично  
✅ **SQL запити** — логуються в dev mode (APP_DEBUG=true)  
✅ **Помилки** — всі винятки логуються з повним контекстом  
✅ **JavaScript помилки** — відправляються на сервер  
✅ **Фінансові транзакції** — логуються при створенні

---

## Як переглянути логи

### Швидкий перегляд

```bash
# Останні помилки
php artisan log:view errors

# Останні HTTP запити
php artisan log:view requests

# Повільні SQL запити
php artisan log:view slow-queries

# Моніторинг в реальному часі
php artisan log:view errors --tail
```

---

## Як додати логування в свій код

### PHP (Backend)

```php
// У контролері
Log::info('User action', ['user_id' => auth()->id()]);

// У сервісі
Log::channel('transactions')->info('Payment processed', [
    'amount' => $amount,
    'user_id' => $user->id
]);

// При помилці
Log::error('Something failed', [
    'error' => $exception->getMessage(),
    'context' => $data
]);
```

### JavaScript (Frontend)

```javascript
// Автоматично працює для всіх необроблених помилок

// Або вручну
logger.error('Button click failed', { button: 'submit' });
logger.warn('Slow API response', { duration: 2000 });
logger.event('User action', { action: 'export' });
```

---

## Діагностика проблем

### Користувач скаржиться на помилку

```bash
# 1. Перевір останні помилки
php artisan log:view errors --lines=100

# 2. Пошук по user_id
php artisan log:view errors --search="user_id\":5"

# 3. Аналіз найчастіших помилок
php artisan log:analyze errors
```

### Сайт працює повільно

```bash
# 1. Перевір повільні запити
php artisan log:view slow-queries

# 2. Аналіз SQL
php artisan log:analyze slow-queries

# 3. Перевір HTTP запити
php artisan log:analyze requests
```

---

## Де знаходяться логи

```
storage/logs/
  ├── laravel-2025-11-02.log      # Основний
  ├── errors-2025-11-02.log       # Тільки помилки
  ├── queries-2025-11-02.log      # SQL запити
  ├── slow-queries-2025-11-02.log # Повільні SQL
  ├── requests-2025-11-02.log     # HTTP запити
  ├── transactions-2025-11-02.log # Фінансові операції
  └── security-2025-11-02.log     # Безпека
```

---

## Production налаштування

```env
# .env
APP_DEBUG=false          # Вимкне SQL логування
LOG_LEVEL=error          # Тільки помилки
LOG_STACK=errors         # Тільки errors канал
```

---

## Корисні команди

```bash
# Очистити старі логи
find storage/logs -name "*.log" -mtime +30 -delete

# Розмір логів
du -sh storage/logs/

# Останні 10 помилок
php artisan log:view errors --lines=10

# Статистика за тиждень
php artisan log:analyze errors --days=7
```

---

📚 **Детальна документація:** `docs/LOGGING-SYSTEM.md`
