#!/usr/bin/env bash
set -e

if [ -z "$1" ]; then
    echo "Usage: $0 <backup_file.sql.gz>"
    exit 1
fi

DB_NAME="${POSTGRES_DB:-smpp_platform}"
DB_USER="${POSTGRES_USER:-smpp}"

gunzip -c "$1" | psql -h localhost -U "$DB_USER" "$DB_NAME"

echo "Restore complete"