# 🚀 Швидкий старт - Вибір валюти в Cashflow

## Для користувачів

### Як змінити валюту?

1. Відкрийте Dashboard
2. Знайдіть графік "📈 Cashflow"
3. Під графіком побачите кнопки: **₴ UAH** | **$ USD** | **zł PLN**
4. Натисніть на потрібну валюту
5. Готово! Графік оновиться автоматично

### Приклад

Хочете побачити доходи в доларах за останній місяць?
- Натисніть **"30д"** (період)
- Натисніть **"$ USD"** (валюта)
- Бачите результат в доларах! 💵

---

## Для розробників

### Тестування

```bash
# Швидкий тест
php scripts/diagnostics/test-currency-selector.php

# Якщо проблеми з курсами
php scripts/currency/force-api-update.php
```

### API запит

```bash
curl "http://localhost:8000/api/v1/stats/cashflow?period=30d&currency=USD" \
  -H "Authorization: Bearer YOUR_TOKEN"
```

### Відповідь
```json
{
    "success": true,
    "data": {
        "cashflow": [...],
        "currency": "USD",  // 👈 Вибрана валюта
        "period": "30d"
    }
}
```

---

## Технічні деталі

### Backend
```php
// StatsService::getCashflow()
public function getCashflow(
    int $userId, 
    string $period = '6m',
    ?string $targetCurrency = null  // 👈 Новий параметр
): array
```

### Frontend
```javascript
// Зміна валюти
function changeCashflowCurrency(currency) {
    loadCashflowData(currentPeriod, currency);
}
```

---

## Документація

📚 Повна документація: `docs/CASHFLOW-CURRENCY-SELECTOR.md`  
📊 Звіт: `docs/CURRENCY-SELECTOR-IMPLEMENTATION.md`  
🧪 Тести: `scripts/diagnostics/test-currency-selector.php`

---

**Готово до використання!** 🎉
