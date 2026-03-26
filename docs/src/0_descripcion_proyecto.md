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

