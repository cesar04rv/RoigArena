# Roig Arena

Plataforma web de venta de entradas para eventos en un pabellón multiusos. Permite a los usuarios consultar el calendario de eventos, seleccionar asientos concretos en un mapa visual del recinto, reservarlos temporalmente y completar la compra con generación de entrada y código QR. Incluye un panel de administración para la gestión de eventos, artistas, sectores del pabellón y solicitudes de cancelación.

## Tecnologías utilizadas

- **Backend**: Laravel 12 (PHP 8.2+)
- **Frontend**: Blade, HTML, CSS y JavaScript vanilla
- **Base de datos**: PostgreSQL alojada en Neon
- **Autenticación**: Sesiones de Laravel + Laravel Sanctum en modo stateful
- **Despliegue**: Render mediante Docker
- **Generación de PDF**: jsPDF (cliente)
- **Generación de QR**: API externa de QR Server

## Funcionalidades principales

### Para usuarios

- Consulta del listado de próximos eventos con su información completa (artistas, fechas, precios por sector).
- Selección de asientos individuales en un mapa visual del pabellón, con distinción de asientos disponibles, reservados y ocupados.
- Sistema de reserva temporal con expiración a los 5 minutos, gestionado con transacciones de base de datos y bloqueos pesimistas para evitar conflictos cuando dos usuarios intentan reservar el mismo asiento.
- Carrito de compra con contador en tiempo real del tiempo restante antes de expirar.
- Confirmación de compra con generación de entradas individuales y código QR único por entrada.
- Consulta de entradas adquiridas, descarga de cada una en formato PDF y solicitud de cancelación pendiente de aprobación por administrador.
- Reanudación de pagos pendientes para reservas que aún no han expirado.

### Para administradores

- Creación de eventos con artistas, fechas, sectores y precios por sector.
- Gestión del catálogo de artistas.
- Editor visual de sectores del pabellón, donde se define un sector como rectángulo en una rejilla con validación de solapamientos y generación automática de todos los asientos del sector.
- Revisión de solicitudes de cancelación con opción de aprobar (liberando el asiento) o rechazar con motivo.

## Arquitectura

El proyecto sigue el patrón MVC propio de Laravel:

- **Rutas** (`routes/web.php` y `routes/api.php`) dirigen cada URL al controlador correspondiente.
- **Controladores** (`app/Http/Controllers`) reciben las peticiones, validan los datos y coordinan la respuesta.
- **Servicios** (`app/Services`) encapsulan la lógica de negocio compleja, como la reserva de asientos con bloqueos, la liberación de reservas expiradas o la geometría de sectores.
- **Modelos Eloquent** (`app/Models`) abstraen el acceso a la base de datos.
- **Vistas Blade** (`resources/views`) renderizan el HTML, con interactividad añadida mediante JavaScript en `public/js`.

## Seguridad

- Contraseñas hasheadas con bcrypt mediante `Hash::make()`.
- Protección CSRF en formularios (directiva `@csrf`) y en llamadas AJAX (cabecera `X-CSRF-TOKEN`).
- Cookies de sesión con flag httpOnly, no accesibles desde JavaScript.
- Sanctum en modo stateful para que las rutas de API acepten la misma autenticación por sesión que las rutas web, sin necesidad de tokens Bearer, ya que frontend y backend comparten dominio.
- Validación de datos en todos los endpoints mediante `$request->validate()`.
- Comprobación de propietario en accesos a recursos sensibles (`where('user_id', auth()->id())`).
- Middleware `IsAdmin` para proteger las rutas administrativas.
- Uso exclusivo de Eloquent y el Query Builder, sin consultas SQL crudas, lo que protege frente a inyecciones SQL.

## Estructura de la base de datos

Las tablas principales son:

- `users`: usuarios registrados, con flag `is_admin`.
- `eventos`: eventos del pabellón con fecha, hora, descripciones e imágenes.
- `artistas` y `artista_evento`: catálogo de artistas y su asociación con eventos.
- `sectores`: zonas del pabellón definidas por un rectángulo en una rejilla con coordenadas de fila y columna.
- `asientos`: butacas individuales pertenecientes a un sector.
- `precios`: tabla pivote evento-sector que define el precio del asiento para cada combinación.
- `estado_asientos`: estado de cada asiento por evento (disponible, reservado, ocupado) con fecha de expiración para las reservas.
- `entradas`: entradas vendidas con código QR único.
- `solicitudes_cancelacion`: peticiones de cancelación pendientes de revisión.

## Requisitos para desarrollo local

- PHP 8.2 o superior
- Composer
- PostgreSQL (local o conexión a Neon)
- Node.js y npm (opcional, para herramientas de desarrollo)

## Instalación local

1. Clonar el repositorio:

```bash
git clone https://github.com/tu-usuario/RoigArena.git
cd RoigArena
```

2. Instalar dependencias de PHP:

```bash
composer install
```

3. Copiar el archivo de entorno y generar la clave de la aplicación:

```bash
cp .env.example .env
php artisan key:generate
```

4. Editar el archivo `.env` con los datos de conexión a la base de datos:

```
DB_CONNECTION=pgsql
DB_HOST=tu-host
DB_PORT=5432
DB_DATABASE=tu-base-de-datos
DB_USERNAME=tu-usuario
DB_PASSWORD=tu-contraseña
```

5. Ejecutar las migraciones y los seeders:

```bash
php artisan migrate
php artisan db:seed
```

6. Levantar el servidor de desarrollo:

```bash
php artisan serve
```

La aplicación estará disponible en `http://localhost:8000`.

## Despliegue en Render con base de datos Neon

### Preparación de la base de datos en Neon

1. Crear una cuenta en https://neon.tech y un nuevo proyecto.
2. En la sección de conexión, copiar la cadena `DATABASE_URL` con formato:

```
postgresql://usuario:password@host/database?sslmode=require
```

### Despliegue en Render

1. Crear una cuenta en https://render.com y conectar el repositorio de GitHub.
2. Crear un nuevo servicio de tipo "Web Service" desde el repositorio.
3. Render detectará automáticamente el archivo `render.yaml` del proyecto y configurará el servicio con Docker usando `Dockerfile.render`.
4. Configurar las variables de entorno marcadas como `sync: false` en `render.yaml`:
   - `APP_URL`: la URL pública del servicio en Render (por ejemplo, `https://roig-arena.onrender.com`).
   - `DATABASE_URL`: la cadena de conexión obtenida de Neon.
5. Desplegar el servicio.

El contenedor ejecuta automáticamente, mediante el script `docker/start.sh`:

- `php artisan migrate --force`
- `php artisan db:seed --force`
- Cacheo de configuración, rutas y vistas
- Arranque de Nginx y PHP-FPM mediante Supervisord

### Configuración adicional importante

Como Render utiliza un proxy inverso delante de la aplicación que recibe las peticiones HTTPS del usuario y las reenvía por HTTP interno al contenedor, es necesario que Laravel confíe en las cabeceras `X-Forwarded-Proto` para que las cookies de sesión se generen correctamente con el flag `secure`. Esta configuración está realizada en `bootstrap/app.php` mediante el middleware `trustProxies`.

## Tarea programada

El proyecto incluye un comando artisan que libera las reservas expiradas:

```bash
php artisan reservas:limpiar
```

Está pensado para ejecutarse periódicamente desde un cron del servidor cada minuto, de forma que las reservas que no se hayan confirmado en los 5 minutos siguientes queden disponibles automáticamente para otros usuarios.

## Estructura de carpetas

```
RoigArena/
├── app/
│   ├── Console/Commands/      Comandos artisan personalizados
│   ├── Http/
│   │   ├── Controllers/        Controladores de rutas web y API
│   │   └── Middleware/         Middleware personalizado (IsAdmin)
│   ├── Models/                 Modelos Eloquent
│   └── Services/               Lógica de negocio
├── bootstrap/                  Configuración de arranque y middleware
├── config/                     Archivos de configuración
├── database/
│   ├── migrations/             Definición de tablas
│   └── seeders/                Datos iniciales
├── docker/                     Configuración de Docker
├── public/
│   ├── css/                    Hojas de estilo
│   ├── js/                     Scripts del frontend
│   └── index.php               Punto de entrada
├── resources/views/            Plantillas Blade
├── routes/
│   ├── web.php                 Rutas web (HTML)
│   └── api.php                 Rutas API (JSON)
├── storage/                    Sesiones, logs y archivos generados
├── Dockerfile.render           Imagen Docker para Render
└── render.yaml                 Configuración del servicio en Render
```
