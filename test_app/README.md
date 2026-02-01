# Test App - Laravel API avec Docker Compose

Guide complet pour utiliser l'image Laravel API publiée sur GitHub Container Registry.

---

## 📋 Table des matières

1. [Prérequis](#prérequis)
2. [Récupération de l'image](#récupération-de-limage)
3. [Configuration initiale](#configuration-initiale)
4. [Démarrage de l'application](#démarrage-de-lapplication)
5. [Post-installation](#post-installation)
6. [Utilisation de l'API](#utilisation-de-lapi)
7. [Gestion quotidienne](#gestion-quotidienne)
8. [Troubleshooting](#troubleshooting)

---

## 🔧 Prérequis

- Docker 20.10+
- Docker Compose 2.0+
- Git (optionnel)
- curl ou Postman pour tester l'API

Vérifier les versions :
```bash
docker --version
docker-compose --version
```

---

## 📦 Récupération de l'image

### Option 1 : Image publique

Si l'image est publique sur GitHub Container Registry :

```bash
docker pull ghcr.io/richmondnda/laravel_example-api:latest
```

### Option 2 : Image privée (authentification requise)

1. **Créer un Personal Access Token (PAT)** sur GitHub :
   - Aller sur : https://github.com/settings/tokens/new
   - Sélectionner : `read:packages`
   - Générer le token

2. **Se connecter à GHCR** :
```bash
echo "YOUR_GITHUB_TOKEN" | docker login ghcr.io -u richmondnda --password-stdin
```

3. **Pull l'image** :
```bash
docker pull ghcr.io/richmondnda/laravel_example-api:latest
```

### Vérifier l'image

```bash
# Lister les images téléchargées
docker images | grep laravel_example-api

# Inspecter l'image
docker inspect ghcr.io/richmondnda/laravel_example-api:latest
```

---

## ⚙️ Configuration initiale

### 1. Préparer le fichier docker-compose.yml

Le fichier `docker-compose.yml` est déjà configuré avec :
- **app** : Laravel API (port 8080)
- **mysql** : MySQL 8.4 (port 3306)
- **postgres** : PostgreSQL 16 (port 5432)
- **redis** : Redis Alpine (port 6379)

### 2. Variables d'environnement importantes

Ouvrir `docker-compose.yml` et vérifier/modifier :

```yaml
APP_KEY: base64:sZ29iOiPlmDGPK9s2031K2qo94SqnXKRIX2bdV/A/RI=  # ✅ Déjà configurée
DB_CONNECTION: mysql        # ou 'pgsql' pour PostgreSQL
DB_HOST: mysql             # ou 'postgres'
DB_DATABASE: laravel
DB_USERNAME: laravel
DB_PASSWORD: secret        # ⚠️ Changer en production
```

**Important** : En production, changez tous les mots de passe !

---

## 🚀 Démarrage de l'application

### Étape 1 : Lancer les services

```bash
cd test_app

# Démarrer tous les conteneurs en arrière-plan
docker-compose up -d
```

### Étape 2 : Vérifier le statut

```bash
# Voir l'état des conteneurs
docker-compose ps

# Tous doivent être "Up" ou "healthy"
```

**Résultat attendu** :
```
NAME               STATUS
laravel-api        Up
laravel-mysql      Up (healthy)
laravel-postgres   Up (healthy)
laravel-redis      Up (healthy)
```

### Étape 3 : Suivre les logs

```bash
# Voir les logs en temps réel
docker-compose logs -f

# Voir uniquement les logs de l'app
docker-compose logs -f app
```

Attendre le message : `Server running on [http://0.0.0.0:80]`

---

## 🔨 Post-installation

### 1. Lancer les migrations

**Obligatoire** pour créer les tables de base de données :

```bash
docker-compose exec app php artisan migrate --force
```

**Sortie attendue** :
```
INFO  Running migrations.

0001_01_01_000000_create_users_table ......... DONE
2026_01_21_055534_create_posts_table ......... DONE
[...]
```

### 2. (Optionnel) Seeder les données de test

```bash
docker-compose exec app php artisan db:seed --force
```

### 3. (Optionnel) Créer un cache de configuration

Pour améliorer les performances :

```bash
docker-compose exec app php artisan config:cache
docker-compose exec app php artisan route:cache
```

### 4. Vérifier que l'API répond

```bash
curl http://localhost:8080/api/posts
```

Si vous obtenez une réponse JSON, tout fonctionne ! ✅

---

## 🌐 Utilisation de l'API

### Endpoints disponibles

#### 📖 Posts (publics)

```bash
# Lister tous les posts
curl http://localhost:8080/api/posts

# Voir un post spécifique
curl http://localhost:8080/api/posts/1
```

#### 🔐 Authentification

**S'inscrire** :
```bash
curl -X POST http://localhost:8080/api/register \
  -H "Content-Type: application/json" \
  -d '{
    "name": "John Doe",
    "email": "john@example.com",
    "password": "password123",
    "password_confirmation": "password123"
  }'
```

**Se connecter** :
```bash
curl -X POST http://localhost:8080/api/login \
  -H "Content-Type: application/json" \
  -d '{
    "email": "john@example.com",
    "password": "password123"
  }'
```

Vous recevrez un `token` dans la réponse. Copiez-le !

#### ✍️ Posts (authentifiés)

**Créer un post** :
```bash
curl -X POST http://localhost:8080/api/posts \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer YOUR_TOKEN_HERE" \
  -d '{
    "title": "Mon premier post",
    "content": "Contenu du post"
  }'
```

**Modifier un post** :
```bash
curl -X PUT http://localhost:8080/api/posts/1 \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer YOUR_TOKEN_HERE" \
  -d '{
    "title": "Titre modifié",
    "content": "Contenu modifié"
  }'
```

**Supprimer un post** :
```bash
curl -X DELETE http://localhost:8080/api/posts/1 \
  -H "Authorization: Bearer YOUR_TOKEN_HERE"
```

### 📚 Documentation complète

Accédez à la documentation Swagger/OpenAPI (si Scramble est installé) :
```
http://localhost:8080/docs/api
```

---

## 🛠️ Gestion quotidienne

### Arrêter l'application

```bash
# Arrêter tous les conteneurs
docker-compose stop

# Arrêter et supprimer les conteneurs (garde les volumes/données)
docker-compose down
```

### Redémarrer l'application

```bash
# Redémarrer tous les services
docker-compose restart

# Redémarrer uniquement l'app
docker-compose restart app
```

### Voir les logs

```bash
# Logs de tous les services
docker-compose logs -f

# Logs d'un service spécifique
docker-compose logs -f app
docker-compose logs -f mysql

# Voir les 100 dernières lignes
docker-compose logs --tail=100 app
```

### Accéder au conteneur

```bash
# Shell dans le conteneur app
docker-compose exec app bash

# Puis vous pouvez exécuter des commandes Laravel
php artisan tinker
php artisan route:list
php artisan migrate:status
```

### Mettre à jour l'image

Quand une nouvelle version est publiée sur GHCR :

```bash
# Pull la nouvelle image
docker-compose pull app

# Recréer le conteneur avec la nouvelle image
docker-compose up -d --force-recreate app

# Vérifier la version
docker-compose exec app php artisan --version
```

## 📦 Services inclus

- **app** (port 8080) - Laravel API
- **mysql** (port 3306) - Base de données MySQL 8.4
- **postgres** (port 5432) - Base de données PostgreSQL 16
- **redis** (port 6379) - Cache et sessions

## 🔧 Commandes utiles

```bash
# Arrêter les services
docker-compose down

# Arrêter et supprimer les volumes
docker-compose down -v

# Reconstruire et redémarrer
docker-compose up -d --force-recreate

# Accéder au conteneur de l'app
docker-compose exec app bash

# Voir les logs d'un service spécifique
docker-compose logs -f mysql

# Voir l'état des services
docker-compose ps
```

## 🗄️ Changer de base de données

### Utiliser PostgreSQL au lieu de MySQL

Modifiez les variables d'environnement dans `docker-compose.yml` :

```yaml
DB_CONNECTION: pgsql
DB_HOST: postgres
DB_PORT: 5432
```

## 🔑 Variables d'environnement

Vous pouvez personnaliser les variables dans le fichier `.env` :

- `APP_PORT` - Port de l'application (défaut: 8080)
- `MYSQL_PORT` - Port MySQL (défaut: 3306)
- `POSTGRES_PORT` - Port PostgreSQL (défaut: 5432)
- `REDIS_PORT` - Port Redis (défaut: 6379)

---

## 📊 Accès aux bases de données

### MySQL (base par défaut)

**Via Docker Compose** :
```bash
docker-compose exec mysql mysql -ularavel -psecret laravel
```

**Via client local** :
```bash
mysql -h 127.0.0.1 -P 3306 -ularavel -psecret laravel
```

**Connexion avec GUI** (DBeaver, MySQL Workbench, etc.) :
- Host: `127.0.0.1`
- Port: `3306`
- User: `laravel`
- Password: `secret`
- Database: `laravel`

### PostgreSQL (optionnel)

**Via Docker Compose** :
```bash
docker-compose exec postgres psql -U laravel -d laravel
```

**Via client local** :
```bash
psql -h 127.0.0.1 -p 5432 -U laravel -d laravel
```

**Pour utiliser PostgreSQL au lieu de MySQL** :

Modifier dans `docker-compose.yml` :
```yaml
DB_CONNECTION: pgsql
DB_HOST: postgres
DB_PORT: 5432
```

Puis redémarrer :
```bash
docker-compose restart app
docker-compose exec app php artisan migrate --force
```

### Redis (cache et sessions)

**Via Docker Compose** :
```bash
docker-compose exec redis redis-cli
```

**Via client local** :
```bash
redis-cli -h 127.0.0.1 -p 6379
```

**Commandes Redis utiles** :
```bash
# Dans redis-cli
PING           # Vérifier la connexion
KEYS *         # Lister toutes les clés
FLUSHALL       # Vider le cache (⚠️ danger)
```

---

## 🐛 Troubleshooting

### Problème : L'image ne se télécharge pas

**Erreur** : `denied: permission denied` ou `not found`

**Solution** :
1. Vérifier que l'image est publique sur GitHub
2. Ou s'authentifier avec un token :
```bash
echo "YOUR_GITHUB_TOKEN" | docker login ghcr.io -u richmondnda --password-stdin
docker pull ghcr.io/richmondnda/laravel_example-api:latest
```

### Problème : Le conteneur redémarre en boucle

**Vérifier** :
```bash
docker-compose ps
docker-compose logs app
```

**Causes possibles** :
1. **APP_KEY manquante ou invalide**
   ```bash
   # Générer une nouvelle clé
   docker-compose exec app php artisan key:generate --show
   # Puis mettre à jour dans docker-compose.yml
   ```

2. **Erreur de connexion à la base de données**
   ```bash
   # Vérifier que MySQL est healthy
   docker-compose ps mysql
   
   # Tester la connexion
   docker-compose exec app php artisan tinker
   >>> DB::connection()->getPdo();
   ```

3. **Permissions de fichiers**
   ```bash
   docker-compose exec app chmod -R 755 storage bootstrap/cache
   docker-compose exec app chown -R sail:sail storage bootstrap/cache
   ```

### Problème : Les migrations échouent

**Erreur** : `SQLSTATE[HY000] [2002] Connection refused`

**Solution** :
```bash
# Attendre que MySQL soit vraiment prêt (healthy)
docker-compose ps mysql

# Si pas healthy, regarder les logs
docker-compose logs mysql

# Réessayer les migrations
docker-compose exec app php artisan migrate --force
```

### Problème : Port déjà utilisé

**Erreur** : `bind: address already in use`

**Solution** : Modifier les ports dans `docker-compose.yml`

```yaml
services:
  app:
    ports:
      - "8081:80"  # Au lieu de 8080:80
  mysql:
    ports:
      - "3307:3306"  # Au lieu de 3306:3306
```

### Problème : Erreur 500 sur l'API

**Vérifier** :
```bash
# Voir les logs Laravel
docker-compose logs -f app

# Mode debug (temporairement)
docker-compose exec app sed -i 's/APP_DEBUG=false/APP_DEBUG=true/' .env
docker-compose restart app

# Puis tester l'API pour voir l'erreur détaillée
curl http://localhost:8080/api/posts
```

### Problème : Cache problématique

**Solution** :
```bash
# Vider tous les caches
docker-compose exec app php artisan cache:clear
docker-compose exec app php artisan config:clear
docker-compose exec app php artisan route:clear
docker-compose exec app php artisan view:clear

# Redémarrer
docker-compose restart app
```

### Problème : Espace disque insuffisant

**Nettoyer Docker** :
```bash
# Supprimer les images inutilisées
docker image prune -a

# Supprimer tout (⚠️ attention)
docker system prune -a --volumes
```

---

## 🔒 Sécurité en production

⚠️ **Avant de déployer en production** :

1. **Changer tous les mots de passe** dans `docker-compose.yml` :
   - `DB_PASSWORD`
   - `MYSQL_ROOT_PASSWORD`
   - `POSTGRES_PASSWORD`

2. **Générer une vraie APP_KEY** :
   ```bash
   docker-compose exec app php artisan key:generate --show
   ```

3. **Désactiver le debug** :
   ```yaml
   APP_DEBUG: "false"
   APP_ENV: production
   ```

4. **Utiliser HTTPS** (avec un reverse proxy comme Nginx ou Traefik)

5. **Limiter les ports exposés** (ne pas exposer MySQL/PostgreSQL publiquement)

6. **Sauvegardes régulières** :
   ```bash
   # Backup MySQL
   docker-compose exec mysql mysqldump -ularavel -psecret laravel > backup.sql
   
   # Backup PostgreSQL
   docker-compose exec postgres pg_dump -U laravel laravel > backup.sql
   ```

---

## 📞 Support

Pour toute question ou problème :
- **GitHub Issues** : https://github.com/richmondnda/laravel_example-api/issues
- **Documentation Laravel** : https://laravel.com/docs
- **Documentation Docker** : https://docs.docker.com

---

## 📝 Résumé des commandes essentielles

```bash
# Démarrer
docker-compose up -d

# Voir l'état
docker-compose ps

# Migrations
docker-compose exec app php artisan migrate --force

# Logs
docker-compose logs -f app

# Arrêter
docker-compose down

# Mettre à jour
docker-compose pull
docker-compose up -d --force-recreate

# Accéder au shell
docker-compose exec app bash

# Tester l'API
curl http://localhost:8080/api/posts
```

---

**Dernière mise à jour** : Février 2026
