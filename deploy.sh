#!/bin/sh
# Despliega la rama de produccion a Hostinger via SSH. Corre esto desde tu
# maquina (Git Bash en Windows, o cualquier shell POSIX), nunca desde el
# servidor. Requiere deploy.config.sh (copia deploy.config.sh.example y
# rellena tus datos reales - ese archivo si esta en .gitignore).
#
# Uso:
#   ./deploy.sh              # checklist completo + build + despliegue
#   ./deploy.sh --skip-tests # salta php artisan test / pint / phpstan /
#                             # lint / typecheck (para un hotfix urgente;
#                             # el build de assets igual se hace siempre)
#   ./deploy.sh --yes        # no pregunta confirmacion antes de desplegar

set -eu

# ---------------------------------------------------------------------------
# Config
# ---------------------------------------------------------------------------

DIRECTORIO_SCRIPT="$(cd "$(dirname "$0")" && pwd)"
ARCHIVO_CONFIG="$DIRECTORIO_SCRIPT/deploy.config.sh"

if [ ! -f "$ARCHIVO_CONFIG" ]; then
    echo "Falta $ARCHIVO_CONFIG" >&2
    echo "Copia deploy.config.sh.example a deploy.config.sh y rellena tus datos reales." >&2
    exit 1
fi

# shellcheck source=/dev/null
. "$ARCHIVO_CONFIG"

: "${SSH_HOST:?falta SSH_HOST en deploy.config.sh}"
: "${REMOTE_APP_PATH:?falta REMOTE_APP_PATH en deploy.config.sh}"
: "${DEPLOY_BRANCH:?falta DEPLOY_BRANCH en deploy.config.sh}"
: "${HEALTH_CHECK_URL:?falta HEALTH_CHECK_URL en deploy.config.sh}"

PUERTO_SSH="${SSH_PORT:-}"
SALTAR_CHECKS=0
CONFIRMAR=1

for arg in "$@"; do
    case "$arg" in
        --skip-tests) SALTAR_CHECKS=1 ;;
        --yes|-y) CONFIRMAR=0 ;;
        *)
            echo "Argumento desconocido: $arg" >&2
            exit 1
            ;;
    esac
done

ssh_args() {
    if [ -n "$PUERTO_SSH" ]; then
        printf '%s' "-p $PUERTO_SSH"
    fi
}

paso() {
    printf '\n\033[1;36m==> %s\033[0m\n' "$1"
}

ok() {
    printf '\033[1;32m%s\033[0m\n' "$1"
}

# ---------------------------------------------------------------------------
# 1. Chequeos locales antes de tocar nada remoto
# ---------------------------------------------------------------------------

paso "Verificando la rama local"
cd "$DIRECTORIO_SCRIPT"

rama_actual="$(git rev-parse --abbrev-ref HEAD)"
if [ "$rama_actual" != "$DEPLOY_BRANCH" ]; then
    echo "Estas en '$rama_actual', pero DEPLOY_BRANCH es '$DEPLOY_BRANCH'. Cambia de rama antes de desplegar." >&2
    exit 1
fi

if [ -n "$(git status --porcelain)" ]; then
    echo "Hay cambios sin commitear. Commitea, descarta o guarda en stash antes de desplegar." >&2
    exit 1
fi

paso "Verificando que local coincide con origin/$DEPLOY_BRANCH"
git fetch origin "$DEPLOY_BRANCH" --quiet
local_rev="$(git rev-parse HEAD)"
remoto_rev="$(git rev-parse "origin/$DEPLOY_BRANCH")"

if [ "$local_rev" != "$remoto_rev" ]; then
    echo "Tu rama local no coincide con origin/$DEPLOY_BRANCH." >&2
    echo "El servidor va a hacer git pull de origin, no de tu copia local - sube o trae los cambios primero." >&2
    exit 1
fi
ok "Rama '$DEPLOY_BRANCH' limpia y sincronizada con origin."

if [ "$SALTAR_CHECKS" -eq 0 ]; then
    paso "Checklist de calidad (php artisan test, pint, phpstan, lint, typecheck)"
    php artisan test
    ./vendor/bin/pint --test
    ./vendor/bin/phpstan analyse
    npm run lint
    npm run typecheck
    ok "Checklist en verde."
else
    echo "Saltando checklist de calidad (--skip-tests)."
fi

paso "Compilando assets de produccion (npm run build)"
npm run build
ok "Build listo en public/build."

# ---------------------------------------------------------------------------
# 2. Confirmacion antes de tocar produccion
# ---------------------------------------------------------------------------

if [ "$CONFIRMAR" -eq 1 ]; then
    printf '\nVas a desplegar "%s" a %s:%s (puerto: %s). Continuar? [s/N] ' \
        "$DEPLOY_BRANCH" "$SSH_HOST" "$REMOTE_APP_PATH" "${PUERTO_SSH:-22 o el del alias}"
    read -r respuesta
    case "$respuesta" in
        s|S|si|Si|SI) ;;
        *)
            echo "Cancelado."
            exit 1
            ;;
    esac
fi

# ---------------------------------------------------------------------------
# 3. Subir los assets compilados (gitignorados, el servidor no los genera)
# ---------------------------------------------------------------------------

paso "Subiendo public/build al servidor"
# shellcheck disable=SC2086
if command -v rsync >/dev/null 2>&1; then
    rsync -az --delete -e "ssh $(ssh_args)" \
        "$DIRECTORIO_SCRIPT/public/build/" \
        "$SSH_HOST:$REMOTE_APP_PATH/public/build/"
else
    echo "rsync no esta disponible, usando scp (mas lento, sin borrar archivos viejos del remoto)." >&2
    ssh $(ssh_args) "$SSH_HOST" "mkdir -p '$REMOTE_APP_PATH/public/build'"
    scp -r $(ssh_args) \
        "$DIRECTORIO_SCRIPT/public/build/." \
        "$SSH_HOST:$REMOTE_APP_PATH/public/build/"
fi
ok "Assets subidos."

# ---------------------------------------------------------------------------
# 4. Desplegar codigo y migrar en el servidor
# ---------------------------------------------------------------------------

paso "Desplegando codigo en el servidor"
# shellcheck disable=SC2086,SC2029
ssh $(ssh_args) "$SSH_HOST" "DEPLOY_BRANCH='$DEPLOY_BRANCH' REMOTE_APP_PATH='$REMOTE_APP_PATH' bash -s" <<'COMANDOS_REMOTOS'
set -eu
cd "$REMOTE_APP_PATH"

# Si algo de aqui en adelante falla, deja el modo mantenimiento activo lo
# menos posible: siempre intenta volver a levantar el sitio al salir,
# exitoso o no.
trap 'php artisan up >/dev/null 2>&1 || true' EXIT

echo "-> Modo mantenimiento"
php artisan down || true

echo "-> git fetch + reset a origin/$DEPLOY_BRANCH"
git fetch origin "$DEPLOY_BRANCH" --quiet
git reset --hard "origin/$DEPLOY_BRANCH"

echo "-> composer install"
composer install --no-dev --optimize-autoloader --no-interaction

echo "-> migraciones"
php artisan migrate --force

echo "-> cache"
php artisan config:clear
php artisan config:cache
php artisan route:cache
php artisan event:cache

echo "-> storage:link (se ignora si ya existe)"
php artisan storage:link || true

echo "-> reiniciar colas (que el proximo queue:work del cron use el codigo nuevo)"
php artisan queue:restart

echo "-> listo del lado del servidor"
COMANDOS_REMOTOS
ok "Despliegue remoto terminado (modo mantenimiento ya deberia estar apagado)."

# ---------------------------------------------------------------------------
# 5. Smoke check
# ---------------------------------------------------------------------------

paso "Verificando $HEALTH_CHECK_URL"
codigo_http="$(curl -s -o /dev/null -w '%{http_code}' "$HEALTH_CHECK_URL" || echo "000")"

if [ "$codigo_http" = "200" ]; then
    ok "/estado respondio 200. Despliegue completo."
else
    echo "/estado respondio '$codigo_http', no 200 - revisa storage/logs/laravel.log en el servidor antes de dar esto por bueno." >&2
    exit 1
fi
