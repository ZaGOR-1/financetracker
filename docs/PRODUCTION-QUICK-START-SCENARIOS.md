# 🚀 Production Quick Start Scenarios

Різні сценарії deployment залежно від вашого хостингу та потреб.

---

## Сценарій 1: VPS з Ubuntu (DigitalOcean, Linode, Vultr)

**Час:** ~2 години  
**Складність:** ⭐⭐⭐  
**Вартість:** від $5/міс

### Крок 1: Підготовка сервера

```bash
# Підключитися до сервера
ssh root@your-server-ip

# Оновити систему
apt update && apt upgrade -y

# Встановити необхідне ПЗ
apt install -y php8.3-fpm php8.3-cli php8.3-mysql php8.3-redis \
    php8.3-xml php8.3-mbstring php8.3-curl php8.3-zip php8.3-intl \
    nginx mysql-server redis-server supervisor git curl unzip

# Встановити Composer
curl -sS https://getcomposer.org/installer | php
mv composer.phar /usr/local/bin/composer

# Встановити Node.js 20
curl -fsSL https://deb.nodesource.com/setup_20.x | bash -
apt install -y nodejs
```

### Крок 2: Налаштування бази даних

```bash
mysql
```

```sql
CREATE DATABASE finance_tracker_prod CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER 'finance_user'@'localhost' IDENTIFIED BY 'STRONG_PASSWORD_HERE';
GRANT ALL PRIVILEGES ON finance_tracker_prod.* TO 'finance_user'@'localhost';
FLUSH PRIVILEGES;
EXIT;
```

### Крок 3: Клонування проекту

```bash
cd /var/www
git clone https://github.com/your-username/finance-tracker.git
cd finance-tracker

# Налаштування прав
useradd -m -s /bin/bash finance-app
chown -R finance-app:www-data /var/www/finance-tracker
```

### Крок 4: Environment налаштування

```bash
# Як користувач finance-app
su - finance-app
cd /var/www/finance-tracker

# Створити .env
cp .env.production.example .env.production
nano .env.production
```

Заповніть:
- `APP_KEY` (згенеруєте далі)
- `APP_URL=https://your-domain.com`
- `DB_DATABASE=finance_tracker_prod`
- `DB_USERNAME=finance_user`
- `DB_PASSWORD=...`
- `REDIS_PASSWORD=...`
- `MAIL_*` credentials

### Крок 5: Встановлення залежностей

```bash
composer install --no-dev --optimize-autoloader
npm ci
npm run build

# Згенерувати APP_KEY
php artisan key:generate
```

### Крок 6: Міграції

```bash
php artisan migrate --force
```

### Крок 7: Оптимізація

```bash
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

### Крок 8: Nginx конфігурація

```bash
# Як root
exit

nano /etc/nginx/sites-available/finance-tracker
```

Вставити конфігурацію з `docs/PRODUCTION-DEPLOYMENT-GUIDE.md`

```bash
ln -s /etc/nginx/sites-available/finance-tracker /etc/nginx/sites-enabled/
nginx -t
systemctl reload nginx
```

### Крок 9: SSL сертифікат

```bash
apt install certbot python3-certbot-nginx
certbot --nginx -d your-domain.com -d www.your-domain.com
```

### Крок 10: Queue workers

```bash
nano /etc/supervisor/conf.d/finance-tracker-worker.conf
```

Вставити конфігурацію з guide

```bash
supervisorctl reread
supervisorctl update
supervisorctl start finance-tracker-worker:*
```

### ✅ Готово!

Відкрийте `https://your-domain.com` в браузері.

---

## Сценарій 2: Docker Compose (найпростіший)

**Час:** ~30 хвилин  
**Складність:** ⭐  
**Вартість:** залежить від хостингу

### Крок 1: Клонування

```bash
git clone https://github.com/your-username/finance-tracker.git
cd finance-tracker
```

### Крок 2: Environment

```bash
cp .env.production.example .env
nano .env
```

Основні налаштування:
```env
APP_ENV=production
APP_DEBUG=false
DB_HOST=db
DB_DATABASE=finance_tracker
REDIS_HOST=redis
```

### Крок 3: Docker Compose

```bash
# Build та запуск
docker-compose up -d --build

# Згенерувати APP_KEY
docker-compose exec app php artisan key:generate

# Міграції
docker-compose exec app php artisan migrate --force

# Оптимізація
docker-compose exec app php artisan config:cache
docker-compose exec app php artisan route:cache
docker-compose exec app php artisan view:cache
```

### Крок 4: SSL (Let's Encrypt)

Якщо використовуєте Nginx reverse proxy:

```bash
certbot --nginx -d your-domain.com
```

### ✅ Готово!

`http://localhost:8000` або `https://your-domain.com`

---

## Сценарій 3: Laravel Forge (managed)

**Час:** ~15 хвилин  
**Складність:** ⭐  
**Вартість:** $12/міс + сервер

### Крок 1: Створення сервера

1. Увійти в Laravel Forge
2. Створити новий сервер (DigitalOcean/Linode/Vultr)
3. Вибрати PHP 8.3

### Крок 2: Створення сайту

1. "New Site" → ваш домен
2. Project Type: Laravel
3. Git Repository: your-username/finance-tracker

### Крок 3: Environment

1. Відкрити "Environment"
2. Вставити вміст `.env.production.example`
3. Заповнити credentials
4. Зберегти

### Крок 4: Deployment Script

Forge автоматично створить deployment script. Додати:

```bash
cd /home/forge/your-domain.com

git pull origin $FORGE_SITE_BRANCH

$FORGE_COMPOSER install --no-dev --optimize-autoloader

npm ci
npm run build

if [ -f artisan ]; then
    $FORGE_PHP artisan migrate --force
    $FORGE_PHP artisan config:cache
    $FORGE_PHP artisan route:cache
    $FORGE_PHP artisan view:cache
fi
```

### Крок 5: Queue Worker

1. "Queue" → "New Worker"
2. Connection: redis
3. Command: `php artisan queue:work redis --sleep=3 --tries=3`

### Крок 6: Scheduler

1. "Scheduler" → Enable Laravel Scheduler

### Крок 7: SSL

1. "SSL" → "Let's Encrypt"
2. Активувати

### ✅ Готово!

Deploy автоматично при push в git!

---

## Сценарій 4: Heroku (найшвидший старт)

**Час:** ~10 хвилин  
**Складність:** ⭐  
**Вартість:** від $0 (безкоштовно з обмеженнями)

### Крок 1: Підготовка

```bash
# Встановити Heroku CLI
curl https://cli-assets.heroku.com/install.sh | sh

# Login
heroku login
```

### Крок 2: Створення app

```bash
cd finance-tracker
heroku create your-app-name

# Додати buildpacks
heroku buildpacks:add heroku/php
heroku buildpacks:add heroku/nodejs
```

### Крок 3: Addons

```bash
# Database
heroku addons:create heroku-postgresql:mini

# Redis
heroku addons:create heroku-redis:mini

# SendGrid (email)
heroku addons:create sendgrid:starter
```

### Крок 4: Environment

```bash
heroku config:set APP_ENV=production
heroku config:set APP_DEBUG=false
heroku config:set APP_KEY=$(php artisan key:generate --show)
heroku config:set LOG_CHANNEL=errorlog
```

### Крок 5: Procfile

Створити `Procfile`:
```
web: vendor/bin/heroku-php-apache2 public/
worker: php artisan queue:work --sleep=3 --tries=3
```

### Крок 6: Deploy

```bash
git add .
git commit -m "Heroku deployment"
git push heroku main

# Міграції
heroku run php artisan migrate --force

# Scale worker
heroku ps:scale worker=1
```

### ✅ Готово!

`https://your-app-name.herokuapp.com`

---

## Сценарій 5: AWS (Enterprise)

**Час:** ~4 години  
**Складність:** ⭐⭐⭐⭐⭐  
**Вартість:** від $50/міс

### Архітектура:
- EC2 (App servers) + Auto Scaling
- RDS MySQL (Database)
- ElastiCache Redis (Cache)
- S3 (Storage)
- CloudFront (CDN)
- SES (Email)
- SQS (Queues)
- Load Balancer

### Крок 1: RDS Database

1. AWS Console → RDS
2. Create database (MySQL 8.0)
3. Production template
4. Multi-AZ deployment
5. Зберегти credentials

### Крок 2: ElastiCache Redis

1. AWS Console → ElastiCache
2. Create Redis cluster
3. Зберегти endpoint

### Крок 3: S3 Bucket

1. AWS Console → S3
2. Create bucket
3. Enable versioning
4. Block public access

### Крок 4: EC2 Instance

1. Launch Ubuntu 22.04 instance
2. t3.medium або більше
3. Security groups (80, 443, 22)
4. Elastic IP

### Крок 5: Setup на EC2

SSH в instance та виконати сценарій 1 (VPS), але з AWS credentials:

```env
DB_HOST=your-rds-endpoint.rds.amazonaws.com
REDIS_HOST=your-redis.cache.amazonaws.com
FILESYSTEM_DISK=s3
AWS_BUCKET=your-bucket
QUEUE_CONNECTION=sqs
```

### Крок 6: Load Balancer

1. Create Application Load Balancer
2. Target group → EC2 instance
3. Health check: `/health`

### Крок 7: Auto Scaling (опціонально)

1. Create Launch Template
2. Auto Scaling Group
3. Min: 2, Max: 10 instances

### ✅ Готово!

Enterprise-grade setup з high availability!

---

## Сценарій 6: Shared Hosting (бюджетний)

**Час:** ~1 година  
**Складність:** ⭐⭐  
**Вартість:** від $3/міс

### Обмеження:
- ❌ Немає SSH доступу
- ❌ Немає Redis
- ❌ Немає queue workers
- ❌ Обмежені версії PHP

### Крок 1: Підготовка локально

```bash
# Build assets
npm run build

# Створити archive
zip -r finance-tracker.zip . -x "node_modules/*" -x ".git/*"
```

### Крок 2: Upload

1. Завантажити через FTP/File Manager
2. Розпакувати в `public_html` або `www`

### Крок 3: .env налаштування

```env
APP_ENV=production
APP_DEBUG=false
CACHE_DRIVER=file
SESSION_DRIVER=file
QUEUE_CONNECTION=database
FILESYSTEM_DISK=local
```

### Крок 4: Database

1. cPanel → MySQL Databases
2. Створити БД та користувача
3. Імпортувати SQL через phpMyAdmin

### Крок 5: .htaccess

Переконайтеся що `public/.htaccess` правильний.

### ⚠️ Обмеження:

- Повільніше (file cache замість Redis)
- Немає background jobs
- Обмежений масштаб

---

## 🎯 Який сценарій вибрати?

### Для навчання/тестування:
→ **Сценарій 2 (Docker)**

### Для малого проекту:
→ **Сценарій 4 (Heroku)** або **Сценарій 6 (Shared Hosting)**

### Для середнього проекту:
→ **Сценарій 1 (VPS)** або **Сценарій 3 (Forge)**

### Для великого/enterprise:
→ **Сценарій 5 (AWS)** або **Laravel Vapor**

### Якщо бюджет обмежений:
→ **Сценарій 6 (Shared Hosting)** - $3/міс

### Якщо потрібна швидкість deployment:
→ **Сценарій 4 (Heroku)** - 10 хвилин

### Якщо потрібен повний контроль:
→ **Сценарій 1 (VPS)** - ви керуєте всім

### Якщо НЕ хочете займатися DevOps:
→ **Сценарій 3 (Forge)** - managed Laravel hosting

---

## 📊 Порівняння

| Критерій | VPS | Docker | Forge | Heroku | AWS | Shared |
|----------|-----|--------|-------|--------|-----|--------|
| **Складність** | ⭐⭐⭐ | ⭐ | ⭐ | ⭐ | ⭐⭐⭐⭐⭐ | ⭐⭐ |
| **Вартість/міс** | $5+ | $5+ | $12+$5 | $0-25 | $50+ | $3+ |
| **Час setup** | 2h | 30m | 15m | 10m | 4h+ | 1h |
| **Контроль** | 100% | 100% | 70% | 50% | 100% | 30% |
| **Масштабування** | ⭐⭐⭐ | ⭐⭐⭐⭐ | ⭐⭐⭐ | ⭐⭐⭐⭐ | ⭐⭐⭐⭐⭐ | ⭐ |
| **Performance** | ⭐⭐⭐⭐ | ⭐⭐⭐⭐ | ⭐⭐⭐⭐ | ⭐⭐⭐ | ⭐⭐⭐⭐⭐ | ⭐⭐ |
| **DevOps needed** | Так | Так | Ні | Ні | Так | Ні |

---

## 🚀 Наступні кроки

Після вибору сценарію:

1. ✅ Прочитайте повний guide: [PRODUCTION-DEPLOYMENT-GUIDE.md](PRODUCTION-DEPLOYMENT-GUIDE.md)
2. ✅ Використайте checklist: [PRODUCTION-CHECKLIST-QUICK.md](PRODUCTION-CHECKLIST-QUICK.md)
3. ✅ Налаштуйте моніторинг (Sentry, UptimeRobot)
4. ✅ Налаштуйте backup
5. ✅ Протестуйте всі функції
6. ✅ Налаштуйте alerts

---

**Потрібна допомога?** Дивіться [PRODUCTION-README.md](../PRODUCTION-README.md)

**Останнє оновлення:** 7 жовтня 2025
