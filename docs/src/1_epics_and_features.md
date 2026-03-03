# 📋 Épicas y Features — Log Management Dashboard

**Proyecto:** Panel de Administración y Gestión de Logs Multi-Aplicación
**Fecha:** 2026-03-03
**Estado:** FASE 1 Completada

---

## EPIC-01: Dashboard Principal (Vista en Tiempo Real)

> Panel central con visibilidad inmediata del estado de salud del sistema.

| Feature ID | Feature | Prioridad MoSCoW |
|---|---|---|
| F-01.1 | Cards/Botones de resumen por tipo de error (Critical, High, Medium, Low, Others) | MUST |
| F-01.2 | Card "Todos los errores" con contador total | MUST |
| F-01.3 | Actualización en tiempo real via SSE (Server-Sent Events) | MUST |
| F-01.4 | Indicador visual de severidad (colores por tipo) | SHOULD |
| F-01.5 | Card con contadores por aplicación fuente | COULD |

---

## EPIC-02: Listado y Filtrado de Logs Activos

> Vista tabular con capacidad de filtrado avanzado sobre los logs en curso.

| Feature ID | Feature | Prioridad MoSCoW |
|---|---|---|
| F-02.1 | Tabla de logs paginada al pulsar una card del Dashboard | MUST |
| F-02.2 | Filtro por tipo de error (Critical, High, Medium, Low) | MUST |
| F-02.3 | Filtro por aplicación origen | MUST |
| F-02.4 | Filtro por rango de fechas | MUST |
| F-02.5 | Filtro por texto libre (búsqueda en mensaje) | SHOULD |
| F-02.6 | Columnas ordenables (fecha, tipo, aplicación) | SHOULD |
| F-02.7 | Persistencia de filtros activos en URL (query params) | COULD |

---

## EPIC-03: Detalle y Archivado de un Log

> Visualización completa de un error individual y flujo de archivado al histórico.

| Feature ID | Feature | Prioridad MoSCoW |
|---|---|---|
| F-03.1 | Vista detalle del log con todos sus metadatos | MUST |
| F-03.2 | Acción "Archivar en Histórico" con comentario inicial obligatorio | MUST |
| F-03.3 | Editor de texto enriquecido (rich text) para comentarios (imágenes, formato) | MUST |
| F-03.4 | Al archivar, el log desaparece de la vista activa | MUST |
| F-03.5 | Al archivar, el log es persistente y no expirable | MUST |

---

## EPIC-04: Histórico de Logs Archivados

> Sección dedicada para consultar y gestionar logs resueltos/archivados.

| Feature ID | Feature | Prioridad MoSCoW |
|---|---|---|
| F-04.1 | Vista de histórico separada del dashboard activo (pestaña dedicada) | MUST |
| F-04.2 | Ordenación por tipo y por fecha | MUST |
| F-04.3 | Filtros equivalentes a los de logs activos | MUST |
| F-04.4 | Hilo de comentarios múltiples por entrada archivada | MUST |
| F-04.5 | Editor rich text en comentarios del histórico (imágenes, formato) | MUST |
| F-04.6 | Visualización del comentario/hilo en la vista detalle del archivado | MUST |

---

## EPIC-05: Infraestructura y Conectividad

> Capa técnica que soporta el sistema sin lógica de negocio visible.

| Feature ID | Feature | Prioridad MoSCoW |
|---|---|---|
| F-05.1 | Conexión a base de datos PostgreSQL existente (read + write) | MUST |
| F-05.2 | Endpoint SSE para push de eventos al frontend | MUST |
| F-05.3 | Autenticación básica de usuario administrador único | SHOULD |
| F-05.4 | API REST para CRUD de logs archivados y comentarios | MUST |

---

## Resumen de Prioridades

| Prioridad | Cantidad de Features |
|---|---|
| MUST | 16 |
| SHOULD | 5 |
| COULD | 3 |
| WON'T | 0 |
