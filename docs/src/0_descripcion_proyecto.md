# Descripción del Proyecto

Quiero diseñar un sistema para administrar los logs de múltiples aplicaciones. A partir de una base de datos PostgreSQL donde estarán los logs categorizados por tipo `[Critical, High, Medium, Low, Others]`, la idea es tener un panel de administración y gestión de los mismos.

El panel tendrá un **dashboard** donde se vean cards con los contadores de error por tipo. Al pulsar una card se accede a una tabla paginada con filtros. Al pulsar sobre una fila se abre la vista detalle. Desde el detalle se puede archivar el log en un histórico añadiendo comentarios con texto enriquecido. Habrá también una pestaña dedicada al histórico con filtros y ordenación. Adicionalmente existirá un módulo independiente de **catálogo de códigos de error** (CRUD completo) para que los equipos registren y clasifiquen sus errores de forma normalizada.

---

## Decisiones de Diseño

| Pregunta | Decisión |
| --- | --- |
| ¿Multi-tenant o monocliente? | **Monocliente** — múltiples apps del mismo sistema, todos los usuarios ven todo |
| ¿Cómo llegan los logs? | **Ya existe un flujo n8n** que inserta en PostgreSQL — no hay que diseñar la ingesta |
| ¿Qué es el histórico? | Log con **campo de persistencia permanente**. Al archivar, desaparece de la vista de logs activos |
| ¿Solucionado vs Archivar? | **Dos acciones distintas**: "Solucionado" (F-02.8) descarta el log sin crear registro en el histórico ni requerir comentario. "Guardar en Histórico" archiva con comentario obligatorio, descripción y URL Tutorial |
| ¿Múltiples comentarios por log archivado? | **Sí**, hilo de comentarios por entrada archivada. Solo se muestran en Histórico |
| ¿Cuántos usuarios? | **Varios usuarios** del equipo dev. El administrador llega autenticado desde API externa |
| ¿Tiempo real? | **SSE (Server-Sent Events)** para el dashboard — eficiente y sin complejidad de WebSockets |
| ¿Autenticación propia? | **No** — el usuario llega autenticado por un sistema externo. Se simula con mock de sesión durante desarrollo |
| ¿Responsive / Mobile? | **Sí** — diseño mobile-first. Las filas de tabla son clicables en su totalidad (sin botón "Ver" independiente) |
| ¿Idiomas? | **i18n con archivos Laravel lang**. UI en español. Base para soporte futuro en valenciano. Código en inglés |

---

## Stack Tecnológico Definitivo

| Capa | Tecnología | Notas |
| --- | --- | --- |
| Backend | **Laravel 12 (PHP)** | Routing web, controladores, modelos Eloquent |
| Frontend dinámico | **Livewire 3** | Componentes reactivos server-side. Sin SPA separada |
| Frontend estático | **Blade + Alpine.js** | Componentes reutilizables, microinteracciones |
| Rich Text | **TipTap 2** (con Alpine.js) | Editor embebido en componentes Livewire vía JS bridge |
| Base de datos | **PostgreSQL** (existente) | Tablas `logs` (read-only), `archived_logs`, `comments`, `error_codes`, `users` |
| Tiempo real | **SSE via StreamedResponse** | Laravel escucha `pg_notify` y emite eventos SSE |
| Autenticación | **Sesión externa + mock** | El usuario viene de API externa. Mock de sesión en desarrollo |
| i18n | **Laravel Lang** | `resources/lang/es/` y futuro `resources/lang/va/` |

---

## Arquitectura de Vistas y Controladores

Habrá **4 controladores** y **3 vistas base** (reutilizadas con condicionales):

| Controlador | Vista | Ruta |
| --- | --- | --- |
| `DashboardController` | `dashboard.blade.php` | `/` |
| `LogController` | `logs/index.blade.php` + `logs/show.blade.php` | `/logs`, `/logs/{id}` |
| `ArchivedLogController` | (reutiliza `logs/show`) | `/historico`, `/historico/{id}` |
| `ErrorCodeController` | `errorcodes/index.blade.php` + `errorcodes/show.blade.php` | `/error-codes`, `/error-codes/{id}` |

La **vista detalle** (`show`) es compartida entre Logs y Histórico, mostrando contenido diferente mediante condicionales:

- **Desde Logs activos:** Título, Código Error, Mensaje, Aplicación, Descripción (solo lectura) + botón "Guardar en Histórico" o "Ver histórico"
- **Desde Histórico:** Todo lo anterior + Descripción editable + URL Tutorial + Sección Comentarios + botones "Editar" y "Borrar"

---

## Componentes Livewire Identificados (dinámicos)

| Componente | Función |
| --- | --- |
| `DashboardCards` | Cards con contadores actualizados vía SSE |
| `LogsTable` | Tabla paginada de logs activos con filtros (GET) y orden (POST) |
| `ArchivedLogsTable` | Tabla del histórico con mismos filtros/orden |
| `LogDetail` | Vista detalle (logs activos): mostrar datos, botón guardar/ver |
| `ArchivedLogDetail` | Vista detalle (histórico): editar descripción, URL tutorial, borrar |
| `CommentSection` | Hilo de comentarios con input sticky, paginable, editable por autor |
| `ErrorCodesTable` | Listado CRUD de la tabla `error_codes` |
| `ErrorCodeForm` | Formulario crear/editar error code |
| `ErrorCodeCommentSection` | Hilo de comentarios por código de error (reutiliza lógica de `CommentSection`) |

## Componentes Blade Reutilizables (estáticos)

| Componente | Función |
| --- | --- |
| `x-layout` | Layout principal con nav y footer |
| `x-nav` | Barra de navegación (Logs / Histórico / Error Codes + Logout) |
| `x-card` | Card genérica del dashboard |
| `x-badge-severity` | Badge de color por tipo (Critical/High/Medium/Low) |
| `x-modal-confirm` | Modal de confirmación de acciones destructivas |

---

## Módulo Error Codes

El equipo necesita un **catálogo normalizado de códigos de error** para que cuando un `try/catch` falle, devuelva un código predefinido y no mensajes arbitrarios.

- Campos: `código` (incremental por aplicación), `nombre/título`, `aplicación`, `descripción del contexto del error`, `fichero`, `línea`
- **Clave compuesta**: `(código + aplicación)` — el mismo código puede existir en distintas aplicaciones
- CRUD completo: crear, listar (con filtros), editar, borrar
- Opcional: comentarios por error code para documentación interna

---

## Notas de Implementación

- **Filtros y paginación → GET** (persiste en URL, compartible)
- **Ordenación de columnas → POST** (no persiste; se envía como formulario)
- **Comentarios** solo en Histórico y Error Codes. Editables únicamente por el autor
- Los **logs activos** se borran automáticamente cada X tiempo por un proceso externo (n8n). El panel solo los lee
- Los **logs archivados** son permanentes por defecto. La acción de borrado (F-04.9, `COULD`) es excepcional: requiere confirmación explícita, está protegida por autenticación y se registra en el log de aplicación (NFR-OBS-02). El usuario de BD del panel no tiene permiso `DELETE` sobre `archived_logs` cuando F-04.9 no está implementado; con F-04.9 activo, el delete se gestiona a nivel de autorización de aplicación (Policy), no de permisos de BD
- El **login** no se implementa en esta aplicación (la pantalla de login está descartada del wireframe)
- Los **textos de la UI** se externalizan en archivos `lang/es/` desde el principio
