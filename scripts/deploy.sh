#!/bin/bash
# Zero-downtime deployment script for Finance Tracker
# Usage: ./scripts/deploy.sh [staging|production]

set -e

ENVIRONMENT=${1:-staging}
PROJECT_DIR="/var/www/finance-tracker"
BACKUP_DIR="/var/backups/finance-tracker"
TIMESTAMP=$(date +%Y%m%d_%H%M%S)

echo "🚀 Starting deployment to $ENVIRONMENT..."
echo "Timestamp: $TIMESTAMP"

# Navigate to project directory
cd $PROJECT_DIR

# Create backup directory if it doesn't exist
mkdir -p $BACKUP_DIR

# 1. Backup database
echo "📦 Creating database backup..."
docker-compose exec -T db mysqldump -u root -p$DB_PASSWORD finance_tracker > $BACKUP_DIR/db_backup_$TIMESTAMP.sql
echo "✅ Database backup created: $BACKUP_DIR/db_backup_$TIMESTAMP.sql"

# 2. Pull latest code (if using git deployment)
if [ -d ".git" ]; then
    echo "📥 Pulling latest code..."
    git pull origin $(git rev-parse --abbrev-ref HEAD)
fi

# 3. Pull latest Docker images
echo "🐳 Pulling latest Docker images..."
docker-compose pull

# 4. Build new images if Dockerfile changed
echo "🔨 Building Docker images..."
docker-compose build --pull

# 5. Start new containers with zero downtime
echo "🔄 Starting new containers..."
docker-compose up -d --no-deps --scale app=2 app
sleep 10

# 6. Run database migrations
echo "🗃️ Running database migrations..."
docker-compose exec -T app php artisan migrate --force

# 7. Clear and rebuild caches
echo "🗑️ Clearing caches..."
docker-compose exec -T app php artisan config:cache
docker-compose exec -T app php artisan route:cache
docker-compose exec -T app php artisan view:cache
docker-compose exec -T app php artisan optimize

# 8. Remove old containers
echo "🧹 Removing old containers..."
docker-compose up -d --no-deps --remove-orphans app

# 9. Restart queue workers
echo "⚙️ Restarting queue workers..."
docker-compose restart queue

# 10. Health check
echo "🏥 Running health check..."
sleep 5
HTTP_CODE=$(curl -s -o /dev/null -w "%{http_code}" http://localhost/health)

if [ "$HTTP_CODE" == "200" ]; then
    echo "✅ Deployment successful! Health check passed."
    
    # Clean up old backups (keep last 10)
    cd $BACKUP_DIR
    ls -t db_backup_*.sql | tail -n +11 | xargs -r rm
    
    echo "🎉 Deployment completed successfully!"
    exit 0
else
    echo "❌ Health check failed with HTTP code: $HTTP_CODE"
    echo "🔙 Rolling back..."
    
    # Rollback: restore database
    docker-compose exec -T db mysql -u root -p$DB_PASSWORD finance_tracker < $BACKUP_DIR/db_backup_$TIMESTAMP.sql
    
    echo "❌ Deployment failed and rolled back."
    exit 1
fi
