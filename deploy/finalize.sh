#!/usr/bin/env bash
#
# Finalisation d'un déploiement atomique sur o2switch.
# Exécuté PAR le serveur, depuis l'intérieur du dossier de release fraîchement
# envoyé par GitHub Actions (voir .github/workflows/deploy.yml).
#
# Séquence : link du contenu partagé -> cache:clear -> migrations ->
# bascule atomique du symlink `current` -> health-check (rollback si KO) ->
# purge des anciennes releases.
#
set -euo pipefail

# --- Chemins (déduits de l'emplacement de ce script) --------------------------
RELEASE_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"   # .../releases/<REL>
BASE="$(cd "$RELEASE_DIR/../.." && pwd)"                          # ~/deploy/guillaumehurard
SHARED="$BASE/shared"
CURRENT="$BASE/current"

# Binaires o2switch : le wrapper CloudLinux-selector-aware (pas /opt/alt/...).
PHP="/usr/local/bin/php"

# URL de health-check final.
HEALTH_URL="https://guillaumehurard.fr/"

echo ">> Finalisation de $RELEASE_DIR"

# --- 1. Rattacher l'état partagé (jamais dans le dépôt) -----------------------
# .env.local (APP_SECRET + DB de prod)
ln -sfn "$SHARED/.env.local" "$RELEASE_DIR/.env.local"

# var/ : cache neuf par release, mais log/ et share/ persistants via shared/.
mkdir -p "$RELEASE_DIR/var"
ln -sfn "$SHARED/var/log"   "$RELEASE_DIR/var/log"
ln -sfn "$SHARED/var/share" "$RELEASE_DIR/var/share"

# --- 2. Cache prod reconstruit à neuf pour ce release -------------------------
"$PHP" "$RELEASE_DIR/bin/console" cache:clear --no-interaction --env=prod

# --- 3. Migrations DB (base partagée : avant la bascule) ----------------------
"$PHP" "$RELEASE_DIR/bin/console" doctrine:migrations:migrate \
    --no-interaction --allow-no-migration --env=prod

# --- 4. Bascule atomique du symlink `current` --------------------------------
PREVIOUS="$(readlink -f "$CURRENT" 2>/dev/null || true)"
ln -sfn "$RELEASE_DIR" "$CURRENT"

# --- 5. Health-check ; rollback immédiat si != 200 ---------------------------
CODE="$(curl -s -o /dev/null -w '%{http_code}' "$HEALTH_URL" || echo 000)"
if [ "$CODE" != "200" ]; then
    echo "!! Health-check KO (HTTP $CODE) — rollback vers ${PREVIOUS:-<aucun>}"
    if [ -n "$PREVIOUS" ] && [ -d "$PREVIOUS" ]; then
        ln -sfn "$PREVIOUS" "$CURRENT"
    fi
    exit 1
fi

# --- 6. Purge : on ne garde que les 5 releases les plus récentes -------------
cd "$BASE/releases"
ls -1dt */ 2>/dev/null | tail -n +6 | xargs -r rm -rf

echo ">> OK — $RELEASE_DIR est en ligne (HTTP $CODE)"
