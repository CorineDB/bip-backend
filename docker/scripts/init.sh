#!/bin/bash

echo "🚀 Initialisation de l'application Laravel..."

# Attendre que la base de données soit prête
echo "⏳ Attente de la base de données..."
until pg_isready -h db -p 5432 -U postgres; do
    echo "Base de données non disponible, attente..."
    sleep 2
done

echo "✅ Base de données disponible"

# Copier le fichier .env pour Docker
if [ ! -f /var/www/.env ]; then
    echo "📝 Copie du fichier .env.docker vers .env"
    cp /var/www/.env.docker /var/www/.env
fi

# Générer la clé d'application si nécessaire
echo "🔑 Génération de la clé d'application..."
php artisan key:generate --force

# Nettoyer le cache
echo "🧹 Nettoyage du cache..."
php artisan config:clear
php artisan cache:clear
php artisan route:clear
php artisan view:clear

# Créer le lien de stockage
echo "🔗 Création du lien de stockage..."
php artisan storage:link

# Exécuter les migrations
echo "🗄️ Exécution des migrations..."
php artisan migrate --force

# Exécuter les seeders
echo "🌱 Exécution des seeders..."
php artisan db:seed --force

# Optimiser pour la production
echo "⚡ Optimisation de l'application..."
php artisan config:cache
php artisan route:cache
php artisan view:cache

php artisan passport:keys --force

php artisan passport:client --password

echo "🎉 Initialisation terminée avec succès!"