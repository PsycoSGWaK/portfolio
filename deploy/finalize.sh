#!/usr/bin/env bash
#
# Finalisation d'un déploiement atomique sur o2switch.
# Exécuté PAR le serveur, depuis l'intérieur du dossier de release fraîchement
# envoyé par GitHub Actions (voir .github/workflows/deploy.yml).
#
# Séquence : link du contenu partagé -> cache:clear -> migrations ->
# vérification locale que le kernel boote -> bascule atomique du symlink
# `current` -> purge des anciennes releases.
#
# Le health-check HTTP final (curl sur le vrai domaine) est fait depuis le
# runner GitHub Actions, PAS ici. Le faire ici causait un blocage : ce script
# tourne déjà dans une requête HTTP traitée par un worker PHP-FPM/Apache du
# compte, et s'auto-interroger en HTTP depuis l'intérieur de cette même
# requête peut créer un auto-deadlock si le pool de workers est restreint
# (constaté : la connexion se coupe côté GitHub Actions avec
# "Recv failure: Connection reset by peer", sans aucune trace dans le domlog
# Apache, signe d'un blocage plutôt que d'un crash PHP classique).
#
set -euo pipefail

# --- Chemins (déduits de l'emplacement de ce script) --------------------------
RELEASE_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"   # .../releases/<REL>
BASE="$(cd "$RELEASE_DIR/../.." && pwd)"                          # ~/deploy/guillaumehurard
SHARED="$BASE/shared"
CURRENT="$BASE/current"

# Binaires o2switch : le wrapper CloudLinux-selector-aware (pas /opt/alt/...).
PHP="/usr/local/bin/php"

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

# --- 4. Vérification locale que le kernel boote, AVANT de basculer -----------
# Pas de requête HTTP ici (voir note en tête de fichier) : juste un boot du
# kernel Symfony en prod, qui échoue déjà (set -e) si la config/DB est cassée.
"$PHP" "$RELEASE_DIR/bin/console" about --env=prod > /dev/null

# --- 5. Bascule atomique du symlink `current` --------------------------------
ln -sfn "$RELEASE_DIR" "$CURRENT"

# --- 6. Purge : on ne garde que les 5 releases les plus récentes -------------
cd "$BASE/releases"
ls -1dt */ 2>/dev/null | tail -n +6 | xargs -r rm -rf

echo ">> OK — $RELEASE_DIR est en ligne (current -> $RELEASE_DIR)"
