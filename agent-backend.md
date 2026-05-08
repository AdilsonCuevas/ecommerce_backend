# AGENT-BACKEND.md - Laravel 12.0

## Stack

- **Framework**: Laravel 12.0
- **Base de datos**: PostgreSQL con UUIDs
- **ORM**: Eloquent
- **Autenticación**: JWT (tymon/jwt-auth)
- **Validación**: Form Requests
- **API**: RESTful con recursos anidados

---

## Convenciones de Código

### Naming

| Tipo | Ejemplo |
|------|---------|
| Controladores | `ProductController`, `OrderController` |
| Modelos | `Product`, `Order`, `User` |
| Tablas | `products`, `orders`, `users` |
| Migraciones | `YYYY_MM_DD_HHMMSS_create_products_table.php` |
| Rutas | `Route::resource('products', ProductController::class)` |
| Servicios | `ProductService` |
| Repositorios | `ProductRepository` |
| Recursos API | `ProductResource` |
| Form Requests | `StoreProductRequest` |

### Código

- **Indentación**: 4 espacios
- **Estándar**: PSR-12
- **Type hints**: Obligatorio en todos los métodos
- **DocBlocks**: En métodos públicos
- **Dependencias**: Usar inyección de constructores

### API

- Prefijo: `/api/v1/`
- Recursos anidados: `/orders/{id}/items`
- IDs en responses: `base64_encode($uuid)`

---

## Patrones

### Repository Pattern

```php
// app/Repositories/BaseRepository.php
abstract class BaseRepository
{
    protected $model;

    public function __construct(Model $model)
    {
        $this->model = $model;
    }

    public function find(string $id): ?Model
    {
        return $this->model->where('uuid', $id)->first();
    }

    public function all(): Collection
    {
        return $this->model->all();
    }
}

// app/Repositories/ProductRepository.php
class ProductRepository extends BaseRepository
{
    protected $model = Product::class;

    public function findActive(): Collection
    {
        return $this->model->where('is_active', true)->get();
    }

    public function findByCategory(string $categoryId): Collection
    {
        return $this->model->where('category_uuid', $categoryId)->get();
    }
}
```

### Service Layer

```php
// app/Services/ProductService.php
class ProductService
{
    public function __construct(
        private ProductRepository $repository
    ) {}

    public function getCatalog(): Collection
    {
        return $this->repository->findActive();
    }

    public function getById(string $id): ?Product
    {
        return $this->repository->find($id);
    }

    public function updateStock(string $id, int $quantity): void
    {
        $product = $this->repository->find($id);
        $product->decrement('stock', $quantity);
    }
}
```

### API Resource

```php
// app/Http/Resources/ProductResource.php
class ProductResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => base64_encode($this->uuid),
            'name' => $this->name,
            'description' => $this->description,
            'price' => $this->price,
            'stock' => $this->stock,
            'image_url' => $this->image_url,
            'category' => new CategoryResource($this->whenLoaded('category')),
            'created_at' => $this->created_at->toIso8601String(),
        ];
    }
}
```

### Form Request

```php
// app/Http/Requests/StoreProductRequest.php
class StoreProductRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->user()->is_admin ?? false;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'price' => ['required', 'numeric', 'min:0'],
            'stock' => ['required', 'integer', 'min:0'],
            'category_id' => ['required', 'uuid'],
            'image_url' => ['nullable', 'url'],
        ];
    }
}
```

### Middleware de Auth

```php
// routes/api.php
Route::middleware(['auth:api', 'role:admin'])->group(function () {
    Route::apiResource('admin/products', ProductController::class);
    Route::apiResource('admin/orders', OrderController::class);
});

Route::middleware(['auth:api'])->group(function () {
    Route::get('/cart', [CartController::class, 'index']);
    Route::post('/cart/items', [CartController::class, 'addItem']);
});
```

### Model con UUID

```php
// app/Models/Product.php
class Product extends Model
{
    use HasUuid;

    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'uuid',
        'name',
        'description',
        'price',
        'stock',
        'is_active',
        'category_uuid',
        'image_url',
    ];

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class, 'category_uuid', 'uuid');
    }
}
```

---

## Prohibiciones

- **NO** exponer UUIDs directamente en URLs
- **NO** usar `DB::raw()` sin sanitización
- **NO** lógica de negocio en Controladores
- **NO** respuestas JSON fuera de Resources
- **NO** validar en controladores (usar Form Requests)
- **NO** queries complejas en modelos (usar scopes o repositorios)
- **NO** retorno de modelos directamente en API

---

## Estructura del Proyecto

```
backend/
├── app/
│   ├── Http/
│   │   ├── Controllers/
│   │   │   └── Api/
│   │   │       └── V1/
│   │   │           ├── ProductController.php
│   │   │           ├── OrderController.php
│   │   │           ├── CartController.php
│   │   │           └── AuthController.php
│   │   ├── Middleware/
│   │   │   └── RoleMiddleware.php
│   │   ├── Requests/
│   │   │   ├── StoreProductRequest.php
│   │   │   └── UpdateProductRequest.php
│   │   └── Resources/
│   │       ├── ProductResource.php
│   │       └── OrderResource.php
│   ├── Models/
│   │   ├── Product.php
│   │   ├── Order.php
│   │   ├── Category.php
│   │   └── User.php
│   ├── Services/
│   │   ├── ProductService.php
│   │   ├── OrderService.php
│   │   └── CartService.php
│   ├── Repositories/
│   │   ├── BaseRepository.php
│   │   ├── ProductRepository.php
│   │   └── OrderRepository.php
│   └── Traits/
│       └── HasUuid.php
├── database/
│   └── migrations/
│       ├── 2024_01_01_000001_create_products_table.php
│       ├── 2024_01_01_000002_create_categories_table.php
│       └── 2024_01_01_000003_create_orders_table.php
├── routes/
│   └── api.php
└── tests/
    └── Feature/
        └── ProductApiTest.php
```

---

## Rutas API

### Auth

| Method | Endpoint | Descripción |
|--------|----------|-------------|
| POST | `/api/v1/auth/register` | Registro de usuario |
| POST | `/api/v1/auth/login` | Login, retorna JWT |
| POST | `/api/v1/auth/logout` | Logout (requiere auth) |
| GET | `/api/v1/auth/me` | Usuario actual (requiere auth) |

### Products

| Method | Endpoint | Descripción | Auth |
|--------|----------|-------------|------|
| GET | `/api/v1/products` | Listar productos | No |
| GET | `/api/v1/products/{id}` | Ver producto | No |
| POST | `/api/v1/admin/products` | Crear producto | Admin |
| PUT | `/api/v1/admin/products/{id}` | Actualizar producto | Admin |
| DELETE | `/api/v1/admin/products/{id}` | Eliminar producto | Admin |

### Categories

| Method | Endpoint | Descripción | Auth |
|--------|----------|-------------|------|
| GET | `/api/v1/categories` | Listar categorías | No |
| POST | `/api/v1/admin/categories` | Crear categoría | Admin |

### Cart

| Method | Endpoint | Descripción | Auth |
|--------|----------|-------------|------|
| GET | `/api/v1/cart` | Ver carrito | Sí |
| POST | `/api/v1/cart/items` | Agregar item | Sí |
| PUT | `/api/v1/cart/items/{id}` | Actualizar cantidad | Sí |
| DELETE | `/api/v1/cart/items/{id}` | Eliminar item | Sí |
| DELETE | `/api/v1/cart` | Vaciar carrito | Sí |

### Orders

| Method | Endpoint | Descripción | Auth |
|--------|----------|-------------|------|
| GET | `/api/v1/orders` | Mis pedidos | Sí |
| GET | `/api/v1/orders/{id}` | Ver pedido | Sí |
| POST | `/api/v1/orders` | Crear pedido | Sí |
| GET | `/api/v1/admin/orders` | Todos los pedidos | Admin |
| PUT | `/api/v1/admin/orders/{id}` | Actualizar estado | Admin |

---

## Testing

- **Unit**: Tests de servicios y repositorios
- **Feature**: Tests de endpoints API
- **Tool**: PHPUnit (incluido en Laravel)
- **Coverage**: Mínimo 70%

```bash
php artisan test
php artisan test --filter=ProductTest
php artisan test --filter=ProductApiTest
```

### Reglas

- Todo feature nuevo requiere tests
- Tests deben ser idempotentes
- Coverage se revisa en PR
- Usar factories para datos de prueba

---

## Estilo de Commits

```
feat: add product listing endpoint
fix: cart total calculation
refactor: extract product repository
docs: update API endpoints
test: add product filter tests
chore: update dependencies
```

---

## Notas

- IDs en URLs: usar base64 del UUID
- Stock se decrementa al confirmar orden
- Solo admins pueden acceder a `/admin/*`
- JWT expira en 1 hora (configurable)
- Siempre usar Resources para responses