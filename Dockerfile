# Dockerfile pour Laravel API
FROM php:8.5-fpm-alpine

# Définir le répertoire de travail
WORKDIR /var/www/html

# Installer les dépendances système
RUN apk add --no-cache \
    git \
    curl \
    libpng-dev \
    oniguruma-dev \
    libxml2-dev \
    zip \
    unzip \
    mysql-client \
    nginx \
    supervisor

# Installer les extensions PHP requises
RUN docker-php-ext-install pdo_mysql mbstring exif pcntl bcmath gd

# Installer Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Copier les fichiers de dépendances
COPY composer.json composer.lock ./

# Installer les dépendances PHP
RUN composer install --no-dev --no-scripts --no-autoloader --prefer-dist

# Copier le reste de l'application
COPY . .

# Générer l'autoloader optimisé
RUN composer dump-autoload --optimize --no-dev

# Configuration Nginx
COPY docker/nginx/default.conf /etc/nginx/http.d/default.conf

# Configuration Supervisor
COPY docker/supervisor/supervisord.conf /etc/supervisor/conf.d/supervisord.conf

# Créer les répertoires nécessaires et définir les permissions
RUN mkdir -p /var/www/html/storage/logs \
    && mkdir -p /var/www/html/bootstrap/cache \
    && chown -R www-data:www-data /var/www/html/storage \
    && chown -R www-data:www-data /var/www/html/bootstrap/cache \
    && chmod -R 775 /var/www/html/storage \
    && chmod -R 775 /var/www/html/bootstrap/cache

# Exposer le port 80
EXPOSE 80

# Démarrer Supervisor
CMD ["/usr/bin/supervisord", "-c", "/etc/supervisor/conf.d/supervisord.conf"]
