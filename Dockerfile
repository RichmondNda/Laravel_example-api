# =============================================================================
# Dockerfile optimisé pour Laravel API
# Taille cible : ~400-500MB (au lieu de ~1.35GB)
# =============================================================================

# Stage 1: Builder - Installation des dépendances
FROM ubuntu:24.04 AS builder

ARG WWWGROUP=1000

ENV DEBIAN_FRONTEND=noninteractive
ENV TZ=UTC

WORKDIR /var/www/html

# Installer uniquement ce qui est nécessaire pour le build
RUN apt-get update \
    && apt-get install -y --no-install-recommends \
       curl \
       ca-certificates \
       zip \
       unzip \
       git \
       gnupg \
    && curl -sS 'https://keyserver.ubuntu.com/pks/lookup?op=get&search=0x14aa40ec0831756756d7f66c4f4ea0aae5267a6c' | gpg --dearmor | tee /etc/apt/keyrings/ppa_ondrej_php.gpg > /dev/null \
    && echo "deb [signed-by=/etc/apt/keyrings/ppa_ondrej_php.gpg] https://ppa.launchpadcontent.net/ondrej/php/ubuntu noble main" > /etc/apt/sources.list.d/ppa_ondrej_php.list \
    && apt-get update \
    && apt-get install -y --no-install-recommends \
       php8.3-cli \
       php8.3-curl \
       php8.3-mbstring \
       php8.3-xml \
       php8.3-zip \
    && curl -sLS https://getcomposer.org/installer | php -- --install-dir=/usr/bin/ --filename=composer \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/* /tmp/* /var/tmp/*

# Copier les fichiers de dépendances
COPY composer.json composer.lock ./

# Installer les dépendances PHP (sans dev)
RUN composer install \
    --no-dev \
    --no-scripts \
    --no-autoloader \
    --prefer-dist \
    --no-interaction \
    --ignore-platform-reqs

# Copier le code source
COPY . .

# Générer l'autoloader optimisé
RUN composer dump-autoload --optimize --classmap-authoritative --no-dev --ignore-platform-reqs

# Supprimer les fichiers inutiles en production
RUN rm -rf \
    tests/ \
    .git/ \
    .github/ \
    .editorconfig \
    .gitignore \
    .gitattributes \
    phpunit.xml \
    README.md \
    DOCKER.md \
    docker-compose*.yml \
    compose.yaml \
    api.rest

# =============================================================================
# Stage 2: Runtime - Image finale optimisée
# =============================================================================
FROM ubuntu:24.04

LABEL maintainer="Laravel"
LABEL org.opencontainers.image.source="https://github.com/richmondnda/laravel_example-api"

ARG WWWGROUP=1000

WORKDIR /var/www/html

ENV DEBIAN_FRONTEND=noninteractive
ENV TZ=UTC
ENV SUPERVISOR_PHP_COMMAND="/usr/bin/php -d variables_order=EGPCS /var/www/html/artisan serve --host=0.0.0.0 --port=80"
ENV SUPERVISOR_PHP_USER="sail"

RUN ln -snf /usr/share/zoneinfo/$TZ /etc/localtime && echo $TZ > /etc/timezone

# Installer UNIQUEMENT les paquets nécessaires pour le runtime (pas de dev tools)
RUN apt-get update \
    && mkdir -p /etc/apt/keyrings \
    && apt-get install -y --no-install-recommends \
       gnupg \
       gosu \
       curl \
       ca-certificates \
       supervisor \
       sqlite3 \
       libpng16-16 \
    && curl -sS 'https://keyserver.ubuntu.com/pks/lookup?op=get&search=0x14aa40ec0831756756d7f66c4f4ea0aae5267a6c' | gpg --dearmor | tee /etc/apt/keyrings/ppa_ondrej_php.gpg > /dev/null \
    && echo "deb [signed-by=/etc/apt/keyrings/ppa_ondrej_php.gpg] https://ppa.launchpadcontent.net/ondrej/php/ubuntu noble main" > /etc/apt/sources.list.d/ppa_ondrej_php.list \
    && apt-get update \
    && apt-get install -y --no-install-recommends \
       php8.3-cli \
       php8.3-mysql \
       php8.3-pgsql \
       php8.3-sqlite3 \
       php8.3-redis \
       php8.3-curl \
       php8.3-mbstring \
       php8.3-xml \
       php8.3-zip \
       php8.3-bcmath \
       php8.3-intl \
       php8.3-gd \
       php8.3-soap \
    && apt-get -y autoremove \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/* /tmp/* /var/tmp/* /usr/share/doc/* /usr/share/man/*

# Créer l'utilisateur sail
RUN groupadd --force -g $WWWGROUP sail \
    && useradd -ms /bin/bash --no-user-group -g $WWWGROUP -u 1337 sail

# Copier l'application depuis le builder
COPY --from=builder --chown=sail:sail /var/www/html /var/www/html

# Configuration Supervisor
COPY --chown=root:root docker/supervisor/supervisord.conf /etc/supervisor/conf.d/supervisord.conf

# Définir les permissions
RUN chown -R sail:sail /var/www/html \
    && chmod -R 755 /var/www/html/storage \
    && chmod -R 755 /var/www/html/bootstrap/cache

EXPOSE 80

ENTRYPOINT ["/usr/bin/supervisord", "-n", "-c", "/etc/supervisor/conf.d/supervisord.conf"]
