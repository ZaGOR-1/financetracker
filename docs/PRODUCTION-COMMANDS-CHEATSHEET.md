# ⚡ Production Commands Cheat Sheet

Швидка довідка команд для production середовища Finance Tracker.

---

## 🚀 Deployment

```bash
# Автоматичний deployment
./scripts/deploy-production.sh

# Deployment з опціями
./scripts/deploy-production.sh --skip-backup
./scripts/deploy-production.sh --skip-tests
./scripts/deploy-production.sh --force

# Ручний deployment
php artisan down
git pull origin production
composer install --no-dev --optimize-autoloader
npm ci && npm run build
php artisan migrate --force
php artisan config:cache && php artisan route:cache && php artisan view:cache
sudo systemctl reload php8.3-fpm
sudo supervisorctl restart finance-tracker-worker:*
php artisan up
```

---

## ⏮️ Rollback

```bash
# Автоматичний rollback
./scripts/rollback-production.sh
./scripts/rollback-production.sh 20250107_140530
./scripts/rollback-production.sh latest

# Ручний rollback (до попереднього коміту)
php artisan down
git reset --hard HEAD~1
php artisan config:cache
php artisan up
```

---

## 🧹 Cache Management

```bash
# Очистити всі кеші
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear
php artisan optimize:clear

# Відновити кеші (production optimization)
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan optimize

# Composer autoload optimization
composer dump-autoload --optimize
```

---

## 🔧 Maintenance Mode

```bash
# Увімкнути maintenance mode
php artisan down
php artisan down --message="Upgrading system. Back soon!"
php artisan down --retry=60

# Вимкнути maintenance mode
php artisan up

# Перевірити статус
curl -I http://localhost
```

---

## 💾 Database Operations

```bash
# Міграції
php artisan migrate --force
php artisan migrate:status
php artisan migrate:rollback --step=1
php artisan migrate:fresh --seed --force  # ⚠️ ВИДАЛЯЄ ВСІ ДАНІ!

# Сідери
php artisan db:seed --force
php artisan db:seed --class=UsersTableSeeder --force

# Backup database
mysqldump -u user -p database | gzip > backup_$(date +%Y%m%d_%H%M%S).sql.gz

# Restore database
gunzip -c backup.sql.gz | mysql -u user -p database

# Database optimization
mysqlcheck -u user -p --optimize --all-databases
```

---

## 🔄 Queue Management

```bash
# Supervisor commands
sudo supervisorctl status
sudo supervisorctl start finance-tracker-worker:*
sudo supervisorctl stop finance-tracker-worker:*
sudo supervisorctl restart finance-tracker-worker:*
sudo supervisorctl reread
sudo supervisorctl update

# Manual queue work (for testing)
php artisan queue:work
php artisan queue:work redis --sleep=3 --tries=3
php artisan queue:listen

# Queue monitoring
php artisan queue:failed
php artisan queue:retry all
php artisan queue:flush
```

---

## 📊 Logs & Monitoring

```bash
# Laravel logs
tail -f storage/logs/laravel.log
tail -100 storage/logs/laravel.log
grep ERROR storage/logs/laravel.log

# Nginx logs
tail -f /var/log/nginx/finance-tracker-access.log
tail -f /var/log/nginx/finance-tracker-error.log

# PHP-FPM logs
tail -f /var/log/php8.3-fpm.log

# Queue worker logs
tail -f storage/logs/worker.log

# System logs
tail -f /var/log/syslog

# Clear old logs
php artisan log:clear
find storage/logs -name "*.log" -mtime +14 -delete
```

---

## 🏥 Health Checks

```bash
# Basic health check
curl http://localhost/health
curl -I http://localhost/health

# Detailed health check
curl http://localhost/health/detailed | jq

# Check all services
systemctl status nginx
systemctl status php8.3-fpm
systemctl status mysql
systemctl status redis
supervisorctl status

# Check ports
netstat -tulpn | grep LISTEN
ss -tulpn
```

---

## 🔐 Security

```bash
# Generate APP_KEY
php artisan key:generate
php artisan key:generate --show

# Check file permissions
ls -la storage/
ls -la bootstrap/cache/

# Fix permissions
sudo chown -R www-data:www-data storage bootstrap/cache
sudo chmod -R 775 storage bootstrap/cache

# Security audit
composer audit
npm audit
npm audit --audit-level=high

# Check for vulnerabilities
php artisan security:check  # якщо встановлено enlightn/security-checker
```

---

## 🔍 Debugging

```bash
# Check configuration
php artisan config:show
php artisan config:show database
php artisan about

# Test database connection
php artisan db:show
php artisan db:table users

# Test Redis connection
redis-cli -h 127.0.0.1 -p 6379 -a password
redis-cli PING

# Test mail configuration
php artisan tinker
>>> Mail::raw('Test', function($msg) { $msg->to('test@example.com')->subject('Test'); });

# Test queue
php artisan queue:test

# Route list
php artisan route:list
php artisan route:list --name=api
```

---

## 📦 Backup & Restore

```bash
# Full backup (database + files)
DATE=$(date +%Y%m%d_%H%M%S)
mysqldump -u user -p database | gzip > /backups/db_$DATE.sql.gz
tar -czf /backups/files_$DATE.tar.gz /var/www/finance-tracker

# Database only backup
mysqldump -u user -p database > backup.sql
mysqldump -u user -p database | gzip > backup.sql.gz

# Restore database
mysql -u user -p database < backup.sql
gunzip -c backup.sql.gz | mysql -u user -p database

# Restore files
tar -xzf backup.tar.gz -C /

# Upload to S3
aws s3 cp backup.sql.gz s3://bucket/backups/
aws s3 sync /backups/ s3://bucket/backups/

# Download from S3
aws s3 cp s3://bucket/backups/db_latest.sql.gz .
```

---

## 🔄 Services Management

```bash
# Nginx
sudo systemctl start nginx
sudo systemctl stop nginx
sudo systemctl restart nginx
sudo systemctl reload nginx  # Без переривання з'єднань
sudo systemctl status nginx
sudo nginx -t  # Перевірити конфігурацію

# PHP-FPM
sudo systemctl start php8.3-fpm
sudo systemctl stop php8.3-fpm
sudo systemctl restart php8.3-fpm
sudo systemctl reload php8.3-fpm
sudo systemctl status php8.3-fpm

# MySQL
sudo systemctl start mysql
sudo systemctl stop mysql
sudo systemctl restart mysql
sudo systemctl status mysql

# Redis
sudo systemctl start redis-server
sudo systemctl stop redis-server
sudo systemctl restart redis-server
sudo systemctl status redis-server
```

---

## 🌐 SSL Certificates

```bash
# Get new certificate (Let's Encrypt)
sudo certbot --nginx -d finance.example.com -d www.finance.example.com

# Renew certificates
sudo certbot renew
sudo certbot renew --dry-run  # Test renewal

# List certificates
sudo certbot certificates

# Check certificate expiry
echo | openssl s_client -servername finance.example.com -connect finance.example.com:443 2>/dev/null | openssl x509 -noout -dates
```

---

## 📈 Performance Monitoring

```bash
# Server resources
top
htop
free -h
df -h

# MySQL performance
mysql -u root -p -e "SHOW PROCESSLIST;"
mysql -u root -p -e "SHOW STATUS;"

# PHP-FPM status
curl http://localhost/php-fpm-status

# Redis info
redis-cli INFO
redis-cli INFO stats
redis-cli DBSIZE

# Check slow queries
tail -f /var/log/mysql/slow-query.log

# Laravel performance
php artisan optimize
php artisan model:show User
```

---

## 🔧 Git Operations

```bash
# Check status
git status
git log --oneline -10

# Pull latest changes
git fetch origin
git pull origin production

# View changes
git diff
git show HEAD

# Rollback to specific commit
git reset --hard COMMIT_HASH
git reset --hard HEAD~1  # Rollback 1 commit

# View commit history
git log --graph --oneline --all
```

---

## 🧪 Testing

```bash
# Run all tests
php artisan test

# Run specific test
php artisan test --filter=TransactionTest

# With coverage
php artisan test --coverage

# PHPStan analysis
vendor/bin/phpstan analyse
vendor/bin/phpstan analyse --memory-limit=2G

# Code style
./vendor/bin/pint
./vendor/bin/php-cs-fixer fix
```

---

## 📊 Database Queries

```bash
# Laravel Tinker
php artisan tinker

# In Tinker:
>>> User::count()
>>> Transaction::latest()->take(10)->get()
>>> DB::table('users')->count()
>>> Cache::get('key')
>>> Redis::ping()

# Direct MySQL
mysql -u user -p database

# In MySQL:
mysql> SELECT COUNT(*) FROM users;
mysql> SHOW TABLES;
mysql> DESCRIBE transactions;
mysql> SHOW INDEX FROM transactions;
```

---

## 🔄 Cron Jobs

```bash
# Edit crontab
crontab -e

# Laravel scheduler (додати в cron)
* * * * * cd /var/www/finance-tracker && php artisan schedule:run >> /dev/null 2>&1

# View cron logs
grep CRON /var/log/syslog

# Test scheduler
php artisan schedule:list
php artisan schedule:run
```

---

## 🌍 Environment

```bash
# Check environment
php artisan env
php artisan about

# Switch environment (local/staging/production)
# Edit .env or
ln -sf .env.production .env

# Reload environment
php artisan config:clear
```

---

## 📞 Quick Diagnostics

```bash
# One-liner system check
echo "=== System Status ===" && \
systemctl is-active nginx php8.3-fpm mysql redis && \
echo "=== Disk Space ===" && df -h / && \
echo "=== Memory ===" && free -h && \
echo "=== Laravel Health ===" && \
curl -s http://localhost/health && \
echo ""

# Check if all services are running
for service in nginx php8.3-fpm mysql redis-server; do
    systemctl is-active $service && echo "$service: OK" || echo "$service: FAILED"
done

# Application health
php artisan about | grep -E "(Environment|Debug|URL|Database|Cache)"
```

---

## 🚨 Emergency Commands

```bash
# Quick restart all services
sudo systemctl restart nginx php8.3-fpm mysql redis-server
sudo supervisorctl restart finance-tracker-worker:*

# Clear everything and restart
php artisan down
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear
sudo systemctl restart php8.3-fpm
php artisan up

# Emergency maintenance mode
php artisan down --message="Emergency maintenance" --retry=3600

# Kill stuck processes
pkill -f "artisan queue:work"
pkill -f "php-fpm"
```

---

## 📋 Daily Maintenance

```bash
# Morning routine
php artisan queue:restart
supervisorctl status
tail -100 storage/logs/laravel.log | grep ERROR
df -h
free -h

# Weekly tasks
composer audit
npm audit
php artisan backup:run  # якщо встановлено spatie/laravel-backup
certbot renew --dry-run
```

---

## 💡 Tips

**Aliases (додати в ~/.bashrc):**
```bash
alias art='php artisan'
alias pf='php artisan'
alias tinker='php artisan tinker'
alias logs='tail -f storage/logs/laravel.log'
alias nginx-reload='sudo systemctl reload nginx'
alias php-reload='sudo systemctl reload php8.3-fpm'
```

**Useful environment variables:**
```bash
export APP_DIR=/var/www/finance-tracker
export BACKUP_DIR=/var/backups/finance-tracker
```

---

**Останнє оновлення:** 7 жовтня 2025  
**Версія:** 1.0.0  
**Проект:** Finance Tracker
