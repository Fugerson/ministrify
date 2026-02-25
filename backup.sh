#!/bin/bash
# Ministrify database backup script
# Runs via cron every 6 hours, keeps last 30 backups

BACKUP_DIR="/var/www/ministrify/backups"
TIMESTAMP=$(date +"%Y-%m-%d_%H-%M-%S")
FILENAME="ministrify_${TIMESTAMP}.sql.gz"
MAX_BACKUPS=30

# Load env vars
set -a
source /var/www/ministrify/.env 2>/dev/null
set +a

# Create backup dir if missing
mkdir -p "$BACKUP_DIR"

# Dump database via MySQL container
docker compose -f /var/www/ministrify/docker-compose.prod.yml exec -T mysql \
    mysqldump -u"${DB_USERNAME:-ministrify}" -p"${DB_PASSWORD}" "${DB_DATABASE:-ministrify}" \
    --single-transaction --quick --routines --triggers \
    2>/dev/null | gzip > "${BACKUP_DIR}/${FILENAME}"

# Verify backup was created and is not empty
if [ -s "${BACKUP_DIR}/${FILENAME}" ]; then
    echo "Backup created: ${BACKUP_DIR}/${FILENAME}"
else
    echo "ERROR: Backup failed or empty: ${BACKUP_DIR}/${FILENAME}"
    rm -f "${BACKUP_DIR}/${FILENAME}"
    exit 1
fi

# Remove old backups, keep last MAX_BACKUPS
cd "$BACKUP_DIR" && ls -1t ministrify_*.sql.gz 2>/dev/null | tail -n +$((MAX_BACKUPS + 1)) | xargs -r rm -f

echo "Backups retained: $(ls -1 ${BACKUP_DIR}/ministrify_*.sql.gz 2>/dev/null | wc -l)/${MAX_BACKUPS}"
