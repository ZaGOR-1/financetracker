#!/bin/bash

# ═══════════════════════════════════════════════════════════════════════════
# ⏮️  Production Rollback Script
# ═══════════════════════════════════════════════════════════════════════════
# Finance Tracker - Emergency Rollback
# 
# Usage:
#   ./scripts/rollback-production.sh [backup_date]
#
# Example:
#   ./scripts/rollback-production.sh 20250107_140530
#
# Author: Finance Tracker Team
# Date: 7 October 2025
# ═══════════════════════════════════════════════════════════════════════════

set -e

# ─── Configuration ──────────────────────────────────────────────────────────
APP_DIR="/var/www/finance-tracker"
BACKUP_DIR="/var/backups/finance-tracker"
LOG_FILE="/var/log/finance-tracker-rollback-$(date +%Y%m%d_%H%M%S).log"

# Colors
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m'

# ─── Helper Functions ───────────────────────────────────────────────────────

log() {
    echo -e "${BLUE}[$(date +'%Y-%m-%d %H:%M:%S')]${NC} $1" | tee -a "$LOG_FILE"
}

log_success() {
    echo -e "${GREEN}✅ $1${NC}" | tee -a "$LOG_FILE"
}

log_error() {
    echo -e "${RED}❌ $1${NC}" | tee -a "$LOG_FILE"
}

log_warning() {
    echo -e "${YELLOW}⚠️  $1${NC}" | tee -a "$LOG_FILE"
}

# ─── Start Rollback ─────────────────────────────────────────────────────────

log "═══════════════════════════════════════════════════════════════"
log "⏮️  Finance Tracker - Emergency Rollback"
log "═══════════════════════════════════════════════════════════════"
log ""

# Load environment
if [ -f ".env.production" ]; then
    source .env.production
else
    log_error ".env.production not found!"
    exit 1
fi

# Check for backup parameter
BACKUP_DATE=$1

if [ -z "$BACKUP_DATE" ]; then
    log "Available backups:"
    ls -lh "$BACKUP_DIR" | grep -E "db_.*\.sql\.gz|files_.*\.tar\.gz"
    log ""
    read -p "Enter backup date (YYYYMMDD_HHMMSS) or 'latest': " BACKUP_DATE
    
    if [ "$BACKUP_DATE" = "latest" ]; then
        BACKUP_DATE=$(ls -t "$BACKUP_DIR"/db_*.sql.gz | head -1 | sed 's/.*db_\(.*\)\.sql\.gz/\1/')
    fi
fi

# Validate backup exists
DB_BACKUP="$BACKUP_DIR/db_${BACKUP_DATE}.sql.gz"
FILES_BACKUP="$BACKUP_DIR/files_${BACKUP_DATE}.tar.gz"

if [ ! -f "$DB_BACKUP" ]; then
    log_error "Database backup not found: $DB_BACKUP"
    exit 1
fi

log "Using backup from: $BACKUP_DATE"
log "  Database: $DB_BACKUP"
log "  Files: $FILES_BACKUP"
log ""

# Confirm rollback
read -p "⚠️  This will rollback the application to $BACKUP_DATE. Continue? (y/n): " -n 1 -r
echo
if [[ ! $REPLY =~ ^[Yy]$ ]]; then
    log "Rollback cancelled"
    exit 0
fi

# ─── Execute Rollback ───────────────────────────────────────────────────────

log "Starting rollback..."
log ""

# Enable maintenance mode
log "Enabling maintenance mode..."
php artisan down --message="System rollback in progress. Please wait..."
log_success "Maintenance mode enabled"

# Create emergency backup of current state
log "Creating emergency backup of current state..."
EMERGENCY_DATE=$(date +%Y%m%d_%H%M%S)
mysqldump -u "$DB_USERNAME" -p"$DB_PASSWORD" "$DB_DATABASE" | gzip > "$BACKUP_DIR/emergency_db_${EMERGENCY_DATE}.sql.gz"
log_success "Emergency backup created"

# Stop queue workers
log "Stopping queue workers..."
supervisorctl stop finance-tracker-worker:*
log_success "Queue workers stopped"

# Restore database
log "Restoring database from backup..."
gunzip -c "$DB_BACKUP" | mysql -u "$DB_USERNAME" -p"$DB_PASSWORD" "$DB_DATABASE"
log_success "Database restored"

# Restore files if backup exists
if [ -f "$FILES_BACKUP" ]; then
    log "Restoring application files..."
    tar -xzf "$FILES_BACKUP" -C /
    log_success "Files restored"
else
    log_warning "Files backup not found, skipping file restoration"
    
    # Rollback git to previous commit
    log "Rolling back git to previous commit..."
    cd "$APP_DIR"
    git reset --hard HEAD~1
    log_success "Git rolled back"
fi

# Install dependencies
log "Installing dependencies..."
composer install --no-dev --optimize-autoloader --no-interaction
npm ci --production=false
log_success "Dependencies installed"

# Build assets
log "Building frontend assets..."
npm run build
log_success "Assets built"

# Clear caches
log "Clearing caches..."
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear
log_success "Caches cleared"

# Optimize application
log "Optimizing application..."
php artisan config:cache
php artisan route:cache
php artisan view:cache
composer dump-autoload --optimize
log_success "Application optimized"

# Restart services
log "Restarting services..."
systemctl reload php8.3-fpm
supervisorctl start finance-tracker-worker:*
log_success "Services restarted"

# Disable maintenance mode
log "Disabling maintenance mode..."
php artisan up
log_success "Maintenance mode disabled"

# ─── Verification ───────────────────────────────────────────────────────────

log ""
log "Verifying rollback..."

# Health check
HEALTH_RESPONSE=$(curl -s -o /dev/null -w "%{http_code}" "$APP_URL/health")
if [ "$HEALTH_RESPONSE" -eq 200 ]; then
    log_success "Health check passed (HTTP $HEALTH_RESPONSE)"
else
    log_error "Health check failed (HTTP $HEALTH_RESPONSE)"
fi

# Check homepage
HOMEPAGE_RESPONSE=$(curl -s -o /dev/null -w "%{http_code}" "$APP_URL")
if [ "$HOMEPAGE_RESPONSE" -eq 200 ]; then
    log_success "Homepage accessible (HTTP $HOMEPAGE_RESPONSE)"
else
    log_warning "Homepage returned HTTP $HOMEPAGE_RESPONSE"
fi

# ─── Summary ────────────────────────────────────────────────────────────────

log ""
log "═══════════════════════════════════════════════════════════════"
log_success "✅ Rollback completed successfully!"
log "═══════════════════════════════════════════════════════════════"
log ""
log "📊 Rollback Summary:"
log "  • Restored to: $BACKUP_DATE"
log "  • Emergency backup: emergency_db_${EMERGENCY_DATE}.sql.gz"
log "  • Rollback time: $(date +'%Y-%m-%d %H:%M:%S')"
log "  • Log file: $LOG_FILE"
log ""
log "⚠️  Important:"
log "  • Verify all critical features work correctly"
log "  • Monitor application logs"
log "  • Investigate the issue that caused the rollback"
log "  • Emergency backup available at: $BACKUP_DIR/emergency_db_${EMERGENCY_DATE}.sql.gz"
log ""
log "═══════════════════════════════════════════════════════════════"

# Send notification
if [ ! -z "$TELEGRAM_BOT_TOKEN" ] && [ ! -z "$TELEGRAM_CHAT_ID" ]; then
    MESSAGE="⏮️  *Rollback Executed*%0A%0A"
    MESSAGE+="Restored to: $BACKUP_DATE%0A"
    MESSAGE+="Time: $(date +'%Y-%m-%d %H:%M:%S')%0A"
    MESSAGE+="Status: Success%0A"
    MESSAGE+="URL: $APP_URL"
    
    curl -s -X POST "https://api.telegram.org/bot$TELEGRAM_BOT_TOKEN/sendMessage" \
        -d "chat_id=$TELEGRAM_CHAT_ID" \
        -d "text=$MESSAGE" \
        -d "parse_mode=Markdown" > /dev/null
fi

exit 0
