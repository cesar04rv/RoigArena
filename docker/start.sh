#!/bin/bash

set -e

echo "🚀 Iniciando Roig Arena..."

# Generar APP_KEY si no existe
if [ -z "$APP_KEY" ]; then
    echo "⚙️  Generando APP_KEY..."
    php artisan key:generate --force
fi

# Ejecutar migraciones
echo "📦 Ejecutando migraciones..."
php artisan migrate --force

# Ejecutar seeders (solo si es el primer despliegue)
echo "🌱 Poblando base de datos..."
php artisan db:seed --force || echo "⚠️  Seeders ya ejecutados"

# Cache de configuración
echo "⚡ Optimizando aplicación..."
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Iniciar supervisord (nginx + php-fpm)
echo "✅ Iniciando servicios..."
/usr/bin/supervisord -c /etc/supervisor/conf.d/supervisord.conf
