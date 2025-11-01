#!/bin/bash
# Backup script for Finance Tracker
# Usage: ./scripts/backup.sh

set -e

PROJECT_DIR="/var/www/finance-tracker"
BACKUP_DIR="/var/backups/finance-tracker"
TIMESTAMP=$(date +%Y%m%d_%H%M%S)

echo "📦 Starting backup..."
echo "Timestamp: $TIMESTAMP"

# Create backup directory
mkdir -p $BACKUP_DIR

cd $PROJECT_DIR

# 1. Backup database
echo "🗃️ Backing up database..."
docker-compose exec -T db mysqldump -u root -p$DB_PASSWORD finance_tracker > $BACKUP_DIR/db_backup_$TIMESTAMP.sql
gzip $BACKUP_DIR/db_backup_$TIMESTAMP.sql
echo "✅ Database backup: $BACKUP_DIR/db_backup_$TIMESTAMP.sql.gz"

# 2. Backup uploaded files (if any)
if [ -d "storage/app/public" ]; then
    echo "📁 Backing up storage files..."
    tar -czf $BACKUP_DIR/storage_backup_$TIMESTAMP.tar.gz storage/app/public
    echo "✅ Storage backup: $BACKUP_DIR/storage_backup_$TIMESTAMP.tar.gz"
fi

# 3. Backup .env file
echo "⚙️ Backing up .env file..."
cp .env $BACKUP_DIR/env_backup_$TIMESTAMP
echo "✅ Environment backup: $BACKUP_DIR/env_backup_$TIMESTAMP"

# 4. Clean up old backups (keep last 30 days)
echo "🧹 Cleaning up old backups..."
find $BACKUP_DIR -name "db_backup_*.sql.gz" -mtime +30 -delete
find $BACKUP_DIR -name "storage_backup_*.tar.gz" -mtime +30 -delete
find $BACKUP_DIR -name "env_backup_*" -mtime +30 -delete

echo "✅ Backup completed successfully!"
echo "📊 Backup statistics:"
du -sh $BACKUP_DIR
ls -lh $BACKUP_DIR | tail -n 3
