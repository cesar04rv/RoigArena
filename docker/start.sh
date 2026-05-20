#!/bin/bash

set -e

echo "🚀 Iniciando Roig Arena..."

# Crear .env desde .env.example si no existe
if [ ! -f .env ]; then
    echo "📝 Creando archivo .env..."
    cp .env.example .env
    
    # Reemplazar valores con variables de entorno
    if [ ! -z "$DATABASE_URL" ]; then
        sed -i "s|DATABASE_URL=.*|DATABASE_URL=${DATABASE_URL}|g" .env
    fi
    
    if [ ! -z "$APP_URL" ]; then
        sed -i "s|APP_URL=.*|APP_URL=${APP_URL}|g" .env
    fi
fi

# Generar APP_KEY si no existe
if ! grep -q "APP_KEY=base64:" .env; then
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
