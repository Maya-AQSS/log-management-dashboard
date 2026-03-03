# 📐 Documentación Visual — Log Management Dashboard

**Proyecto:** Panel de Administración y Gestión de Logs Multi-Aplicación
**Fecha:** 2026-03-03
**Skill:** System Architect — C4 Model + Flujos
**Estado:** FASE 4 Completada

---

## 1. C4 Level 1 — System Context

> Cómo el sistema encaja en el mundo, interactuando con usuarios y sistemas externos.

```mermaid
C4Context
    title System Context — Log Management Dashboard

    Person(admin, "Administrador", "Usuario único que revisa,\ngestiona y archiva logs de error.")

    System(dashboard, "Log Management Dashboard", "Panel web para visualizar logs en\ntiempo real, filtrarlos y archivarlos\ncon comentarios enriquecidos.")

    System_Ext(n8n, "n8n (Automatización)", "Orquesta flujos de datos.\nInserta logs en PostgreSQL\ndesde múltiples aplicaciones.")

    System_Ext(apps, "Aplicaciones Monitorizadas", "Servicios que generan errores\n(api-gateway, workers, etc.).\nNo interactúan directamente con el panel.")

    System_Ext(postgres, "PostgreSQL", "Base de datos compartida.\nContiene tabla `logs` (ingesta de n8n)\ny tablas `archived_logs` y `comments`\n(gestionadas por el panel).")

    Rel(admin, dashboard, "Usa", "HTTPS / Navegador")
    Rel(apps, n8n, "Envían eventos de error", "HTTP / Webhook")
    Rel(n8n, postgres, "Inserta logs", "SQL / TCP")
    Rel(dashboard, postgres, "Lee logs activos,\nescribe archivados y comentarios", "SQL / TCP")
```

---

## 2. C4 Level 2 — Container Diagram

> Las aplicaciones y bases de datos que componen el sistema.

```mermaid
C4Container
    title Container Diagram — Log Management Dashboard

    Person(admin, "Administrador", "Usuario único del panel.")

    System_Ext(n8n, "n8n", "Inserta logs en PostgreSQL.")

    System_Boundary(dashboard, "Log Management Dashboard (Monorepo)") {
        Container(frontend, "Frontend SPA", "React 19 + Vite", "Interfaz de usuario.\nDashboard, listado, detalle,\nhistórico y editor rich text (TipTap 2).\nCompilado por Vite a public/build/.")

        Container(backend, "Backend + Static Server", "Laravel 12 (PHP)", "Sirve la SPA compilada en public/build.\nExpone API REST autenticada con Sanctum.\nGestiona logs, archivado, comentarios\ny streaming SSE. Sin CORS necesario.")

        Container(sse_worker, "SSE Worker", "Laravel Artisan Command\n(Supervisor)", "Escucha pg_notify de PostgreSQL\ny emite eventos al cliente SSE\ncuando hay nuevos logs.")
    }

    SystemDb_Ext(postgres, "PostgreSQL", "BD compartida.\nTablas: `logs`, `archived_logs`, `comments`.")

    Rel(admin, frontend, "Usa", "HTTPS / Navegador")
    Rel(frontend, backend, "Peticiones API REST\ny conexión SSE", "HTTPS / JSON")
    Rel(backend, postgres, "Lee y escribe\n(Eloquent ORM)", "TCP / SQL")
    Rel(sse_worker, postgres, "Escucha NOTIFY\nen tabla `logs`", "TCP / pg_notify")
    Rel(sse_worker, backend, "Notifica nuevos logs\npara broadcast SSE", "Proceso interno")
    Rel(n8n, postgres, "INSERT en `logs`", "TCP / SQL")
```

---

## 3. Flujo 1 — Archivado de un Log

> Secuencia completa desde que el admin decide archivar hasta que el log desaparece de la vista activa.

```mermaid
sequenceDiagram
    actor Admin
    participant FE as Frontend (React)
    participant BE as Backend (Laravel 12)
    participant DB as PostgreSQL

    Admin->>FE: Pulsa "Archivar en Histórico" con comentario
    FE->>FE: Valida que el comentario no esté vacío
    FE->>BE: POST /api/logs/{id}/archive\n{ comment: "..." }
    BE->>BE: Autentica sesión Sanctum
    BE->>BE: Valida FormRequest (comentario obligatorio)
    BE->>BE: Sanitiza HTML del comentario (HTMLPurifier)
    BE->>DB: BEGIN TRANSACTION
    DB-->>BE: OK
    BE->>DB: INSERT INTO archived_logs (...)
    BE->>DB: INSERT INTO comments (archived_log_id, content, ...)
    BE->>DB: UPDATE logs SET archived = true WHERE id = {id}
    BE->>DB: COMMIT
    DB-->>BE: OK
    BE-->>FE: 201 Created { archived_log: {...} }
    FE->>FE: Elimina el log de la lista activa (estado local)
    FE->>FE: Muestra notificación "Log archivado correctamente"
    Note over FE,BE: El contador del dashboard se actualiza\nvía SSE en < 2s
```

---

## 4. Flujo 2 — Actualización en Tiempo Real (SSE)

> Cómo un nuevo log insertado por n8n llega al dashboard del administrador.

```mermaid
sequenceDiagram
    participant N8N as n8n
    participant DB as PostgreSQL
    participant Worker as SSE Worker (Artisan)
    participant BE as Backend (Laravel 12)
    participant FE as Frontend (React)
    actor Admin

    N8N->>DB: INSERT INTO logs (type, app_source, message, ...)
    DB->>Worker: NOTIFY 'logs_channel' (pg_notify)
    Worker->>BE: Señaliza nuevo evento disponible
    BE->>DB: SELECT COUNT(*) GROUP BY type (query agregada)
    DB-->>BE: Contadores actualizados
    BE-->>FE: SSE event: { type: "log_counts", data: {...} }
    FE->>FE: Actualiza contadores de cards en el dashboard
    FE-->>Admin: Cards actualizadas en < 2s sin recarga
```

---

## 5. Flujo 3 — Autenticación SPA (Sanctum)

> Login del administrador y protección de rutas.

```mermaid
sequenceDiagram
    actor Admin
    participant FE as Frontend (React)
    participant BE as Backend (Laravel 12)
    participant DB as PostgreSQL

    Admin->>FE: Introduce usuario y contraseña
    FE->>BE: GET /sanctum/csrf-cookie
    BE-->>FE: Cookie XSRF-TOKEN
    FE->>BE: POST /login { email, password }
    BE->>BE: Throttle check (rate limiting)
    BE->>DB: Verifica credenciales
    DB-->>BE: Usuario válido
    BE-->>FE: 200 OK + Set-Cookie: session (httpOnly, SameSite=Strict)
    FE->>FE: Redirige al dashboard
    Note over FE,BE: Todas las peticiones posteriores\nincluyen la cookie de sesión automáticamente
    Admin->>FE: Pulsa "Cerrar sesión"
    FE->>BE: POST /logout
    BE->>DB: Invalida sesión
    BE-->>FE: 200 OK + Clear cookie
    FE->>FE: Redirige al login
```

---

## 6. Diagrama de Base de Datos (Entity-Relationship)

> Schema completo de PostgreSQL. La tabla `logs` es de solo lectura para el panel (la gestiona n8n). Las tablas `archived_logs` y `comments` son creadas y gestionadas por el panel.

```mermaid
erDiagram
    users {
        bigserial   id              PK
        varchar     name
        varchar     email           "Único. Usado como login"
        varchar     password        "Hash bcrypt"
        timestamptz created_at
        timestamptz updated_at
    }

    personal_access_tokens {
        bigserial   id              PK
        varchar     tokenable_type  "App\\Models\\User"
        bigint      tokenable_id    FK
        varchar     name
        varchar     token           "Hash SHA-256 del token"
        text        abilities
        timestamptz last_used_at
        timestamptz created_at
        timestamptz updated_at
    }

    logs {
        bigserial   id              PK
        varchar     type            "Critical | High | Medium | Low | Others"
        varchar     app_source      "Aplicación origen"
        text        message         "Mensaje del error"
        jsonb       metadata        "Datos adicionales (stack trace, etc.)"
        boolean     archived        "false = activo, true = archivado"
        timestamptz created_at
    }

    archived_logs {
        bigserial   id              PK
        bigint      original_log_id "Referencia al log original (sin FK obligatoria)"
        bigint      archived_by_id  FK "Usuario que archivó"
        varchar     type            "Copia del tipo en el momento del archivado"
        varchar     app_source      "Copia de la app origen"
        text        message         "Copia del mensaje completo"
        jsonb       metadata        "Copia de los metadatos"
        timestamptz archived_at
        timestamptz created_at      "Fecha del log original"
    }

    comments {
        bigserial   id              PK
        bigint      archived_log_id FK
        bigint      user_id         FK "Usuario que escribió el comentario"
        text        content         "HTML sanitizado (TipTap output)"
        timestamptz created_at
        timestamptz updated_at
    }

    users ||--o{ personal_access_tokens : "tiene"
    users ||--o{ archived_logs : "archiva"
    users ||--o{ comments : "escribe"
    archived_logs ||--o{ comments : "tiene"
```

> **Notas de diseño:**
> - `users` es gestionada por Laravel 12 (migración estándar). Un único registro de administrador creado via seeder.
> - `personal_access_tokens` es la tabla estándar de **Laravel Sanctum** para autenticación SPA con cookies. Se crea automáticamente con `php artisan migrate`.
> - `logs.archived` se marca `true` al archivar. El panel nunca hace DELETE sobre `logs`.
> - `archived_logs` almacena una **copia desnormalizada** de los campos clave del log original para garantizar que el histórico es independiente aunque la tabla `logs` sea purgada por n8n en el futuro.
> - `original_log_id` no tiene FK obligatoria para no generar errores de integridad si n8n limpia la tabla `logs`.
> - `archived_by_id` y `comments.user_id` referencian `users.id` con FK. Permiten auditar quién hizo qué (NFR-OBS-02).
> - El panel **no tiene permiso DELETE ni TRUNCATE** sobre `archived_logs` ni `comments` (STRIDE T-DB-01).

---

## 7. Mapa de Rutas — Frontend (React Router)

```mermaid
graph TD
    A["/login"] -->|Autenticado| B["/dashboard"]
    B --> C["/logs?type=Critical"]
    B --> D["/logs (sin filtro)"]
    C --> E["/logs/:id (detalle)"]
    D --> E
    E --> F["Acción: Archivar\n→ Vuelve a /logs"]
    B --> G["/historico"]
    G --> H["/historico?type=High&app=api-gateway"]
    H --> I["/historico/:id (detalle archivado)"]
    I --> J["Añadir comentario al hilo"]
```
