# 🚀 Production Environment Setup

Цей файл містить швидку інформацію про production налаштування для **Finance Tracker**.

---

## 📂 Файли конфігурації

### `.env.production`
Основний production environment файл. **КРИТИЧНО:** ніколи не комітьте цей файл в git!

**Створення:**
```bash
cp .env.production.example .env.production
nano .env.production
```

**Обов'язкові налаштування:**
- `APP_KEY` - згенеруйте: `php artisan key:generate --show`
- `APP_DEBUG=false` - ОБОВ'ЯЗКОВО false
- `APP_URL` - ваш production домен
- `DB_*` - credentials бази даних
- `REDIS_PASSWORD` - пароль Redis
- `MAIL_*` - налаштування пошти
- `SENTRY_LARAVEL_DSN` - для відстеження помилок

### `.env.production.example`
Шаблон з прикладами налаштувань. Цей файл безпечно комітити в git.

---

## 🔐 Безпека

### ❌ НІКОЛИ не комітьте:
- `.env.production` - production credentials
- `*.key` файли - приватні ключі
- Database dumps - резервні копії БД
- `auth.json` - composer credentials

### ✅ Використовуйте Secrets Manager:
- AWS Secrets Manager
- HashiCorp Vault
- Doppler
- або зашифровані змінні в CI/CD

---

## 📝 Швидкий старт

### 1. Підготовка сервера
```bash
# Встановити залежності
sudo apt update
sudo apt install php8.3-fpm nginx mysql-server redis-server supervisor

# Налаштувати SSL
sudo certbot --nginx -d your-domain.com
```

### 2. Налаштування додатку
```bash
# Клонувати репозиторій
git clone https://github.com/your-repo/finance-tracker.git
cd finance-tracker

# Створити .env.production
cp .env.production.example .env.production
nano .env.production

# Встановити залежності
composer install --no-dev --optimize-autoloader
npm ci && npm run build

# Згенерувати ключ
php artisan key:generate

# Запустити міграції
php artisan migrate --force

# Оптимізувати
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

### 3. Deployment
```bash
# Використати автоматичний скрипт
./scripts/deploy-production.sh

# Або вручну
php artisan down
git pull origin production
composer install --no-dev --optimize-autoloader
npm ci && npm run build
php artisan migrate --force
php artisan config:cache
php artisan up
```

---

## 🔧 Maintenance Commands

### Backup
```bash
# Створити backup
./scripts/backup.sh

# Backup бази даних
mysqldump -u user -p database > backup.sql
```

### Cache Management
```bash
# Очистити всі кеші
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear

# Відновити кеші
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

### Queue Workers
```bash
# Перезапустити workers
sudo supervisorctl restart finance-tracker-worker:*

# Перевірити статус
sudo supervisorctl status
```

---

## 🆘 Emergency Procedures

### Rollback
```bash
# Швидкий rollback
./scripts/rollback-production.sh

# Вручну до попереднього коміту
php artisan down
git reset --hard HEAD~1
php artisan config:cache
php artisan up
```

### Database Restore
```bash
# Відновити з backup
mysql -u user -p database < backup_YYYYMMDD_HHMMSS.sql
```

---

## 📊 Monitoring

### Health Checks
- Health endpoint: `https://your-domain.com/health`
- Detailed health: `https://your-domain.com/health/detailed`

### Logs Location
- Laravel logs: `/var/www/finance-tracker/storage/logs/laravel.log`
- Nginx logs: `/var/log/nginx/finance-tracker-*.log`
- PHP-FPM logs: `/var/log/php8.3-fpm.log`
- Worker logs: `/var/www/finance-tracker/storage/logs/worker.log`

### Monitoring Tools
- **Sentry** - Error tracking
- **UptimeRobot** - Uptime monitoring
- **Prometheus** - Metrics collection
- **Grafana** - Dashboards (опціонально)

---

## 📚 Документація

- 📖 [Production Deployment Guide](docs/PRODUCTION-DEPLOYMENT-GUIDE.md) - Повний гайд
- ✅ [Production Checklist](docs/PRODUCTION-CHECKLIST-QUICK.md) - Швидкий чеклист
- 🔒 [Security Guide](docs/SECURITY.md) - Безпека
- ⚡ [Performance Optimization](docs/PERFORMANCE-OPTIMIZATION.md) - Оптимізація

---

## 🤝 Support

**Production Issues:**
- Email: support@your-domain.com
- Telegram: @your_support
- Emergency: +380XXXXXXXXX

**Documentation:**
- Internal wiki: [URL]
- API docs: [URL]
- Runbook: [URL]

---

## ✅ Pre-Deployment Checklist

Перед кожним deployment перевіряйте:

- [ ] Тести пройдено: `php artisan test`
- [ ] Static analysis OK: `vendor/bin/phpstan analyse`
- [ ] Security audit OK: `composer audit`
- [ ] Backup створено
- [ ] `.env.production` налаштовано
- [ ] `APP_DEBUG=false`
- [ ] SSL сертифікат валідний
- [ ] Monitoring працює

---

**Останнє оновлення:** 7 жовтня 2025  
**Версія:** 1.0.0  
**Maintainer:** Finance Tracker Team
