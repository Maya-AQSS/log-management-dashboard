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

