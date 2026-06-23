#!/usr/bin/env bash
set -e

DB_NAME="${POSTGRES_DB:-smpp_platform}"
DB_USER="${POSTGRES_USER:-smpp}"
BACKUP_DIR="/opt/smpp-platform/backups/daily"

mkdir -p "$BACKUP_DIR"

DATE=$(date +%Y%m%d_%H%M%S)
FILE="$BACKUP_DIR/backup_$DATE.sql.gz"

pg_dump -h localhost -U "$DB_USER" "$DB_NAME" | gzip > "$FILE"

echo "Backup created: $FILE"

find "$BACKUP_DIR" -name "backup_*.sql.gz" -mtime +7 -delete