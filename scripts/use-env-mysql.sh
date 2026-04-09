#!/bin/sh

set -eu

cp .env.mysql .env
php artisan config:clear >/dev/null 2>&1 || true

printf '%s\n' 'Environment switched to MySQL (.env.mysql -> .env)'
