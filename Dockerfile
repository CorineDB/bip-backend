# Dockerfile
FROM php:8.2-fpm

# Installer les dépendances système
RUN apt-get update && apt-get install -y \
    git \
    curl \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    zip \
    unzip \
    libpq-dev \
    supervisor \
    nginx \
    nodejs \
    npm \
    postgresql-client

# Nettoyer le cache APT
RUN apt-get clean && rm -rf /var/lib/apt/lists/*

# Installer les extensions PHP
RUN docker-php-ext-install pdo_pgsql mbstring exif pcntl bcmath gd

# Installer Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Créer l'utilisateur d'application
RUN groupadd -g 1000 www
RUN useradd -u 1000 -ms /bin/bash -g www www

# Définir le répertoire de travail
WORKDIR /var/www

# Copier les fichiers de configuration existants
COPY --chown=www:www . .

# Installer les dépendances PHP
RUN composer install --no-interaction --no-dev --optimize-autoloader

# Installer les dépendances Node.js
RUN npm install && npm run build

# Configurer Nginx
COPY .docker/nginx/default.conf /etc/nginx/sites-available/default

# Configurer Supervisor
COPY .docker/supervisor/supervisord.conf /etc/supervisor/conf.d/supervisord.conf

# Définir les permissions
RUN chown -R www:www /var/www
RUN chmod -R 755 /var/www/storage
RUN chmod -R 755 /var/www/bootstrap/cache
RUN chmod +x /var/www/.docker/scripts/init.sh

# Exposer le port
EXPOSE 80

# Commande par défaut
CMD ["/usr/bin/supervisord", "-c", "/etc/supervisor/conf.d/supervisord.conf"]