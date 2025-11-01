# 📚 Документація проекту

Централізоване сховище всієї документації фінансового трекера.

## 🚀 Швидкий старт

### Для нових розробників
1. **[README.md](../README.md)** - Головна документація, встановлення
2. **[deployment.md](deployment.md)** - Інструкції по деплою
3. **[models.md](models.md)** - Структура моделей та відношень

### Для роботи з API
1. **[api-contracts.md](api-contracts.md)** - Специфікація API
2. **[api-examples.md](api-examples.md)** - Приклади використання
3. **[openapi.yaml](openapi.yaml)** - OpenAPI 3.0 документація

## 📖 Основна документація

### 💱 Мультивалютність
- **[multi-currency-guide.md](multi-currency-guide.md)** ⭐ - Повний гайд по роботі з валютами
- **[EXCHANGERATE-API-SETUP.md](EXCHANGERATE-API-SETUP.md)** - Налаштування ExchangeRate-API
- **[EXCHANGERATE-API-DONE.md](EXCHANGERATE-API-DONE.md)** - Підсумок інтеграції

### 🏗️ Архітектура
- **[er-diagram.md](er-diagram.md)** - ER діаграма бази даних
- **[models.md](models.md)** - Опис моделей Laravel

### 🚀 Production
- **[production-checklist.md](production-checklist.md)** - Чеклист перед запуском
- **[PRODUCTION-READINESS-REPORT.md](PRODUCTION-READINESS-REPORT.md)** - Звіт про готовність
- **[deployment.md](deployment.md)** - Інструкції по деплою

### 📊 Розробка
- **[roadmap.md](roadmap.md)** - Roadmap проекту
- **[READY-TO-LAUNCH.md](READY-TO-LAUNCH.md)** - Статус готовності
- **[test-coverage-completion.md](test-coverage-completion.md)** - Покриття тестами

### 📈 Історія розробки
- **[stage-7-summary.md](stage-7-summary.md)** - Підсумок 7 етапу
- **[stages-2-3-summary.md](stages-2-3-summary.md)** - Підсумок 2-3 етапів
- **[stages-5-6-summary.md](stages-5-6-summary.md)** - Підсумок 5-6 етапів

## 🗂️ Архів

**[archive/](archive/)** - Старі документи, вирішені проблеми, історичні записи

## 🔍 Що де знайти?

### Як працювати з валютами?
→ [multi-currency-guide.md](multi-currency-guide.md)

### Як розгорнути на production?
→ [deployment.md](deployment.md) + [production-checklist.md](production-checklist.md)

### Які є API endpoints?
→ [api-contracts.md](api-contracts.md) + [openapi.yaml](openapi.yaml)

### Як працює база даних?
→ [er-diagram.md](er-diagram.md) + [models.md](models.md)

### Що далі розробляти?
→ [roadmap.md](roadmap.md)

### Як тестувати систему?
→ [test-coverage-completion.md](test-coverage-completion.md)

### Історія проблем та рішень?
→ [archive/](archive/)

## 📝 Оновлення документації

При додаванні нових функцій:

1. ✅ Оновити відповідний розділ в основній документації
2. ✅ Додати приклади в api-examples.md (якщо API)
3. ✅ Оновити roadmap.md
4. ✅ Додати тести та оновити test-coverage-completion.md

## 🛠️ Генерація документації

```bash
# Згенерувати ER діаграму (якщо встановлено laravel-er-diagram-generator)
php artisan generate:erd

# Експортувати OpenAPI документацію
php artisan openapi:export

# Генерувати API документацію через Swagger
npm run api-docs
```

## 🔗 Корисні посилання

- [Laravel Documentation](https://laravel.com/docs)
- [ExchangeRate-API Docs](https://www.exchangerate-api.com/docs)
- [Chart.js Documentation](https://www.chartjs.org/docs)
- [Tailwind CSS](https://tailwindcss.com/docs)
- [Flowbite Components](https://flowbite.com/docs)

## 📧 Контакти

При питаннях по документації - створіть issue або зв'яжіться з командою.

---

**Останнє оновлення**: 06.10.2025  
**Версія документації**: 2.0
