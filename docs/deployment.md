# Deployment Guide - Finance Tracker

This guide covers deploying Finance Tracker to production using Docker.

## Table of Contents

- [Prerequisites](#prerequisites)
- [Server Setup](#server-setup)
- [Initial Deployment](#initial-deployment)
- [CI/CD with GitHub Actions](#cicd-with-github-actions)
- [Manual Deployment](#manual-deployment)
- [Rollback Procedure](#rollback-procedure)
- [Monitoring](#monitoring)
- [Troubleshooting](#troubleshooting)

## Prerequisites

### Server Requirements

- Ubuntu 22.04 LTS or similar Linux distribution
- Minimum 2 CPU cores, 4GB RAM, 40GB SSD
- Docker 24+ and Docker Compose 2+
- Git 2.3+
- Domain name with DNS configured
- SSL certificate (Let's Encrypt recommended)

### Local Requirements

- Docker Desktop (for testing)
- SSH access to production server
- GitHub account with repository access

## Server Setup

### 1. Initial Server Configuration

```bash
# Update system
sudo apt update && sudo apt upgrade -y

# Install required packages
sudo apt install -y curl git ufw fail2ban

# Configure firewall
sudo ufw default deny incoming
sudo ufw default allow outgoing
sudo ufw allow ssh
sudo ufw allow 80/tcp
sudo ufw allow 443/tcp
sudo ufw enable

# Install Docker
curl -fsSL https://get.docker.com -o get-docker.sh
sudo sh get-docker.sh
sudo usermod -aG docker $USER

# Install Docker Compose
sudo curl -L "https://github.com/docker/compose/releases/latest/download/docker-compose-$(uname -s)-$(uname -m)" -o /usr/local/bin/docker-compose
sudo chmod +x /usr/local/bin/docker-compose
```

### 2. Create Application User

```bash
# Create deploy user
sudo useradd -m -s /bin/bash deploy
sudo usermod -aG docker deploy
sudo mkdir -p /home/deploy/.ssh
sudo cp ~/.ssh/authorized_keys /home/deploy/.ssh/
sudo chown -R deploy:deploy /home/deploy/.ssh
sudo chmod 700 /home/deploy/.ssh
sudo chmod 600 /home/deploy/.ssh/authorized_keys
```

### 3. Setup Application Directory

```bash
# Create directories
sudo mkdir -p /var/www/finance-tracker
sudo mkdir -p /var/backups/finance-tracker
sudo chown -R deploy:deploy /var/www/finance-tracker
sudo chown -R deploy:deploy /var/backups/finance-tracker

# Create logs directory
sudo mkdir -p /var/log/finance-tracker
sudo chown -R deploy:deploy /var/log/finance-tracker
```

### 4. Configure SSL (Let's Encrypt)

```bash
# Install Certbot
sudo apt install -y certbot

# Generate certificate
sudo certbot certonly --standalone -d finance-tracker.com -d www.finance-tracker.com

# Setup auto-renewal
sudo systemctl enable certbot.timer
```

## Initial Deployment

### 1. Clone Repository

```bash
cd /var/www/finance-tracker
git clone https://github.com/your-username/finance-tracker.git .
```

### 2. Configure Environment

```bash
# Copy production environment file
cp .env.production .env

# Edit configuration
nano .env

# Generate application key
docker-compose run --rm app php artisan key:generate
```

Required `.env` settings:

```env
APP_NAME="Finance Tracker"
APP_ENV=production
APP_DEBUG=false
APP_URL=https://finance-tracker.com

DB_CONNECTION=mysql
DB_HOST=db
DB_DATABASE=finance_tracker
DB_USERNAME=finance_user
DB_PASSWORD=<strong-password>

REDIS_HOST=redis

MAIL_MAILER=smtp
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=your-email@gmail.com
MAIL_PASSWORD=<app-password>
MAIL_ENCRYPTION=tls
```

### 3. Build and Start Services

```bash
# Build Docker images
docker-compose build

# Start services
docker-compose up -d

# Run migrations
docker-compose exec app php artisan migrate --force

# Seed initial data (categories)
docker-compose exec app php artisan db:seed --class=CategorySeeder

# Clear and cache configurations
docker-compose exec app php artisan config:cache
docker-compose exec app php artisan route:cache
docker-compose exec app php artisan view:cache
```

### 4. Verify Deployment

```bash
# Check container status
docker-compose ps

# View logs
docker-compose logs -f app

# Test health endpoint
curl http://localhost/health

# Test detailed health check
curl http://localhost/health/detailed
```

## CI/CD with GitHub Actions

### 1. Configure Repository Secrets

Go to GitHub repository → Settings → Secrets and Variables → Actions

Add the following secrets:

- `DOCKER_USERNAME` - Docker Hub username
- `DOCKER_PASSWORD` - Docker Hub password/token
- `STAGING_HOST` - Staging server IP
- `STAGING_USER` - SSH username for staging
- `STAGING_SSH_KEY` - Private SSH key for staging
- `PRODUCTION_HOST` - Production server IP
- `PRODUCTION_USER` - SSH username for production
- `PRODUCTION_SSH_KEY` - Private SSH key for production
- `SLACK_WEBHOOK_URL` - Slack webhook for notifications (optional)

### 2. Workflow Triggers

**CI Workflow** (`.github/workflows/ci.yml`):
- Runs on: Push to `main` or `develop`, Pull Requests
- Actions: Tests, PHPStan, Security audit, Linting

**CD Workflow** (`.github/workflows/deploy.yml`):
- Staging: Automatic on push to `develop`
- Production: Automatic on git tags `v*` (e.g., `v1.0.0`)

### 3. Create Release

```bash
# Tag new version
git tag -a v1.0.0 -m "Release version 1.0.0"
git push origin v1.0.0

# GitHub Actions will automatically:
# 1. Build Docker image
# 2. Push to Docker Hub
# 3. Deploy to production
# 4. Run migrations
# 5. Clear caches
# 6. Send Slack notification
```

## Manual Deployment

If you need to deploy manually without GitHub Actions:

### 1. Using Deployment Script

```bash
cd /var/www/finance-tracker

# Backup current state
./scripts/backup.sh

# Deploy
./scripts/deploy.sh production

# Monitor logs
docker-compose logs -f
```

### 2. Step-by-Step Manual Deployment

```bash
# 1. Backup database
docker-compose exec db mysqldump -u root -p$DB_PASSWORD finance_tracker > backup_$(date +%Y%m%d_%H%M%S).sql

# 2. Pull latest code
git pull origin main

# 3. Pull latest images
docker-compose pull

# 4. Rebuild images
docker-compose build --no-cache

# 5. Zero-downtime deployment
docker-compose up -d --no-deps --scale app=2 app
sleep 10
docker-compose up -d --no-deps --remove-orphans app

# 6. Run migrations
docker-compose exec app php artisan migrate --force

# 7. Clear caches
docker-compose exec app php artisan config:cache
docker-compose exec app php artisan route:cache
docker-compose exec app php artisan view:cache
docker-compose exec app php artisan optimize

# 8. Restart queue workers
docker-compose restart queue

# 9. Health check
curl http://localhost/health
```

## Rollback Procedure

### Using Rollback Script

```bash
cd /var/www/finance-tracker

# List available backups
ls -lh /var/backups/finance-tracker/

# Rollback to specific backup
./scripts/rollback.sh db_backup_20251006_120000.sql
```

### Manual Rollback

```bash
# 1. Enable maintenance mode
docker-compose exec app php artisan down --retry=60

# 2. Rollback code
git log --oneline -10  # Find previous commit
git reset --hard <commit-hash>

# 3. Rebuild containers
docker-compose build
docker-compose up -d

# 4. Restore database
docker-compose exec -T db mysql -u root -p$DB_PASSWORD finance_tracker < backup_YYYYMMDD_HHMMSS.sql

# 5. Clear caches
docker-compose exec app php artisan cache:clear
docker-compose exec app php artisan config:clear

# 6. Disable maintenance mode
docker-compose exec app php artisan up

# 7. Verify
curl http://localhost/health
```

## Monitoring

### 1. Check Application Logs

```bash
# Application logs
docker-compose logs -f app

# Nginx logs
docker-compose logs -f app | grep nginx

# Queue worker logs
docker-compose logs -f queue

# Scheduler logs
docker-compose logs -f scheduler

# Database logs
docker-compose logs -f db
```

### 2. Monitor Resources

```bash
# Container stats
docker stats

# Disk usage
df -h
docker system df

# Memory usage
free -h

# Check specific container
docker exec finance-tracker-app ps aux
```

### 3. Health Checks

```bash
# Simple health check
curl http://localhost/health

# Detailed health check with all dependencies
curl http://localhost/health/detailed | jq

# Expected response:
{
  "status": "healthy",
  "timestamp": "2025-10-06T12:00:00+00:00",
  "checks": {
    "app": { "status": "ok", "version": "1.0.0", "environment": "production" },
    "database": { "status": "ok", "connection": "available", "database": "finance_tracker" },
    "cache": { "status": "ok", "driver": "redis" },
    "storage": { "status": "ok", "writable": true }
  }
}
```

### 4. Database Monitoring

```bash
# Connect to database
docker-compose exec db mysql -u root -p$DB_PASSWORD finance_tracker

# Check table sizes
SELECT 
  table_name AS "Table",
  ROUND(((data_length + index_length) / 1024 / 1024), 2) AS "Size (MB)"
FROM information_schema.TABLES
WHERE table_schema = "finance_tracker"
ORDER BY (data_length + index_length) DESC;

# Check slow queries
SHOW PROCESSLIST;
```

## Troubleshooting

### Common Issues

#### 1. Containers not starting

```bash
# Check logs
docker-compose logs

# Check disk space
df -h

# Clean up unused images
docker system prune -a

# Restart services
docker-compose down
docker-compose up -d
```

#### 2. Database connection errors

```bash
# Check database container
docker-compose ps db

# Check database logs
docker-compose logs db

# Verify credentials in .env
cat .env | grep DB_

# Test connection
docker-compose exec app php artisan tinker
>>> DB::connection()->getPdo();
```

#### 3. Permission issues

```bash
# Fix storage permissions
docker-compose exec app chown -R www:www /var/www/html/storage
docker-compose exec app chmod -R 755 /var/www/html/storage

# Fix bootstrap cache permissions
docker-compose exec app chown -R www:www /var/www/html/bootstrap/cache
docker-compose exec app chmod -R 755 /var/www/html/bootstrap/cache
```

#### 4. Queue workers not processing jobs

```bash
# Check queue worker logs
docker-compose logs queue

# Restart queue worker
docker-compose restart queue

# Check failed jobs
docker-compose exec app php artisan queue:failed

# Retry failed jobs
docker-compose exec app php artisan queue:retry all
```

#### 5. High memory usage

```bash
# Check memory usage
docker stats --no-stream

# Optimize OPcache
# Edit docker/php/opcache.ini and adjust memory_consumption

# Clear application cache
docker-compose exec app php artisan cache:clear
docker-compose exec app php artisan view:clear

# Restart PHP-FPM
docker-compose restart app
```

### Emergency Procedures

#### Complete Service Restart

```bash
docker-compose down
docker-compose up -d
```

#### Reset Everything (DANGER - data loss)

```bash
# BACKUP FIRST!
./scripts/backup.sh

# Remove all containers and volumes
docker-compose down -v

# Remove images
docker-compose down --rmi all

# Start fresh
docker-compose up -d
docker-compose exec app php artisan migrate:fresh --seed
```

## Maintenance

### Regular Tasks

#### Daily

- Monitor error logs
- Check disk space
- Verify backup creation

#### Weekly

- Review application metrics
- Check for security updates
- Test backup restoration

#### Monthly

- Update dependencies (composer, npm)
- Review and optimize database queries
- Clean up old backups

### Backup Schedule

Automated backups should run daily. Manual backup:

```bash
./scripts/backup.sh
```

Backups are stored in `/var/backups/finance-tracker/` and kept for 30 days.

## Support

For issues or questions:

- Documentation: `docs/` folder
- GitHub Issues: https://github.com/your-username/finance-tracker/issues
- Email: support@finance-tracker.com

---

**Last Updated:** October 6, 2025  
**Version:** 1.0.0
