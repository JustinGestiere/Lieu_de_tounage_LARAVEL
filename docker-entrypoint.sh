#!/bin/bash
set -e

# ═══════════════════════════════════════════════
# Script d'entrée Docker
# Exécuté à chaque démarrage du conteneur
# ═══════════════════════════════════════════════

# Génère la clé Laravel si elle n'existe pas encore
if [ -z "$APP_KEY" ]; then
    php artisan key:generate --force
fi

# Lance les migrations automatiquement
# --force est nécessaire en production
php artisan migrate --force

# Vide les caches pour prendre en compte la nouvelle config
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Lance la commande originale (apache2-foreground)
exec "$@"
