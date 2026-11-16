#!/usr/bin/env bash
#
# Levanta la aplicación en Linux sin tocar los servicios del sistema:
# - MariaDB con un directorio de datos local (.dev/db)
# - PHP con el servidor embebido
#
# Uso:   ./dev/linux-dev.sh
#        TALLER_DEV_PORT=8081 ./dev/linux-dev.sh
#        TALLER_DEV_RESET=1 ./dev/linux-dev.sh   (borra la base local)
#
# Detener: Ctrl+C

set -euo pipefail

ROOT="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
DATA_DIR="${TALLER_DEV_DATA:-$ROOT/.dev/db}"
SOCK="$DATA_DIR/mysql.sock"
PORT="${TALLER_DEV_PORT:-8080}"

MARIADBD="$(command -v mariadbd || true)"
[ -n "$MARIADBD" ] || MARIADBD="/usr/sbin/mariadbd"
INSTALL_DB="$(command -v mariadb-install-db || command -v mysql_install_db || true)"
MYSQL="$(command -v mariadb || command -v mysql || true)"
MYSQL_ADMIN="$(command -v mariadb-admin || command -v mysqladmin || true)"
PHP="$(command -v php || true)"

for bin in "$MARIADBD" "$INSTALL_DB" "$MYSQL" "$MYSQL_ADMIN" "$PHP"; do
    if [ -z "$bin" ] || ! command -v "$bin" >/dev/null 2>&1; then
        echo "Falta un programa necesario: $bin" >&2
        echo "Instala: php php-mysqlnd php-mbstring mariadb-server" >&2
        exit 1
    fi
done

if [ "${TALLER_DEV_RESET:-0}" = "1" ]; then
    echo "Borrando base local en $DATA_DIR"
    rm -rf "$DATA_DIR"
fi

mkdir -p "$DATA_DIR"

DB_PID=""
PHP_PID=""

cleanup() {
    [ -n "$PHP_PID" ] && kill "$PHP_PID" 2>/dev/null || true
    [ -n "$DB_PID" ] && kill "$DB_PID" 2>/dev/null || true
    wait "$PHP_PID" "$DB_PID" 2>/dev/null || true
}
trap cleanup EXIT INT TERM

if [ ! -d "$DATA_DIR/mysql" ]; then
    echo "Inicializando MariaDB local..."
    "$INSTALL_DB" \
        --datadir="$DATA_DIR" \
        --auth-root-authentication-method=normal \
        --user="$(id -un)" >/dev/null 2>&1
fi

echo "Arrancando MariaDB..."
"$MARIADBD" \
    --datadir="$DATA_DIR" \
    --socket="$SOCK" \
    --skip-networking \
    --pid-file="$DATA_DIR/mariadb.pid" \
    --user="$(id -un)" >"$DATA_DIR/mariadb.log" 2>&1 &
DB_PID=$!

for _ in $(seq 1 60); do
    "$MYSQL_ADMIN" --socket="$SOCK" ping >/dev/null 2>&1 && break
    sleep 0.5
done

if ! "$MYSQL_ADMIN" --socket="$SOCK" ping >/dev/null 2>&1; then
    echo "MariaDB no arrancó. Revisa $DATA_DIR/mariadb.log" >&2
    tail -20 "$DATA_DIR/mariadb.log" >&2 || true
    exit 1
fi

if ! "$MYSQL" --socket="$SOCK" -u root -e "USE taller_automotriz" >/dev/null 2>&1; then
    echo "Creando e importando taller_automotriz..."
    "$MYSQL" --socket="$SOCK" -u root \
        -e "CREATE DATABASE taller_automotriz CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
    "$MYSQL" --socket="$SOCK" -u root taller_automotriz \
        < "$ROOT/BASE DE DATO/taller_automotriz.sql"
fi

echo
echo "Aplicación:  http://127.0.0.1:$PORT/VISTAS/LOGIN.HTML"
echo "Recepción:   Asadmin / 123456"
echo "Mecánico:    AsaCorredor / 789456"
echo "Detener:     Ctrl+C"
echo

"$PHP" -d "pdo_mysql.default_socket=$SOCK" \
    -S "127.0.0.1:$PORT" -t "$ROOT" &
PHP_PID=$!

wait "$PHP_PID"
