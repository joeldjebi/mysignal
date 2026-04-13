#!/bin/sh

set -eu

cp .env.postgres .env
php artisan config:clear >/dev/null 2>&1 || true

printf '%s\n' 'Environment switched to PostgreSQL (.env.postgres -> .env)'
