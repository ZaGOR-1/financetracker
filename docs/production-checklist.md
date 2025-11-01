# Production Deployment Checklist

## Pre-Deployment

### Environment Configuration
- [ ] `.env.production` налаштовано з правильними значеннями
- [ ] `APP_ENV=production` встановлено
- [ ] `APP_DEBUG=false` встановлено
- [ ] `APP_KEY` згенеровано (`php artisan key:generate`)
- [ ] `APP_URL` вказує на production domain
- [ ] Database credentials налаштовано
- [ ] Redis/Cache credentials налаштовано
- [ ] Mail settings налаштовано (SMTP)
- [ ] Sentry DSN налаштовано (error tracking)

### Security
- [ ] HTTPS налаштовано (Let's Encrypt / SSL сертифікат)
- [ ] Firewall rules налаштовано (тільки 80, 443, SSH)
- [ ] SSH ключі налаштовано (вимкнути password auth)
- [ ] Database користувач має обмежені права
- [ ] Секрети не в git репозиторії
- [ ] CORS налаштовано правильно
- [ ] Rate limiting активовано
- [ ] Sanctum cookies secure mode увімкнено

### Database
- [ ] Migrations протестовано на staging
- [ ] Database backup strategy налаштовано
- [ ] Indexes перевірено та оптимізовано
- [ ] Rollback plan підготовлено

### Docker & Infrastructure
- [ ] Docker Compose файли перевірено
- [ ] Multi-stage Dockerfile оптимізовано
- [ ] Image size оптимізовано
- [ ] Health checks налаштовано
- [ ] Volumes для persistent data налаштовано
- [ ] Memory/CPU limits встановлено
- [ ] Restart policies налаштовано

### Performance
- [ ] OPcache налаштовано
- [ ] Redis cache налаштовано
- [ ] Query optimization перевірено (N+1 queries)
- [ ] Eager loading використовується де потрібно
- [ ] Static assets compressed (Gzip/Brotli)
- [ ] CDN налаштовано для assets (опціонально)
- [ ] Database indexes оптимізовано

### Monitoring & Logging
- [ ] Laravel logging налаштовано (daily channel)
- [ ] Error tracking налаштовано (Sentry)
- [ ] Application monitoring налаштовано
- [ ] Server monitoring налаштовано (CPU, Memory, Disk)
- [ ] Database monitoring налаштовано
- [ ] Uptime monitoring налаштовано
- [ ] Alerts налаштовано (email/Slack)

### CI/CD
- [ ] GitHub Actions workflows налаштовано
- [ ] Tests проходять успішно
- [ ] PHPStan аналіз проходить без помилок
- [ ] Deployment scripts протестовано
- [ ] Rollback procedure задокументовано

### Backup & Recovery
- [ ] Automated database backups налаштовано
- [ ] Backup retention policy визначено
- [ ] Backup restoration procedure протестовано
- [ ] Disaster recovery plan підготовлено

## Deployment Steps

1. **Pre-deployment checks:**
   ```bash
   # Run all tests
   php artisan test
   
   # Run static analysis
   vendor/bin/phpstan analyse
   
   # Check for security vulnerabilities
   composer audit
   ```

2. **Backup current state:**
   ```bash
   ./scripts/backup.sh
   ```

3. **Deploy:**
   ```bash
   ./scripts/deploy.sh production
   ```

4. **Verify deployment:**
   - [ ] Health check endpoint returns 200
   - [ ] Login functionality works
   - [ ] Dashboard loads correctly
   - [ ] Transactions CRUD works
   - [ ] Budget calculations correct
   - [ ] Export functionality works
   - [ ] Email notifications send

5. **Monitor for 30 minutes:**
   - [ ] Check error logs
   - [ ] Monitor server resources
   - [ ] Watch application metrics
   - [ ] Check database performance

## Post-Deployment

### Smoke Tests
- [ ] User registration works
- [ ] User login works
- [ ] Dashboard displays data
- [ ] Create transaction works
- [ ] Edit transaction works
- [ ] Delete transaction works
- [ ] Budget creation works
- [ ] Export to Excel works
- [ ] Email notifications send
- [ ] API endpoints respond correctly

### Performance Tests
- [ ] Page load time < 2 seconds
- [ ] API response time < 500ms
- [ ] Database queries optimized
- [ ] Memory usage within limits
- [ ] CPU usage acceptable

### Documentation
- [ ] Deployment documented
- [ ] Rollback procedure documented
- [ ] Monitoring dashboards shared
- [ ] Runbook updated

## Rollback Plan

If deployment fails:

1. **Enable maintenance mode:**
   ```bash
   docker-compose exec app php artisan down
   ```

2. **Rollback database:**
   ```bash
   ./scripts/rollback.sh db_backup_YYYYMMDD_HHMMSS.sql
   ```

3. **Rollback code:**
   ```bash
   git reset --hard <previous-commit>
   docker-compose up -d --build
   ```

4. **Verify rollback:**
   ```bash
   curl http://localhost/health
   ```

5. **Disable maintenance mode:**
   ```bash
   docker-compose exec app php artisan up
   ```

## Emergency Contacts

- **DevOps Lead:** [Name] - [Email] - [Phone]
- **Backend Lead:** [Name] - [Email] - [Phone]
- **Database Admin:** [Name] - [Email] - [Phone]
- **Server Provider:** [Support Contact]

## Monitoring URLs

- **Application:** https://finance-tracker.com
- **Health Check:** https://finance-tracker.com/health
- **Detailed Health:** https://finance-tracker.com/health/detailed
- **Monitoring Dashboard:** [URL if applicable]
- **Error Tracking:** [Sentry URL]

## Notes

- Always test deployment on staging first
- Coordinate with team before production deployment
- Schedule deployments during low traffic periods
- Keep stakeholders informed
- Document any issues encountered
