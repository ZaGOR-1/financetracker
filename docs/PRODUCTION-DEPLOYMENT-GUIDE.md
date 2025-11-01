# 🚀 Production Deployment Guide

**Finance Tracker** - Повний гайд з розгортання на production

---

## 📋 Зміст

1. [Перед початком](#перед-початком)
2. [Підготовка середовища](#підготовка-середовища)
3. [Налаштування .env.production](#налаштування-envproduction)
4. [Deployment процес](#deployment-процес)
5. [Пост-deployment перевірки](#пост-deployment-перевірки)
6. [Моніторинг та обслуговування](#моніторинг-та-обслуговування)
7. [Rollback процедура](#rollback-процедура)
8. [Troubleshooting](#troubleshooting)

---

## Перед початком

### Вимоги до серверу

**Мінімальні вимоги:**
- CPU: 2 cores
- RAM: 4 GB
- Disk: 40 GB SSD
- OS: Ubuntu 22.04 LTS або новіший

**Рекомендовані вимоги:**
- CPU: 4 cores
- RAM: 8 GB
- Disk: 100 GB SSD
- OS: Ubuntu 22.04 LTS

### Необхідне ПЗ

```bash
# PHP 8.3+
sudo apt install php8.3-fpm php8.3-cli php8.3-mysql php8.3-redis \
  php8.3-xml php8.3-mbstring php8.3-curl php8.3-zip php8.3-intl

# Composer 2.x
curl -sS https://getcomposer.org/installer | php
sudo mv composer.phar /usr/local/bin/composer

# Node.js 20 LTS
curl -fsSL https://deb.nodesource.com/setup_20.x | sudo -E bash -
sudo apt install nodejs

# MySQL 8.0
sudo apt install mysql-server-8.0

# Redis 7.x
sudo apt install redis-server

# Nginx
sudo apt install nginx

# Supervisor (для черг)
sudo apt install supervisor

# Certbot (для SSL)
sudo apt install certbot python3-certbot-nginx
```

---

## Підготовка середовища

### 1. Створення користувача та директорій

```bash
# Створити користувача для додатку
sudo useradd -m -s /bin/bash finance-app
sudo usermod -aG www-data finance-app

# Створити директорії
sudo mkdir -p /var/www/finance-tracker
sudo chown finance-app:www-data /var/www/finance-tracker
```

### 2. Налаштування MySQL

```bash
# Увійти в MySQL
sudo mysql

# Створити базу даних та користувача
CREATE DATABASE finance_tracker_prod CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER 'finance_prod_user'@'localhost' IDENTIFIED BY 'STRONG_PASSWORD_HERE';
GRANT ALL PRIVILEGES ON finance_tracker_prod.* TO 'finance_prod_user'@'localhost';
FLUSH PRIVILEGES;
EXIT;

# Налаштувати MySQL для оптимальної роботи
sudo nano /etc/mysql/mysql.conf.d/mysqld.cnf
```

Додайте в `mysqld.cnf`:

```ini
[mysqld]
# Performance
innodb_buffer_pool_size = 2G
innodb_log_file_size = 512M
innodb_flush_log_at_trx_commit = 2
query_cache_type = 1
query_cache_size = 64M

# Connections
max_connections = 200
max_allowed_packet = 64M

# Charset
character-set-server = utf8mb4
collation-server = utf8mb4_unicode_ci
```

Перезапустіть MySQL:
```bash
sudo systemctl restart mysql
```

### 3. Налаштування Redis

```bash
# Відредагувати конфігурацію Redis
sudo nano /etc/redis/redis.conf
```

Знайдіть та змініть:

```conf
# Встановити пароль
requirepass YOUR_STRONG_REDIS_PASSWORD

# Налаштування пам'яті
maxmemory 1gb
maxmemory-policy allkeys-lru

# Persistence
save 900 1
save 300 10
save 60 10000

# Performance
tcp-backlog 511
timeout 0
tcp-keepalive 300
```

Перезапустіть Redis:
```bash
sudo systemctl restart redis-server
sudo systemctl enable redis-server
```

### 4. Налаштування Nginx

Створіть конфігурацію для сайту:

```bash
sudo nano /etc/nginx/sites-available/finance-tracker
```

```nginx
# Rate limiting zones
limit_req_zone $binary_remote_addr zone=login_limit:10m rate=5r/m;
limit_req_zone $binary_remote_addr zone=api_limit:10m rate=60r/m;
limit_req_zone $binary_remote_addr zone=global_limit:10m rate=100r/s;

# Redirect HTTP to HTTPS
server {
    listen 80;
    listen [::]:80;
    server_name finance.example.com www.finance.example.com;
    
    # Certbot challenge
    location /.well-known/acme-challenge/ {
        root /var/www/certbot;
    }
    
    location / {
        return 301 https://$server_name$request_uri;
    }
}

# HTTPS Server
server {
    listen 443 ssl http2;
    listen [::]:443 ssl http2;
    
    server_name finance.example.com www.finance.example.com;
    root /var/www/finance-tracker/public;
    
    index index.php;
    charset utf-8;
    
    # SSL Configuration (буде додано Certbot)
    ssl_certificate /etc/letsencrypt/live/finance.example.com/fullchain.pem;
    ssl_certificate_key /etc/letsencrypt/live/finance.example.com/privkey.pem;
    ssl_protocols TLSv1.2 TLSv1.3;
    ssl_ciphers HIGH:!aNULL:!MD5;
    ssl_prefer_server_ciphers on;
    ssl_session_cache shared:SSL:10m;
    ssl_session_timeout 10m;
    
    # Security Headers
    add_header Strict-Transport-Security "max-age=31536000; includeSubDomains; preload" always;
    add_header X-Frame-Options "SAMEORIGIN" always;
    add_header X-Content-Type-Options "nosniff" always;
    add_header X-XSS-Protection "1; mode=block" always;
    add_header Referrer-Policy "strict-origin-when-cross-origin" always;
    add_header Permissions-Policy "geolocation=(), microphone=(), camera=()" always;
    
    # Logging
    access_log /var/log/nginx/finance-tracker-access.log;
    error_log /var/log/nginx/finance-tracker-error.log;
    
    # Rate limiting
    limit_req zone=global_limit burst=20 nodelay;
    
    # Main location
    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }
    
    # Login rate limiting
    location ~ ^/(login|register) {
        limit_req zone=login_limit burst=3 nodelay;
        try_files $uri $uri/ /index.php?$query_string;
    }
    
    # API rate limiting
    location ~ ^/api/ {
        limit_req zone=api_limit burst=10 nodelay;
        try_files $uri $uri/ /index.php?$query_string;
    }
    
    # PHP-FPM
    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.3-fpm.sock;
        fastcgi_index index.php;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
        
        # Timeouts
        fastcgi_read_timeout 300;
        fastcgi_send_timeout 300;
        
        # Buffer settings
        fastcgi_buffers 16 16k;
        fastcgi_buffer_size 32k;
    }
    
    # Static files caching
    location ~* \.(jpg|jpeg|png|gif|ico|css|js|svg|woff|woff2|ttf|eot)$ {
        expires 1y;
        add_header Cache-Control "public, immutable";
        access_log off;
    }
    
    # Deny access to sensitive files
    location ~ /\.(?!well-known).* {
        deny all;
    }
    
    # Health check endpoint
    location /health {
        access_log off;
        return 200 "OK";
        add_header Content-Type text/plain;
    }
}
```

Активуйте конфігурацію:

```bash
sudo ln -s /etc/nginx/sites-available/finance-tracker /etc/nginx/sites-enabled/
sudo nginx -t
sudo systemctl restart nginx
```

### 5. Отримання SSL сертифікату

```bash
# Отримати сертифікат Let's Encrypt
sudo certbot --nginx -d finance.example.com -d www.finance.example.com

# Налаштувати автоматичне оновлення
sudo certbot renew --dry-run
```

### 6. Налаштування Supervisor для черг

```bash
sudo nano /etc/supervisor/conf.d/finance-tracker-worker.conf
```

```ini
[program:finance-tracker-worker]
process_name=%(program_name)s_%(process_num)02d
command=/usr/bin/php /var/www/finance-tracker/artisan queue:work redis --sleep=3 --tries=3 --max-time=3600 --timeout=60
autostart=true
autorestart=true
stopasgroup=true
killasgroup=true
user=finance-app
numprocs=2
redirect_stderr=true
stdout_logfile=/var/www/finance-tracker/storage/logs/worker.log
stopwaitsecs=3600
```

Запустіть worker:
```bash
sudo supervisorctl reread
sudo supervisorctl update
sudo supervisorctl start finance-tracker-worker:*
```

---

## Налаштування .env.production

### 1. Клонування репозиторію

```bash
# Перейти в директорію
cd /var/www/finance-tracker

# Клонувати репозиторій
sudo -u finance-app git clone https://github.com/your-username/finance-tracker.git .

# Переключитися на production гілку
sudo -u finance-app git checkout production
```

### 2. Створення .env файлу

```bash
# Скопіювати шаблон
sudo -u finance-app cp .env.production.example .env.production

# Відредагувати файл
sudo -u finance-app nano .env.production
```

### 3. Заповнення критичних значень

```bash
# Згенерувати APP_KEY
php artisan key:generate --show

# Додати в .env.production
APP_KEY=base64:GENERATED_KEY_HERE
```

Заповніть всі інші значення згідно з вашим середовищем:

- `APP_URL` - ваш домен
- `DB_*` - дані для підключення до MySQL
- `REDIS_PASSWORD` - пароль Redis
- `MAIL_*` - налаштування пошти
- `SENTRY_LARAVEL_DSN` - Sentry DSN для відстеження помилок
- `AWS_*` - якщо використовуєте S3

### 4. Встановлення залежностей

```bash
# PHP залежності (production only)
sudo -u finance-app composer install --no-dev --optimize-autoloader

# Node.js залежності
sudo -u finance-app npm ci

# Збудувати frontend assets
sudo -u finance-app npm run build
```

### 5. Міграції та сідери

```bash
# Запустити міграції
sudo -u finance-app php artisan migrate --force

# Заповнити початкові дані (опціонально)
sudo -u finance-app php artisan db:seed --force
```

### 6. Налаштування прав доступу

```bash
# Встановити правильні права
sudo chown -R finance-app:www-data /var/www/finance-tracker
sudo find /var/www/finance-tracker -type f -exec chmod 644 {} \;
sudo find /var/www/finance-tracker -type d -exec chmod 755 {} \;

# Спеціальні директорії з правами запису
sudo chmod -R 775 /var/www/finance-tracker/storage
sudo chmod -R 775 /var/www/finance-tracker/bootstrap/cache
```

### 7. Оптимізація Laravel

```bash
# Кешувати конфігурацію
sudo -u finance-app php artisan config:cache

# Кешувати роути
sudo -u finance-app php artisan route:cache

# Кешувати views
sudo -u finance-app php artisan view:cache

# Оптимізувати автозавантаження
sudo -u finance-app composer dump-autoload --optimize
```

---

## Deployment процес

### Автоматичний deployment script

Створіть файл `scripts/deploy.sh`:

```bash
#!/bin/bash

set -e

echo "🚀 Starting deployment..."

# Variables
APP_DIR="/var/www/finance-tracker"
BACKUP_DIR="/var/backups/finance-tracker"
DATE=$(date +%Y%m%d_%H%M%S)

# Create backup
echo "📦 Creating backup..."
mkdir -p $BACKUP_DIR
mysqldump -u finance_prod_user -p finance_tracker_prod > $BACKUP_DIR/db_$DATE.sql
tar -czf $BACKUP_DIR/files_$DATE.tar.gz $APP_DIR

# Enable maintenance mode
echo "🔧 Enabling maintenance mode..."
cd $APP_DIR
php artisan down

# Pull latest code
echo "📥 Pulling latest code..."
git pull origin production

# Install dependencies
echo "📚 Installing dependencies..."
composer install --no-dev --optimize-autoloader
npm ci
npm run build

# Run migrations
echo "🗄️ Running migrations..."
php artisan migrate --force

# Clear and rebuild cache
echo "🧹 Clearing caches..."
php artisan cache:clear
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Restart services
echo "♻️ Restarting services..."
sudo systemctl reload php8.3-fpm
sudo supervisorctl restart finance-tracker-worker:*

# Disable maintenance mode
echo "✅ Disabling maintenance mode..."
php artisan up

# Health check
echo "🏥 Running health check..."
RESPONSE=$(curl -s -o /dev/null -w "%{http_code}" https://finance.example.com/health)
if [ $RESPONSE -eq 200 ]; then
    echo "✅ Deployment successful! Health check passed."
else
    echo "❌ Deployment failed! Health check returned: $RESPONSE"
    exit 1
fi

echo "🎉 Deployment completed successfully!"
```

Зробіть script виконуваним:
```bash
chmod +x scripts/deploy.sh
```

Запустіть deployment:
```bash
sudo -u finance-app ./scripts/deploy.sh
```

---

## Пост-deployment перевірки

### 1. Перевірка здоров'я додатку

```bash
# Health check endpoint
curl -I https://finance.example.com/health
# Очікується: HTTP/2 200

# Detailed health
curl https://finance.example.com/health/detailed
```

### 2. Функціональне тестування

Перевірте ключові функції через браузер:

- ✅ Реєстрація користувача
- ✅ Вхід в систему
- ✅ Створення транзакції
- ✅ Перегляд дашборду
- ✅ Експорт даних
- ✅ Перемикання валют

### 3. Перевірка логів

```bash
# Laravel logs
tail -f /var/www/finance-tracker/storage/logs/laravel.log

# Nginx logs
tail -f /var/log/nginx/finance-tracker-error.log

# PHP-FPM logs
tail -f /var/log/php8.3-fpm.log

# Queue worker logs
tail -f /var/www/finance-tracker/storage/logs/worker.log
```

### 4. Перевірка продуктивності

```bash
# Середній час відповіді
ab -n 100 -c 10 https://finance.example.com/

# Навантажувальне тестування (опціонально)
wrk -t4 -c100 -d30s https://finance.example.com/
```

---

## Моніторинг та обслуговування

### 1. Налаштування моніторингу

**Sentry (відстеження помилок):**
- Увійдіть в Sentry.io
- Створіть новий проект для Laravel
- Скопіюйте DSN в `.env.production`
- Перевірте що помилки надходять

**Uptime monitoring:**
- UptimeRobot (безкоштовний)
- Pingdom
- StatusCake

Налаштуйте alerts на:
- ❌ Сайт недоступний (HTTP 5xx)
- ⚠️ Повільні відповіді (>2s)
- 💾 Високе використання диску (>90%)
- 🧠 Високе використання пам'яті (>90%)

### 2. Автоматичні backup

Створіть cron job для backup:

```bash
sudo crontab -e
```

```cron
# Database backup щодня о 2:00 AM
0 2 * * * /var/www/finance-tracker/scripts/backup-db.sh

# Files backup щотижня по неділях о 3:00 AM
0 3 * * 0 /var/www/finance-tracker/scripts/backup-files.sh

# Очистка старих backup (>30 днів)
0 4 * * 0 find /var/backups/finance-tracker -type f -mtime +30 -delete
```

Створіть `scripts/backup-db.sh`:

```bash
#!/bin/bash
DATE=$(date +%Y%m%d_%H%M%S)
BACKUP_DIR="/var/backups/finance-tracker"
mkdir -p $BACKUP_DIR

mysqldump -u finance_prod_user -p'YOUR_PASSWORD' finance_tracker_prod \
  | gzip > $BACKUP_DIR/db_$DATE.sql.gz

# Завантажити в S3 (опціонально)
aws s3 cp $BACKUP_DIR/db_$DATE.sql.gz \
  s3://finance-tracker-backups/database/
```

### 3. Оновлення системи

```bash
# Щомісяця оновлюйте системні пакети
sudo apt update && sudo apt upgrade -y

# Перезапустіть сервіси після оновлення
sudo systemctl restart nginx
sudo systemctl restart php8.3-fpm
sudo systemctl restart mysql
```

---

## Rollback процедура

Якщо щось пішло не так:

### 1. Швидкий rollback

```bash
#!/bin/bash
# scripts/rollback.sh

echo "⚠️ Starting rollback..."

# Enable maintenance mode
php artisan down

# Rollback to previous commit
git reset --hard HEAD~1

# Restore previous database backup
mysql -u finance_prod_user -p finance_tracker_prod < /var/backups/finance-tracker/db_LATEST.sql

# Rebuild caches
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Restart services
sudo systemctl reload php8.3-fpm
sudo supervisorctl restart finance-tracker-worker:*

# Disable maintenance mode
php artisan up

echo "✅ Rollback completed"
```

### 2. Rollback бази даних

```bash
# Список доступних backup
ls -lh /var/backups/finance-tracker/

# Відновити конкретний backup
mysql -u finance_prod_user -p finance_tracker_prod \
  < /var/backups/finance-tracker/db_20250107_020000.sql
```

---

## Troubleshooting

### Проблема: Сайт повертає 500 помилку

**Рішення:**
```bash
# Перевірити логи
tail -100 storage/logs/laravel.log

# Перевірити права доступу
ls -la storage/
ls -la bootstrap/cache/

# Очистити кеш
php artisan cache:clear
php artisan config:clear
php artisan view:clear
```

### Проблема: Черги не обробляються

**Рішення:**
```bash
# Перевірити статус worker
sudo supervisorctl status

# Перезапустити worker
sudo supervisorctl restart finance-tracker-worker:*

# Перевірити логи
tail -f storage/logs/worker.log
```

### Проблема: Високе навантаження CPU/Memory

**Рішення:**
```bash
# Перевірити процеси
top
htop

# Перевірити MySQL запити
mysql -u root -p -e "SHOW PROCESSLIST;"

# Оптимізувати кеш
php artisan cache:clear
php artisan optimize

# Збільшити ресурси Redis
sudo nano /etc/redis/redis.conf
# maxmemory 2gb
```

### Проблема: SSL сертифікат expired

**Рішення:**
```bash
# Перевірити термін дії
sudo certbot certificates

# Оновити сертифікат
sudo certbot renew

# Перезапустити Nginx
sudo systemctl reload nginx
```

---

## 📞 Контакти підтримки

**Технічна підтримка:**
- Email: support@finance-tracker.com
- Telegram: @finance_tracker_support

**Emergency контакти:**
- DevOps: +380XXXXXXXXX
- Backend Lead: +380XXXXXXXXX

---

## 📚 Додаткові ресурси

- [Production Checklist](production-checklist.md)
- [Security Guide](SECURITY.md)
- [Performance Optimization](PERFORMANCE-OPTIMIZATION.md)
- [API Documentation](api-contracts.md)

---

**Останнє оновлення:** 7 жовтня 2025
**Версія:** 1.0.0
