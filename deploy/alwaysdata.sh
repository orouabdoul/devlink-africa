#!/usr/bin/env bash
# Installation de DevLink Africa sur alwaysdata (offre gratuite).
# À lancer une seule fois, depuis le dossier du projet :  bash deploy/alwaysdata.sh
set -euo pipefail
cd "$(dirname "$0")/.."

echo "=== Installation de DevLink Africa sur alwaysdata ==="

if ! php -r 'exit(version_compare(PHP_VERSION, "8.2.0", ">=") ? 0 : 1);'; then
    echo "ERREUR : PHP $(php -r 'echo PHP_VERSION;') détecté, il faut PHP 8.2 ou plus."
    echo "Dans l'administration alwaysdata : Environnement > PHP > choisissez 8.3, puis relancez ce script."
    exit 1
fi
echo "PHP $(php -r 'echo PHP_VERSION;') : OK"

read -rp "Nom de votre compte alwaysdata (ex. devlinkafrica) : " ACCOUNT
read -rp "Nom de la base MySQL (ex. ${ACCOUNT}_devlink) : " DB_NAME
read -rp "Utilisateur MySQL (ex. ${ACCOUNT}) : " DB_USER
read -rsp "Mot de passe de cet utilisateur MySQL : " DB_PASS; echo

# Échappe les caractères spéciaux pour le fichier .env
DB_PASS_ESC=${DB_PASS//\\/\\\\}
DB_PASS_ESC=${DB_PASS_ESC//\"/\\\"}

cat > .env <<ENV
APP_NAME="DevLink Africa"
APP_ENV=production
APP_KEY=
APP_DEBUG=false
APP_URL=https://${ACCOUNT}.alwaysdata.net

APP_LOCALE=fr
APP_FALLBACK_LOCALE=en
APP_FAKER_LOCALE=fr_FR
APP_MAINTENANCE_DRIVER=file
BCRYPT_ROUNDS=12

LOG_CHANNEL=stack
LOG_STACK=single
LOG_LEVEL=error

DB_CONNECTION=mysql
DB_HOST=mysql-${ACCOUNT}.alwaysdata.net
DB_PORT=3306
DB_DATABASE=${DB_NAME}
DB_USERNAME=${DB_USER}
DB_PASSWORD="${DB_PASS_ESC}"

SESSION_DRIVER=database
SESSION_LIFETIME=120
SESSION_ENCRYPT=false
SESSION_PATH=/
SESSION_DOMAIN=null
SESSION_SECURE_COOKIE=true

BROADCAST_CONNECTION=log
FILESYSTEM_DISK=local
QUEUE_CONNECTION=database
CACHE_STORE=database
MAIL_MAILER=log

VITE_APP_NAME="\${APP_NAME}"

# Affiche le compte de démonstration sur la page de connexion (pour le jury)
DEVLINK_SHOW_DEMO=true
ENV
chmod 600 .env
echo ".env créé."

php artisan key:generate --force
echo "Test de la connexion à la base…"
php artisan migrate --seed --force
php artisan storage:link || true
chmod -R u+rwX storage bootstrap/cache
php artisan config:cache
php artisan route:cache
php artisan view:cache

echo ""
echo "=== Terminé ! ==="
echo "Vérifiez que le site pointe vers le dossier : $(pwd)/public"
echo "Puis ouvrez : https://${ACCOUNT}.alwaysdata.net"
echo "Compte de démonstration : demo@devlink.africa / DevLink2026"
