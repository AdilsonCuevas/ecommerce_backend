# Ecommerce Backend API

API RESTful para tienda virtual construida con Laravel 12.0 y autenticación JWT.

## Descripción

Backend completo para una tienda virtual con gestión de usuarios, productos, carrito de compras y pedidos. Implementa una API RESTful con recursos anidados y autenticación mediante JWT.

## Requisitos

- **PHP**: 8.2+
- **Composer**: última versión
- **PostgreSQL**: 14+ (o SQLite para desarrollo local)
- **Extensiones PHP**: `pdo_pgsql`, `mbstring`, `openssl`, `json`, `curl`

## Instalación

```bash
# 1. Clonar el repositorio
git clone <repo-url> backend
cd backend

# 2. Instalar dependencias
composer install

# 3. Copiar archivo de configuración
cp .env.example .env

# 4. Generar clave de aplicación
php artisan key:generate

# 5. Configurar base de datos en .env
# Luego ejecutar migraciones
php artisan migrate

# 6. (Opcional) Poblar base de datos
php artisan db:seed

# 7. Iniciar servidor de desarrollo
php artisan serve
```

## Configuración

### Variables de entorno importantes (`.env`)

```env
# App
APP_NAME="Ecommerce API"
APP_URL=http://localhost:8000
APP_ENV=local
APP_DEBUG=true

# Database - PostgreSQL
DB_CONNECTION=pgsql
DB_HOST=127.0.0.1
DB_PORT=5432
DB_DATABASE=ecommerce
DB_USERNAME=postgres
DB_PASSWORD=secret

# JWT Configuration
JWT_SECRET=your-jwt-secret-key
JWT_TTL=60
JWT_TTL_REFRESH=20160
```

### Generar clave JWT

```bash
php artisan jwt:secret
```

## Estructura del Proyecto

```
backend/
├── app/
│   ├── Http/
│   │   ├── Controllers/      # Controladores API
│   │   ├── Middleware/       # Auth, CORS, etc.
│   │   └── Requests/         # Request validation
│   ├── Models/               # Modelos Eloquent
│   ├── Services/            # Lógica de negocio
│   ├── Repositories/         # Patrón Repository
│   └── Traits/               # Traits reutilizables
├── config/                   # Configuración Laravel
├── database/
│   ├── migrations/           # Migraciones BD
│   ├── seeders/              # Seeders datos iniciales
│   └── factories/            # Factories para testing
├── routes/
│   ├── api.php              # Rutas API v1
│   └── web.php              # Rutas web
└── tests/                    # Pruebas PHPUnit
```

### Carpetas principales

| Carpeta | Descripción |
|---------|-------------|
| `app/Http/Controllers` | Controladores REST para auth, products, cart, orders |
| `app/Models` | Modelos Eloquent (User, Product, Order, CartItem) |
| `app/Services` | Lógica de negocio (AuthService, ProductService) |
| `app/Repositories` | Acceso a datos (ProductRepository, OrderRepository) |
| `database/migrations` | Definición de tablas y relaciones |

## API Endpoints

### Autenticación

| Método | Endpoint | Descripción |
|--------|----------|-------------|
| POST | `/api/v1/auth/register` | Registrar nuevo usuario |
| POST | `/api/v1/auth/login` | Iniciar sesión, obtener token |
| GET | `/api/v1/auth/me` | Obtener usuario autenticado |
| POST | `/api/v1/auth/logout` | Cerrar sesión (invalidar token) |
| POST | `/api/v1/auth/refresh` | Refrescar token JWT |

### Productos

| Método | Endpoint | Descripción |
|--------|----------|-------------|
| GET | `/api/v1/products` | Listar productos (paginación) |
| GET | `/api/v1/products/{id}` | Ver detalle de producto |
| POST | `/api/v1/products` | Crear producto (admin) |
| PUT | `/api/v1/products/{id}` | Actualizar producto (admin) |
| DELETE | `/api/v1/products/{id}` | Eliminar producto (admin) |

### Carrito

| Método | Endpoint | Descripción |
|--------|----------|-------------|
| GET | `/api/v1/cart` | Ver contenido del carrito |
| POST | `/api/v1/cart/items` | Agregar item al carrito |
| PUT | `/api/v1/cart/items/{id}` | Actualizar cantidad |
| DELETE | `/api/v1/cart/items/{id}` | Eliminar item |
| DELETE | `/api/v1/cart` | Vaciar carrito |

### Pedidos

| Método | Endpoint | Descripción |
|--------|----------|-------------|
| GET | `/api/v1/orders` | Listar pedidos usuario |
| GET | `/api/v1/orders/{id}` | Ver detalle pedido |
| POST | `/api/v1/orders` | Crear pedido desde carrito |
| PUT | `/api/v1/orders/{id}/cancel` | Cancelar pedido |

### Admin

| Método | Endpoint | Descripción |
|--------|----------|-------------|
| GET | `/api/v1/admin/products` | Gestión de productos |
| GET | `/api/v1/admin/orders` | Gestión de pedidos |
| GET | `/api/v1/admin/users` | Gestión de usuarios |

## Autenticación

### Obtener Token JWT

```bash
# Request
curl -X POST http://localhost:8000/api/v1/auth/login \
  -H "Content-Type: application/json" \
  -d '{"email":"user@example.com","password":"password"}'

# Response
{
  "access_token": "eyJ0eXAiOiJKV1QiLCJhbGc...",
  "token_type": "bearer",
  "expires_in": 60
}
```

### Usar Token en Requests

```bash
# Include Authorization header
curl -X GET http://localhost:8000/api/v1/products \
  -H "Authorization: Bearer eyJ0eXAiOiJKV1QiLCJhbGc..."

# WithAccept JSON
curl -X GET http://localhost:8000/api/v1/products \
  -H "Authorization: Bearer eyJ0eXAiOiJKV1QiLCJhbGc..." \
  -H "Accept: application/json"
```

### Ejemplo: Agregar al Carrito

```bash
curl -X POST http://localhost:8000/api/v1/cart/items \
  -H "Authorization: Bearer eyJ0eXAiOiJKV1QiLCJhbGc..." \
  -H "Content-Type: application/json" \
  -d '{"product_id":1,"quantity":2}'
```

## Testing

```bash
# Ejecutar todos los tests
php artisan test

# Ejecutar tests unitarios
./vendor/bin/phpunit --testsuite Unit

# Ejecutar tests de Feature
./vendor/bin/phpunit --testsuite Feature
```

## Comandos Artisan Útiles

| Comando | Descripción |
|---------|-------------|
| `php artisan serve` | Iniciar servidor desarrollo |
| `php artisan migrate` | Ejecutar migraciones |
| `php artisan migrate:rollback` | Revertir última migración |
| `php artisan db:seed` | Ejecutar seeders |
| `php artisan make:model Product` | Crear modelo |
| `php artisan make:controller ProductController` | Crear controlador |
| `php artisan make:migration create_products_table` | Crear migración |
| `php artisan route:list` | Listar rutas registradas |
| `php artisan config:cache` | Cachear configuración |
| `php artisan cache:clear` | Limpiar cache |
| `php artisan jwt:secret` | Generar clave JWT |

## Notas

- La API usa prefijo `v1` para versionamiento: `/api/v1/...`
- Todos los endpoints de usuario requieren header `Authorization: Bearer <token>`
- Los endpoints admin requieren rol de administrador
- Los errores retornan estructura JSON con códigos de estado HTTP
- La paginación usa query params `?page=1&per_page=15`
- Los timestamps están en formato ISO 8601
