# 🚀 Guide complet : Créer une API Laravel similaire

Guide pas-à-pas pour reproduire cette architecture d'API dans un nouveau projet.

---

## 📋 Prérequis

- PHP 8.2+
- Composer
- Docker Desktop (pour Sail)
- Git

---

## 🏗️ Étape 1 : Créer le projet Laravel

```bash
# Créer un nouveau projet Laravel
composer create-project laravel/laravel mon-api

# Aller dans le dossier
cd mon-api

# Initialiser Git
git init
git add .
git commit -m "Initial commit"
```

---

## 📦 Étape 2 : Installer les packages essentiels

```bash
# Laravel Sanctum (authentification API)
composer require laravel/sanctum

# Spatie Laravel Data (DTOs typés)
composer require spatie/laravel-data

# Spatie Query Builder (filtres et recherche)
composer require spatie/laravel-query-builder

# Scramble (documentation API auto-générée)
composer require --dev dedoc/scramble

# Laravel Sail (Docker)
composer require --dev laravel/sail

# Pest (tests)
composer require --dev pestphp/pest pestphp/pest-plugin-laravel
php artisan pest:install

# Laravel Pint (code formatter)
composer require --dev laravel/pint
```

---

## 🐳 Étape 3 : Configurer Docker avec Sail

```bash
# Installer Sail (choisir mysql + redis)
php artisan sail:install

# Démarrer les conteneurs
./vendor/bin/sail up -d

# Créer un alias permanent
echo "alias sail='./vendor/bin/sail'" >> ~/.bashrc
source ~/.bashrc

# À partir de maintenant, utiliser 'sail' au lieu de './vendor/bin/sail'
```

---

## ⚙️ Étape 4 : Configuration de base

### 4.1 Fichier .env

```bash
# Copier .env.example si nécessaire
cp .env.example .env

# Générer la clé d'application
sail artisan key:generate
```

Modifier `.env` :

```env
APP_NAME="Mon API"
APP_URL=http://localhost

# Base de données (pour Sail)
DB_CONNECTION=mysql
DB_HOST=mysql
DB_PORT=3306
DB_DATABASE=laravel
DB_USERNAME=sail
DB_PASSWORD=password

# Cache avec Redis
CACHE_STORE=redis
CACHE_PREFIX=mon_api

# Redis (pour Sail)
REDIS_HOST=redis
REDIS_PASSWORD=null
REDIS_PORT=6379

# Queue avec Redis
QUEUE_CONNECTION=redis

# Session
SESSION_DRIVER=database
```

### 4.2 Publier les configs

```bash
# Publier la config Sanctum
sail artisan vendor:publish --provider="Laravel\Sanctum\SanctumServiceProvider"

# Publier la config Scramble
sail artisan vendor:publish --tag=scramble-config
```

---

## 🗂️ Étape 5 : Structure de base

### 5.1 Migrations

```bash
# Migration pour Sanctum tokens
sail artisan migrate

# Créer migration pour votre modèle principal (exemple: Product)
sail artisan make:migration create_products_table
```

Exemple de migration (`database/migrations/xxxx_create_products_table.php`) :

```php
Schema::create('products', function (Blueprint $table) {
    $table->id();
    $table->foreignId('user_id')->constrained()->cascadeOnDelete();
    $table->string('name');
    $table->text('description')->nullable();
    $table->decimal('price', 10, 2);
    $table->integer('stock')->default(0);
    $table->boolean('is_active')->default(true);
    $table->timestamps();
    $table->softDeletes();
});
```

### 5.2 Models

```bash
# Créer le modèle avec factory et migration
sail artisan make:model Product -mf
```

```php
// app/Models/Product.php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Product extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'user_id',
        'name',
        'description',
        'price',
        'stock',
        'is_active',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'stock' => 'integer',
        'is_active' => 'boolean',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
```

### 5.3 Data Objects (DTOs)

```bash
# Créer le dossier Data
mkdir -p app/Data
```

```php
// app/Data/ProductData.php
namespace App\Data;

use Spatie\LaravelData\Data;

class ProductData extends Data
{
    public function __construct(
        public int $id,
        public int $user_id,
        public string $name,
        public ?string $description,
        public float $price,
        public int $stock,
        public bool $is_active,
        public string $created_at,
        public string $updated_at,
    ) {}
}
```

```php
// app/Data/StoreProductData.php
namespace App\Data;

use Spatie\LaravelData\Attributes\Validation\Required;
use Spatie\LaravelData\Attributes\Validation\Min;
use Spatie\LaravelData\Data;

class StoreProductData extends Data
{
    public function __construct(
        #[Required, Min(3)]
        public string $name,
        
        public ?string $description,
        
        #[Required, Min(0)]
        public float $price,
        
        #[Required, Min(0)]
        public int $stock,
        
        public bool $is_active = true,
    ) {}
}
```

### 5.4 Services

```bash
mkdir -p app/Services
```

```php
// app/Services/ProductService.php
namespace App\Services;

use App\Data\ProductData;
use App\Data\StoreProductData;
use App\Data\UpdateProductData;
use App\Models\Product;
use Illuminate\Support\Facades\Cache;
use Spatie\LaravelData\PaginatedDataCollection;

class ProductService
{
    public function getAllProducts(int $perPage = 15): PaginatedDataCollection
    {
        $cacheKey = "products_page_{$perPage}_" . request()->fullUrl();
        
        return Cache::remember($cacheKey, 300, function () use ($perPage) {
            return ProductData::collection(
                Product::query()
                    ->with('user')
                    ->latest()
                    ->paginate($perPage)
            );
        });
    }

    public function createProduct(StoreProductData $data): ProductData
    {
        $product = Product::create([
            ...$data->toArray(),
            'user_id' => auth()->id(),
        ]);

        Cache::flush();

        return ProductData::from($product->fresh());
    }

    public function getProduct(Product $product): ProductData
    {
        return ProductData::from($product->load('user'));
    }

    public function updateProduct(Product $product, UpdateProductData $data): ProductData
    {
        $product->update($data->toArray());
        Cache::flush();

        return ProductData::from($product->fresh());
    }

    public function deleteProduct(Product $product): void
    {
        $product->delete();
        Cache::flush();
    }
}
```

### 5.5 Policies

```bash
sail artisan make:policy ProductPolicy --model=Product
```

```php
// app/Policies/ProductPolicy.php
namespace App\Policies;

use App\Models\Product;
use App\Models\User;

class ProductPolicy
{
    public function update(User $user, Product $product): bool
    {
        return $user->id === $product->user_id;
    }

    public function delete(User $user, Product $product): bool
    {
        return $user->id === $product->user_id;
    }
}
```

### 5.6 Controllers

```bash
mkdir -p app/Http/Controllers/Api
sail artisan make:controller Api/ProductController --api
sail artisan make:controller Api/AuthController
```

```php
// app/Http/Controllers/Api/AuthController.php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function register(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|unique:users',
            'password' => 'required|string|min:8|confirmed',
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
        ]);

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'success' => true,
            'message' => 'User registered successfully',
            'user' => $user,
            'token' => $token,
        ], 201);
    }

    public function login(Request $request): JsonResponse
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        $user = User::where('email', $request->email)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['The provided credentials are incorrect.'],
            ]);
        }

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'success' => true,
            'message' => 'Login successful',
            'user' => $user,
            'token' => $token,
        ]);
    }

    public function logout(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'success' => true,
            'message' => 'Logged out successfully',
        ]);
    }

    public function me(Request $request): JsonResponse
    {
        return response()->json([
            'success' => true,
            'user' => $request->user(),
        ]);
    }
}
```

```php
// app/Http/Controllers/Api/ProductController.php
namespace App\Http\Controllers\Api;

use App\Data\ProductData;
use App\Data\StoreProductData;
use App\Data\UpdateProductData;
use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Services\ProductService;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\JsonResponse;
use Spatie\LaravelData\PaginatedDataCollection;

class ProductController extends Controller
{
    use AuthorizesRequests;

    public function __construct(
        protected ProductService $productService
    ) {}

    public function index(): PaginatedDataCollection
    {
        return $this->productService->getAllProducts(15);
    }

    public function store(StoreProductData $data): JsonResponse
    {
        $product = $this->productService->createProduct($data);
        
        return response()->json($product, 201);
    }

    public function show(Product $product): ProductData
    {
        return $this->productService->getProduct($product);
    }

    public function update(UpdateProductData $data, Product $product): ProductData
    {
        $this->authorize('update', $product);
        
        return $this->productService->updateProduct($product, $data);
    }

    public function destroy(Product $product): JsonResponse
    {
        $this->authorize('delete', $product);
        
        $this->productService->deleteProduct($product);
        
        return response()->json([
            'success' => true,
            'message' => 'Product deleted successfully',
        ]);
    }
}
```

---

## 🛣️ Étape 6 : Routes API

```php
// routes/api.php
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\ProductController;
use Illuminate\Support\Facades\Route;

// Public routes
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

// Public read-only
Route::get('/products', [ProductController::class, 'index']);
Route::get('/products/{product}', [ProductController::class, 'show']);

// Protected routes
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/me', [AuthController::class, 'me']);
    
    Route::post('/products', [ProductController::class, 'store']);
    Route::put('/products/{product}', [ProductController::class, 'update']);
    Route::delete('/products/{product}', [ProductController::class, 'destroy']);
});
```

---

## 🚫 Étape 7 : Bloquer les routes web (API only)

```php
// routes/web.php
use Illuminate\Support\Facades\Route;

Route::fallback(function () {
    return response()->json([
        'success' => false,
        'message' => 'Not Found',
        'error' => 'This is an API-only application. Use /api/* endpoints or visit /docs/api for documentation.'
    ], 404);
});
```

---

## 🔧 Étape 8 : Gestion des erreurs globale

```php
// bootstrap/app.php
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\HttpException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        apiPrefix: 'api',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        //
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        // Force JSON responses except for docs
        $exceptions->render(function (NotFoundHttpException $e, Request $request) {
            if ($request->is('docs/*') || $request->is('docs/api') || $request->is('docs/api.json')) {
                return null;
            }
            
            return response()->json([
                'success' => false,
                'message' => 'Not Found',
                'error' => $e->getMessage() ?: 'The requested resource could not be found',
            ], 404);
        });

        $exceptions->render(function (ModelNotFoundException $e, Request $request) {
            if ($request->is('docs/*')) return null;
            
            return response()->json([
                'success' => false,
                'message' => 'Resource not found',
                'error' => 'The requested resource does not exist',
            ], 404);
        });

        $exceptions->render(function (AuthenticationException $e, Request $request) {
            if ($request->is('docs/*')) return null;
            
            return response()->json([
                'success' => false,
                'message' => 'Unauthenticated',
                'error' => $e->getMessage(),
            ], 401);
        });

        $exceptions->render(function (ValidationException $e, Request $request) {
            if ($request->is('docs/*')) return null;
            
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors(),
            ], 422);
        });

        $exceptions->render(function (HttpException $e, Request $request) {
            if ($request->is('docs/*')) return null;
            
            return response()->json([
                'success' => false,
                'message' => $e->getMessage() ?: 'Server Error',
                'error' => $e->getMessage(),
            ], $e->getStatusCode());
        });

        $exceptions->render(function (Throwable $e, Request $request) {
            if ($request->is('docs/*')) return null;
            
            $statusCode = method_exists($e, 'getStatusCode') ? $e->getStatusCode() : 500;
            
            return response()->json([
                'success' => false,
                'message' => 'Server Error',
                'error' => app()->environment('local') ? $e->getMessage() : 'An unexpected error occurred',
            ], $statusCode >= 100 && $statusCode < 600 ? $statusCode : 500);
        });
    })->create();
```

---

## 🧪 Étape 9 : Tests

```bash
# Créer les tests
sail artisan make:test AuthTest
sail artisan make:test ProductTest
```

```php
// tests/Feature/AuthTest.php
<?php

use App\Models\User;

test('user can register', function () {
    $response = $this->postJson('/api/register', [
        'name' => 'Test User',
        'email' => 'test@example.com',
        'password' => 'password123',
        'password_confirmation' => 'password123',
    ]);

    $response->assertStatus(201)
        ->assertJsonStructure([
            'success',
            'message',
            'user',
            'token',
        ]);
});

test('user can login', function () {
    $user = User::factory()->create([
        'password' => bcrypt('password123'),
    ]);

    $response = $this->postJson('/api/login', [
        'email' => $user->email,
        'password' => 'password123',
    ]);

    $response->assertStatus(200)
        ->assertJsonStructure([
            'success',
            'message',
            'user',
            'token',
        ]);
});
```

---

## 🏃 Étape 10 : Exécution

```bash
# Lancer les migrations
sail artisan migrate --seed

# Lancer les tests
sail test

# Démarrer le serveur (déjà actif avec Sail)
# API disponible sur http://localhost

# Voir la documentation
# http://localhost/docs/api
```

---

## 📝 Étape 11 : Configuration finale

### Factories

```php
// database/factories/ProductFactory.php
namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class ProductFactory extends Factory
{
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'name' => fake()->words(3, true),
            'description' => fake()->paragraph(),
            'price' => fake()->randomFloat(2, 10, 1000),
            'stock' => fake()->numberBetween(0, 100),
            'is_active' => fake()->boolean(80),
        ];
    }
}
```

### Seeders

```bash
sail artisan make:seeder ProductSeeder
```

```php
// database/seeders/DatabaseSeeder.php
public function run(): void
{
    // Créer des utilisateurs
    $users = User::factory(5)->create();

    // Créer des produits pour chaque utilisateur
    $users->each(function ($user) {
        Product::factory(10)->create([
            'user_id' => $user->id,
        ]);
    });
}
```

---

## 📚 Commandes utiles

```bash
# Développement
sail up -d                          # Démarrer Docker
sail artisan migrate:fresh --seed  # Reset DB avec données
sail artisan optimize:clear         # Nettoyer les caches
sail test                           # Lancer les tests
sail artisan pail                   # Logs en temps réel

# Créer des ressources
sail artisan make:model NomModel -mf
sail artisan make:controller Api/NomController
sail artisan make:migration create_table_name
sail artisan make:policy NomPolicy --model=NomModel

# Cache
sail artisan cache:clear
sail artisan config:cache
sail artisan route:cache

# Base de données
sail artisan migrate
sail artisan migrate:rollback
sail artisan db:seed
sail mysql                          # Accès MySQL

# Tests & qualité
sail test
sail artisan pint                   # Formatter le code
```

---

## 🎯 Checklist finale

- [ ] Docker Sail configuré et fonctionnel
- [ ] Base de données migrée
- [ ] Sanctum installé pour l'authentification
- [ ] Routes API définies
- [ ] Controllers avec logique métier
- [ ] Data Objects (DTOs) créés
- [ ] Services layer pour la logique
- [ ] Policies pour les autorisations
- [ ] Tests écrits et passants
- [ ] Documentation Scramble accessible
- [ ] Erreurs JSON partout (sauf docs)
- [ ] Cache Redis configuré
- [ ] .env configuré correctement
- [ ] Routes web bloquées (API only)

---

## 📖 Structure finale du projet

```
mon-api/
├── app/
│   ├── Data/
│   │   ├── ProductData.php
│   │   ├── StoreProductData.php
│   │   └── UpdateProductData.php
│   ├── Http/
│   │   └── Controllers/
│   │       └── Api/
│   │           ├── AuthController.php
│   │           └── ProductController.php
│   ├── Models/
│   │   ├── Product.php
│   │   └── User.php
│   ├── Policies/
│   │   └── ProductPolicy.php
│   └── Services/
│       └── ProductService.php
├── database/
│   ├── factories/
│   ├── migrations/
│   └── seeders/
├── routes/
│   ├── api.php
│   └── web.php
├── tests/
│   └── Feature/
│       ├── AuthTest.php
│       └── ProductTest.php
├── .env
├── compose.yaml
└── README.md
```

---

## 🚀 Prêt !

Votre API est maintenant prête avec :
- ✅ Architecture propre et scalable
- ✅ Authentification Sanctum
- ✅ DTOs typés avec Laravel Data
- ✅ Documentation auto-générée
- ✅ Tests automatisés
- ✅ Docker pour l'environnement
- ✅ Cache Redis
- ✅ Gestion d'erreurs robuste

**Accès :**
- API : `http://localhost/api/*`
- Documentation : `http://localhost/docs/api`
