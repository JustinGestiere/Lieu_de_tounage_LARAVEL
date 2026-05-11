# ═══════════════════════════════════════════════════════════
# ÉTAPE 1 : Build des assets front-end (Vite + Tailwind)
# ═══════════════════════════════════════════════════════════
FROM node:20-alpine AS frontend

WORKDIR /app

# Copie les fichiers de dépendances Node
COPY package.json package-lock.json ./

# Installe les dépendances Node
RUN npm ci

# Copie le reste du code source pour le build
COPY . .

# Build les assets (CSS/JS) pour la production
RUN npm run build

# ═══════════════════════════════════════════════════════════
# ÉTAPE 2 : Image PHP finale (Apache + extensions)
# ═══════════════════════════════════════════════════════════
FROM php:8.3-apache

# Active le module Apache mod_rewrite (nécessaire pour Laravel)
RUN a2enmod rewrite

# Installe les extensions PHP nécessaires :
# - pdo_pgsql : driver PostgreSQL pour Eloquent
# - pdo_sqlite : driver SQLite (pour les tests)
# - zip/unzip : pour Composer
# - bcmath : pour Stripe
RUN apt-get update && apt-get install -y \
    libpq-dev \
    libzip-dev \
    unzip \
    libsqlite3-dev \
    && docker-php-ext-install pdo_pgsql pdo_sqlite zip bcmath \
    && rm -rf /var/lib/apt/lists/*

# Configure Apache pour pointer vers /var/www/html/public
# (Laravel sert depuis le dossier public/)
ENV APACHE_DOCUMENT_ROOT=/var/www/html/public
RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/sites-available/*.conf \
    && sed -ri -e 's!/var/www/!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/apache2.conf /etc/apache2/conf-available/*.conf

# Autorise les .htaccess (AllowOverride All)
RUN sed -i '/<Directory \/var\/www\/>/,/<\/Directory>/ s/AllowOverride None/AllowOverride All/' /etc/apache2/apache2.conf

WORKDIR /var/www/html

# Installe Composer depuis l'image officielle
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Copie les fichiers Composer et installe les dépendances PHP (sans dev)
COPY composer.json composer.lock ./
RUN composer install --no-dev --no-scripts --no-autoloader

# Copie tout le code source
COPY . .

# Copie les assets buildés depuis l'étape Node
COPY --from=frontend /app/public/build public/build

# Finalise l'autoloader Composer (optimisé pour la prod)
RUN composer dump-autoload --optimize

# Crée les dossiers de cache/logs et donne les permissions à Apache
RUN mkdir -p storage/logs storage/framework/cache storage/framework/sessions storage/framework/views bootstrap/cache \
    && chown -R www-data:www-data storage bootstrap/cache \
    && chmod -R 775 storage bootstrap/cache

# Script d'entrée : lance les migrations puis démarre Apache
COPY docker-entrypoint.sh /usr/local/bin/
RUN chmod +x /usr/local/bin/docker-entrypoint.sh

EXPOSE 80

ENTRYPOINT ["docker-entrypoint.sh"]
CMD ["apache2-foreground"]
