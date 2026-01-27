# Laravel API - Docker Setup Guide 🐳

## ✅ Sail est maintenant configuré !

Vos conteneurs Docker sont prêts avec :
- **PHP 8.5** avec Laravel
- **MySQL 8.4**
- **Redis Alpine**

## 🚀 Quick Start

### Première utilisation

```bash
# 1. Créer l'alias Sail (recommandé)
alias sail='./vendor/bin/sail'

# Ou ajouter dans votre ~/.bashrc ou ~/.zshrc pour le rendre permanent
echo "alias sail='./vendor/bin/sail'" >> ~/.bashrc
source ~/.bashrc

# 2. Les conteneurs sont déjà démarrés !
# Vérifier le statut :
sail ps

# 3. Installer les dépendances (si première fois)
sail composer install

# 4. Générer la clé
sail artisan key:generate

# 5. Lancer les migrations
sail artisan migrate --seed

# 6. Votre API est prête ! 🎉
# http://localhost
```

## 📦 Services inclus

- **PHP 8.3** - Application Laravel
- **MySQL 8.0** - Base de données
- **Redis** - Cache & Queues

## 🛠️ Commandes essentielles

### 🔄 Gestion des conteneurs

```bash
# Démarrer tous les services en arrière-plan
sail up -d

# Démarrer avec les logs visibles
sail up

# Arrêter les conteneurs (conserve les données)
sail down

# Arrêter et supprimer les volumes (⚠️ efface la DB)
sail down -v

# Redémarrer un service spécifique
sail restart mysql
sail restart redis

# Voir le statut de tous les conteneurs
sail ps

# Voir les logs en temps réel
sail logs -f

# Logs d'un service spécifique
sail logs -f laravel.test
sail logs -f mysql
sail logs -f redis

# Rebuild les images (après modification de compose.yaml)
sail build --no-cache
sail up -d --build
```

### 📂 Accès aux conteneurs

```bash
# Shell dans le conteneur PHP
sail shell

# Shell avec un utilisateur spécifique
sail root-shell

# Exécuter une commande dans le conteneur
sail exec laravel.test ls -la

# Accéder à MySQL CLI
sail mysql

# Accéder à MySQL avec une commande SQL directe
sail mysql -e "SHOW DATABASES;"

# Accéder à Redis CLI
sail redis

# Test Redis
sail redis-cli ping
```

### 🎨 Laravel Artisan

```bash
# Migration et seeds
sail artisan migrate
sail artisan migrate:fresh
sail artisan migrate:fresh --seed
sail artisan migrate:rollback
sail artisan migrate:status

# Database
sail artisan db:seed
sail artisan db:wipe

# Cache management
sail artisan cache:clear
sail artisan config:clear
sail artisan route:clear
sail artisan view:clear
sail artisan optimize:clear

# Optimization
sail artisan config:cache
sail artisan route:cache
sail artisan view:cache
sail artisan optimize

# Queue workers
sail artisan queue:work
sail artisan queue:listen
sail artisan queue:restart
sail artisan queue:failed

# Tinker (REPL)
sail artisan tinker

# Créer des ressources
sail artisan make:controller ApiController
sail artisan make:model Product -mf
sail artisan make:migration create_products_table
sail artisan make:seeder ProductSeeder
sail artisan make:request StoreProductRequest
sail artisan make:policy ProductPolicy
```

### 📦 Composer

```bash
# Installer les dépendances
sail composer install

# Ajouter un package
sail composer require spatie/laravel-permission

# Ajouter un package de dev
sail composer require --dev barryvdh/laravel-debugbar

# Mettre à jour les packages
sail composer update

# Mettre à jour un package spécifique
sail composer update laravel/framework

# Dumper l'autoload
sail composer dump-autoload

# Voir les packages installés
sail composer show

# Analyser les dépendances
sail composer why laravel/sanctum
```

### 🧪 Tests

```bash
# Lancer tous les tests
sail test
sail artisan test

# Tests avec coverage
sail artisan test --coverage
sail artisan test --coverage --min=80

# Test d'un fichier spécifique
sail artisan test tests/Feature/PostTest.php

# Test avec un filtre
sail artisan test --filter=test_user_can_create_post

# Tests parallèles
sail artisan test --parallel

# Tests avec Pest
sail pest
sail pest --filter=PostTest
```

### 🗄️ Base de données

```bash
# Exporter la base de données
sail exec mysql mysqldump -u sail -ppassword laravel > backup.sql

# Importer une base de données
sail mysql laravel < backup.sql

# Créer une nouvelle base
sail mysql -e "CREATE DATABASE test_db;"

# Supprimer une base
sail mysql -e "DROP DATABASE test_db;"

# Voir les tables
sail mysql -e "SHOW TABLES;" laravel

# Requête SQL directe
sail mysql -e "SELECT * FROM users LIMIT 5;" laravel

# Accès MySQL Workbench/TablePlus
# Host: 127.0.0.1
# Port: 3306
# User: sail
# Password: password
# Database: laravel
```

### 🔴 Redis

```bash
# Tester la connexion
sail redis ping

# Voir toutes les clés
sail redis keys '*'

# Voir une clé spécifique
sail redis get laravel_cache:posts

# Supprimer toutes les clés
sail redis flushall

# Voir les infos Redis
sail redis info

# Monitor en temps réel
sail redis monitor
```

### 📊 Monitoring & Debug

```bash
# Voir l'utilisation CPU/RAM
sail exec laravel.test top

# Espace disque
sail exec laravel.test df -h

# Processus PHP
sail exec laravel.test ps aux | grep php

# Variables d'environnement
sail exec laravel.test env

# Version PHP et extensions
sail php -v
sail php -m

# Informations phpinfo
sail php -r "phpinfo();" | less

# Logs Laravel en temps réel
sail artisan pail
tail -f storage/logs/laravel.log
```

### 🔧 Maintenance

```bash
# Nettoyer les caches application
sail artisan optimize:clear

# Nettoyer les logs
sail exec laravel.test truncate -s 0 storage/logs/laravel.log

# Nettoyer les sessions
sail artisan session:flush

# Nettoyer les jobs échoués
sail artisan queue:flush

# Optimiser la base de données
sail mysql -e "OPTIMIZE TABLE posts, users;" laravel

# Vérifier les permissions
sail exec laravel.test ls -la storage/
sail exec laravel.test chmod -R 775 storage bootstrap/cache
```

### 🚀 Mise en production (export)

```bash
# Créer une image Docker personnalisée
sail build --no-cache
docker tag sail-8.5/app votre-registry/api:latest
docker push votre-registry/api:latest

# Exporter la base de données
sail exec mysql mysqldump -u sail -ppassword laravel > production-backup.sql

# Copier les fichiers depuis le conteneur
sail cp laravel.test:/var/www/html/storage/app/uploads ./backups/

# Copier vers le conteneur
sail cp ./local-file.txt laravel.test:/var/www/html/storage/
```

## 🌐 Accès aux services

- **API** : http://localhost
- **Documentation API** : http://localhost/docs/api
- **MySQL** : localhost:3306
  - User: `sail`
  - Password: `password`
  - Database: `laravel`
- **Redis** : localhost:6379

## 🔧 Configuration

Tous les paramètres sont dans [.env](.env) :

```env
# Ports personnalisés
APP_PORT=80
FORWARD_DB_PORT=3306
FORWARD_REDIS_PORT=6379

# Base de données
DB_HOST=mysql
DB_DATABASE=laravel
DB_USERNAME=sail
DB_PASSWORD=password

# Cache & Queues
REDIS_HOST=redis
CACHE_STORE=redis
QUEUE_CONNECTION=redis
```

## 🧪 Tests dans Docker

```bash
# Lancer tous les tests
sail test

# Tests avec coverage
sail test --coverage

# Un test spécifique
sail artisan test --filter=PostTest
```

## 🚀 Production

Pour le déploiement en production, utilisez un service comme :
- **Laravel Forge**
- **Laravel Vapor** (serverless)
- **DigitalOcean App Platform**
- **AWS ECS/Fargate**

## 📝 Notes

- Les données MySQL sont persistées dans un volume Docker
- Le code source est monté en volume (hot reload)
- Redis est configuré pour le cache et les queues
- Pas besoin d'installer PHP/MySQL/Redis localement

## 🐛 Dépannage

### Problèmes courants et solutions

```bash
# ❌ Erreur "port already in use"
# Solution : Changer les ports dans .env
APP_PORT=8080
FORWARD_DB_PORT=33060
FORWARD_REDIS_PORT=63790

# ❌ Permissions denied sur storage/
sail exec laravel.test chmod -R 775 storage bootstrap/cache
sail exec laravel.test chown -R sail:sail storage bootstrap/cache

# ❌ "Connection refused" à MySQL
# Attendre que MySQL soit prêt
sail mysql -e "SELECT 1;"
# Vérifier la santé
sail ps

# ❌ Conteneurs qui ne démarrent pas
sail down
sail build --no-cache
sail up -d

# ❌ Base de données corrompue
sail down -v
sail up -d
sail artisan migrate:fresh --seed

# ❌ Cache problématique
sail artisan optimize:clear
sail redis flushall

# ❌ Out of memory
# Augmenter la mémoire Docker Desktop
# Settings > Resources > Memory: 4GB minimum

# ❌ Lenteur réseau
# Utiliser des volumes nommés au lieu du bind mount
# Modifier compose.yaml pour performance

# ❌ Logs trop volumineux
find storage/logs -name "*.log" -type f -mtime +7 -delete

# ❌ Reset complet
sail down -v
docker system prune -a --volumes
sail up -d
sail artisan migrate:fresh --seed
```

### Vérifications de santé

```bash
# Vérifier tous les services
sail ps

# Tester la connexion API
curl http://localhost/api/posts

# Tester MySQL
sail mysql -e "SELECT VERSION();"

# Tester Redis
sail redis ping

# Vérifier les logs d'erreurs
sail logs -f --tail=50

# Vérifier l'espace disque
docker system df

# Nettoyer l'espace Docker
docker system prune -a
```

## 📋 Workflow quotidien recommandé

```bash
# 🌅 Démarrage de journée
sail up -d                          # Démarrer les conteneurs
sail artisan migrate                # Appliquer nouvelles migrations
sail composer install               # Installer nouvelles dépendances

# 💻 Développement
sail artisan pail                   # Logs en temps réel
sail test --filter=MonTest          # Tests pendant le dev
sail artisan tinker                 # Tester du code rapidement

# 🧪 Avant de commit
sail test                           # Tous les tests
sail artisan pint                   # Formatter le code
sail artisan optimize:clear         # Nettoyer les caches

# 🌙 Fin de journée
sail down                           # Arrêter les conteneurs
```

## 🔐 Sécurité en production

```bash
# Ne jamais committer
.env                                # Contient les secrets
storage/logs/                       # Logs avec données sensibles
vendor/                             # Dépendances

# Variables d'environnement critiques
APP_ENV=production
APP_DEBUG=false
DB_PASSWORD=mot_de_passe_fort
REDIS_PASSWORD=autre_mot_de_passe
```

## ⚡ Performance & Optimisation

```bash
# Optimiser l'application
sail artisan config:cache
sail artisan route:cache
sail artisan view:cache

# Optimiser Composer
sail composer install --optimize-autoloader --no-dev

# Utiliser Redis pour cache et sessions
CACHE_STORE=redis
SESSION_DRIVER=redis
QUEUE_CONNECTION=redis

# Monitoring des performances
sail artisan horizon              # Si Laravel Horizon installé
sail artisan queue:monitor        # Monitor les queues
```

## 🔗 Resources

- [Laravel Sail Documentation](https://laravel.com/docs/sail)
- [Docker Documentation](https://docs.docker.com/)
- [Compose File Reference](https://docs.docker.com/compose/compose-file/)

## 📚 Commandes avancées

### Multi-environment setup

```bash
# Utiliser différents fichiers .env
sail up -d --env-file .env.testing

# Variables d'environnement inline
APP_ENV=testing sail artisan test
```

### Debugging avec Xdebug

```bash
# Activer Xdebug
SAIL_XDEBUG_MODE=develop,debug sail up -d

# Configurer dans VS Code (.vscode/launch.json)
# {
#   "name": "Listen for Xdebug",
#   "type": "php",
#   "request": "launch",
#   "port": 9003,
#   "pathMappings": {
#     "/var/www/html": "${workspaceFolder}"
#   }
# }
```

### Commandes batch utiles

```bash
# Setup complet nouveau dev
sail up -d && \
sail composer install && \
sail artisan key:generate && \
sail artisan migrate:fresh --seed && \
sail artisan optimize:clear

# Reset complet pour debug
sail down -v && \
sail up -d && \
sail artisan migrate:fresh --seed && \
sail test

# Backup complet
mkdir -p backups/$(date +%Y%m%d) && \
sail exec mysql mysqldump -u sail -ppassword laravel > backups/$(date +%Y%m%d)/db.sql && \
cp .env backups/$(date +%Y%m%d)/.env.backup

# Mise à jour sécurisée
sail down && \
git pull && \
sail up -d && \
sail composer install && \
sail artisan migrate && \
sail artisan optimize:clear && \
sail test
```

### Scripts personnalisés

Créez un fichier `scripts/sail-helpers.sh` :

```bash
#!/bin/bash

# Fonction de backup
backup() {
    DATE=$(date +%Y%m%d_%H%M%S)
    ./vendor/bin/sail exec mysql mysqldump -u sail -ppassword laravel > "backup_${DATE}.sql"
    echo "✅ Backup créé: backup_${DATE}.sql"
}

# Fonction de reset
reset() {
    echo "⚠️  Reset complet de l'environnement..."
    ./vendor/bin/sail down -v
    ./vendor/bin/sail up -d
    ./vendor/bin/sail artisan migrate:fresh --seed
    ./vendor/bin/sail artisan optimize:clear
    echo "✅ Reset terminé!"
}

# Fonction de test complet
test-all() {
    echo "🧪 Lancement des tests..."
    ./vendor/bin/sail artisan optimize:clear
    ./vendor/bin/sail test --coverage
    echo "✅ Tests terminés!"
}

# Appeler avec: source scripts/sail-helpers.sh && backup
```

## 🎯 Checklist de déploiement

### Avant de pousser en production

- [ ] Tous les tests passent : `sail test`
- [ ] Code formatté : `sail artisan pint`
- [ ] Pas d'erreurs dans les logs : `sail logs`
- [ ] Migrations testées : `sail artisan migrate:status`
- [ ] .env.example à jour
- [ ] README.md à jour
- [ ] Backup de la DB créé
- [ ] Variables d'environnement en production configurées
- [ ] APP_DEBUG=false en production
- [ ] APP_ENV=production
- [ ] Clés Sanctum régénérées

## 💡 Bonnes pratiques

### Développement quotidien

1. **Toujours utiliser Sail** pour garantir la cohérence
2. **Logs en temps réel** pendant le dev : `sail artisan pail`
3. **Tests après chaque feature** : `sail test --filter=MonTest`
4. **Nettoyage régulier** : `sail artisan optimize:clear`
5. **Commits atomiques** avec tests passants

### Gestion de la base de données

1. **Seeders pour les données de test**
2. **Migrations versionnées** (jamais modifier une migration existante)
3. **Backups réguliers** : `sail exec mysql mysqldump...`
4. **Transactions dans les tests** (rollback automatique)

### Performance

1. **Cache Redis** pour les données fréquentes
2. **Eager loading** pour éviter N+1 queries
3. **Queue jobs** pour tâches longues
4. **Route/config caching** en production

### Sécurité

1. **Pas de secrets dans le code**
2. **Validation stricte** des inputs
3. **Rate limiting** sur l'API
4. **HTTPS en production**
5. **Logs d'audit** pour actions sensibles

---

**📘 Pour plus d'aide** : `sail help` ou consultez la [documentation officielle](https://laravel.com/docs/sail)
