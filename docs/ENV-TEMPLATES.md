# Environment Variables Templates

Шаблони налаштувань для різних hosting провайдерів та сценаріїв.

---

## 🌐 VPS/Dedicated Server (DigitalOcean, Linode, Vultr)

**Оптимально для:** Повний контроль, середні та великі проекти

```bash
# Application
APP_NAME="Finance Tracker"
APP_ENV=production
APP_KEY=base64:XXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXX
APP_DEBUG=false
APP_URL=https://finance.example.com
APP_TIMEZONE=Europe/Kiev

# Database (MySQL на тому ж сервері)
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=finance_tracker_prod
DB_USERNAME=finance_user
DB_PASSWORD=STRONG_PASSWORD_HERE

# Cache & Session (Redis на тому ж сервері)
CACHE_DRIVER=redis
SESSION_DRIVER=redis
QUEUE_CONNECTION=redis

REDIS_HOST=127.0.0.1
REDIS_PASSWORD=REDIS_PASSWORD_HERE
REDIS_PORT=6379

# Mail (Gmail SMTP)
MAIL_MAILER=smtp
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=your-email@gmail.com
MAIL_PASSWORD=your-app-password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=noreply@example.com

# Storage (Local disk)
FILESYSTEM_DISK=local

# Monitoring
SENTRY_LARAVEL_DSN=https://xxxxx@xxxxx.ingest.sentry.io/xxxxx
```

---

## 🐳 Docker Compose

**Оптимально для:** Containerized deployments, microservices

```bash
# Application
APP_NAME="Finance Tracker"
APP_ENV=production
APP_KEY=base64:XXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXX
APP_DEBUG=false
APP_URL=https://finance.example.com

# Database (MySQL container)
DB_CONNECTION=mysql
DB_HOST=db                    # Docker service name
DB_PORT=3306
DB_DATABASE=finance_tracker
DB_USERNAME=finance_user
DB_PASSWORD=STRONG_PASSWORD_HERE

# Cache & Session (Redis container)
CACHE_DRIVER=redis
SESSION_DRIVER=redis
QUEUE_CONNECTION=redis

REDIS_HOST=redis              # Docker service name
REDIS_PASSWORD=REDIS_PASSWORD_HERE
REDIS_PORT=6379

# Mail
MAIL_MAILER=smtp
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=your-email@gmail.com
MAIL_PASSWORD=your-app-password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=noreply@example.com

# Storage (S3 for production)
FILESYSTEM_DISK=s3
AWS_ACCESS_KEY_ID=AKIAXXXXXXXXXXXXXXXX
AWS_SECRET_ACCESS_KEY=XXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXX
AWS_DEFAULT_REGION=eu-central-1
AWS_BUCKET=finance-tracker-prod

# Monitoring
SENTRY_LARAVEL_DSN=https://xxxxx@xxxxx.ingest.sentry.io/xxxxx
PROMETHEUS_ENABLED=true
```

---

## ☁️ AWS (Elastic Beanstalk / ECS)

**Оптимально для:** Enterprise, auto-scaling, high availability

```bash
# Application
APP_NAME="Finance Tracker"
APP_ENV=production
APP_KEY=base64:XXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXX
APP_DEBUG=false
APP_URL=https://finance.example.com

# Database (RDS)
DB_CONNECTION=mysql
DB_HOST=finance-db.xxxxx.eu-central-1.rds.amazonaws.com
DB_PORT=3306
DB_DATABASE=finance_tracker
DB_USERNAME=admin
DB_PASSWORD=STRONG_PASSWORD_HERE

# Cache (ElastiCache Redis)
CACHE_DRIVER=redis
SESSION_DRIVER=redis
QUEUE_CONNECTION=sqs

REDIS_HOST=finance-redis.xxxxx.cache.amazonaws.com
REDIS_PASSWORD=
REDIS_PORT=6379

# Queue (SQS)
SQS_PREFIX=https://sqs.eu-central-1.amazonaws.com/123456789
SQS_QUEUE=finance-tracker-queue

# Mail (SES)
MAIL_MAILER=ses
AWS_SES_KEY=AKIAXXXXXXXXXXXXXXXX
AWS_SES_SECRET=XXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXX
AWS_SES_REGION=eu-west-1
MAIL_FROM_ADDRESS=noreply@example.com

# Storage (S3)
FILESYSTEM_DISK=s3
AWS_ACCESS_KEY_ID=AKIAXXXXXXXXXXXXXXXX
AWS_SECRET_ACCESS_KEY=XXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXX
AWS_DEFAULT_REGION=eu-central-1
AWS_BUCKET=finance-tracker-prod
AWS_URL=https://finance-tracker-prod.s3.eu-central-1.amazonaws.com

# Monitoring
SENTRY_LARAVEL_DSN=https://xxxxx@xxxxx.ingest.sentry.io/xxxxx
AWS_CLOUDWATCH_ENABLED=true
```

---

## 🔷 Azure

**Оптимально для:** Microsoft ecosystem, enterprise

```bash
# Application
APP_NAME="Finance Tracker"
APP_ENV=production
APP_KEY=base64:XXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXX
APP_DEBUG=false
APP_URL=https://finance.azurewebsites.net

# Database (Azure Database for MySQL)
DB_CONNECTION=mysql
DB_HOST=finance-db.mysql.database.azure.com
DB_PORT=3306
DB_DATABASE=finance_tracker
DB_USERNAME=admin@finance-db
DB_PASSWORD=STRONG_PASSWORD_HERE

# Cache (Azure Redis Cache)
CACHE_DRIVER=redis
SESSION_DRIVER=redis

REDIS_HOST=finance-redis.redis.cache.windows.net
REDIS_PASSWORD=REDIS_PASSWORD_HERE
REDIS_PORT=6380
REDIS_CLIENT=phpredis

# Queue (Azure Service Bus)
QUEUE_CONNECTION=beanstalkd  # або custom Azure Queue driver

# Mail (SendGrid)
MAIL_MAILER=smtp
MAIL_HOST=smtp.sendgrid.net
MAIL_PORT=587
MAIL_USERNAME=apikey
MAIL_PASSWORD=SG.xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx
MAIL_ENCRYPTION=tls

# Storage (Azure Blob Storage)
FILESYSTEM_DISK=azure
AZURE_STORAGE_NAME=financetracker
AZURE_STORAGE_KEY=XXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXX
AZURE_STORAGE_CONTAINER=uploads

# Monitoring
SENTRY_LARAVEL_DSN=https://xxxxx@xxxxx.ingest.sentry.io/xxxxx
APPLICATIONINSIGHTS_CONNECTION_STRING=InstrumentationKey=xxxxx
```

---

## 🟢 Heroku

**Оптимально для:** Швидкий deployment, малі та середні проекти

```bash
# Application
APP_NAME="Finance Tracker"
APP_ENV=production
APP_KEY=base64:XXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXX
APP_DEBUG=false
APP_URL=https://finance-tracker.herokuapp.com

# Database (Heroku Postgres addon - автоматично)
# DATABASE_URL буде автоматично встановлено Heroku

# Cache (Heroku Redis addon)
# REDIS_URL буде автоматично встановлено Heroku

CACHE_DRIVER=redis
SESSION_DRIVER=redis
QUEUE_CONNECTION=redis

# Mail (SendGrid addon)
MAIL_MAILER=smtp
MAIL_HOST=smtp.sendgrid.net
MAIL_PORT=587
MAIL_USERNAME=apikey
MAIL_PASSWORD=SG.xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx
MAIL_ENCRYPTION=tls

# Storage (AWS S3 або Cloudinary)
FILESYSTEM_DISK=s3
AWS_ACCESS_KEY_ID=AKIAXXXXXXXXXXXXXXXX
AWS_SECRET_ACCESS_KEY=XXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXX
AWS_DEFAULT_REGION=eu-central-1
AWS_BUCKET=finance-tracker-prod

# Monitoring (Heroku built-in + Sentry)
SENTRY_LARAVEL_DSN=https://xxxxx@xxxxx.ingest.sentry.io/xxxxx

# Heroku specific
LOG_CHANNEL=errorlog  # Використовувати stderr для Heroku logs
```

**Heroku Procfile:**
```
web: vendor/bin/heroku-php-apache2 public/
worker: php artisan queue:work --sleep=3 --tries=3
```

---

## 🔵 Laravel Forge

**Оптимально для:** Managed Laravel hosting

```bash
# Application
APP_NAME="Finance Tracker"
APP_ENV=production
APP_KEY=base64:XXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXX
APP_DEBUG=false
APP_URL=https://finance.example.com

# Database (Forge managed MySQL)
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=forge
DB_USERNAME=forge
DB_PASSWORD=STRONG_PASSWORD_HERE

# Cache (Forge managed Redis)
CACHE_DRIVER=redis
SESSION_DRIVER=redis
QUEUE_CONNECTION=redis

REDIS_HOST=127.0.0.1
REDIS_PASSWORD=null
REDIS_PORT=6379

# Mail
MAIL_MAILER=smtp
MAIL_HOST=smtp.mailtrap.io
MAIL_PORT=2525
MAIL_USERNAME=your_username
MAIL_PASSWORD=your_password
MAIL_ENCRYPTION=tls

# Storage
FILESYSTEM_DISK=s3
AWS_ACCESS_KEY_ID=AKIAXXXXXXXXXXXXXXXX
AWS_SECRET_ACCESS_KEY=XXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXX
AWS_DEFAULT_REGION=eu-central-1
AWS_BUCKET=finance-tracker-prod

# Monitoring
SENTRY_LARAVEL_DSN=https://xxxxx@xxxxx.ingest.sentry.io/xxxxx
```

---

## 🟣 Laravel Vapor

**Оптимально для:** Serverless, auto-scaling AWS Lambda

```bash
# Application
APP_NAME="Finance Tracker"
APP_ENV=production
APP_KEY=base64:XXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXX
APP_DEBUG=false
APP_URL=https://finance.example.com

# Database (Aurora Serverless)
DB_CONNECTION=mysql
DB_HOST=finance-db.cluster-xxxxx.eu-central-1.rds.amazonaws.com
DB_PORT=3306
DB_DATABASE=finance_tracker
DB_USERNAME=admin
DB_PASSWORD=STRONG_PASSWORD_HERE

# Cache (DynamoDB via Vapor)
CACHE_DRIVER=dynamodb
SESSION_DRIVER=dynamodb
QUEUE_CONNECTION=sqs

# DynamoDB
DYNAMODB_CACHE_TABLE=finance-tracker-cache

# Queue (SQS via Vapor)
SQS_PREFIX=https://sqs.eu-central-1.amazonaws.com/123456789
SQS_QUEUE=finance-tracker-queue

# Mail (SES)
MAIL_MAILER=ses
AWS_SES_KEY=AKIAXXXXXXXXXXXXXXXX
AWS_SES_SECRET=XXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXX
AWS_SES_REGION=eu-west-1

# Storage (S3)
FILESYSTEM_DISK=s3
AWS_ACCESS_KEY_ID=AKIAXXXXXXXXXXXXXXXX
AWS_SECRET_ACCESS_KEY=XXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXX
AWS_DEFAULT_REGION=eu-central-1
AWS_BUCKET=finance-tracker-prod

# Vapor specific
ASSET_URL=https://xxxxx.cloudfront.net
MIX_ASSET_URL=https://xxxxx.cloudfront.net

# Monitoring
SENTRY_LARAVEL_DSN=https://xxxxx@xxxxx.ingest.sentry.io/xxxxx
```

---

## 🔶 Shared Hosting (обмежені можливості)

**Оптимально для:** Бюджетні проекти, малий трафік

```bash
# Application
APP_NAME="Finance Tracker"
APP_ENV=production
APP_KEY=base64:XXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXX
APP_DEBUG=false
APP_URL=https://finance.example.com

# Database (MySQL на хостингу)
DB_CONNECTION=mysql
DB_HOST=localhost
DB_PORT=3306
DB_DATABASE=username_financedb
DB_USERNAME=username_dbuser
DB_PASSWORD=PASSWORD_HERE

# Cache (file-based, Redis може бути недоступний)
CACHE_DRIVER=file
SESSION_DRIVER=file
QUEUE_CONNECTION=database

# Mail (SMTP хостингу або Gmail)
MAIL_MAILER=smtp
MAIL_HOST=smtp.example.com
MAIL_PORT=587
MAIL_USERNAME=your-email@example.com
MAIL_PASSWORD=your-password
MAIL_ENCRYPTION=tls

# Storage (local)
FILESYSTEM_DISK=local

# Monitoring (обмежений)
SENTRY_LARAVEL_DSN=https://xxxxx@xxxxx.ingest.sentry.io/xxxxx

# Shared hosting specific
LOG_CHANNEL=daily
LOG_LEVEL=error
DEBUGBAR_ENABLED=false
TELESCOPE_ENABLED=false
```

---

## 🧪 Staging Environment

**Для тестування перед production:**

```bash
# Application
APP_NAME="Finance Tracker (Staging)"
APP_ENV=staging
APP_KEY=base64:XXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXX
APP_DEBUG=true  # Можна увімкнути для debugging
APP_URL=https://staging.finance.example.com

# Database (окрема staging БД)
DB_CONNECTION=mysql
DB_HOST=staging-db.example.com
DB_PORT=3306
DB_DATABASE=finance_tracker_staging
DB_USERNAME=staging_user
DB_PASSWORD=PASSWORD_HERE

# Cache
CACHE_DRIVER=redis
SESSION_DRIVER=redis
QUEUE_CONNECTION=redis

REDIS_HOST=staging-redis.example.com
REDIS_PASSWORD=PASSWORD_HERE
REDIS_PORT=6379

# Mail (Mailtrap для тестування)
MAIL_MAILER=smtp
MAIL_HOST=smtp.mailtrap.io
MAIL_PORT=2525
MAIL_USERNAME=your_mailtrap_username
MAIL_PASSWORD=your_mailtrap_password
MAIL_ENCRYPTION=tls

# Storage
FILESYSTEM_DISK=s3
AWS_BUCKET=finance-tracker-staging

# Monitoring
SENTRY_LARAVEL_DSN=https://xxxxx@xxxxx.ingest.sentry.io/xxxxx
SENTRY_ENVIRONMENT=staging

# Debugging tools (можна увімкнути на staging)
DEBUGBAR_ENABLED=true
TELESCOPE_ENABLED=true
```

---

## 📝 Notes

### Вибір Mail Provider:

**Gmail (безкоштовно, обмеження):**
- Ліміт: 500 emails/день
- Потрібен App Password
- Підходить для малих проектів

**SendGrid (безкоштовно 100/день):**
- Professional email delivery
- Analytics
- Гарна deliverability

**Amazon SES (дуже дешево):**
- $0.10 за 1000 emails
- Потрібна верифікація
- Інтеграція з AWS

**Mailgun (безкоштовно 5000/місяць):**
- Good for EU
- Просте API
- Хороша документація

### Вибір Storage:

**Local Disk:**
- Безкоштовно
- Обмеження масштабування
- Backup складніший

**AWS S3:**
- Масштабується
- Дешево ($0.023/GB)
- CDN інтеграція

**DigitalOcean Spaces:**
- S3-compatible
- $5/250GB
- Простіше ніж S3

**Cloudinary:**
- Оптимізація зображень
- Безкоштовно до 25GB
- Автоматичні transformations

---

**Рекомендація:** Для production використовуйте окремі сервіси для бази даних, кешу та storage для кращої надійності та масштабування.
