# 📋 Épicas y Features — Log Management Dashboard

**Proyecto:** Panel de Administración y Gestión de Logs Multi-Aplicación
**Fecha actualización:** 2026-03-14
**Stack:** Laravel 12 + Livewire 3 + PostgreSQL
**Estado:** Actualizado tras SCRUM Daily 2026-03-13

---

## EPIC-00: Setup e Infraestructura Base

> Fundamentos técnicos necesarios antes de cualquier funcionalidad de negocio. Orden obligatorio de implementación.

| Feature ID | Feature | Prioridad MoSCoW |
| --- | --- | --- |
| F-00.1 | Instalación de Laravel 12 + Livewire 3 + Alpine.js | MUST |
| F-00.2 | Migraciones de BD y modelos Eloquent (logs, archived_logs, error_codes, users, comments) | MUST |
| F-00.3 | Layout compartido + componente Blade `x-nav` (Logs / Histórico / Error Codes + Logout) | MUST |
| F-00.4 | Internacionalización (i18n) con archivos Laravel lang (es + base va) | SHOULD |

---

## EPIC-01: Dashboard Principal (Vista en Tiempo Real)

> Panel central con visibilidad inmediata del estado de salud del sistema.

| Feature ID | Feature | Prioridad MoSCoW |
| --- | --- | --- |
| F-01.1 | Cards/Botones de resumen por tipo de error (Critical, High, Medium, Low, Others) | MUST |
| F-01.2 | Card "Todos los errores" con contador total | MUST |
| F-01.3 | Actualización en tiempo real via SSE (Server-Sent Events) — componente Livewire `DashboardCards` | MUST |
| F-01.4 | Indicador visual de severidad (colores por tipo) — componente Blade `x-badge-severity` | SHOULD |
| F-01.5 | Card con contadores por aplicación fuente | COULD |

---

## EPIC-02: Listado y Filtrado de Logs Activos

> Vista tabular paginada con filtrado avanzado — componente Livewire `LogsTable`.

| Feature ID | Feature | Prioridad MoSCoW |
| --- | --- | --- |
| F-02.1 | Tabla de logs paginada al pulsar una card del Dashboard. Fila entera clicable (mobile-first) | MUST |
| F-02.2 | Filtro por tipo de error (Critical, High, Medium, Low) | MUST |
| F-02.3 | Filtro por aplicación origen | MUST |
| F-02.4 | Filtro por rango de fechas | MUST |
| F-02.5 | Filtro por texto libre (búsqueda en mensaje) | SHOULD |
| F-02.6 | Columnas ordenables — clic en cabecera envía orden por POST | SHOULD |
| F-02.7 | Filtros y paginación persistidos en URL (query params GET) | MUST |
| F-02.8 | Estado del log: activo / solucionado (soft state). Los solucionados desaparecen de la vista activa | SHOULD |

---

## EPIC-03: Detalle y Archivado de un Log

> Vista detalle compartida con Histórico — componente Livewire `LogDetail`.

| Feature ID | Feature | Prioridad MoSCoW |
| --- | --- | --- |
| F-03.1 | Vista detalle del log con todos sus metadatos (título, código error, mensaje, aplicación, descripción) | MUST |
| F-03.2 | Acción "Guardar en Histórico" (con comentario inicial obligatorio) o "Ver histórico" si ya existe | MUST |
| F-03.3 | Editor de texto enriquecido (TipTap 2 + Alpine.js bridge) para todos los comentarios del sistema | MUST |
| F-03.4 | Al guardar en histórico, el log desaparece de la vista activa | MUST |
| F-03.5 | Log archivado es persistente y no expirable. Sin botón borrar en esta vista | MUST |

---

## EPIC-04: Histórico de Logs Archivados

> Sección dedicada — componente Livewire `ArchivedLogsTable` + `ArchivedLogDetail`.

| Feature ID | Feature | Prioridad MoSCoW |
| --- | --- | --- |
| F-04.1 | Vista de histórico en pestaña dedicada de la navegación | MUST |
| F-04.2 | Ordenación por tipo, fecha de archivado y aplicación | MUST |
| F-04.3 | Filtros equivalentes a los de logs activos (tipo, app, fecha) | MUST |
| F-04.4 | Hilo de comentarios múltiples con paginación y nombre del autor | MUST |
| F-04.5 | Editor rich text (TipTap 2) en nuevos comentarios del histórico | MUST |
| F-04.6 | Visualización del hilo completo de comentarios en la vista detalle del histórico | MUST |
| F-04.7 | Campo URL Tutorial editable (enlace a gestor documental) | SHOULD |
| F-04.8 | Descripción editable desde la vista detalle del histórico | SHOULD |
| F-04.9 | Botón "Borrar" del registro histórico (solo desde vista histórico) | COULD |

---

## EPIC-05: Infraestructura y Conectividad

> Capa técnica que soporta el sistema.

| Feature ID | Feature | Prioridad MoSCoW |
| --- | --- | --- |
| F-05.1 | Conexión a base de datos PostgreSQL existente (read + write) | MUST |
| F-05.2 | Endpoint SSE para push de eventos al componente `DashboardCards` | MUST |
| F-05.3 | Autenticación externa: usuario llega de API externa. Mock de sesión para desarrollo | MUST |
| F-05.4 | Acciones Livewire para CRUD de logs archivados y comentarios (sin API REST independiente) | MUST |
| F-05.5 | Tabla `users` y mock de sesión de usuario para testing de comentarios y atribución | MUST |

---

## EPIC-06: Catálogo de Códigos de Error

> Módulo CRUD independiente — componentes Livewire `ErrorCodesTable` + `ErrorCodeForm`.

| Feature ID | Feature | Prioridad MoSCoW |
| --- | --- | --- |
| F-06.1 | Listado paginado de error codes con filtros por aplicación y búsqueda. Botón "Crear" | MUST |
| F-06.2 | Vista detalle de error code: ver, editar (título, descripción, fichero, línea) y borrar | MUST |
| F-06.3 | Comentarios internos por error code (misma lógica que histórico) | COULD |

---

## Resumen de Prioridades

| Prioridad | Cantidad de Features |
| --- | --- |
| MUST | 29 |
| SHOULD | 7 |
| COULD | 3 |
| WON'T | 0 |

---

## Orden de Implementación Recomendado

```text
Sprint 0 — Base
  1. F-00.1  Setup Laravel + Livewire
  2. F-00.2  Migraciones y modelos
  3. F-05.1  Conexión PostgreSQL
  4. F-05.5  Tabla users + mock sesión
  5. F-00.3  Layout + navegación

Sprint 1 — Vistas principales
  6. F-00.4  i18n
  7. F-01.1/01.2  Dashboard cards
  8. F-01.4  Badges de severidad
  9. F-02.1  Tabla de logs (sin filtros aún)
 10. F-02.7  Persistencia filtros en URL
 11. F-02.2/03/04  Filtros de tipo/app/fecha
 12. F-02.6  Ordenación por columnas
 13. F-02.8  Estado activo/solucionado

Sprint 2 — Detalle y archivado
 14. F-03.1  Vista detalle log
 15. F-03.3  Editor rich text
 16. F-03.2  Guardar en histórico
 17. F-03.4  Log desaparece de activos
 18. F-01.3  SSE tiempo real
 19. F-05.2  Endpoint SSE

Sprint 3 — Histórico
 20. F-04.1  Vista histórico (pestaña)
 21. F-04.2/03  Filtros y ordenación histórico
 22. F-04.7/08  URL Tutorial + Descripción editable
 23. F-04.4  Hilo de comentarios
 24. F-04.5/06  Rich text + sticky + edición autor
 25. F-04.9  Borrar del histórico

Sprint 4 — Error Codes
 26. F-06.1  Error codes index
 27. F-06.2  Error code detalle/editar/borrar
 28. F-06.3  Comentarios en error codes

Sprint 5 — Mejoras y pulido
 29. F-02.5  Filtro texto libre
 30. F-01.5  Cards por aplicación
 31. F-05.3  Integración auth externa real
```
