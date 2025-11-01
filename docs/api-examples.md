# API Usage Examples

Приклади використання REST API фінансового трекера.

**Base URL:** `http://127.0.0.1:8000/api/v1`

---

## 🔐 Authentication

### Register
```http
POST /api/v1/auth/register
Content-Type: application/json

{
  "name": "John Doe",
  "email": "john@example.com",
  "password": "password123",
  "password_confirmation": "password123"
}
```

**Response (201):**
```json
{
  "success": true,
  "message": "Користувача успішно зареєстровано",
  "data": {
    "user": {
      "id": 1,
      "name": "John Doe",
      "email": "john@example.com",
      "created_at": "2025-10-06T12:00:00.000000Z"
    },
    "token": "1|abc123..."
  }
}
```

### Login
```http
POST /api/v1/auth/login
Content-Type: application/json

{
  "email": "test@example.com",
  "password": "password"
}
```

**Response (200):**
```json
{
  "success": true,
  "message": "Успішна авторизація",
  "data": {
    "user": { ... },
    "token": "2|xyz789..."
  }
}
```

### Get Profile
```http
GET /api/v1/auth/me
Authorization: Bearer YOUR_TOKEN_HERE
```

### Logout
```http
POST /api/v1/auth/logout
Authorization: Bearer YOUR_TOKEN_HERE
```

---

## 📂 Categories

### Get All Categories
```http
GET /api/v1/categories
Authorization: Bearer YOUR_TOKEN_HERE
```

**Query Parameters:**
- `type` - income|expense (фільтр по типу)
- `is_active` - 1|0 (тільки активні)

**Response (200):**
```json
{
  "success": true,
  "data": {
    "categories": [
      {
        "id": 1,
        "user_id": null,
        "name": "Зарплата",
        "type": "income",
        "icon": "wallet",
        "color": "#10B981",
        "is_active": 1
      },
      ...
    ]
  }
}
```

### Create Category
```http
POST /api/v1/categories
Authorization: Bearer YOUR_TOKEN_HERE
Content-Type: application/json

{
  "name": "Моя категорія",
  "type": "expense",
  "icon": "coffee",
  "color": "#FF5733",
  "is_active": true
}
```

### Update Category
```http
PUT /api/v1/categories/{id}
Authorization: Bearer YOUR_TOKEN_HERE
Content-Type: application/json

{
  "name": "Оновлена назва",
  "color": "#00FF00"
}
```

**Note:** Системні категорії (user_id = null) не можна редагувати → 403

### Delete Category
```http
DELETE /api/v1/categories/{id}
Authorization: Bearer YOUR_TOKEN_HERE
```

---

## 💸 Transactions

### Get All Transactions
```http
GET /api/v1/transactions?page=1&per_page=15
Authorization: Bearer YOUR_TOKEN_HERE
```

**Query Parameters:**
- `page` - номер сторінки (default: 1)
- `per_page` - к-сть на сторінку (default: 15)
- `date_from` - YYYY-MM-DD (фільтр)
- `date_to` - YYYY-MM-DD (фільтр)
- `category_id` - ID категорії
- `type` - income|expense

**Response (200):**
```json
{
  "success": true,
  "data": {
    "transactions": [
      {
        "id": 1,
        "user_id": 1,
        "category_id": 6,
        "amount": "150.50",
        "description": "Покупки в супермаркеті",
        "transaction_date": "2025-10-05",
        "category": {
          "id": 6,
          "name": "Їжа",
          "type": "expense",
          "icon": "shopping-cart",
          "color": "#EF4444"
        }
      },
      ...
    ],
    "pagination": {
      "current_page": 1,
      "per_page": 15,
      "total": 72,
      "last_page": 5
    }
  }
}
```

### Create Transaction
```http
POST /api/v1/transactions
Authorization: Bearer YOUR_TOKEN_HERE
Content-Type: application/json

{
  "category_id": 1,
  "amount": 5000,
  "description": "Зарплата за жовтень",
  "transaction_date": "2025-10-01"
}
```

**Validation:**
- `amount` - required, min: 0.01
- `transaction_date` - required, before_or_equal: today

### Get Transaction Stats
```http
GET /api/v1/transactions-stats?date_from=2025-10-01&date_to=2025-10-31
Authorization: Bearer YOUR_TOKEN_HERE
```

**Response (200):**
```json
{
  "success": true,
  "data": {
    "total_income": 25000.00,
    "total_expense": 18450.75,
    "balance": 6549.25
  }
}
```

---

## 💰 Budgets

### Get All Budgets
```http
GET /api/v1/budgets
Authorization: Bearer YOUR_TOKEN_HERE
```

**Query Parameters:**
- `is_active` - 1|0 (тільки активні)
- `current` - 1 (тільки поточні бюджети)

**Response (200):**
```json
{
  "success": true,
  "data": {
    "budgets": [
      {
        "id": 1,
        "user_id": 1,
        "category_id": 6,
        "amount": 5000,
        "period": "monthly",
        "start_date": "2025-10-01",
        "end_date": "2025-10-31",
        "alert_threshold": 80,
        "is_active": 1,
        "spent": 3200.50,
        "remaining": 1799.50,
        "percentage": 64.01,
        "is_over_budget": false,
        "is_alert_triggered": false,
        "category": { ... }
      },
      ...
    ]
  }
}
```

### Create Budget
```http
POST /api/v1/budgets
Authorization: Bearer YOUR_TOKEN_HERE
Content-Type: application/json

{
  "category_id": 6,
  "amount": 5000,
  "period": "monthly",
  "start_date": "2025-10-01",
  "end_date": "2025-10-31",
  "alert_threshold": 80,
  "is_active": true
}
```

**Validation:**
- `start_date` < `end_date`
- `amount` - required, min: 0.01
- `alert_threshold` - 0-100
- `period` - daily|weekly|monthly|yearly

### Update Budget
```http
PUT /api/v1/budgets/{id}
Authorization: Bearer YOUR_TOKEN_HERE
Content-Type: application/json

{
  "amount": 6000,
  "alert_threshold": 70
}
```

---

## 📊 Statistics

### Get Overview (Dashboard)
```http
GET /api/v1/stats/overview?date_from=2025-10-01&date_to=2025-10-31
Authorization: Bearer YOUR_TOKEN_HERE
```

**Response (200):**
```json
{
  "success": true,
  "data": {
    "total_income": 25000.00,
    "total_expense": 18450.75,
    "balance": 6549.25,
    "top_expense_categories": [
      {
        "category_id": 6,
        "category_name": "Їжа",
        "category_color": "#EF4444",
        "total": 5200.00
      },
      ...
    ]
  }
}
```

### Get Cashflow (Chart Data)
```http
GET /api/v1/stats/cashflow?months=6
Authorization: Bearer YOUR_TOKEN_HERE
```

**Response (200):**
```json
{
  "success": true,
  "data": {
    "cashflow": [
      {
        "month": "2025-05",
        "income": 23000.00,
        "expense": 17800.00
      },
      {
        "month": "2025-06",
        "income": 25000.00,
        "expense": 19200.00
      },
      ...
    ]
  }
}
```

### Get Category Breakdown (Pie Chart)
```http
GET /api/v1/stats/category-breakdown?date_from=2025-10-01&date_to=2025-10-31
Authorization: Bearer YOUR_TOKEN_HERE
```

**Response (200):**
```json
{
  "success": true,
  "data": {
    "breakdown": [
      {
        "category_id": 6,
        "category_name": "Їжа",
        "category_color": "#EF4444",
        "total": 5200.00,
        "percentage": 28.18
      },
      {
        "category_id": 7,
        "category_name": "Транспорт",
        "category_color": "#F59E0B",
        "total": 3800.00,
        "percentage": 20.59
      },
      ...
    ]
  }
}
```

---

## ❌ Error Responses

### 401 Unauthorized
```json
{
  "message": "Unauthenticated."
}
```

### 403 Forbidden
```json
{
  "success": false,
  "message": "Cannot edit system category"
}
```

### 422 Validation Error
```json
{
  "message": "The given data was invalid.",
  "errors": {
    "email": [
      "The email field is required."
    ],
    "password": [
      "The password must be at least 8 characters."
    ]
  }
}
```

### 500 Server Error
```json
{
  "success": false,
  "message": "Internal server error"
}
```

---

## 🔧 Testing with curl (PowerShell)

### Register:
```powershell
$body = @{
    name = 'Test User'
    email = 'test@example.com'
    password = 'password123'
    password_confirmation = 'password123'
} | ConvertTo-Json

Invoke-RestMethod -Uri 'http://127.0.0.1:8000/api/v1/auth/register' `
    -Method POST -Body $body -ContentType 'application/json'
```

### Login and save token:
```powershell
$loginBody = @{
    email = 'test@example.com'
    password = 'password'
} | ConvertTo-Json

$response = Invoke-RestMethod -Uri 'http://127.0.0.1:8000/api/v1/auth/login' `
    -Method POST -Body $loginBody -ContentType 'application/json'

$token = $response.data.token
```

### Get categories with auth:
```powershell
$headers = @{
    'Authorization' = "Bearer $token"
}

Invoke-RestMethod -Uri 'http://127.0.0.1:8000/api/v1/categories' `
    -Method GET -Headers $headers
```

---

## 📝 Notes

- Всі захищені endpoints потребують заголовок `Authorization: Bearer TOKEN`
- Rate limit: 60 запитів/хвилину per user
- Дати у форматі `YYYY-MM-DD`
- Суми у форматі decimal (2 знаки після коми)
- Всі відповіді у форматі JSON з полями `success`, `data`, `message`

---

**Тестовий користувач (після `php artisan migrate:fresh --seed`):**
- Email: `test@example.com`
- Password: `password`
