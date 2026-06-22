#!/usr/bin/env bash
# ================================================================
#  GTB BANK — Setup VPS (Ubuntu/Debian)
#  Lance UNE SEULE FOIS après le premier déploiement.
#
#  Usage :
#    cd /var/www/gtb-bank-FINAL
#    bash bin/setup.sh
# ================================================================

set -euo pipefail
RED='\033[0;31m'; GREEN='\033[0;32m'; YELLOW='\033[1;33m'; NC='\033[0m'

info()    { echo -e "${GREEN}[GTB]${NC} $*"; }
warn()    { echo -e "${YELLOW}[WARN]${NC} $*"; }
error()   { echo -e "${RED}[ERR]${NC} $*"; exit 1; }
prompt()  { read -rp "$(echo -e "${YELLOW}$*${NC} ") " REPLY; echo "$REPLY"; }

echo ""
echo "╔══════════════════════════════════════════════╗"
echo "║     GTB BANK — Setup initial du serveur      ║"
echo "╚══════════════════════════════════════════════╝"
echo ""

# ── 1. Vérifications prérequis ───────────────────────────────────
info "Vérification des prérequis..."
command -v php  >/dev/null 2>&1 || error "PHP non trouvé. Installer : apt install php8.1-cli php8.1-mysql"
command -v mysql >/dev/null 2>&1 || error "MySQL non trouvé."
PHP_VER=$(php -r "echo PHP_MAJOR_VERSION.'.'.PHP_MINOR_VERSION;")
info "PHP $PHP_VER détecté."

# ── 2. Fichier .env ──────────────────────────────────────────────
if [ ! -f .env ]; then
    warn ".env introuvable. Création depuis .env.example..."
    cp .env.example .env

    echo ""
    info "=== Configuration de la base de données ==="
    DB_HOST=$(prompt "DB_HOST [127.0.0.1] :")
    DB_HOST=${DB_HOST:-127.0.0.1}
    DB_PORT=$(prompt "DB_PORT [3306] :")
    DB_PORT=${DB_PORT:-3306}
    DB_NAME=$(prompt "DB_NAME [gtb] :")
    DB_NAME=${DB_NAME:-gtb}
    DB_USER=$(prompt "DB_USER [root] :")
    DB_USER=${DB_USER:-root}
    read -rsp "$(echo -e "${YELLOW}DB_PASS :${NC} ")" DB_PASS; echo ""

    echo ""
    info "=== Clé secrète de l'application ==="
    APP_SECRET=$(openssl rand -hex 32 2>/dev/null || php -r "echo bin2hex(random_bytes(32));")
    info "APP_SECRET généré automatiquement."

    echo ""
    info "=== API Brevo (email) ==="
    read -rp "$(echo -e "${YELLOW}BREVO_API_KEY (laisser vide pour plus tard) :${NC} ")" BREVO_KEY

    # Écrire le .env
    cat > .env <<EOF
GTB_ENV=production
DB_HOST=${DB_HOST}
DB_PORT=${DB_PORT}
DB_NAME=${DB_NAME}
DB_USER=${DB_USER}
DB_PASS=${DB_PASS}
APP_SECRET=${APP_SECRET}
BREVO_API_KEY=${BREVO_KEY}
EOF
    info ".env créé."
else
    info ".env déjà présent, on continue."
fi

# Charger les variables .env
set -a; source .env; set +a

# ── 3. Base de données ───────────────────────────────────────────
echo ""
info "=== Import du schéma SQL ==="
MYSQL_CMD="mysql -h${DB_HOST} -P${DB_PORT} -u${DB_USER}"
if [ -n "${DB_PASS:-}" ]; then
    MYSQL_CMD="$MYSQL_CMD -p${DB_PASS}"
fi

if $MYSQL_CMD -e "USE ${DB_NAME}; SELECT COUNT(*) FROM users;" >/dev/null 2>&1; then
    warn "La base '${DB_NAME}' et la table 'users' existent déjà."
    REIMPORT=$(prompt "Réimporter le schéma ? Cela effacera TOUTES les données [oui/NON] :")
    if [[ "$REIMPORT" =~ ^[Oo][Uu][Ii]$ ]]; then
        $MYSQL_CMD < sql/schema.sql && info "Schéma importé."
    else
        info "Schéma ignoré."
    fi
else
    info "Import du schéma..."
    $MYSQL_CMD < sql/schema.sql && info "Schéma importé avec succès."
fi

# ── 4. Compte administrateur ─────────────────────────────────────
echo ""
info "=== Création du compte administrateur ==="
ADMIN_EXISTS=$($MYSQL_CMD -N -s -e "SELECT COUNT(*) FROM ${DB_NAME}.admins;" 2>/dev/null || echo "0")
if [ "$ADMIN_EXISTS" -gt 0 ]; then
    warn "Un admin existe déjà. Création ignorée."
else
    ADMIN_EMAIL=$(prompt "Email admin :")
    read -rsp "$(echo -e "${YELLOW}Mot de passe admin :${NC} ")" ADMIN_PASS; echo ""
    ADMIN_PRENOM=$(prompt "Prénom :")
    ADMIN_NOM=$(prompt "Nom :")

    php bin/create-admin.php "$ADMIN_EMAIL" "$ADMIN_PASS" "$ADMIN_PRENOM" "$ADMIN_NOM" superadmin
fi

# ── 5. Permissions dossiers ──────────────────────────────────────
echo ""
info "=== Permissions dossiers ==="
mkdir -p uploads/kyc uploads/avatars logs
chmod -R 775 uploads logs
[ "$(id -u)" = "0" ] && chown -R www-data:www-data uploads logs || warn "Non root — chown www-data ignoré."
info "Dossiers uploads/ et logs/ configurés."

# ── 6. Cron OTP cleanup ──────────────────────────────────────────
echo ""
CRON_CMD="0 3 * * * php $(pwd)/bin/otp-cleanup.php >> $(pwd)/logs/cron.log 2>&1"
if ! crontab -l 2>/dev/null | grep -q "otp-cleanup"; then
    (crontab -l 2>/dev/null; echo "$CRON_CMD") | crontab -
    info "Cron OTP cleanup ajouté (3h00 chaque nuit)."
else
    info "Cron OTP cleanup déjà présent."
fi

# ── 7. Résumé ────────────────────────────────────────────────────
echo ""
echo "╔══════════════════════════════════════════════╗"
echo "║           ✅  Setup terminé                  ║"
echo "╠══════════════════════════════════════════════╣"
echo "║  Site :    https://globaltrust-b.com         ║"
echo "║  Admin :   /admin/index.php                  ║"
echo "║  DB :      ${DB_NAME}@${DB_HOST}                ║"
echo "╚══════════════════════════════════════════════╝"
echo ""
warn "Pensez à configurer votre vhost Apache/Nginx pour pointer vers ce dossier."
