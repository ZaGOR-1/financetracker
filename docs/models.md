# Моделі та зв'язки – Фінансовий трекер

> Опис доменних моделей, полів, індексів та relationships для Laravel Eloquent ORM.

## User (Користувач)
**Таблиця:** `users`  
**Namespace:** `App\Models\User`

### Поля:
- `id` (bigIncrements) — первинний ключ
- `name` (string, 255) — ім'я користувача
- `email` (string, 255, unique) — email
- `email_verified_at` (timestamp, nullable) — дата верифікації
- `password` (string) — хеш паролю (bcrypt/argon2id)
- `remember_token` (string, 100, nullable) — токен "пам'ятати мене"
- `created_at`, `updated_at` (timestamps)

### Relationships:
- `hasMany(Category::class)` — категорії користувача
- `hasMany(Transaction::class)` — транзакції користувача
- `hasMany(Budget::class)` — бюджети користувача
- `hasMany(ReportSnapshot::class)` — збережені звіти

### Індекси:
- Unique: `email`

---

## Category (Категорія)
**Таблиця:** `categories`  
**Namespace:** `App\Models\Category`

### Поля:
- `id` (bigIncrements) — первинний ключ
- `user_id` (unsignedBigInteger, nullable) — власник категорії (null = системна категорія)
- `name` (string, 100) — назва категорії
- `type` (enum: 'income', 'expense') — тип (дохід/витрата)
- `icon` (string, 50, nullable) — іконка (Lucide/Font Awesome ідентифікатор)
- `color` (string, 7, nullable) — колір у HEX (наприклад, `#3B82F6`)
- `is_active` (boolean, default: true) — активна категорія
- `created_at`, `updated_at` (timestamps)

### Relationships:
- `belongsTo(User::class)` — власник (nullable для системних категорій)
- `hasMany(Transaction::class)` — транзакції у цій категорії
- `hasMany(Budget::class)` — бюджети за категорією

### Індекси:
- Index: `user_id`, `type`
- Foreign key: `user_id` references `users(id)` onDelete cascade

---

## Transaction (Транзакція)
**Таблиця:** `transactions`  
**Namespace:** `App\Models\Transaction`

### Поля:
- `id` (bigIncrements) — первинний ключ
- `user_id` (unsignedBigInteger) — власник транзакції
- `category_id` (unsignedBigInteger) — категорія
- `amount` (decimal, 15, 2) — сума (завжди позитивна; тип визначає category)
- `description` (text, nullable) — опис транзакції
- `transaction_date` (date) — дата транзакції (не обов'язково = created_at)
- `created_at`, `updated_at` (timestamps)

### Relationships:
- `belongsTo(User::class)` — власник
- `belongsTo(Category::class)` — категорія

### Індекси:
- Index: `user_id`, `category_id`, `transaction_date`
- Foreign keys:
  - `user_id` references `users(id)` onDelete cascade
  - `category_id` references `categories(id)` onDelete restrict

### Бізнес-правила:
- `amount` > 0
- `transaction_date` не може бути у майбутньому (валідація на рівні FormRequest)

---

## Budget (Бюджет)
**Таблиця:** `budgets`  
**Namespace:** `App\Models\Budget`

### Поля:
- `id` (bigIncrements) — первинний ключ
- `user_id` (unsignedBigInteger) — власник бюджету
- `category_id` (unsignedBigInteger, nullable) — категорія (null = загальний бюджет)
- `amount` (decimal, 15, 2) — ліміт бюджету
- `period` (enum: 'daily', 'weekly', 'monthly', 'yearly') — період бюджету
- `start_date` (date) — початок періоду
- `end_date` (date) — кінець періоду
- `alert_threshold` (integer, 0-100, default: 80) — поріг попередження (%)
- `is_active` (boolean, default: true) — активний бюджет
- `created_at`, `updated_at` (timestamps)

### Relationships:
- `belongsTo(User::class)` — власник
- `belongsTo(Category::class)` — категорія (nullable)

### Індекси:
- Index: `user_id`, `category_id`, `start_date`, `end_date`
- Foreign keys:
  - `user_id` references `users(id)` onDelete cascade
  - `category_id` references `categories(id)` onDelete cascade

### Бізнес-правила:
- `start_date` < `end_date`
- `alert_threshold` між 0 і 100

---

## ReportSnapshot (Знімок звіту)
**Таблиця:** `report_snapshots`  
**Namespace:** `App\Models\ReportSnapshot`

### Поля:
- `id` (bigIncrements) — первинний ключ
- `user_id` (unsignedBigInteger) — власник звіту
- `title` (string, 255) — назва звіту
- `report_type` (string, 50) — тип звіту ('overview', 'cashflow', 'category_breakdown')
- `filters` (json, nullable) — фільтри звіту (дати, категорії, тощо)
- `data` (json) — результати звіту (агреговані дані)
- `generated_at` (timestamp) — дата генерації
- `created_at`, `updated_at` (timestamps)

### Relationships:
- `belongsTo(User::class)` — власник

### Індекси:
- Index: `user_id`, `report_type`, `generated_at`
- Foreign key: `user_id` references `users(id)` onDelete cascade

### Примітки:
- Використовується для збереження та кешування складних звітів
- Поле `data` зберігає JSON з результатами агрегацій

---

## Додаткові правила

### Soft Deletes
Моделі **не** використовують `SoftDeletes` на цьому етапі. Видалення користувача каскадно видаляє всі пов'язані дані.

### Timestamps
Всі моделі мають `created_at` та `updated_at`.

### Factories та Seeders
- `UserFactory` — генерує тестових користувачів
- `CategorySeeder` — системні категорії (Їжа, Транспорт, Зарплата, тощо)
- `TransactionFactory` — випадкові транзакції для демо
- `BudgetFactory` — приклади бюджетів

---

## Наступні кроки
1. Створити міграції для всіх таблиць (`php artisan make:migration`)
2. Згенерувати моделі з relationships (`php artisan make:model`)
3. Налаштувати фабрики та сидери для тестових даних
4. Додати валідацію у FormRequests
