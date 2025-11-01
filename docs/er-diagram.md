# ER-діаграма – Фінансовий трекер

```mermaid
erDiagram
    USERS ||--o{ CATEGORIES : "має"
    USERS ||--o{ TRANSACTIONS : "створює"
    USERS ||--o{ BUDGETS : "встановлює"
    USERS ||--o{ REPORT_SNAPSHOTS : "генерує"
    
    CATEGORIES ||--o{ TRANSACTIONS : "класифікує"
    CATEGORIES ||--o{ BUDGETS : "обмежує"
    
    USERS {
        bigint id PK
        string name
        string email UK
        timestamp email_verified_at
        string password
        string remember_token
        timestamp created_at
        timestamp updated_at
    }
    
    CATEGORIES {
        bigint id PK
        bigint user_id FK "nullable"
        string name
        enum type "income, expense"
        string icon "nullable"
        string color "nullable"
        boolean is_active
        timestamp created_at
        timestamp updated_at
    }
    
    TRANSACTIONS {
        bigint id PK
        bigint user_id FK
        bigint category_id FK
        decimal amount "15,2"
        text description "nullable"
        date transaction_date
        timestamp created_at
        timestamp updated_at
    }
    
    BUDGETS {
        bigint id PK
        bigint user_id FK
        bigint category_id FK "nullable"
        decimal amount "15,2"
        enum period "daily, weekly, monthly, yearly"
        date start_date
        date end_date
        integer alert_threshold "0-100"
        boolean is_active
        timestamp created_at
        timestamp updated_at
    }
    
    REPORT_SNAPSHOTS {
        bigint id PK
        bigint user_id FK
        string title
        string report_type
        json filters "nullable"
        json data
        timestamp generated_at
        timestamp created_at
        timestamp updated_at
    }
```

## Примітки до діаграми

### Зв'язки (Cardinality):
- **User → Categories**: один користувач може мати багато категорій (1:N)
- **User → Transactions**: один користувач може створити багато транзакцій (1:N)
- **User → Budgets**: один користувач може встановити багато бюджетів (1:N)
- **User → ReportSnapshots**: один користувач може згенерувати багато звітів (1:N)
- **Category → Transactions**: одна категорія може мати багато транзакцій (1:N)
- **Category → Budgets**: одна категорія може бути у багатьох бюджетах (1:N)

### Каскадні дії:
- При видаленні `User`: видаляються всі пов'язані `Categories`, `Transactions`, `Budgets`, `ReportSnapshots`
- При видаленні `Category`: видаляються пов'язані `Budgets`; `Transactions` **не видаляються** (restrict)

### Індекси (для продуктивності):
- `users.email` (unique)
- `categories(user_id, type)`
- `transactions(user_id, category_id, transaction_date)`
- `budgets(user_id, category_id, start_date, end_date)`
- `report_snapshots(user_id, report_type, generated_at)`

### Особливості:
1. **Системні категорії**: `categories.user_id` nullable дозволяє створювати глобальні категорії для всіх користувачів
2. **Загальні бюджети**: `budgets.category_id` nullable дозволяє встановити ліміт на всі витрати без прив'язки до категорії
3. **JSON-поля**: `report_snapshots.filters` та `data` зберігають складні структури для кешування звітів

---

## Візуалізація у Mermaid Live Editor
Скопіюйте блок коду Mermaid та відкрийте у [Mermaid Live Editor](https://mermaid.live) для інтерактивного перегляду.
