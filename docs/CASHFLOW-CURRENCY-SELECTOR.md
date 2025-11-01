# Вибір валюти в Cashflow

## Огляд

Функція дозволяє користувачам переглядати дані Cashflow в будь-якій підтримуваній валюті (UAH, USD, PLN, EUR), незалежно від їх базової валюти.

## Дата впровадження
6 жовтня 2025 р.

---

## Функціональність

### 1. Підтримувані валюти
- **UAH** (₴) - Українська гривня
- **USD** ($) - Долар США
- **PLN** (zł) - Польський злотий
- **EUR** (€) - Євро (підтримка готова, але поки не використовується)

### 2. UI компоненти

**Кнопки вибору валюти** розташовані на дашборді під назвою "Cashflow", поруч з фільтрами періодів:

```
Валюта: [₴ UAH] [$ USD] [zł PLN]
```

- **Активна кнопка**: синій фон (`bg-blue-600`)
- **Неактивна кнопка**: сірий фон з hover-ефектом
- **За замовчуванням**: UAH

### 3. Інтеграція з періодами

Вибір валюти працює разом з фільтрами періодів:
- 7 днів
- 14 днів
- 30 днів
- 3 місяці
- 6 місяців

Користувач може комбінувати будь-який період з будь-якою валютою.

---

## Технічна реалізація

### Backend

#### 1. API Endpoint

**URL**: `GET /api/v1/stats/cashflow`

**Параметри**:
```php
[
    'period' => 'nullable|string|in:7d,14d,30d,3m,6m',
    'currency' => 'nullable|string|in:UAH,USD,PLN,EUR'
]
```

**Приклад запиту**:
```
/api/v1/stats/cashflow?period=30d&currency=USD
```

**Відповідь**:
```json
{
    "success": true,
    "data": {
        "cashflow": [
            {
                "period": "2024-10-01",
                "income": 200.00,
                "expense": 15.50
            },
            ...
        ],
        "currency": "USD",
        "period": "30d"
    }
}
```

#### 2. StatsController

Файл: `app/Http/Controllers/Api/StatsController.php`

```php
public function cashflow(Request $request): JsonResponse
{
    $validated = $request->validate([
        'period' => 'nullable|string|in:7d,14d,30d,3m,6m',
        'currency' => 'nullable|string|in:UAH,USD,PLN,EUR',
    ]);

    $period = $validated['period'] ?? '6m';
    $currency = $validated['currency'] ?? null;

    $result = $this->statsService->getCashflow(
        $request->user()->id,
        $period,
        $currency
    );

    return response()->json([
        'success' => true,
        'data' => [
            'cashflow' => $result['data'],
            'currency' => $result['currency'],
            'period' => $period,
        ],
    ]);
}
```

#### 3. StatsService

Файл: `app/Services/StatsService.php`

**Метод**: `getCashflow(int $userId, string $period = '6m', ?string $targetCurrency = null): array`

**Логіка**:
1. Якщо `$targetCurrency` передано → конвертує у вказану валюту
2. Якщо `null` → використовує базову валюту користувача (`users.default_currency`)
3. Для кожної транзакції:
   - Перевіряє валюту транзакції
   - Якщо відрізняється від цільової → конвертує через `CurrencyService`
   - Додає до відповідного періоду

**Приклад**:
```php
// Конвертація кожної транзакції
if ($transaction->currency !== $baseCurrency) {
    $amount = $this->currencyService->convert(
        $amount,
        $transaction->currency,
        $baseCurrency,
        new \DateTime($transaction->transaction_date)
    );
}
```

#### 4. Currency Conversion

**Сервіс**: `CurrencyService`

- Використовує ExchangeRate-API.com для актуальних курсів
- Кешування на 1 годину
- Підтримує історичні курси (за датою транзакції)

**Поточні курси** (6 жовтня 2025):
- 1,000 UAH = 24.21 USD
- 1,000 UAH = 87.77 PLN
- 1,000 USD = 41,301.30 UAH
- 1,000 PLN = 11,392.90 UAH

---

### Frontend

#### 1. HTML структура

Файл: `resources/views/dashboard/index.blade.php`

```html
<!-- Currency Selector -->
<div class="flex items-center gap-2">
    <span class="text-sm text-gray-600 dark:text-gray-400">Валюта:</span>
    <div class="flex gap-1 bg-gray-200 dark:bg-gray-700 rounded-lg p-1">
        <button onclick="changeCashflowCurrency('UAH')" 
                class="currency-btn ... bg-blue-600 text-white"
                data-currency="UAH">
            ₴ UAH
        </button>
        <button onclick="changeCashflowCurrency('USD')" 
                class="currency-btn ..."
                data-currency="USD">
            $ USD
        </button>
        <button onclick="changeCashflowCurrency('PLN')" 
                class="currency-btn ..."
                data-currency="PLN">
            zł PLN
        </button>
    </div>
</div>
```

#### 2. JavaScript функції

**Глобальні змінні**:
```javascript
let currentPeriod = '6m';
let currentCurrency = 'UAH';
```

**Функція завантаження даних**:
```javascript
function loadCashflowData(period = '6m', currency = null) {
    currentPeriod = period;
    if (currency) {
        currentCurrency = currency;
    }
    
    const url = `/api/v1/stats/cashflow?period=${period}${currency ? '&currency=' + currency : ''}`;
    
    fetch(url, { headers, credentials: 'same-origin' })
        .then(response => response.json())
        .then(data => {
            // Отримуємо валюту з відповіді
            const currency = data.data.currency || 'UAH';
            
            // Символи валют
            const currencySymbols = {
                'UAH': '₴',
                'USD': '$',
                'PLN': 'zł',
                'EUR': '€'
            };
            
            const currencySymbol = currencySymbols[currency] || currency;
            
            // Створюємо графік з назвою "Cashflow (₴)"
            // та форматуванням осі Y: "5,000 ₴"
        });
}
```

**Функція зміни валюти**:
```javascript
window.changeCashflowCurrency = function(currency) {
    currentCurrency = currency;
    
    // Оновлюємо стилі кнопок
    document.querySelectorAll('.currency-btn').forEach(btn => {
        if (btn.dataset.currency === currency) {
            btn.classList.add('bg-blue-600', 'text-white');
        } else {
            btn.classList.remove('bg-blue-600', 'text-white');
        }
    });
    
    // Завантажуємо нові дані
    loadCashflowData(currentPeriod, currency);
};
```

#### 3. CSS стилі

```css
/* Currency buttons */
.currency-btn {
    transition: all 0.2s ease;
}

.currency-btn:not(.bg-blue-600) {
    color: #374151;
}

.dark .currency-btn:not(.bg-blue-600) {
    color: #d1d5db;
}

.currency-btn:not(.bg-blue-600):hover {
    background-color: #e5e7eb;
}

.dark .currency-btn:not(.bg-blue-600):hover {
    background-color: #4b5563;
}
```

#### 4. Chart.js інтеграція

**Назва графіка**:
```javascript
plugins: {
    title: {
        display: true,
        text: `Cashflow (${currencySymbol})`,
        color: '#f9fafb',
        font: { size: 16, weight: 'bold' }
    }
}
```

**Форматування осі Y**:
```javascript
scales: {
    y: {
        ticks: {
            callback: function(value) {
                return value.toLocaleString('uk-UA') + ' ' + currencySymbol;
            }
        }
    }
}
```

---

## Приклади використання

### 1. Переглянути доходи/витрати в доларах за останній місяць

1. Відкрити дашборд
2. Натиснути "30д" в Period Selector
3. Натиснути "$ USD" в Currency Selector
4. Графік оновиться і покаже дані в доларах

**Очікуваний результат**:
- Назва графіка: "Cashflow ($)"
- Вісь Y: "200.00 $", "500.50 $" тощо
- Всі суми сконвертовані з UAH/PLN в USD

### 2. Порівняння валют

**Сценарій**: перевірити скільки заробив за 6 місяців в різних валютах

1. Вибрати "6м" період
2. Натиснути "₴ UAH" → побачити 53,770.23 ₴
3. Натиснути "$ USD" → побачити 1,301.81 $
4. Натиснути "zł PLN" → побачити 4,782.74 zł

### 3. API запит через cURL

```bash
# Отримати cashflow в доларах за 3 місяці
curl -X GET "http://localhost:8000/api/v1/stats/cashflow?period=3m&currency=USD" \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Accept: application/json"
```

**Відповідь**:
```json
{
    "success": true,
    "data": {
        "cashflow": [
            {"period": "2024-08", "income": 400.00, "expense": 45.20},
            {"period": "2024-09", "income": 550.81, "expense": 38.18},
            {"period": "2024-10", "income": 351.00, "expense": 28.00}
        ],
        "currency": "USD",
        "period": "3m"
    }
}
```

---

## Тестування

### Automated тест

Файл: `scripts/diagnostics/test-currency-selector.php`

**Запуск**:
```bash
php scripts/diagnostics/test-currency-selector.php
```

**Що перевіряється**:
1. Конвертація всіх валют (UAH, USD, PLN)
2. Всі періоди (7d, 30d, 6m)
3. Правильність курсів конвертації
4. Відповідність сум після конвертації

**Приклад результату**:
```
💱 Тестування валюти: USD
📅 Період: 30d
💵 Валюта відповіді: USD
📈 Загальні доходи: 798.23 $
📉 Загальні витрати: 37.53 $
💰 Баланс: 760.70 $
📊 Кількість періодів: 30
```

### Manual тестування

1. **UI тест**:
   - Відкрити http://localhost:8000/dashboard
   - Перемикати між валютами → перевірити оновлення графіка
   - Перемикати періоди → перевірити збереження валюти

2. **Конвертація тест**:
   - Створити транзакцію в USD
   - Переглянути в UAH
   - Перевірити що курс застосовується правильно

3. **Dark mode тест**:
   - Перемкнути темну тему
   - Перевірити що кнопки валют видно
   - Перевірити hover ефекти

---

## Troubleshooting

### Проблема: валюта не змінюється

**Рішення**:
1. Перевірити консоль браузера на помилки
2. Очистити кеш: `php artisan view:clear && php artisan cache:clear`
3. Перевірити що ExchangeRate API працює: `php scripts/diagnostics/test-api-connection.php`

### Проблема: неправильна конвертація

**Рішення**:
1. Перевірити курси в БД: `php scripts/currency/check-rates.php`
2. Оновити курси: `php scripts/currency/force-api-update.php`
3. Перевірити логи: `tail -f storage/logs/laravel.log`

### Проблема: кнопки валют не відображаються

**Рішення**:
1. Очистити view кеш: `php artisan view:clear`
2. Перезавантажити сторінку з Ctrl+F5
3. Перевірити що Tailwind CSS завантажився

---

## Майбутні покращення

### Короткострокові
- [ ] Додати підтримку EUR (євро)
- [ ] Запам'ятовувати вибрану валюту в localStorage
- [ ] Додати анімацію при перемиканні валют

### Довгострокові
- [ ] Мультивалютні графіки (показувати всі валюти одночасно)
- [ ] Експорт даних у вибраній валюті
- [ ] Історія курсів на окремій сторінці
- [ ] Алерти про різкі зміни курсів

---

## API Reference

### StatsController::cashflow()

**Метод**: `GET`  
**URL**: `/api/v1/stats/cashflow`  
**Auth**: Required (Sanctum)

**Query Parameters**:
| Параметр | Тип | Обов'язковий | Значення | За замовчуванням |
|----------|-----|--------------|----------|-------------------|
| period | string | Ні | 7d, 14d, 30d, 3m, 6m | 6m |
| currency | string | Ні | UAH, USD, PLN, EUR | User's default |

**Response Schema**:
```json
{
    "success": boolean,
    "data": {
        "cashflow": [
            {
                "period": string,    // "2024-10-01" or "2024-10"
                "income": float,     // Сконвертована сума
                "expense": float     // Сконвертована сума
            }
        ],
        "currency": string,          // Валюта відповіді
        "period": string             // Повернутий період
    }
}
```

**Error Response**:
```json
{
    "success": false,
    "message": "Validation error",
    "errors": {
        "currency": ["The selected currency is invalid."]
    }
}
```

---

## Changelog

### [1.0.0] - 2025-10-06

#### Added
- Кнопки вибору валюти на дашборді (UAH, USD, PLN)
- Параметр `currency` в API endpoint `/api/v1/stats/cashflow`
- Підтримка цільової валюти в `StatsService::getCashflow()`
- Автоматична конвертація всіх транзакцій у вибрану валюту
- Відображення символу валюти в назві графіка
- Форматування осі Y з символом валюти
- JavaScript функції `changeCashflowCurrency()` та `loadCashflowData()`
- CSS стилі для `.currency-btn`
- Діагностичний скрипт `test-currency-selector.php`

#### Changed
- `loadCashflowData()` тепер приймає параметр `currency`
- Назва графіка змінена з "📈 Cashflow" на "📈 Cashflow (₴)"
- API відповідь включає поле `currency`

#### Technical
- Backend: додано валідацію `in:UAH,USD,PLN,EUR`
- Frontend: глобальна змінна `currentCurrency`
- Конвертація через `CurrencyService` з історичними курсами

---

## Пов'язані файли

### Backend
- `app/Http/Controllers/Api/StatsController.php`
- `app/Services/StatsService.php`
- `app/Services/CurrencyService.php`

### Frontend
- `resources/views/dashboard/index.blade.php`

### Tests
- `scripts/diagnostics/test-currency-selector.php`

### Documentation
- `docs/CASHFLOW-PERIOD-FILTERS.md` (попередня функціональність)
- `docs/EXCHANGERATE-API-DONE.md` (інтеграція ExchangeRate API)

---

## Підтримка

Питання та проблеми:
1. Перевірити документацію ExchangeRate API: `docs/EXCHANGERATE-API-DONE.md`
2. Запустити діагностику: `php scripts/diagnostics/test-currency-selector.php`
3. Перевірити логи: `storage/logs/laravel.log`

**Автор**: GitHub Copilot  
**Дата**: 6 жовтня 2025 р.
