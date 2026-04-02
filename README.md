# Log Management Dashboard

Panel de administración web para la gestión centralizada de logs de error multi-aplicación.

Permite visualizar errores en tiempo real (SSE), filtrarlos por severidad, origen y fecha, y archivarlos en un histórico permanente con hilos de comentarios enriquecidos.

## Stack

- Laravel 12 + Livewire 4
- PostgreSQL 17 (compartido vía `infra/`)
- TailwindCSS 4 + Alpine.js
- Docker

## Prerequisitos

- Docker Engine 20.10+
- Docker Compose v2+
- Repo `infra/` clonado al mismo nivel que este proyecto

## Infraestructura compartida

La base de datos PostgreSQL **no** está en este proyecto — vive en el repo `infra/`, compartido por todo el ecosistema Maya. El script `up.sh` la levanta automáticamente si no está corriendo.

### Clonar infra

Clona el repo de infra **al mismo nivel** que este proyecto:

```bash
git clone <url-repo-infra> ../infra
```

Resultado esperado:

```text
~/desarrollo/
├── infra/                       ← repo infra
├── maya_authorization/
├── maya-dms/
├── log-management-dashboard/    ← este proyecto
└── maya-dashboard/
```

Si tienes infra en otra ubicación, usa la variable de entorno:

```bash
MAYA_INFRA_DIR=/ruta/absoluta/a/infra ./up.sh
```

## Instalación

```bash
git clone <repository-url>
cd log-management-dashboard
./up.sh --build
```

El script `up.sh` se encarga de todo automáticamente:

- Copia `.env.example` → `.env` si no existe
- Levanta la infra compartida (Traefik, PostgreSQL, Keycloak, Redis, RabbitMQ)
- Construye y levanta el contenedor
- Genera `APP_KEY` si es un `.env` nuevo
- Ejecuta migraciones y seeders si la BD está vacía

> Solo necesitas editar `.env` si quieres cambiar valores por defecto. El `.env.example` ya contiene los valores correctos del ecosistema.

## Arranque diario

```bash
./up.sh
```

Para parar:

```bash
./up.sh down
```

Otros subcomandos:

```bash
./up.sh logs            # seguir logs
./up.sh ps              # ver estado de contenedores
./up.sh --build         # reconstruir imagen
```

## URLs de acceso

| Servicio | URL (vía Traefik) | URL directa |
| --- | --- | --- |
| Dashboard | <http://logs.localhost> | <http://localhost:8002> |
| Keycloak | <http://keycloak.localhost> | <http://localhost:8180> |
| Traefik dashboard | <http://localhost:8888> | — |

### Credenciales por defecto

| Servicio | Usuario | Contraseña |
| --- | --- | --- |
| PostgreSQL | `log_mgmt_user` | `secret` |
| Keycloak Admin | `admin` | `admin` |

## Comandos útiles

```bash
# Migraciones
docker exec maya_log_mgmt php artisan migrate
docker exec maya_log_mgmt php artisan migrate --seed --force

# Cache
docker exec maya_log_mgmt php artisan config:clear
docker exec maya_log_mgmt php artisan cache:clear

# Shell
docker exec -it maya_log_mgmt bash

# Tests
docker exec maya_log_mgmt php artisan test

# Vite assets (desarrollo)
docker exec maya_log_mgmt npm run dev
```

## Solución de problemas

### Infra no arranca / red maya_network no existe

```bash
cd ../infra && ./ensure-running.sh
```

### Reset completo de la BD

```bash
./up.sh down
# Destruir volume de postgres en infra si necesitas recrear la BD
docker exec maya_log_mgmt php artisan migrate:fresh --seed --force
```

### Backend no conecta a la BD

El `.env` se crea automáticamente con los valores correctos. Si necesitas verificar:

```env
DB_HOST=maya_infra_postgres   # NO 127.0.0.1, NO pgsql
DB_DATABASE=log_mgmt_db
DB_USERNAME=log_mgmt_user
```

## Arquitectura

```text
infra/
  Traefik (:80, :8888) ──── enruta *.localhost
  PostgreSQL (:5432)    ──── BD compartida (log_mgmt_db)
  Keycloak (:8180)      ──── IdP compartido del ecosistema

log-management-dashboard/
  Backend (:8002) ──→ PostgreSQL (maya_infra_postgres)
       ↓
  Livewire 4 + Alpine.js (UI)
```

Todos los servicios comparten la red Docker `maya_network`.

