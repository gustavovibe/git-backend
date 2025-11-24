#!/bin/sh
set -e

PORT="${PORT:-8080}"

# update Apache listen and vhost ports
sed -i "s/Listen 80/Listen ${PORT}/" /etc/apache2/ports.conf
sed -i "s/:80/:${PORT}/g" /etc/apache2/sites-available/*.conf

exec "$@"
