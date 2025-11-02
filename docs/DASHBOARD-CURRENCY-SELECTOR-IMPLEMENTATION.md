# Реалізація селектора валют на Dashboard

**Дата:** 7 січня 2025  
**Статус:** ✅ Завершено

## Огляд

Додано можливість перемикання валюти на головному дашборді з автоматичною конвертацією всіх показників за актуальним курсом.

## Реалізовані функції

### 1. UI компонент (Dashboard Header)

**Файл:** `resources/views/dashboard/index.blade.php`

Додано кнопки вибору валюти біля заголовка дашборду:

```html
<div class="flex items-center gap-2">
    <span class="text-sm text-gray-600 dark:text-gray-400">Валюта:</span>
    <div class="inline-flex rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 p-1">
        <button onclick="changeDashboardCurrency('UAH')" 
                class="dashboard-currency-btn px-3 py-1 text-sm font-medium rounded-md transition-colors duration-200 bg-blue-600 text-white"
                data-currency="UAH">
            UAH (₴)
        </button>
        <button onclick="changeDashboardCurrency('USD')" 
                class="dashboard-currency-btn px-3 py-1 text-sm font-medium rounded-md transition-colors duration-200"
                data-currency="USD">
            USD ($)
        </button>
        <button onclick="changeDashboardCurrency('PLN')" 
                class="dashboard-currency-btn px-3 py-1 text-sm font-medium rounded-md transition-colors duration-200"
                data-currency="PLN">
            PLN (zł)
        </button>
    </div>
</div>
```

### 2. CSS стилі

Розширено наявні стилі `.currency-btn` для підтримки `.dashboard-currency-btn`:

```css
.currency-btn, .dashboard-currency-btn {
    transition: all 0.2s ease;
}

.currency-btn:not(.bg-blue-600), .dashboard-currency-btn:not(.bg-blue-600) {
    color: #374151;
}

.dark .currency-btn:not(.bg-blue-600), .dark .dashboard-currency-btn:not(.bg-blue-600) {
    color: #d1d5db;
}
```

### 3. JavaScript функціонал

**Додано глобальні змінні:**

```javascript
let currentDashboardCurrency = 'UAH';
let dashboardHeaders = null;
let dashboardFetchWithTimeout = null;
```

**Функція зміни валюти:**

```javascript
function changeDashboardCurrency(currency) {
    if (currentDashboardCurrency === currency) return;
    
    currentDashboardCurrency = currency;
    
    // Оновлення активної кнопки
    document.querySelectorAll('.dashboard-currency-btn').forEach(btn => {
        if (btn.dataset.currency === currency) {
            btn.classList.add('bg-blue-600', 'text-white');
            btn.classList.remove('hover:bg-gray-100', 'dark:hover:bg-gray-700');
        } else {
            btn.classList.remove('bg-blue-600', 'text-white');
            btn.classList.add('hover:bg-gray-100', 'dark:hover:bg-gray-700');
        }
    });
    
    // Перезавантаження даних з новою валютою
    loadDashboardData(currency);
    
    // Збереження у localStorage
    localStorage.setItem('dashboardCurrency', currency);
}
```

**Функція завантаження даних:**

```javascript
function loadDashboardData(currency = null) {
    const url = currency 
        ? `/api/v1/stats/overview?currency=${currency}` 
        : '/api/v1/stats/overview';
    
    dashboardFetchWithTimeout(url, {
        headers: dashboardHeaders,
        credentials: 'same-origin'
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            const stats = data.data;
            const currencySymbols = {
                'UAH': '₴',
                'USD': '$',
                'PLN': 'zł',
                'EUR': '€'
            };
            const currencySymbol = currencySymbols[stats.currency] || stats.currency;
            
            // Оновлення KPI cards
            document.getElementById('total-income').textContent = 
                `${currencySymbol}${stats.total_income.toLocaleString('uk-UA', {minimumFractionDigits: 2})}`;
            document.getElementById('total-expense').textContent = 
                `${currencySymbol}${stats.total_expense.toLocaleString('uk-UA', {minimumFractionDigits: 2})}`;
            document.getElementById('balance').textContent = 
                `${currencySymbol}${stats.balance.toLocaleString('uk-UA', {minimumFractionDigits: 2})}`;
            
            // Оновлення топ категорій
            // ...
        }
    });
}
```

**Відновлення вибору з localStorage:**

```javascript
document.addEventListener('DOMContentLoaded', function() {
    // Ініціалізація headers та fetchWithTimeout
    dashboardHeaders = { /* ... */ };
    dashboardFetchWithTimeout = (url, options, timeout = 5000) => { /* ... */ };
    
    // Відновлення вибраної валюти
    const savedCurrency = localStorage.getItem('dashboardCurrency');
    if (savedCurrency && ['UAH', 'USD', 'PLN'].includes(savedCurrency)) {
        currentDashboardCurrency = savedCurrency;
        // Оновлення активної кнопки
        document.querySelectorAll('.dashboard-currency-btn').forEach(btn => {
            if (btn.dataset.currency === savedCurrency) {
                btn.classList.add('bg-blue-600', 'text-white');
            }
        });
    }
    
    // Завантаження даних при старті
    loadDashboardData(currentDashboardCurrency);
});
```

### 4. Backend підтримка

**Контролер:** `app/Http/Controllers/Api/StatsController.php`

```php
public function overview(Request $request)
{
    $request->validate([
        'currency' => 'nullable|string|in:UAH,USD,PLN,EUR'
    ]);

    $currency = $request->input('currency');
    $stats = $this->statsService->getOverview(auth()->id(), $currency);

    return response()->json([
        'success' => true,
        'data' => $stats
    ]);
}
```

**Сервіс:** `app/Services/StatsService.php`

```php
public function getOverview(int $userId, ?string $currency = null): array
{
    $cacheKey = "stats_overview_user_{$userId}_currency_" . ($currency ?? 'default');
    
    return Cache::remember($cacheKey, 300, function () use ($userId, $currency) {
        return $this->calculateOverview($userId, $currency);
    });
}

private function calculateOverview(int $userId, ?string $currency = null): array
{
    if (!$currency) {
        $user = User::find($userId);
        $currency = $user->currency ?? 'UAH';
    }
    
    $transactions = $this->transactionRepository->getUserTransactions($userId);
    
    $totalIncome = 0;
    $totalExpense = 0;
    
    foreach ($transactions as $transaction) {
        $amount = $this->currencyService->convert(
            $transaction->amount,
            $transaction->currency,
            $currency
        );
        
        if ($transaction->type === 'income') {
            $totalIncome += $amount;
        } else {
            $totalExpense += $amount;
        }
    }
    
    return [
        'total_income' => round($totalIncome, 2),
        'total_expense' => round($totalExpense, 2),
        'balance' => round($totalIncome - $totalExpense, 2),
        'currency' => $currency,
        'top_expense_categories' => $this->getTopExpenseCategories($userId, $currency)
    ];
}
```

## Підтримувані валюти

- **UAH** (₴) - Українська гривня
- **USD** ($) - Долар США  
- **PLN** (zł) - Польський злотий
- **EUR** (€) - Євро (backend підтримує, потрібно додати кнопку)

## Конвертація валют

- Використовується **ExchangeRate-API**
- Кешування курсів: **60 хвилин** (config: `EXCHANGE_RATE_CACHE_TTL`)
- Кешування статистики: **5 хвилин**
- Сервіс: `app/Services/CurrencyService.php`

## Користувацький досвід

1. **Вибір валюти:** Натискання на кнопку UAH/USD/PLN
2. **Миттєве оновлення:** Всі суми на дашборді конвертуються
3. **Збереження вибору:** localStorage зберігає останній вибір
4. **Автовідновлення:** При перезавантаженні сторінки відновлюється вибрана валюта

## Тестування

### Ручне тестування:

1. Відкрити Dashboard
2. Натиснути на кнопку USD
3. Перевірити, що всі суми показані в доларах
4. Натиснути PLN - суми перераховуються
5. Перезавантажити сторінку - валюта залишається PLN
6. Перевірити консоль браузера на наявність помилок

### Перевірка конвертації:

```bash
# Отримати поточні курси
curl http://localhost:8000/api/v1/currencies/rates

# Отримати статистику в UAH
curl -X GET "http://localhost:8000/api/v1/stats/overview" \
     -H "Accept: application/json" \
     --cookie "laravel_session=YOUR_SESSION"

# Отримати статистику в USD
curl -X GET "http://localhost:8000/api/v1/stats/overview?currency=USD" \
     -H "Accept: application/json" \
     --cookie "laravel_session=YOUR_SESSION"
```

## Збірка та deployment

```bash
# Збірка frontend активів
npm run build

# Очищення кешу
php artisan config:clear
php artisan cache:clear
php artisan view:clear

# Запуск сервера
php artisan serve
```

## Змінені файли

- `resources/views/dashboard/index.blade.php` - UI та JavaScript
- `app/Http/Controllers/Api/StatsController.php` - Валідація currency параметра
- `app/Services/StatsService.php` - Прийняття currency параметра, конвертація

## Наступні кроки (опціонально)

- [ ] Додати кнопку EUR (backend вже підтримує)
- [ ] Додати селектор валюти для графіка Cashflow
- [ ] Додати селектор для сторінки Categories
- [ ] Показувати індикатор завантаження при перемиканні валюти
- [ ] Додати tooltip з актуальним курсом конвертації

## Технічні деталі

### Архітектура:

```
User clicks button
    ↓
changeDashboardCurrency(currency)
    ↓
Update button states + localStorage
    ↓
loadDashboardData(currency)
    ↓
GET /api/v1/stats/overview?currency=USD
    ↓
StatsController::overview() validates currency
    ↓
StatsService::getOverview($userId, $currency)
    ↓
CurrencyService::convert() for each transaction
    ↓
ExchangeRate-API (cached)
    ↓
Return converted amounts
    ↓
Update DOM elements
```

### Кешування:

1. **Exchange rates:** 60 хвилин (`currencies:rates`)
2. **Stats overview:** 5 хвилин (`stats_overview_user_{id}_currency_{code}`)

### Обробка помилок:

- Timeout API запиту: 5 секунд
- Відображення помилок у червоному кольорі
- Логування в консоль браузера
- Fallback на валюту за замовчуванням UAH

---

**Автор:** GitHub Copilot  
**Проект:** Personal Finance Tracker  
**Framework:** Laravel 10.49.1 + Vite 5.4.21
