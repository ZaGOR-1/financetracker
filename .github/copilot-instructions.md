<!--
  Updated: 6 октября 2025 г.
  Project: PHP 8+ особистий фінансовий трекер (MVC, сучасний UI)
-->

# Copilot instructions – Домашній облік фінансів (PHP)
## 1. Мета та контекст
- Ціль: веб застосунок для обліку доходів/витрат з категоріями, дашбордами, графіками, темною/світлою темами.
- Вимоги до бекенду: PHP 8+, MVC (Laravel або власний каркас), чистий код, захист даних.
- Вимоги до фронтенду: сучасний UI (TailwindCSS/Bootstrap 5 + Flowbite, Chart.js, Alpine/Vue якщо потрібно).
## 2. Як виявляти структуру
- Спершу шукай `README.md`, `composer.json`, `package.json`, `.env.example`, `database/`, `public/`.
- Laravel: очікуй `app/`, `routes/web.php`, `routes/api.php`, `resources/views/`, `database/migrations/`.
- Власний MVC: шукай `app/Controllers`, `app/Models`, `app/Services`, `public/index.php`, `config/*.php`.
- Фронтенд-джерела: `resources/js`, `resources/css`, `resources/views`, `public/assets`, `tailwind.config.js`, `postcss.config.js`.
## 3. Архітектурні принципи
- Розділяй домен: `Transactions`, `Categories`, `Budgets`, `Reports` (контролери + сервіси + репозиторії/ORM).
- REST API повертає JSON (`/api/transactions`, `/api/stats/overview`). Валідація на бекенді (PHP FormRequests/сервіс) і дубль на фронті.
- Сервіси/репозиторії мають працювати через інтерфейси; ін’єкції роби через контейнер Laravel або власний DI.
- Дотримуйся міграцій/сидів для MySQL/SQLite (`database/migrations/*`). При оновленні схеми додавай опис у PR.
## 4. Робочі процеси
- Після клонування: `composer install`, `npm install` (Tailwind/Flowbite/Chart.js). Створи `.env` з `.env.example`.
- Dev-сервери:
  ```powershell
  # Laravel
  php artisan serve
  npm run dev

  # Власний MVC
  php -S localhost:8000 -t public
  npm run dev
  ```
- Тести: `php artisan test` або `vendor/bin/phpunit`. JS/статичні тести: `npm run lint`, `npm run test` (якщо налаштовано).
- Збірка продакшену: `npm run build`, `php artisan config:cache` (Laravel).
## 5. Фронтенд патерни
- Tailwind: конфіги в `tailwind.config.js`; компоненти Flowbite/Alpine - тримай у `resources/js/components`.
- Графіки: Chart.js 4, шари даних від API, кешуй відповіді на фронті через localStorage/pinia/vuex якщо з’явиться SPA.
- Теми: зберігай вибір користувача в `localStorage`, перемикай класом `dark` на `<html>`.
## 6. Безпека та якість
- Використовуй підготовлені запити / Eloquent; ніколи не вставляй сирий SQL без перевірок.
- CSRF: для Laravel — `@csrf`, для кастомного MVC — токен у сесії та прихованому полі.
- Паролі — `password_hash` (bcrypt/argon2id). Будь-які API ключі з `.env`, не коміть секрети.
- Логіка авторизації: middleware (`app/Http/Middleware` або власний `App\Middlewares`).
## 7. Інтеграції та розширення
- Перевіряй `composer.json` на сторонні пакети (наприклад, `nesbot/carbon`, `league/flysystem`, платіжні шлюзи).
- JS залежності: Tailwind, Flowbite, Chart.js, Lucide/Font Awesome; версії і налаштування у `package.json`.
- Якщо додаєш нові інтеграції (експорт у CSV, синхронізація банківських даних) — опиши конфіг у README та `.env.example`.
## 8. Робота над змінами
- Робота маленькими PR: короткий опис, список файлів, пост-скрипт з командами (`php artisan migrate`, `npm run build`).
- Перед PR перевір: тести, лінти, `phpstan`/`larastan` (якщо з’явиться), відсутність debug-коду.
- Для UI — додавай скріншоти/гіфи у PR-опис, якщо змінюється візуал.
## 9. Безпечні дії для агента
- Не створюй реальних облікових записів/свідчень. Використовуй фейкові дані у сидерах.
- Не запускай міграції в продакшен середовищі без явного дозволу.
- При невизначеності щодо каркасу — перепитай людину, щоб уникнути непотрібного scaffold.
---
Потрібно більше деталей (Laravel vs власний MVC, фронтенд-фреймворк)? Повідом — оновлю інструкції та підготую стартові шаблони.
