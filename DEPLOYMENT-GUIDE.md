# Guide de déploiement - Image Docker Laravel API

Documentation complète pour les développeurs qui souhaitent déployer l'image Docker de l'API Laravel.

---

## 📋 Vue d'ensemble du workflow

```
Développement → GitHub Push → GitHub Actions → GHCR → Production
```

1. **Développement** : Code dans votre environnement local (avec Sail)
2. **Push** : Push vers GitHub (branche `main` ou tags)
3. **CI/CD** : GitHub Actions build automatiquement l'image Docker
4. **Registry** : Image publiée sur GitHub Container Registry (GHCR)
5. **Déploiement** : Pull et déploiement sur serveur de production

---

## 🔄 Workflow de publication automatique

### Déclencheurs de build

L'image Docker est buildée et publiée automatiquement quand :

1. **Push sur main/master** :
   ```bash
   git push origin main
   ```
   → Crée le tag `:latest` et `:main`

2. **Création d'un tag Git** :
   ```bash
   git tag v1.0.0
   git push origin v1.0.0
   ```
   → Crée les tags `:v1.0.0`, `:1.0`, `:1`, `:latest`

3. **Manuellement** :
   - Aller sur GitHub Actions
   - Sélectionner "Publier l'image Docker sur GHCR"
   - Cliquer "Run workflow"

### Tags générés automatiquement

| Événement | Tags créés | Exemple |
|-----------|-----------|---------|
| Push sur `main` | `main`, `latest`, `sha-xxxxx` | `main`, `latest`, `sha-abc123` |
| Push sur autre branche | `branch-name`, `sha-xxxxx` | `develop`, `sha-def456` |
| Tag `v1.2.3` | `v1.2.3`, `1.2`, `1`, `latest` | `v1.2.3`, `1.2`, `1` |
| Pull Request | `pr-42` | `pr-42` (non publié) |

---

## 🏗️ Processus de build

### Étapes de la GitHub Action

1. **Checkout** : Récupération du code source
2. **Docker Buildx** : Configuration du builder multi-plateforme
3. **Login GHCR** : Authentification automatique (via `GITHUB_TOKEN`)
4. **Metadata** : Génération des tags et labels
5. **Build & Push** : Construction et publication de l'image
6. **Summary** : Génération du résumé avec instructions

### Durée du build

- **Premier build** : ~8-12 minutes
- **Builds suivants** (avec cache) : ~3-5 minutes

---

## 📦 Gestion des versions

### Stratégie de versioning recommandée

Utiliser le **Semantic Versioning** (SemVer) :

```
v[MAJOR].[MINOR].[PATCH]
```

- **MAJOR** : Breaking changes (v1.x.x → v2.0.0)
- **MINOR** : Nouvelles fonctionnalités (v1.0.x → v1.1.0)
- **PATCH** : Bug fixes (v1.0.0 → v1.0.1)

### Exemples de workflow

**Release d'une nouvelle version** :
```bash
# Terminer les développements
git add .
git commit -m "feat: add new features for v1.2.0"
git push origin main

# Créer le tag
git tag -a v1.2.0 -m "Release v1.2.0: New features"
git push origin v1.2.0

# L'image sera automatiquement buildée et publiée
```

**Hotfix rapide** :
```bash
# Corriger le bug
git add .
git commit -m "fix: critical security patch"
git push origin main

# Tag de patch
git tag -a v1.2.1 -m "Hotfix v1.2.1"
git push origin v1.2.1
```

---

## 🎯 Déploiement en production

### Option 1 : Docker Compose (recommandé)

**Fichier `docker-compose.prod.yml`** :

```yaml
version: '3.8'

services:
  app:
    image: ghcr.io/richmondnda/laravel_example-api:v1.2.0
    restart: always
    ports:
      - "8080:80"
    environment:
      APP_ENV: production
      APP_DEBUG: "false"
      APP_KEY: ${APP_KEY}
      DB_CONNECTION: mysql
      DB_HOST: mysql
      DB_DATABASE: laravel_prod
      DB_USERNAME: laravel_prod
      DB_PASSWORD: ${DB_PASSWORD}
      REDIS_HOST: redis
    depends_on:
      - mysql
      - redis
    networks:
      - app-network

  mysql:
    image: mysql:8.4
    restart: always
    environment:
      MYSQL_ROOT_PASSWORD: ${MYSQL_ROOT_PASSWORD}
      MYSQL_DATABASE: laravel_prod
      MYSQL_USER: laravel_prod
      MYSQL_PASSWORD: ${DB_PASSWORD}
    volumes:
      - mysql-data:/var/lib/mysql
    networks:
      - app-network

  redis:
    image: redis:alpine
    restart: always
    volumes:
      - redis-data:/data
    networks:
      - app-network

volumes:
  mysql-data:
  redis-data:

networks:
  app-network:
    driver: bridge
```

**Fichier `.env.prod`** :
```env
APP_KEY=base64:YOUR_PRODUCTION_KEY_HERE
DB_PASSWORD=strong_production_password
MYSQL_ROOT_PASSWORD=strong_root_password
```

**Déploiement** :
```bash
# Sur le serveur de production
cd /opt/laravel-api

# Télécharger les fichiers
wget https://raw.githubusercontent.com/richmondnda/laravel_example-api/main/test_app/docker-compose.yml -O docker-compose.prod.yml

# Créer .env.prod avec les bonnes valeurs
nano .env.prod

# Démarrer
docker-compose -f docker-compose.prod.yml --env-file .env.prod up -d

# Migrations
docker-compose -f docker-compose.prod.yml exec app php artisan migrate --force
```

### Option 2 : Docker Swarm

```bash
# Initialiser Swarm
docker swarm init

# Déployer le stack
docker stack deploy -c docker-compose.prod.yml laravel-api

# Voir les services
docker stack services laravel-api

# Scaler l'application
docker service scale laravel-api_app=3
```

### Option 3 : Kubernetes

**Deployment** (`k8s/deployment.yaml`) :

```yaml
apiVersion: apps/v1
kind: Deployment
metadata:
  name: laravel-api
spec:
  replicas: 3
  selector:
    matchLabels:
      app: laravel-api
  template:
    metadata:
      labels:
        app: laravel-api
    spec:
      containers:
      - name: api
        image: ghcr.io/richmondnda/laravel_example-api:v1.2.0
        ports:
        - containerPort: 80
        env:
        - name: APP_KEY
          valueFrom:
            secretKeyRef:
              name: laravel-secrets
              key: app-key
        - name: DB_HOST
          value: "mysql-service"
```

**Déploiement** :
```bash
kubectl apply -f k8s/deployment.yaml
kubectl apply -f k8s/service.yaml
kubectl get pods
```

---

## 🔄 Mise à jour de l'application en production

### Déploiement avec zéro downtime

**Avec Docker Compose** :
```bash
# Pull la nouvelle version
docker-compose pull app

# Recréer le conteneur (rolling update automatique)
docker-compose up -d --no-deps --build app

# Vérifier
docker-compose ps
curl http://localhost:8080/api/posts
```

**Avec plusieurs réplicas** (pour zéro downtime) :
```bash
# Scale à 2 instances
docker-compose up -d --scale app=2

# Update progressif
docker-compose up -d --no-deps --scale app=2 app

# Retour à 1 instance
docker-compose up -d --scale app=1
```

### Rollback rapide

Si une version pose problème :

```bash
# Option 1 : Utiliser la version précédente
docker-compose pull app:v1.1.0
docker-compose up -d app

# Option 2 : Revenir au tag précédent
docker tag ghcr.io/richmondnda/laravel_example-api:v1.1.0 latest
docker-compose up -d --force-recreate app
```

---

## 🔐 Sécurité et bonnes pratiques

### Secrets et variables sensibles

**NE JAMAIS committer** :
- `.env` avec des vraies valeurs
- Mots de passe
- Tokens API
- APP_KEY de production

**Utiliser** :
- Variables d'environnement
- Docker secrets
- Vault (HashiCorp)
- GitHub Secrets (pour CI/CD uniquement)

### Scanner les vulnérabilités

```bash
# Avec Trivy
docker run --rm -v /var/run/docker.sock:/var/run/docker.sock \
  aquasec/trivy image ghcr.io/richmondnda/laravel_example-api:latest

# Avec Docker Scout
docker scout cves ghcr.io/richmondnda/laravel_example-api:latest
```

### Hardening

1. **Ne pas exposer les ports de DB publiquement** :
```yaml
mysql:
  ports:
    - "127.0.0.1:3306:3306"  # Seulement localhost
```

2. **Utiliser un reverse proxy** (Nginx, Traefik) :
```yaml
labels:
  - "traefik.enable=true"
  - "traefik.http.routers.api.rule=Host(`api.example.com`)"
  - "traefik.http.routers.api.tls=true"
```

3. **Rate limiting** sur l'API

4. **Monitoring** avec Prometheus + Grafana

---

## 📊 Monitoring et logs

### Logs centralisés

**Avec ELK Stack** :
```yaml
app:
  logging:
    driver: "json-file"
    options:
      max-size: "10m"
      max-file: "3"
      labels: "production"
```

**Avec Loki** :
```bash
docker plugin install grafana/loki-docker-driver:latest --alias loki --grant-all-permissions

# Dans docker-compose.yml
logging:
  driver: loki
  options:
    loki-url: "http://localhost:3100/loki/api/v1/push"
```

### Health checks

Ajouter un endpoint de health :
```bash
curl http://localhost:8080/api/health
```

Configurer Docker health check :
```yaml
app:
  healthcheck:
    test: ["CMD", "curl", "-f", "http://localhost/api/health"]
    interval: 30s
    timeout: 10s
    retries: 3
    start_period: 40s
```

---

## 🧪 Tests avant déploiement

### Test local de l'image de production

```bash
# Pull l'image
docker pull ghcr.io/richmondnda/laravel_example-api:latest

# Lancer en mode test
docker run --rm -p 8080:80 \
  -e APP_KEY="base64:test" \
  -e DB_CONNECTION=sqlite \
  ghcr.io/richmondnda/laravel_example-api:latest

# Tester
curl http://localhost:8080/api/posts
```

### Smoke tests

```bash
# Health check
curl -f http://production-url/api/health || exit 1

# API fonctionnelle
curl -f http://production-url/api/posts || exit 1

# Response time
time curl http://production-url/api/posts
```

---

## 📞 Support et maintenance

### Backup et restauration

**Backup** :
```bash
# Base de données
docker-compose exec mysql mysqldump -ularavel -p laravel > backup-$(date +%Y%m%d).sql

# Volumes
docker run --rm -v laravel_mysql-data:/data -v $(pwd):/backup alpine tar czf /backup/mysql-backup.tar.gz /data
```

**Restauration** :
```bash
# Restaurer MySQL
docker-compose exec -T mysql mysql -ularavel -p laravel < backup-20260201.sql

# Restaurer volume
docker run --rm -v laravel_mysql-data:/data -v $(pwd):/backup alpine tar xzf /backup/mysql-backup.tar.gz -C /
```

### Monitoring des ressources

```bash
# CPU et RAM
docker stats laravel-api

# Espace disque
docker system df

# Logs de taille
du -sh /var/lib/docker/containers/*/*-json.log
```

---

## 📝 Checklist de déploiement

Avant chaque déploiement en production :

- [ ] Tests locaux réussis
- [ ] Version taguée sur Git (`v1.x.x`)
- [ ] Image buildée et publiée sur GHCR
- [ ] Backup de la base de données
- [ ] Variables d'environnement configurées
- [ ] Migrations testées
- [ ] Health checks fonctionnels
- [ ] Monitoring actif
- [ ] Plan de rollback prêt
- [ ] Documentation à jour

---

**Auteur** : Richmond NDA  
**Dernière mise à jour** : Février 2026
