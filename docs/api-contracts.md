# REST API Контракти – Фінансовий трекер

> Base URL: `/api/v1`  
> Автентифікація: Laravel Sanctum (Bearer token)  
> Content-Type: `application/json`

## Загальні правила

### Формат відповідей
**Успішна відповідь:**
```json
{
  "success": true,
  "data": { ... },
  "message": "Operation successful"
}
```

**Помилка:**
```json
{
  "success": false,
  "message": "Error description",
  "errors": {
    "field": ["Validation error message"]
  }
}
```

### HTTP коди статусу:
- `200 OK` — успішний запит (GET, PUT)
- `201 Created` — ресурс створено (POST)
- `204 No Content` — успішне видалення (DELETE)
- `400 Bad Request` — помилка валідації
- `401 Unauthorized` — не автентифіковано
- `403 Forbidden` — немає прав доступу
- `404 Not Found` — ресурс не знайдено
- `422 Unprocessable Entity` — помилка валідації (Laravel)
- `500 Internal Server Error` — серверна помилка

---

## 1. Автентифікація

### POST /auth/register
Реєстрація нового користувача.

**Request Body:**
```json
{
  "name": "Іван Петренко",
  "email": "ivan@example.com",
  "password": "SecurePass123!",
  "password_confirmation": "SecurePass123!"
}
```

**Response (201):**
```json
{
  "success": true,
  "data": {
    "user": {
      "id": 1,
      "name": "Іван Петренко",
      "email": "ivan@example.com",
      "created_at": "2025-10-06T10:30:00Z"
    },
    "token": "1|abcd1234..."
  },
  "message": "User registered successfully"
}
```

---

### POST /auth/login
Вхід користувача.

**Request Body:**
```json
{
  "email": "ivan@example.com",
  "password": "SecurePass123!"
}
```

**Response (200):**
```json
{
  "success": true,
  "data": {
    "user": { ... },
    "token": "2|xyz9876..."
  },
  "message": "Logged in successfully"
}
```

---

### POST /auth/logout
Вихід користувача (потребує автентифікації).

**Headers:**
```
Authorization: Bearer {token}
```

**Response (200):**
```json
{
  "success": true,
  "message": "Logged out successfully"
}
```

---

## 2. Категорії

### GET /categories
Список категорій користувача + системні.

**Query Parameters:**
- `type` (optional): `income` | `expense` — фільтр за типом
- `is_active` (optional): `true` | `false` — тільки активні

**Response (200):**
```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "name": "Зарплата",
      "type": "income",
      "icon": "wallet",
      "color": "#10B981",
      "is_active": true,
      "is_system": false
    },
    ...
  ]
}
```

---

### POST /categories
Створити нову категорію.

**Request Body:**
```json
{
  "name": "Кава",
  "type": "expense",
  "icon": "coffee",
  "color": "#F59E0B"
}
```

**Response (201):**
```json
{
  "success": true,
  "data": {
    "id": 10,
    "name": "Кава",
    "type": "expense",
    "icon": "coffee",
    "color": "#F59E0B",
    "is_active": true,
    "created_at": "2025-10-06T11:00:00Z"
  },
  "message": "Category created successfully"
}
```

---

### PUT /categories/{id}
Оновити категорію.

**Request Body:**
```json
{
  "name": "Кава та чай",
  "color": "#EF4444"
}
```

**Response (200):**
```json
{
  "success": true,
  "data": { ... },
  "message": "Category updated successfully"
}
```

---

### DELETE /categories/{id}
Видалити категорію (тільки власні, не системні).

**Response (204):** No Content

---

## 3. Транзакції

### GET /transactions
Список транзакцій користувача.

**Query Parameters:**
- `page` (default: 1) — пагінація
- `per_page` (default: 20, max: 100)
- `from_date` (optional): `YYYY-MM-DD`
- `to_date` (optional): `YYYY-MM-DD`
- `category_id` (optional): ID категорії
- `type` (optional): `income` | `expense`

**Response (200):**
```json
{
  "success": true,
  "data": {
    "transactions": [
      {
        "id": 1,
        "amount": 5000.00,
        "description": "Зарплата жовтень",
        "transaction_date": "2025-10-01",
        "category": {
          "id": 1,
          "name": "Зарплата",
          "type": "income"
        },
        "created_at": "2025-10-01T09:00:00Z"
      },
      ...
    ],
    "pagination": {
      "current_page": 1,
      "total_pages": 5,
      "per_page": 20,
      "total": 98
    }
  }
}
```

---

### POST /transactions
Створити нову транзакцію.

**Request Body:**
```json
{
  "category_id": 5,
  "amount": 250.50,
  "description": "Покупка продуктів",
  "transaction_date": "2025-10-06"
}
```

**Response (201):**
```json
{
  "success": true,
  "data": { ... },
  "message": "Transaction created successfully"
}
```

---

### PUT /transactions/{id}
Оновити транзакцію.

**Request Body:**
```json
{
  "amount": 300.00,
  "description": "Покупка продуктів та господарства"
}
```

**Response (200):**
```json
{
  "success": true,
  "data": { ... },
  "message": "Transaction updated successfully"
}
```

---

### DELETE /transactions/{id}
Видалити транзакцію.

**Response (204):** No Content

---

## 4. Бюджети

### GET /budgets
Список бюджетів користувача.

**Query Parameters:**
- `is_active` (optional): `true` | `false`

**Response (200):**
```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "category": {
        "id": 3,
        "name": "Їжа"
      },
      "amount": 3000.00,
      "period": "monthly",
      "start_date": "2025-10-01",
      "end_date": "2025-10-31",
      "spent": 1250.75,
      "remaining": 1749.25,
      "percentage": 41.69,
      "alert_threshold": 80,
      "is_over_budget": false,
      "is_active": true
    },
    ...
  ]
}
```

---

### POST /budgets
Створити новий бюджет.

**Request Body:**
```json
{
  "category_id": 3,
  "amount": 3000.00,
  "period": "monthly",
  "start_date": "2025-10-01",
  "end_date": "2025-10-31",
  "alert_threshold": 80
}
```

**Response (201):**
```json
{
  "success": true,
  "data": { ... },
  "message": "Budget created successfully"
}
```

---

### PUT /budgets/{id}
Оновити бюджет.

**Response (200):**
```json
{
  "success": true,
  "data": { ... },
  "message": "Budget updated successfully"
}
```

---

### DELETE /budgets/{id}
Видалити бюджет.

**Response (204):** No Content

---

## 5. Статистика та звіти

### GET /stats/overview
Загальна статистика за період.

**Query Parameters:**
- `from_date` (optional): `YYYY-MM-DD` (default: перший день поточного місяця)
- `to_date` (optional): `YYYY-MM-DD` (default: сьогодні)

**Response (200):**
```json
{
  "success": true,
  "data": {
    "period": {
      "from": "2025-10-01",
      "to": "2025-10-06"
    },
    "total_income": 5000.00,
    "total_expense": 1850.50,
    "balance": 3149.50,
    "transactions_count": 12,
    "top_categories": [
      {
        "category": "Їжа",
        "amount": 800.00,
        "percentage": 43.2
      },
      ...
    ]
  }
}
```

---

### GET /stats/cashflow
Cashflow за місяцями (для графіків).

**Query Parameters:**
- `months` (default: 6, max: 24) — кількість місяців назад

**Response (200):**
```json
{
  "success": true,
  "data": {
    "labels": ["2025-05", "2025-06", ..., "2025-10"],
    "income": [4500, 5000, 4800, 5200, 5000, 5500],
    "expense": [3200, 2800, 3500, 3100, 2900, 1850]
  }
}
```

---

### GET /stats/category-breakdown
Розподіл витрат за категоріями (для pie chart).

**Query Parameters:**
- `from_date`, `to_date` (optional)

**Response (200):**
```json
{
  "success": true,
  "data": [
    {
      "category": "Їжа",
      "amount": 800.00,
      "percentage": 43.2,
      "color": "#EF4444"
    },
    ...
  ]
}
```

---

## 6. Експорт

### GET /export/transactions
Експортувати транзакції у CSV.

**Query Parameters:**
- `from_date`, `to_date`, `category_id` (опціонально)

**Response (200):**
```
Content-Type: text/csv
Content-Disposition: attachment; filename="transactions_2025-10-06.csv"

ID,Date,Category,Amount,Description
1,2025-10-01,Зарплата,5000.00,"Зарплата жовтень"
...
```

---

## Наступні кроки
1. Імплементувати контролери та FormRequests
2. Додати middleware для автентифікації (sanctum)
3. Налаштуватиrate limiting (60 req/min)
4. Написати API tests (PHPUnit/Pest)
5. Згенерувати OpenAPI/Swagger документацію (L5-Swagger або Scramble)
