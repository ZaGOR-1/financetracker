#!/bin/bash

# ═══════════════════════════════════════════════════════════════════════════
# 🚀 Production Deployment Script
# ═══════════════════════════════════════════════════════════════════════════
# Finance Tracker - Automated Production Deployment
# 
# Usage:
#   ./scripts/deploy-production.sh [options]
#
# Options:
#   --skip-backup    Skip database backup
#   --skip-tests     Skip running tests
#   --force          Force deployment without confirmation
#
# Prerequisites:
#   - SSH access to production server
#   - Proper .env.production configured
#   - Git repository access
#
# Author: Finance Tracker Team
# Date: 7 October 2025
# ═══════════════════════════════════════════════════════════════════════════

set -e  # Exit on any error

# ─── Configuration ──────────────────────────────────────────────────────────
APP_DIR="/var/www/finance-tracker"
BACKUP_DIR="/var/backups/finance-tracker"
DATE=$(date +%Y%m%d_%H%M%S)
LOG_FILE="/var/log/finance-tracker-deploy-${DATE}.log"

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

# Parse arguments
SKIP_BACKUP=false
SKIP_TESTS=false
FORCE=false

while [[ $# -gt 0 ]]; do
    case $1 in
        --skip-backup)
            SKIP_BACKUP=true
            shift
            ;;
        --skip-tests)
            SKIP_TESTS=true
            shift
            ;;
        --force)
            FORCE=true
            shift
            ;;
        *)
            echo "Unknown option: $1"
            exit 1
            ;;
    esac
done

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

confirm() {
    if [ "$FORCE" = true ]; then
        return 0
    fi
    
    read -p "$1 (y/n): " -n 1 -r
    echo
    if [[ ! $REPLY =~ ^[Yy]$ ]]; then
        log_error "Deployment cancelled by user"
        exit 1
    fi
}

check_command() {
    if ! command -v $1 &> /dev/null; then
        log_error "$1 could not be found. Please install it first."
        exit 1
    fi
}

rollback() {
    log_error "Deployment failed! Starting rollback..."
    
    # Disable maintenance mode
    php artisan up
    
    # Restore database backup if exists
    if [ -f "$BACKUP_DIR/db_${DATE}.sql.gz" ]; then
        log "Restoring database backup..."
        gunzip -c "$BACKUP_DIR/db_${DATE}.sql.gz" | mysql -u "$DB_USERNAME" -p"$DB_PASSWORD" "$DB_DATABASE"
    fi
    
    # Restore previous git commit
    git reset --hard HEAD~1
    
    log_success "Rollback completed"
    exit 1
}

# Set trap for errors
trap rollback ERR

# ─── Pre-deployment Checks ──────────────────────────────────────────────────

log "═══════════════════════════════════════════════════════════════"
log "🚀 Finance Tracker - Production Deployment"
log "═══════════════════════════════════════════════════════════════"
log ""

# Check required commands
log "Checking required commands..."
check_command php
check_command composer
check_command npm
check_command git
check_command mysql
check_command supervisorctl
log_success "All required commands available"

# Check if we're in the correct directory
if [ ! -f "artisan" ]; then
    log_error "artisan file not found. Are you in the correct directory?"
    exit 1
fi

# Check if .env.production exists
if [ ! -f ".env.production" ]; then
    log_error ".env.production file not found!"
    exit 1
fi

# Load environment variables
log "Loading environment variables..."
source .env.production
log_success "Environment variables loaded"

# Check git status
log "Checking git status..."
if [ -n "$(git status --porcelain)" ]; then
    log_warning "There are uncommitted changes in the repository"
    confirm "Continue anyway?"
fi

# Show current git info
CURRENT_BRANCH=$(git rev-parse --abbrev-ref HEAD)
CURRENT_COMMIT=$(git rev-parse --short HEAD)
log "Current branch: $CURRENT_BRANCH"
log "Current commit: $CURRENT_COMMIT"
log ""

confirm "Continue with deployment?"

# ─── Pre-deployment Tests ───────────────────────────────────────────────────

if [ "$SKIP_TESTS" = false ]; then
    log "Running tests..."
    
    # PHP tests
    log "Running PHPUnit tests..."
    php artisan test || {
        log_error "Tests failed!"
        exit 1
    }
    
    # Static analysis
    log "Running PHPStan analysis..."
    vendor/bin/phpstan analyse --memory-limit=2G || {
        log_error "PHPStan analysis failed!"
        exit 1
    }
    
    # Security audit
    log "Running composer security audit..."
    composer audit || {
        log_warning "Composer audit found vulnerabilities"
        confirm "Continue anyway?"
    }
    
    log "Running npm security audit..."
    npm audit --audit-level=high || {
        log_warning "NPM audit found vulnerabilities"
        confirm "Continue anyway?"
    }
    
    log_success "All tests passed"
else
    log_warning "Skipping tests (--skip-tests flag)"
fi

# ─── Backup ─────────────────────────────────────────────────────────────────

if [ "$SKIP_BACKUP" = false ]; then
    log "Creating backup..."
    
    # Create backup directory
    mkdir -p "$BACKUP_DIR"
    
    # Database backup
    log "Backing up database..."
    mysqldump -u "$DB_USERNAME" -p"$DB_PASSWORD" "$DB_DATABASE" | gzip > "$BACKUP_DIR/db_${DATE}.sql.gz"
    log_success "Database backup created: db_${DATE}.sql.gz"
    
    # Files backup
    log "Backing up application files..."
    tar -czf "$BACKUP_DIR/files_${DATE}.tar.gz" \
        --exclude='node_modules' \
        --exclude='vendor' \
        --exclude='storage/logs' \
        --exclude='storage/framework/cache' \
        "$APP_DIR"
    log_success "Files backup created: files_${DATE}.tar.gz"
    
    # Upload backups to S3 (if configured)
    if [ ! -z "$AWS_ACCESS_KEY_ID" ]; then
        log "Uploading backups to S3..."
        aws s3 cp "$BACKUP_DIR/db_${DATE}.sql.gz" "s3://$AWS_BUCKET/backups/database/" || log_warning "S3 upload failed"
        aws s3 cp "$BACKUP_DIR/files_${DATE}.tar.gz" "s3://$AWS_BUCKET/backups/files/" || log_warning "S3 upload failed"
    fi
else
    log_warning "Skipping backup (--skip-backup flag)"
fi

# ─── Deployment ─────────────────────────────────────────────────────────────

log "Starting deployment..."
log ""

# Enable maintenance mode
log "Enabling maintenance mode..."
php artisan down --message="Deploying new version. We'll be back shortly!" || log_warning "Maintenance mode failed"
log_success "Maintenance mode enabled"

# Pull latest code
log "Pulling latest code from git..."
git fetch origin
git pull origin production
NEW_COMMIT=$(git rev-parse --short HEAD)
log_success "Code updated to commit: $NEW_COMMIT"

# Install PHP dependencies
log "Installing PHP dependencies..."
composer install --no-dev --optimize-autoloader --no-interaction
log_success "PHP dependencies installed"

# Install Node dependencies
log "Installing Node dependencies..."
npm ci --production=false
log_success "Node dependencies installed"

# Build frontend assets
log "Building frontend assets..."
npm run build
log_success "Frontend assets built"

# Run database migrations
log "Running database migrations..."
php artisan migrate --force
log_success "Migrations completed"

# Clear all caches
log "Clearing application caches..."
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

# Restart queue workers
log "Restarting queue workers..."
supervisorctl restart finance-tracker-worker:*
log_success "Queue workers restarted"

# Restart PHP-FPM
log "Restarting PHP-FPM..."
systemctl reload php8.3-fpm
log_success "PHP-FPM restarted"

# Disable maintenance mode
log "Disabling maintenance mode..."
php artisan up
log_success "Maintenance mode disabled"

# ─── Post-deployment Verification ───────────────────────────────────────────

log ""
log "Running post-deployment checks..."

# Health check
log "Running health check..."
HEALTH_RESPONSE=$(curl -s -o /dev/null -w "%{http_code}" "$APP_URL/health")
if [ "$HEALTH_RESPONSE" -eq 200 ]; then
    log_success "Health check passed (HTTP $HEALTH_RESPONSE)"
else
    log_error "Health check failed (HTTP $HEALTH_RESPONSE)"
    rollback
fi

# Check if homepage loads
log "Checking homepage..."
HOMEPAGE_RESPONSE=$(curl -s -o /dev/null -w "%{http_code}" "$APP_URL")
if [ "$HOMEPAGE_RESPONSE" -eq 200 ]; then
    log_success "Homepage accessible (HTTP $HOMEPAGE_RESPONSE)"
else
    log_warning "Homepage returned HTTP $HOMEPAGE_RESPONSE"
fi

# Check queue workers
log "Checking queue workers..."
WORKERS_STATUS=$(supervisorctl status finance-tracker-worker:* | grep RUNNING | wc -l)
if [ "$WORKERS_STATUS" -gt 0 ]; then
    log_success "Queue workers running ($WORKERS_STATUS workers)"
else
    log_error "No queue workers running!"
    rollback
fi

# ─── Cleanup ────────────────────────────────────────────────────────────────

log ""
log "Cleaning up old backups..."
# Keep only last 30 days of backups
find "$BACKUP_DIR" -type f -mtime +30 -delete
log_success "Old backups cleaned up"

# ─── Summary ────────────────────────────────────────────────────────────────

log ""
log "═══════════════════════════════════════════════════════════════"
log_success "🎉 Deployment completed successfully!"
log "═══════════════════════════════════════════════════════════════"
log ""
log "📊 Deployment Summary:"
log "  • Environment: production"
log "  • Previous commit: $CURRENT_COMMIT"
log "  • New commit: $NEW_COMMIT"
log "  • Deployment time: $(date +'%Y-%m-%d %H:%M:%S')"
log "  • Log file: $LOG_FILE"
log ""
log "🔗 Application URL: $APP_URL"
log "🏥 Health check: $APP_URL/health"
log ""
log "📝 Next steps:"
log "  1. Monitor application logs: tail -f storage/logs/laravel.log"
log "  2. Check error tracking: Sentry dashboard"
log "  3. Monitor server resources"
log "  4. Verify critical features work correctly"
log ""
log "═══════════════════════════════════════════════════════════════"

# Send notification (optional)
if [ ! -z "$TELEGRAM_BOT_TOKEN" ] && [ ! -z "$TELEGRAM_CHAT_ID" ]; then
    MESSAGE="🚀 *Deployment Successful*%0A%0A"
    MESSAGE+="Environment: production%0A"
    MESSAGE+="Commit: $NEW_COMMIT%0A"
    MESSAGE+="Time: $(date +'%Y-%m-%d %H:%M:%S')%0A"
    MESSAGE+="URL: $APP_URL"
    
    curl -s -X POST "https://api.telegram.org/bot$TELEGRAM_BOT_TOKEN/sendMessage" \
        -d "chat_id=$TELEGRAM_CHAT_ID" \
        -d "text=$MESSAGE" \
        -d "parse_mode=Markdown" > /dev/null
fi

exit 0
