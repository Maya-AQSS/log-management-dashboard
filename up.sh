#!/usr/bin/env bash
# ─────────────────────────────────────────────────────────────────────────────
# up.sh — Script de arranque de Log Management Dashboard
#
# Uso:
#   ./up.sh            Arranca todos los servicios
#   ./up.sh --build    Fuerza rebuild de imágenes
#   ./up.sh down       Para todos los servicios
#   ./up.sh logs       Sigue los logs de todos los servicios
# ─────────────────────────────────────────────────────────────────────────────
set -euo pipefail

SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
cd "$SCRIPT_DIR"

# ─── Colores ─────────────────────────────────────────────────────────────────
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
CYAN='\033[0;36m'
NC='\033[0m'

info()    { echo -e "${CYAN}[log-mgmt]${NC} $*"; }
success() { echo -e "${GREEN}[log-mgmt]${NC} $*"; }
warn()    { echo -e "${YELLOW}[log-mgmt]${NC} $*"; }

# ─── Cargar .env ─────────────────────────────────────────────────────────────
if [[ ! -f .env ]]; then
    warn ".env no encontrado — copiando desde .env.example"
    cp .env.example .env
    NEED_KEY_GENERATE=true
else
    NEED_KEY_GENERATE=false
fi
set -a; source .env; set +a

# ─── Subcomandos rápidos ──────────────────────────────────────────────────────
DC="docker compose -f docker-compose.yml"

case "${1:-}" in
    down)
        info "Parando todos los servicios..."
        $DC down
        exit 0
        ;;
    logs)
        $DC logs -f "${@:2}"
        exit 0
        ;;
    ps|status)
        $DC ps
        exit 0
        ;;
esac

# ─── Verificar y levantar infra compartida ───────────────────────────────────
# Por defecto busca en ../infra (repo hermano). Puedes sobreescribir con:
#   MAYA_INFRA_DIR=/ruta/absoluta/a/infra ./up.sh
INFRA_SCRIPT="${MAYA_INFRA_DIR:-$SCRIPT_DIR/../infra}/ensure-running.sh"
if [[ -f "$INFRA_SCRIPT" ]]; then
    bash "$INFRA_SCRIPT"
else
    warn "Script de infra no encontrado en: $INFRA_SCRIPT"
    warn "Clona el repo de infra al mismo nivel o define MAYA_INFRA_DIR=/ruta/a/infra"
    exit 1
fi

# ─── Flags extra ─────────────────────────────────────────────────────────────
EXTRA_FLAGS=()
[[ "${1:-}" == "--build" ]] && EXTRA_FLAGS+=("--build")

# ─── Levantar servicios ──────────────────────────────────────────────────────
info "Levantando servicios..."
$DC up -d ${EXTRA_FLAGS[@]+"${EXTRA_FLAGS[@]}"}

# ─── Generar APP_KEY si es .env nuevo ─────────────────────────────────────────
if [[ "$NEED_KEY_GENERATE" == true ]]; then
    info "Generando APP_KEY..."
    for i in $(seq 1 10); do
      if docker exec maya_log_mgmt php -v > /dev/null 2>&1; then
        # Generate key and capture it
        NEW_KEY=$(docker exec maya_log_mgmt php artisan key:generate --show 2>/dev/null)
        if [[ -n "$NEW_KEY" ]]; then
          # Write to host .env so docker compose can pass it as env var
          sed -i "s|^APP_KEY=.*|APP_KEY=${NEW_KEY}|" .env
          # Restart container to pick up the new env var
          $DC restart
          success "APP_KEY generada y aplicada."
        fi
        break
      fi
      sleep 2
    done
fi

# ─── Migraciones automáticas ──────────────────────────────────────────────────
BACKEND_CONTAINER="maya_log_mgmt"
DB_READY=false

# 1) Esperar a que el contenedor responda
info "Esperando a que el backend esté listo..."
for i in $(seq 1 20); do
  if docker exec "$BACKEND_CONTAINER" php -v > /dev/null 2>&1; then
    break
  fi
  sleep 2
done

# 1b) Build de assets Vite (bind mount sobrescribe la imagen)
if ! docker exec "$BACKEND_CONTAINER" test -f public/build/manifest.json; then
  info "Compilando assets Vite..."
  docker exec "$BACKEND_CONTAINER" npm run build
  success "Assets compilados."
fi

# 2) Esperar conexión con la BD (PDO directo — sin bootstrap de Laravel)
info "Esperando conexión con la base de datos..."
for i in $(seq 1 40); do
  DB_ERR=$(docker exec "$BACKEND_CONTAINER" php -r '
    try {
      $h = getenv("DB_HOST") ?: "maya_infra_postgres";
      $p = getenv("DB_PORT") ?: "5432";
      $d = getenv("DB_DATABASE");
      $u = getenv("DB_USERNAME");
      $w = getenv("DB_PASSWORD");
      new PDO("pgsql:host=$h;port=$p;dbname=$d", $u, $w, [PDO::ATTR_TIMEOUT => 3]);
    } catch (Exception $e) {
      fwrite(STDERR, $e->getMessage());
      exit(1);
    }' 2>&1 >/dev/null) && { DB_READY=true; break; }
  if (( i % 10 == 0 )); then
    info "  … esperando BD ($((i * 3))s/120s): $DB_ERR"
  fi
  sleep 3
done

# 3) Ejecutar migraciones si la BD está accesible
if [[ "$DB_READY" == true ]]; then
  PENDING=$(docker exec "$BACKEND_CONTAINER" php artisan migrate:status 2>&1 | grep -c "Pending" || true)
  TOTAL=$(docker exec "$BACKEND_CONTAINER" php artisan migrate:status 2>&1 | grep -cE "Ran|Pending" || true)

  if [[ "$TOTAL" -eq 0 ]] || [[ "$TOTAL" -eq "$PENDING" ]]; then
    info "Base de datos vacía — ejecutando migraciones y seeds..."
    docker exec "$BACKEND_CONTAINER" php artisan migrate --seed --force
    success "Migraciones y seeds aplicados."
  elif [[ "$PENDING" -gt 0 ]]; then
    info "${PENDING} migraciones pendientes — ejecutando migrate..."
    docker exec "$BACKEND_CONTAINER" php artisan migrate --force
    success "Migraciones aplicadas."
  else
    success "Base de datos al día — nada que migrar."
  fi
else
  warn "No se pudo conectar con la BD — omitiendo migraciones automáticas."
  warn "Ejecuta manualmente: docker exec $BACKEND_CONTAINER php artisan migrate --seed --force"
fi

# ─── URLs de acceso ───────────────────────────────────────────────────────────
echo ""
success "Sistema listo. Accesos disponibles:"
echo -e "  ${GREEN}Dashboard:${NC}        http://logs.localhost"
echo -e "  ${GREEN}Keycloak:${NC}         http://keycloak.localhost"
echo -e "  ${GREEN}Traefik dashboard:${NC} http://localhost:8888"
echo ""
echo -e "  ${YELLOW}Acceso directo (sin Traefik):${NC}"
echo -e "    Backend:   http://localhost:${BACKEND_PORT:-8002}"
echo -e "    Vite HMR:  http://localhost:${VITE_PORT:-5176}"
echo ""
