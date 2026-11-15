#!/usr/bin/env bash
# Mise à jour de DevLink Africa déjà installé sur alwaysdata.
# Usage, depuis le dossier personnel (~) après avoir envoyé l'archive de mise à jour :
#   unzip -o devlink-africa-mise-a-jour.zip && bash devlink-africa/deploy/update.sh
set -euo pipefail
cd "$(dirname "$0")/.."

echo "=== Mise à jour de DevLink Africa ==="
if [ ! -f .env ]; then
    echo "ERREUR : .env introuvable. Lancez d'abord deploy/alwaysdata.sh (première installation)."
    exit 1
fi

# Quoi qu'il arrive (même en cas d'erreur), le site est remis en ligne à la fin.
trap 'status=$?; php artisan up >/dev/null 2>&1 || true; if [ $status -ne 0 ]; then echo ""; echo "ERREUR pendant la mise à jour (le site reste en ligne). Copiez le message ci-dessus."; fi' EXIT

php artisan down --retry=15 || true
php artisan optimize:clear
php artisan migrate --force
php artisan config:cache
php artisan route:cache
php artisan view:cache

echo ""
echo "=== Mise à jour terminée ! ==="
echo "Rechargez le site avec Ctrl+F5 : $(grep '^APP_URL=' .env | cut -d= -f2)"
