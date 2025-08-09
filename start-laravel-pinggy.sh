#!/bin/bash

# Arrêter le script si une commande échoue
set -e

# Lancer Laravel en arrière-plan sur le port 8001
echo "🚀 Démarrage du serveur Laravel..."
php artisan serve --host=0.0.0.0 --port=8001 &
LARAVEL_PID=$!

# Attendre un peu pour être sûr que Laravel est prêt
sleep 3

# Lancer Pinggy vers le port 8001
echo "🌐 Ouverture du tunnel Pinggy..."
ssh -p 443 -R0:localhost:8001 -L4300:localhost:4300 \
    -o StrictHostKeyChecking=no \
    -o ServerAliveInterval=30 \
    -t N4kGTj2I5o5@free.pinggy.io x:https

# Si on interrompt Pinggy, arrêter Laravel aussi
trap "kill $LARAVEL_PID" EXIT