.PHONY: install up down restart logs shell-backend shell-frontend shell-worker migrate migrate-fresh seed test test-backend test-frontend lint key-generate route-list worker

# ─── Setup inicial ────────────────────────────────────────────
install:
	@echo ">>> Construyendo imágenes..."
	docker compose build
	@echo ">>> Levantando servicios..."
	docker compose up -d
	@echo ">>> Esperando PostgreSQL..."
	docker compose exec -T backend sh -c 'until php -r "new PDO(\"pgsql:host=$${DB_HOST};port=$${DB_PORT};dbname=$${DB_DATABASE}\", \"$${DB_USERNAME}\", \"$${DB_PASSWORD}\");" 2>/dev/null; do sleep 2; done' || sleep 5
	@echo ">>> Generando APP_KEY..."
	docker compose exec backend php artisan key:generate --force
	@echo ">>> Ejecutando migraciones..."
	docker compose exec backend php artisan migrate --force
	@echo ">>> Ejecutando seeders..."
	docker compose exec backend php artisan db:seed --force
	@echo ">>> Instalando dependencias frontend..."
	docker compose exec frontend npm install
	@echo ""
	@echo "✓ Maya Logs listo en:"
	@echo "  Frontend → http://logs.localhost"
	@echo "  API      → http://logs-api.localhost"
	@echo "  Backend  → http://localhost:8002"

# ─── Ciclo de vida ────────────────────────────────────────────
up:
	docker compose up -d

down:
	docker compose down

restart:
	docker compose restart

logs:
	docker compose logs -f

# ─── Shells ───────────────────────────────────────────────────
shell-backend:
	docker compose exec backend bash

shell-frontend:
	docker compose exec frontend sh

shell-worker:
	docker compose exec worker bash

# ─── Base de datos ────────────────────────────────────────────
migrate:
	docker compose exec backend php artisan migrate

migrate-fresh:
	docker compose exec backend php artisan migrate:fresh --seed

seed:
	docker compose exec backend php artisan db:seed

# ─── Tests ────────────────────────────────────────────────────
# `env ...` evita que el compose (DB_CONNECTION=pgsql) gane sobre phpunit.xml; la suite usa
# sqlite :memory: y no toca log_mgmt_db.
test:
	docker compose exec backend env APP_ENV=testing DB_CONNECTION=sqlite DB_DATABASE=:memory: DB_URL= php artisan test

test-backend:
	docker compose exec backend env APP_ENV=testing DB_CONNECTION=sqlite DB_DATABASE=:memory: DB_URL= php artisan test --coverage --min=80

test-frontend:
	docker compose exec frontend npm run test

# ─── Linting ─────────────────────────────────────────────────
lint:
	docker compose exec backend ./vendor/bin/pint
	docker compose exec frontend npm run lint

# ─── Worker (consumer AMQP logs.ingest) ───────────────────────
worker:
	docker compose exec worker php artisan logs:consume

# ─── Utilidades ───────────────────────────────────────────────
key-generate:
	docker compose exec backend php artisan key:generate --force

route-list:
	docker compose exec backend php artisan route:list --path=api
