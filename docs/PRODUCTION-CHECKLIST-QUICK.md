# ✅ Production Deployment Checklist

**Finance Tracker** - Швидкий чеклист перед випуском

---

## 🎯 Критичні налаштування (ОБОВ'ЯЗКОВО)

### Основні
- [ ] `APP_ENV=production` в `.env.production`
- [ ] `APP_DEBUG=false` в `.env.production`
- [ ] `APP_KEY` згенеровано (`php artisan key:generate --show`)
- [ ] `APP_URL` встановлено на реальний домен
- [ ] SSL сертифікат встановлено та працює

### База даних
- [ ] Database створено з UTF8MB4 charset
- [ ] Database user НЕ root (окремий користувач з обмеженими правами)
- [ ] `DB_PASSWORD` складний (мінімум 32 символи)
- [ ] Міграції успішно виконано на production DB
- [ ] Database backup налаштовано та протестовано

### Кеш та сесії
- [ ] Redis встановлено та працює
- [ ] `REDIS_PASSWORD` встановлено
- [ ] `CACHE_DRIVER=redis`
- [ ] `SESSION_DRIVER=redis`
- [ ] `SESSION_SECURE_COOKIE=true`
- [ ] `SESSION_DOMAIN` встановлено правильно

### Безпека
- [ ] HTTPS увімкнено (Let's Encrypt або платний SSL)
- [ ] Firewall налаштовано (тільки 80, 443, 22)
- [ ] Rate limiting активовано
- [ ] CSRF protection працює
- [ ] Security headers налаштовано в Nginx
- [ ] Secrets НЕ в git репозиторії

### Email
- [ ] `MAIL_*` credentials налаштовано
- [ ] Email відправка протестована
- [ ] `MAIL_FROM_ADDRESS` використовує ваш домен
- [ ] SPF/DKIM записи налаштовано (опціонально)

---

## 📊 Моніторинг та логування

- [ ] Sentry налаштовано (`SENTRY_LARAVEL_DSN`)
- [ ] Error tracking працює та тестується
- [ ] Uptime monitoring налаштовано (UptimeRobot, Pingdom)
- [ ] Log rotation налаштовано
- [ ] Alerts налаштовано (email/Telegram)

---

## ⚡ Продуктивність

- [ ] OPcache увімкнено (`OPCACHE_ENABLE=1`)
- [ ] `OPCACHE_VALIDATE_TIMESTAMPS=0` для production
- [ ] Config cache: `php artisan config:cache`
- [ ] Route cache: `php artisan route:cache`
- [ ] View cache: `php artisan view:cache`
- [ ] Composer autoload optimized: `composer dump-autoload --optimize`
- [ ] Frontend assets build: `npm run build`
- [ ] Database indexes оптимізовано

---

## 🔧 Інфраструктура

### Nginx
- [ ] Nginx конфігурація перевірена: `nginx -t`
- [ ] Rate limiting налаштовано
- [ ] Gzip compression увімкнено
- [ ] Static files caching налаштовано
- [ ] Security headers додано

### PHP-FPM
- [ ] PHP-FPM pool налаштовано для production
- [ ] Memory limits встановлено відповідно
- [ ] Timeouts налаштовано

### Supervisor
- [ ] Queue workers налаштовано в Supervisor
- [ ] Workers автоматично перезапускаються
- [ ] Логи workers налаштовано

### Backup
- [ ] Automatic database backup налаштовано (cron)
- [ ] Files backup налаштовано
- [ ] Backup retention policy встановлено (30 днів)
- [ ] Backup restoration протестовано
- [ ] Offsite backup налаштовано (S3/FTP)

---

## 🧪 Тестування

### Функціональні тести
- [ ] Реєстрація користувача працює
- [ ] Вхід в систему працює
- [ ] Створення транзакції працює
- [ ] Редагування транзакції працює
- [ ] Видалення транзакції працює
- [ ] Dashboard відображається коректно
- [ ] Бюджети працюють
- [ ] Експорт в Excel працює
- [ ] Графіки відображаються
- [ ] Перемикання валют працює
- [ ] Темна/світла тема працює

### Технічні тести
- [ ] Health check endpoint: `curl https://your-domain.com/health`
- [ ] API endpoints відповідають правильно
- [ ] Rate limiting спрацьовує
- [ ] CSRF protection спрацьовує
- [ ] Email notifications надсилаються
- [ ] Queue jobs виконуються

### Продуктивність
- [ ] Page load time < 2 секунди
- [ ] API response time < 500ms
- [ ] N+1 queries відсутні
- [ ] Memory usage в нормі

---

## 📱 Responive & UI

- [ ] Desktop version працює (1920px+)
- [ ] Tablet version працює (768-1024px)
- [ ] Mobile version працює (320-767px)
- [ ] Touch interactions працюють на мобільних
- [ ] Всі іконки відображаються
- [ ] Fonts завантажуються коректно

---

## 🔐 Security Scan

- [ ] `composer audit` без критичних вразливостей
- [ ] `npm audit` без критичних вразливостей
- [ ] PHPStan аналіз пройдено без помилок
- [ ] SQL injection захист перевірено
- [ ] XSS захист перевірено
- [ ] CSRF захист перевірено
- [ ] File upload валідація працює

---

## 📋 Документація

- [ ] README.md оновлено
- [ ] API documentation актуальна
- [ ] Deployment guide підготовлено
- [ ] Runbook створено
- [ ] Emergency contacts задокументовано
- [ ] Rollback процедура описана

---

## 🚀 Pre-Deploy Commands

```bash
# 1. Run tests
php artisan test
vendor/bin/phpstan analyse

# 2. Check security
composer audit
npm audit

# 3. Build assets
npm run build

# 4. Create backup
./scripts/backup.sh

# 5. Enable maintenance mode
php artisan down

# 6. Deploy
./scripts/deploy.sh

# 7. Verify deployment
curl -I https://your-domain.com/health

# 8. Disable maintenance mode
php artisan up
```

---

## 🆘 Emergency Rollback

```bash
# Quick rollback
./scripts/rollback.sh

# Or manually:
php artisan down
git reset --hard HEAD~1
mysql -u user -p database < backup.sql
php artisan config:cache
php artisan up
```

---

## 📞 Support

**Emergency:** +380XXXXXXXXX
**Email:** support@your-domain.com
**Telegram:** @your_support_bot

---

## ✅ Sign-off

**Reviewed by:**
- [ ] Backend Developer: ________________ Date: ______
- [ ] DevOps Engineer: __________________ Date: ______
- [ ] Project Manager: __________________ Date: ______

**Approved for production:** YES / NO

**Deployment Date:** _______________
**Deployment Time:** _______________

---

**Останнє оновлення:** 7 жовтня 2025
