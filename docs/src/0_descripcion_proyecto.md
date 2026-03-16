# Descripción del Proyecto

Quiero diseñar un sistema para administrar los logs de múltiples aplicaciones. Para ello se espera que a partir de una base de datos Postgres donde estarán los logs categorizados por tipo [Critical, High, Medium, Low], la idea es tener un panel de administración y gestión de los mismos.

Tendrán un dashboard donde se vean unos botones o cards con los diferentes errores; si no existe el error irá a otros y también han de poder verse todos.

Al pulsar sobre un error deben listarse como una tabla con filtros donde podrán filtrar los errores por tipo, aplicación que produce el error, fecha, etc. Al pulsar en el error se debe visualizar el error y sus datos pudiendo almacenarlo en un histórico con comentarios. Los comentarios deben ser un texto enriquecido donde pueda añadir imágenes, etc.

Después habrá otra pestaña que permitirá ver los errores del histórico ordenados por tipo y fecha o que se puedan ordenar y filtrar.

| Pregunta | Decisión |
| --- | --- |
| ¿Multi-tenant o monocliente? | **Monocliente** — múltiples apps del mismo sistema, todos los usuarios ven todo |
| ¿Cómo llegan los logs? | **Ya existe un flujo n8n** que inserta en Postgres — no hay que diseñar la ingesta |
| ¿Qué es el histórico? | Log con **campo de persistencia permanente**; los logs vivos son borrables/expirables. Al archivar, desaparece de la vista principal |
| ¿Múltiples comentarios por log archivado? | **Sí**, hilo de comentarios por entrada archivada |
| ¿Cuántos usuarios? | **Un único usuario** administrador |
| ¿Tiempo real? | **SSE (Server-Sent Events)** para el dashboard — eficiente y sin complejidad de WebSockets |

## Estructura esperada de la tabla `logs` (gestionada por n8n)

La tabla `logs` **no es creada por el panel** — existe previamente en producción, insertada y mantenida por el flujo n8n. La migración `2026_03_16_135340_create_logs_table.php` usa `Schema::hasTable('logs')` para evitar recrearla; únicamente documenta su estructura y la crea en entornos limpios de desarrollo/test.

| Columna | Tipo | Restricciones |
| --- | --- | --- |
| `id` | bigserial | PK |
| `error_code_id` | bigint | FK nullable → `error_codes.id` (nullOnDelete) |
| `application_id` | bigint | FK → `applications.id` (cascadeOnDelete) |
| `severity` | enum | `critical`, `high`, `medium`, `low`, `other` |
| `message` | text | NOT NULL |
| `file` | varchar | nullable |
| `line` | integer | nullable |
| `metadata` | jsonb | nullable |
| `matched_archived_log_id` | bigint | nullable, sin FK (referencia débil al histórico) |
| `resolved` | boolean | default `false` |
| `created_at` | timestamptz | nullable |

**Sin `deleted_at`**: los logs se eliminan físicamente al archivar o por el script de purga programada (no se usa soft delete).

**Índices críticos** (NFR-ESC-02):
- `logs(error_code_id)`
- `logs(application_id, created_at)`
- `logs(severity, resolved)`
- `logs(matched_archived_log_id)`
