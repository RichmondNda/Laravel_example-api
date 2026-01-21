# 🚀 Laravel API - Documentation Complète

API REST moderne et production-ready construite avec Laravel 11, Laravel Data, Sanctum et best practices.

## ✨ Fonctionnalités Implémentées

### 🔐 **Authentification & Sécurité**
- ✅ Laravel Sanctum pour l'authentification API par tokens
- ✅ Endpoints register/login/logout
- ✅ Policies pour contrôler les permissions (CRUD posts)
- ✅ Middleware auth:sanctum sur les routes protégées

### 📊 **Data Management**
- ✅ **Laravel Data (Spatie)** - DTOs typés avec validation intégrée
- ✅ Form Requests transformés en Data Objects
- ✅ API Resources avec Laravel Data
- ✅ Type safety et autocomplétion IDE

### 🔍 **Query & Filters**
- ✅ **Laravel Query Builder (Spatie)** pour filtrage avancé
- ✅ Filtres : `filter[user_id]`, `filter[title]`, `filter[published]`
- ✅ Tri : `sort=-created_at`, `sort=title`
- ✅ Includes : `include=user`
- ✅ Scopes réutilisables (`published`, `draft`)

### 💾 **Base de Données**
- ✅ **Soft Deletes** sur les posts
- ✅ Relations Eloquent optimisées
- ✅ Migrations et factories complètes

### ⚡ **Performance**
- ✅ **Cache Strategy** avec invalidation automatique
- ✅ Cache sur les listings (5 min TTL)
- ✅ N+1 queries prevention
- ✅ Eager loading optimisé

### 🧪 **Tests**
- ✅ **15 tests Pest** (Feature tests)
- ✅ Tests authentification completsTests CRUD posts
- ✅ Tests policies et autorisations
- ✅ Tests filtres et recherche

### 📚 **Documentation**
- ✅ **Scramble** - Documentation OpenAPI interactive
- ✅ Accès : `http://localhost:8000/docs/api`

### 🛡️ **Gestion des Erreurs**
- ✅ Handler global avec réponses JSON standardisées
- ✅ Format cohérent : `{success, message, error/errors}`
- ✅ Messages d'erreur personnalisés en français

### 🏗️ **Architecture**
- ✅ **Service Layer** - Logique métier séparée
- ✅ Controllers minimalistes
- ✅ Repository pattern via services
- ✅ Clean Code & SOLID principles

---

## 📦 Installation

```bash
# Cloner & installer
composer install

# Configuration
cp .env.example .env
php artisan key:generate

# Base de données
php artisan migrate:fresh --seed

# Lancer le serveur
php artisan serve
```

---

## 🔑 Endpoints API

### **Authentification**

#### Register
```bash
POST /api/register
Content-Type: application/json

{
  "name": "John Doe",
  "email": "john@example.com",
  "password": "password123",
  "password_confirmation": "password123"
}
```

#### Login
```bash
POST /api/login
Content-Type: application/json

{
  "email": "john@example.com",
  "password": "password123"
}

# Response
{
  "success": true,
  "user": {...},
  "token": "1|xxxxx..."
}
```

#### Logout
```bash
POST /api/logout
Authorization: Bearer {token}
```

#### Get Current User
```bash
GET /api/me
Authorization: Bearer {token}
```

---

### **Posts** (CRUD)

#### Lister les posts
```bash
GET /api/posts

# Avec filtres
GET /api/posts?filter[user_id]=1
GET /api/posts?filter[title]=Laravel
GET /api/posts?filter[published]=true

# Avec tri
GET /api/posts?sort=-created_at
GET /api/posts?sort=title

# Avec includes
GET /api/posts?include=user

# Combiné
GET /api/posts?filter[user_id]=1&sort=-created_at&include=user
```

#### Voir un post
```bash
GET /api/posts/{id}
```

#### Créer un post (authentifié)
```bash
POST /api/posts
Authorization: Bearer {token}
Content-Type: application/json

{
  "title": "Mon post",
  "content": "Contenu du post (min 10 caractères)",
  "user_id": 1,
  "published_at": "2026-01-21 12:00:00" // optionnel
}
```

#### Modifier un post (propriétaire seulement)
```bash
PUT /api/posts/{id}
Authorization: Bearer {token}
Content-Type: application/json

{
  "title": "Titre modifié"
}
```

#### Supprimer un post (propriétaire seulement)
```bash
DELETE /api/posts/{id}
Authorization: Bearer {token}
```

---

## 🧪 Tests

```bash
# Tous les tests
php artisan test

# Tests Feature seulement
php artisan test --testsuite=Feature

# Test spécifique
php artisan test --filter=AuthTest

# Avec coverage
php artisan test --coverage
```

**Résultats actuels : ✅ 15/15 tests passants**

---

## 📁 Structure du Projet

```
app/
├── Data/                          # Laravel Data DTOs
│   ├── PostData.php              # Response DTO
│   ├── UserData.php              # Response DTO
│   ├── StorePostData.php         # Request validation DTO
│   └── UpdatePostData.php        # Request validation DTO
├── Http/
│   ├── Controllers/Api/
│   │   ├── AuthController.php    # Auth endpoints
│   │   └── PostController.php    # Posts CRUD
│   ├── Requests/                  # Form Requests (legacy, peut être supprimé)
│   └── Resources/                 # API Resources (legacy, peut être supprimé)
├── Models/
│   ├── Post.php                   # Eloquent Model + Scopes
│   └── User.php                   # User Model + HasApiTokens
├── Policies/
│   └── PostPolicy.php             # Authorization logic
└── Services/
    └── PostService.php            # Business logic

routes/
├── api.php                        # Routes API
└── web.php                        # Routes web (optionnel)

tests/
├── Feature/
│   ├── AuthTest.php              # Tests authentification
│   └── PostTest.php              # Tests CRUD + filters
└── TestCase.php                  # Base test setup

database/
├── factories/
│   ├── PostFactory.php           # Factory pour tests
│   └── UserFactory.php
└── migrations/
    └── ...                        # Toutes les migrations
```

---

## 🎯 Exemples d'Utilisation

### Workflow complet

```bash
# 1. S'enregistrer
curl -X POST http://localhost:8000/api/register \
  -H "Content-Type: application/json" \
  -d '{
    "name": "Alice",
    "email": "alice@example.com",
    "password": "password123",
    "password_confirmation": "password123"
  }'

# Récupérer le token de la réponse
export TOKEN="1|xxxxx..."

# 2. Créer un post
curl -X POST http://localhost:8000/api/posts \
  -H "Authorization: Bearer $TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "title": "Mon premier article",
    "content": "Contenu de mon article sur Laravel",
    "user_id": 1
  }'

# 3. Lister les posts avec filtres
curl "http://localhost:8000/api/posts?filter[user_id]=1&sort=-created_at"

# 4. Modifier son post
curl -X PUT http://localhost:8000/api/posts/1 \
  -H "Authorization: Bearer $TOKEN" \
  -H "Content-Type: application/json" \
  -d '{"title": "Titre mis à jour"}'

# 5. Supprimer son post
curl -X DELETE http://localhost:8000/api/posts/1 \
  -H "Authorization: Bearer $TOKEN"
```

---

## 🔧 Configuration

### Cache
Le cache est configuré pour 5 minutes sur les listings. Pour modifier :

```php
// app/Services/PostService.php
cache()->remember($cacheKey, 300, function () { // 300 = 5 min
```

### Validation
Modifier les règles dans les Data classes :

```php
// app/Data/StorePostData.php
#[Required, Max(255)]
public string $title,
```

### Permissions
Modifier les policies :

```php
// app/Policies/PostPolicy.php
public function update(User $user, Post $post): bool
{
    return $user->id === $post->user_id;
}
```

---

## 📊 API Responses Format

### Success Response
```json
{
  "id": 1,
  "title": "Mon post",
  "content": "...",
  "user": {
    "id": 1,
    "name": "John",
    "email": "john@example.com"
  },
  "created_at": "2026-01-21 12:00:00"
}
```

### Error Response
```json
{
  "success": false,
  "message": "Validation failed",
  "errors": {
    "title": ["Le titre est obligatoire"],
    "content": ["Le contenu doit contenir au moins 10 caractères"]
  }
}
```

### Paginated Response
```json
{
  "data": [...],
  "links": {
    "first": "http://localhost:8000/api/posts?page=1",
    "last": "http://localhost:8000/api/posts?page=5",
    "prev": null,
    "next": "http://localhost:8000/api/posts?page=2"
  },
  "meta": {
    "current_page": 1,
    "per_page": 15,
    "total": 67
  }
}
```

---

## 🚀 Technologies Utilisées

- **Laravel 11** - Framework PHP
- **Laravel Sanctum** - Authentification API
- **Spatie Laravel Data** - DTOs et validation
- **Spatie Laravel Query Builder** - Filtres avancés
- **Pest PHP** - Framework de tests
- **Scramble** - Documentation OpenAPI
- **SQLite** - Base de données (configurable)

---

## 📈 Prochaines Améliorations Possibles

- [ ] Rate limiting configurable par utilisateur
- [ ] Webhooks pour notifications externes
- [ ] File uploads (images pour posts)
- [ ] API Versioning (`/api/v1`, `/api/v2`)
- [ ] Background jobs avec queues
- [ ] Redis pour cache/sessions
- [ ] Telescope pour debugging
- [ ] Audit trail avec `owen-it/laravel-auditing`

---

## 📝 Notes

- Les routes `/api/posts` (GET) et `/api/posts/{id}` (GET) sont publiques
- Toutes les autres actions nécessitent une authentification
- Seul le propriétaire d'un post peut le modifier/supprimer
- Les posts supprimés sont soft deleted (récupérables)
- La documentation interactive est disponible sur `/docs/api`

---

## 🤝 Contribution

Cette API suit les conventions Laravel et les best practices :
- PSR-12 pour le code style
- Clean Architecture
- SOLID principles
- Test-Driven Development

---

## 📄 Licence

Open source - Utilisation libre

---

**Développé avec ❤️ et Laravel**
