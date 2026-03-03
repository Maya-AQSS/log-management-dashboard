Quiero diseñar un sistema para administrar los logs de multiples aplicaciones. Para ello se espera que a partir de una base de datos Postgres donde estaran los logs categorizados por tipo [Critical, higth, medium, low] la idea es tener un panel de administracion y gestion de los mismos.
Tendran un dashboard donde se vean unos botones o cards con los diferentes errores, si no existe el error ira a otros y tambien han de poder verse todos.
al pulsar sobre un error deben lisrtarse como una tabla con filtros donde podran filtrar los errores por tipo, aplicacion que produce el error, fecha, etc. Al pulsar en el error se debe visualizar el error y sus datos pudiendo almacenarlo en un historico con comentarios. Los comentarios deben ser un texto vitaminado donde pueda añadir imagenes etc.
Despues habra otra pestaña que permitira ver los errores del historico ordenados por tipo y fecha o que se puedan ordenar y filtrar

| Pregunta | Decisión |
|---|---|
| ¿Multi-tenant o monocliente? | **Monocliente** — múltiples apps del mismo sistema, todos los usuarios ven todo |
| ¿Cómo llegan los logs? | **Ya existe un flujo n8n** que inserta en Postgres — no hay que diseñar la ingesta |
| ¿Qué es el histórico? | Log con **campo de persistencia permanente**; los logs vivos son borrables/expirables. Al archivar, desaparece de la vista principal |
| ¿Múltiples comentarios por log archivado? | **Sí**, hilo de comentarios por entrada archivada |
| ¿Cuántos usuarios? | **Un único usuario** administrador |
| ¿Tiempo real? | **SSE (Server-Sent Events)** para el dashboard — eficiente y sin complejidad de WebSockets |
