# Log Management Dashboard

Panel de administración web para la gestión centralizada de logs de error multi-aplicación.

Permite visualizar errores en tiempo real (SSE), filtrarlos por severidad, origen y fecha, y archivarlos en un histórico permanente con hilos de comentarios enriquecidos.

## Stack

- Laravel 12
- React 19
- PostgreSQL
- Docker (Laravel Sail)

---

# Requisitos

Antes de empezar necesitas tener instalado:

- Git
- Docker Desktop

No necesitas instalar PHP ni PostgreSQL en tu máquina.

---

# Instalación del proyecto

Clona el repositorio:

```bash
git clone https://github.com/TU_USUARIO/log-management-dashboard.git
cd log-management-dashboard
```

Copia el archivo de entorno a partir de la plantilla

```bash
cp .env.example .env
```

Instala las dependencias de PHP

```bash
composer install
```

Levanta el entorno con Docker

```bash
./vendor/bin/sail up -d
```

Genera la clave de Laravel

```bash
./vendor/bin/sail artisan key:generate
```

Ejecuta las migraciones

```bash
./vendor/bin/sail artisan migrate
```

Instala las dependencias del frontend

```bash
./vendor/bin/sail npm install
```

Ejecuta Vite

```bash
./vendor/bin/sail npm run dev
```

# Acceso

Comprueba que en http://localhost se visualiza la página inicial de Laravel

