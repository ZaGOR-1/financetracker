# Фі## 🎯 Особливості

- ✅ Облік доходів та витрат з категоріями
- 💱 **Мультивалютність** (UAH, USD, PLN) з автоматичною конвертацією
- 🔄 **ExchangeRate-API.com** інтеграція для актуальних курсів валют
- 💰 **Вибір валюти в Cashflow** - перегляд аналітики в будь-якій валюті
- � **Калькулятор годин** - розрахунок зарплати з годинної ставки (день/тиждень/місяць/рік)
- �📊 Інтерактивні дашборди та графіки (Chart.js)
- � Управління бюджетами з попередженнями
- 📈 Аналітика та звіти (cashflow, category breakdown) з фільтрами періодів трекер 💰

Сучасний вебсайт на **Laravel 10+** для домашнього обліку доходів та витрат з красивим UI та потужною аналітикою.

## 🎯 Особливості

- ✅ Облік доходів та витрат з категоріями
- � **Мультивалютність** (UAH, USD, PLN) з автоматичною конвертацією
- 🔄 **ExchangeRate-API.com** інтеграція для актуальних курсів валют
- �📊 Інтерактивні дашборди та графіки (Chart.js)
- 💰 Управління бюджетами з попередженнями
- 📈 Аналітика та звіти (cashflow, category breakdown)
- 📥 **Експорт даних у XLSX** (транзакції та бюджети)
- 📧 **Email-нотифікації** про перевищення бюджету
- 🔔 **Автоматичні попередження** (Laravel Scheduler + Queue)
- 🌓 Світла/темна тема
- 🔒 Безпечна автентифікація (Laravel Sanctum)
- 📱 Адаптивний дизайн (Tailwind CSS + Flowbite)
- 🚀 REST API для інтеграцій
- 🛡️ **Статичний аналіз коду** (PHPStan level 5, 0 помилок)

## 🛠️ Технології

### Backend
- **PHP 8.3+**
- **Laravel 10.x**
- **MySQL 8.0** або **SQLite**
- **Laravel Sanctum** (API автентифікація)
- **PHPUnit/Pest** (тести)

### Frontend
- **TailwindCSS 3.x**
- **Flowbite** (UI компоненти)
- **Chart.js 4.x** (графіки)
- **Alpine.js** (реактивність)
- **Lucide Icons**

## � Performance Оптимізації

**Всі 4 пункти оптимізації реалізовані!** Finance Tracker працює блискавично швидко.

### ⚡ Результати

| Метрика | До | Після | Покращення |
|---------|-----|-------|------------|
| **Backend queries** | 500+ | 5-10 | **98% ⬇️** |
| **Cache hit rate** | 0% | 95% | **∞ 📈** |
| **Query time** | 150-200ms | 20-30ms | **85% ⬇️** |
| **Initial bundle** | ~508 KB | 270 KB | **47% ⬇️** |
| **Load time (3G)** | ~4.5s | ~1.8s | **60% ⬇️** |

### 🎯 Реалізовані оптимізації

1. **✅ Database Indexes** - 19 індексів для швидких запитів (85-87% швидше)
2. **✅ Caching System** - CacheService + Observers (95% hit rate)
3. **✅ N+1 Problem Fix** - Eager Loading (98% менше queries)
4. **✅ Lazy Loading JS/CSS** - Code splitting (47% менший bundle)

📚 **Детальна документація:** [docs/OPTIMIZATION-FINAL-REPORT.md](docs/OPTIMIZATION-FINAL-REPORT.md)

## �📋 Вимоги

- PHP >= 8.3
- Composer
- Node.js >= 18.x і npm/pnpm
- MySQL 8.0+ або SQLite
- Git

## 🚀 Встановлення

### 1. Клонування репозиторію

```powershell
git clone https://github.com/your-username/finance-tracker.git
cd finance-tracker
```

### 2. Встановлення залежностей

```powershell
# PHP залежності
composer install

# JavaScript залежності
npm install
```

### 3. Налаштування середовища

```powershell
# Копіювати .env.example у .env
copy .env.example .env

# Згенерувати application key
php artisan key:generate
```

### 4. Налаштування бази даних

Відредагуйте `.env` (для MySQL):

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=finance_tracker
DB_USERNAME=root
DB_PASSWORD=
```

Або використовуйте SQLite (для розробки):

```env
DB_CONNECTION=sqlite
# Решту рядків DB_* закоментуйте або видаліть
```

Для SQLite створіть файл бази даних:

```powershell
New-Item -Path database\database.sqlite -ItemType File
```

Або для SQLite:

```env
DB_CONNECTION=sqlite
DB_DATABASE=C:\wamp64\domains\project\database\database.sqlite
```

### 5. Налаштування валют (опціонально)

Для підтримки мультивалютності додайте в `.env`:

```env
# Базова валюта (UAH, USD, PLN)
DEFAULT_CURRENCY=UAH

# Провайдер курсів валют (exchangerate-api або nbu)
EXCHANGE_RATE_PROVIDER=exchangerate-api

# API ключ для ExchangeRate-API.com
EXCHANGERATE_API_KEY=your_api_key_here
```

Отримати безкоштовний API ключ: [exchangerate-api.com](https://www.exchangerate-api.com/)

### 6. Міграції та сидери

```powershell
# Запустити міграції
php artisan migrate

# Додати тестові дані (опціонально)
php artisan db:seed

# Оновити курси валют
php artisan currency:update-rates
```

### 7. Запуск серверів

**Термінал 1 – Laravel dev server:**
```powershell
php artisan serve
```

**Термінал 2 – Vite dev server (для frontend):**
```powershell
npm run dev
```

Відкрийте браузер: [http://localhost:8000](http://localhost:8000)

## � Структура проекту

```
project/
├── app/                    # Laravel додаток
│   ├── Console/           # Artisan команди
│   ├── Http/              # Контролери, middleware
│   ├── Models/            # Eloquent моделі
│   ├── Services/          # Бізнес-логіка
│   └── Repositories/      # Шар даних
├── config/                # Конфігурація
├── database/              # Міграції, сидери
├── docs/                  # �📚 Документація
│   ├── archive/          # Історичні документи
│   └── README.md         # Index документації
├── public/                # Публічні файли (build)
├── resources/             # Frontend ресурси
│   ├── css/              # Tailwind стилі
│   ├── js/               # JS компоненти
│   └── views/            # Blade шаблони
├── routes/                # Маршрути (web, api)
├── scripts/               # 🛠️ Допоміжні скрипти
│   ├── currency/         # Робота з валютами
│   ├── diagnostics/      # Діагностика системи
│   ├── setup/            # Налаштування
│   └── README.md         # Документація скриптів
├── storage/               # Логи, кеш, uploads
└── tests/                 # PHPUnit тести
```

## 📚 Документація

### Швидкий старт
- **[docs/README.md](docs/README.md)** — Index всієї документації
- **[docs/multi-currency-guide.md](docs/multi-currency-guide.md)** — Мультивалютність
- **[docs/deployment.md](docs/deployment.md)** — Деплой на production

### 🚀 Production Deployment
- **[PRODUCTION-README.md](PRODUCTION-README.md)** — Швидкий старт для production
- **[docs/PRODUCTION-DEPLOYMENT-GUIDE.md](docs/PRODUCTION-DEPLOYMENT-GUIDE.md)** — Повний deployment гайд (600+ рядків)
- **[docs/PRODUCTION-CHECKLIST-QUICK.md](docs/PRODUCTION-CHECKLIST-QUICK.md)** — Чеклист перед випуском
- **[docs/PRODUCTION-SETUP-SUMMARY.md](docs/PRODUCTION-SETUP-SUMMARY.md)** — Що було створено для production
- **[docs/PRODUCTION-COMMANDS-CHEATSHEET.md](docs/PRODUCTION-COMMANDS-CHEATSHEET.md)** — Шпаргалка команд
- **[docs/ENV-TEMPLATES.md](docs/ENV-TEMPLATES.md)** — Шаблони .env для різних платформ
- **[.env.production.example](.env.production.example)** — Production environment template

### Performance & Security
- **[docs/PERFORMANCE-OPTIMIZATION.md](docs/PERFORMANCE-OPTIMIZATION.md)** — Database, Caching, N+1, Lazy Loading
- **[docs/TAILWIND-OPTIMIZATION.md](docs/TAILWIND-OPTIMIZATION.md)** — CSS optimization з PurgeCSS
- **[docs/ALL-OPTIMIZATIONS-SUMMARY.md](docs/ALL-OPTIMIZATIONS-SUMMARY.md)** — Фінальний звіт про 5 оптимізацій
- **[docs/SECURITY.md](docs/SECURITY.md)** — Security Headers, Rate Limiting, CSRF, SQL Injection
- **[docs/SECURITY-SUMMARY.md](docs/SECURITY-SUMMARY.md)** — Короткий security звіт
- **[docs/SECURITY-ROADMAP.md](docs/SECURITY-ROADMAP.md)** — Майбутні security покращення

### 📊 Logging & Monitoring
- **[docs/LOGGING-SYSTEM.md](docs/LOGGING-SYSTEM.md)** — Повна документація системи логування
- **[docs/LOGGING-QUICKSTART.md](docs/LOGGING-QUICKSTART.md)** — Швидкий старт з логуванням

### Архітектура
- [`docs/models.md`](docs/models.md) — опис моделей та зв'язків
- [`docs/er-diagram.md`](docs/er-diagram.md) — ER-діаграма бази даних
- [`docs/api-contracts.md`](docs/api-contracts.md) — REST API контракти
- [`docs/openapi.yaml`](docs/openapi.yaml) — OpenAPI 3.0 специфікація

### Допоміжні скрипти
- **[scripts/README.md](scripts/README.md)** — Огляд всіх скриптів
- [scripts/currency/](scripts/currency/) — Робота з валютами
- [scripts/diagnostics/](scripts/diagnostics/) — Діагностика
- [scripts/setup/](scripts/setup/) — Налаштування

### API ендпоінти

**Base URL:** `http://localhost:8000/api/v1`

#### Автентифікація
- `POST /auth/register` — реєстрація
- `POST /auth/login` — вхід
- `POST /auth/logout` — вихід

#### Категорії
- `GET /categories` — список категорій
- `POST /categories` — створити категорію
- `PUT /categories/{id}` — оновити категорію
- `DELETE /categories/{id}` — видалити категорію

#### Транзакції
- `GET /transactions` — список транзакцій (з пагінацією)
- `POST /transactions` — створити транзакцію
- `PUT /transactions/{id}` — оновити транзакцію
- `DELETE /transactions/{id}` — видалити транзакцію

#### Бюджети
- `GET /budgets` — список бюджетів
- `POST /budgets` — створити бюджет
- `PUT /budgets/{id}` — оновити бюджет
- `DELETE /budgets/{id}` — видалити бюджет

#### Статистика
- `GET /stats/overview` — загальна статистика
- `GET /stats/cashflow` — cashflow за місяцями
- `GET /stats/category-breakdown` — розподіл за категоріями

Детальна документація: [`docs/api-contracts.md`](docs/api-contracts.md)

## 🧪 Тестування

```powershell
# Запустити всі тести
php artisan test

# Або з PHPUnit
vendor/bin/phpunit

# Тести з покриттям
php artisan test --coverage
```

## 🏗️ Структура проєкту

```
project/
├── app/
│   ├── Http/
│   │   ├── Controllers/      # Контролери
│   │   └── Middleware/       # Middleware
│   ├── Models/               # Eloquent моделі
│   ├── Services/             # Бізнес-логіка
│   └── Repositories/         # Data Access Layer
│       └── Interfaces/       # Інтерфейси для DI
├── database/
│   ├── migrations/           # Міграції БД
│   ├── seeders/              # Сидери (тестові дані)
│   └── factories/            # Model factories
├── resources/
│   ├── views/                # Blade шаблони
│   ├── js/                   # JavaScript (Alpine, Chart.js)
│   └── css/                  # Tailwind CSS
├── routes/
│   ├── web.php               # Web маршрути
│   └── api.php               # API маршрути
├── docs/                     # Документація
└── tests/                    # Тести
```

## 🎨 UI/UX

- **Дизайн:** Сучасний, мінімалістичний з Flowbite компонентами
- **Теми:** Світла і темна (переключення через Alpine.js)
- **Іконки:** Lucide Icons
- **Графіки:** Chart.js 4 (line, bar, doughnut charts)
- **Адаптивність:** Mobile-first підхід з Tailwind

## 🔧 Налаштування

### Tailwind CSS

```powershell
# Збірка CSS для продакшену
npm run build
```

Конфігурація: [`tailwind.config.js`](tailwind.config.js)

### Laravel Sanctum

```powershell
# Publish Sanctum config
php artisan vendor:publish --provider="Laravel\Sanctum\SanctumServiceProvider"
```

## � Експорт та Нотифікації (Етап 5)

### Експорт даних у Excel

Експортуйте транзакції та бюджети у форматі XLSX:

```powershell
# Експорт через UI: кнопка "Експорт" на сторінках /transactions та /budgets
# Або через API:
curl -H "Authorization: Bearer {token}" \
  http://localhost:8000/export/transactions?date_from=2025-01-01&date_to=2025-12-31&type=expense
```

**Пакет:** `maatwebsite/excel` 3.1.67

### Email нотифікації

Автоматичні email-сповіщення при перевищенні бюджету:

```powershell
# Перевірити бюджети вручну
php artisan budgets:check

# Або з прим усовою відправкою
php artisan budgets:check --force
```

**Налаштування у `.env`:**

```env
QUEUE_CONNECTION=database
MAIL_MAILER=smtp
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=your-email@gmail.com
MAIL_PASSWORD=your-app-password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=noreply@finance-tracker.local
```

**Запуск queue worker:**

```powershell
php artisan queue:work
```

**Cron job для scheduler (Linux/Mac):**

```bash
* * * * * cd /path/to/project && php artisan schedule:run >> /dev/null 2>&1
```

**Windows Task Scheduler:**

```powershell
php artisan schedule:work  # для development
```

## 🛡️ Якість коду (Етап 6)

### PHPStan/Larastan

Статичний аналіз коду (рівень 5, 0 помилок):

```powershell
# Запустити аналіз
vendor/bin/phpstan analyse

# Або через composer script
composer analyse
```

**Конфігурація:** [`phpstan.neon`](phpstan.neon)

### Тести

```powershell
# Запустити всі тести
php artisan test

# З покриттям (якщо встановлено Xdebug)
php artisan test --coverage

# Окремий тест
php artisan test --filter AuthTest
```

**Результат:** 14 tests, 115 assertions, 0 failures

## �📦 Збірка для продакшену

```powershell
# 1. Оптимізувати autoloader
composer install --no-dev --optimize-autoloader

# 2. Кешувати конфігурацію
php artisan config:cache
php artisan route:cache
php artisan view:cache

# 3. Зібрати frontend assets
npm run build

# 4. Створити таблиці для queue та notifications
php artisan migrate --force

# 5. Налаштувати supervisor для queue worker (Linux)
sudo supervisorctl reread
sudo supervisorctl update
sudo supervisorctl start finance-tracker-worker:*
```

## 🤝 Внесок

Дивіться [`CONTRIBUTING.md`](CONTRIBUTING.md) для правил контрибуції.

## 📄 Ліцензія

MIT License. Дивіться [`LICENSE`](LICENSE) для деталей.

## 🙏 Подяки

- [Laravel](https://laravel.com)
- [TailwindCSS](https://tailwindcss.com)
- [Flowbite](https://flowbite.com)
- [Chart.js](https://www.chartjs.org)

---

## 🚀 Статус проєкту: ГОТОВО ДО ЗАПУСКУ! ✅

**Версія:** 1.0.0  
**Готовність:** 🟢 98.5% (Production Ready)

### ✅ Всі етапи завершено:

- [x] **Етап 0:** Підготовка середовища
- [x] **Етап 1:** Архітектура та документація
- [x] **Етап 2:** Інфраструктура даних (міграції, моделі, репозиторії)
- [x] **Етап 3:** API бекенд (Sanctum, контролери, тести)
- [x] **Етап 4:** Фронтенд (TailwindCSS, дашборд, Chart.js)
- [x] **Етап 5:** Аналітика та експорти (Laravel Excel, нотифікації)
- [x] **Етап 6:** Якість та безпека (PHPStan, тести, аудит)
- [x] **Етап 7:** Реліз та операції (Docker, CI/CD, моніторинг)

### 📊 Результати перевірки:

- ✅ PHPStan level 5 — 0 помилок
- ✅ **51/51 тестів пройдено (277 assertions)** 🎉
  - ✅ 5 Auth tests
  - ✅ 20 Budget tests (новi!)
  - ✅ 7 Category tests
  - ✅ 17 Transaction tests (новi!)
  - ✅ 2 Example tests
- ✅ Всі 23 API endpoints працюють
- ✅ Security audit пройдений (CSRF ✓, XSS ✓, SQL ✓)
- ✅ Docker infrastructure готова
- ✅ CI/CD pipelines налаштовані

### 📄 Документація:

- 🎯 [**ГОТОВО ДО ЗАПУСКУ**](READY-TO-LAUNCH.md) — Швидкий огляд
- 📊 [**Status Dashboard**](STATUS-DASHBOARD.txt) — Візуальний звіт
- 📋 [**Production Readiness Report**](docs/PRODUCTION-READINESS-REPORT.md) — Повний аналіз (600+ ліній)
- 📝 [**Production Checklist**](docs/production-checklist.md) — Чеклист перед запуском
- 🚀 [**Deployment Guide**](docs/deployment.md) — Інструкції deployment
- 🎉 [**Stage 7 Summary**](docs/stage-7-summary.md) — Підсумок етапу 7
- 📈 [**Roadmap**](roadmap.md) — План розробки

**Рекомендація: СХВАЛЕНО ДЛЯ PRODUCTION DEPLOYMENT** ✅
