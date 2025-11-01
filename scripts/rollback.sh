#!/bin/bash
# Rollback script for Finance Tracker
# Usage: ./scripts/rollback.sh [backup_file]

set -e

PROJECT_DIR="/var/www/finance-tracker"
BACKUP_DIR="/var/backups/finance-tracker"

if [ -z "$1" ]; then
    echo "📋 Available backups:"
    ls -lh $BACKUP_DIR/db_backup_*.sql
    echo ""
    echo "Usage: ./scripts/rollback.sh [backup_file]"
    echo "Example: ./scripts/rollback.sh db_backup_20251006_120000.sql"
    exit 1
fi

BACKUP_FILE="$BACKUP_DIR/$1"

if [ ! -f "$BACKUP_FILE" ]; then
    echo "❌ Backup file not found: $BACKUP_FILE"
    exit 1
fi

echo "🔙 Starting rollback..."
echo "Backup file: $BACKUP_FILE"
read -p "Are you sure you want to rollback? (yes/no): " CONFIRM

if [ "$CONFIRM" != "yes" ]; then
    echo "Rollback cancelled."
    exit 0
fi

cd $PROJECT_DIR

# 1. Put application in maintenance mode
echo "🚧 Enabling maintenance mode..."
docker-compose exec -T app php artisan down --retry=60

# 2. Restore database
echo "🗃️ Restoring database..."
docker-compose exec -T db mysql -u root -p$DB_PASSWORD finance_tracker < $BACKUP_FILE

# 3. Rollback migrations (optional - uncomment if needed)
# echo "⏪ Rolling back migrations..."
# docker-compose exec -T app php artisan migrate:rollback --step=1 --force

# 4. Clear caches
echo "🗑️ Clearing caches..."
docker-compose exec -T app php artisan cache:clear
docker-compose exec -T app php artisan config:clear
docker-compose exec -T app php artisan route:clear
docker-compose exec -T app php artisan view:clear

# 5. Restart containers
echo "🔄 Restarting containers..."
docker-compose restart app queue

# 6. Disable maintenance mode
echo "✅ Disabling maintenance mode..."
docker-compose exec -T app php artisan up

# 7. Health check
echo "🏥 Running health check..."
sleep 5
HTTP_CODE=$(curl -s -o /dev/null -w "%{http_code}" http://localhost/health)

if [ "$HTTP_CODE" == "200" ]; then
    echo "✅ Rollback successful! Health check passed."
    exit 0
else
    echo "❌ Health check failed with HTTP code: $HTTP_CODE"
    echo "Please investigate manually."
    exit 1
fi
