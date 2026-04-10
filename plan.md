Plan: Unificación exhaustiva de diseño — log-management-dashboard

 Context

 El diseño del layout ya se adaptó (sidebar + topbar), pero los componentes internos siguen usando clases hardcoded de Tailwind (slate-*, rounded-2xl, rounded-xl, bg-white,
 bg-slate-50) en lugar del sistema de tokens del ecosistema CEEDCV. Este plan cubre todos los archivos que requieren cambios, con los reemplazos exactos de clases CSS.

 ---
 Tabla de sustitución estándar

 Estas son las equivalencias globales que se aplican en todos los archivos:

 ┌─────────────────────────────────┬────────────────────────────────────┬───────────────────────────────────────────────────────────────────────────────────────────────────────┐
 │      Clase actual (light)       │        Clase actual (dark)         │                                          Reemplazo unificado                                          │
 ├─────────────────────────────────┼────────────────────────────────────┼───────────────────────────────────────────────────────────────────────────────────────────────────────┤
 │ border-slate-200                │ dark:border-slate-600/700          │ border-ui-border dark:border-ui-dark-border                                                           │
 ├─────────────────────────────────┼────────────────────────────────────┼───────────────────────────────────────────────────────────────────────────────────────────────────────┤
 │ border-slate-300                │ dark:border-slate-700              │ border-ui-border dark:border-ui-dark-border                                                           │
 ├─────────────────────────────────┼────────────────────────────────────┼───────────────────────────────────────────────────────────────────────────────────────────────────────┤
 │ bg-slate-50 (fondos de sección) │ dark:bg-slate-900                  │ bg-ui-body dark:bg-ui-dark-bg                                                                         │
 ├─────────────────────────────────┼────────────────────────────────────┼───────────────────────────────────────────────────────────────────────────────────────────────────────┤
 │ bg-white (fondos de card/input) │ dark:bg-slate-800/950              │ bg-ui-card dark:bg-ui-dark-card                                                                       │
 ├─────────────────────────────────┼────────────────────────────────────┼───────────────────────────────────────────────────────────────────────────────────────────────────────┤
 │ text-slate-900 / text-slate-800 │ dark:text-slate-100/200            │ text-text-primary dark:text-text-dark-primary                                                         │
 ├─────────────────────────────────┼────────────────────────────────────┼───────────────────────────────────────────────────────────────────────────────────────────────────────┤
 │ text-slate-700 / text-slate-600 │ dark:text-slate-200/300            │ text-text-secondary dark:text-text-dark-secondary                                                     │
 ├─────────────────────────────────┼────────────────────────────────────┼───────────────────────────────────────────────────────────────────────────────────────────────────────┤
 │ text-slate-500 / text-slate-400 │ —                                  │ text-text-muted dark:text-text-dark-muted                                                             │
 ├─────────────────────────────────┼────────────────────────────────────┼───────────────────────────────────────────────────────────────────────────────────────────────────────┤
 │ hover:bg-slate-50               │ dark:hover:bg-slate-800            │ hover:bg-ui-body dark:hover:bg-ui-dark-card                                                           │
 ├─────────────────────────────────┼────────────────────────────────────┼───────────────────────────────────────────────────────────────────────────────────────────────────────┤
 │ rounded-2xl (contenedores)      │ —                                  │ rounded-lg                                                                                            │
 ├─────────────────────────────────┼────────────────────────────────────┼───────────────────────────────────────────────────────────────────────────────────────────────────────┤
 │ rounded-xl (contenedores e      │ —                                  │ rounded-lg                                                                                            │
 │ inputs)                         │                                    │                                                                                                       │
 ├─────────────────────────────────┼────────────────────────────────────┼───────────────────────────────────────────────────────────────────────────────────────────────────────┤
 │ border-slate-200 bg-white       │ dark:border-slate-600/700          │ border-ui-border bg-ui-card dark:border-ui-dark-border dark:bg-ui-dark-card                           │
 │ (cancel btn)                    │ dark:bg-slate-800                  │                                                                                                       │
 ├─────────────────────────────────┼────────────────────────────────────┼───────────────────────────────────────────────────────────────────────────────────────────────────────┤
 │ text-slate-800                  │ dark:text-slate-200                │ text-text-primary hover:bg-ui-body dark:text-text-dark-primary dark:hover:bg-ui-dark-border           │
 │ hover:bg-slate-50 (cancel btn)  │ dark:hover:bg-slate-700            │                                                                                                       │
 ├─────────────────────────────────┼────────────────────────────────────┼───────────────────────────────────────────────────────────────────────────────────────────────────────┤
 │ bg-[#f7a736] (botón back amber) │ dark:bg-amber-500                  │ border border-ui-border bg-ui-card text-text-primary hover:bg-ui-body dark:border-ui-dark-border      │
 │                                 │                                    │ dark:bg-ui-dark-card dark:text-text-dark-primary dark:hover:bg-ui-dark-border                         │
 ├─────────────────────────────────┼────────────────────────────────────┼───────────────────────────────────────────────────────────────────────────────────────────────────────┤
 │ bg-cyan-100 text-cyan-800       │ dark:bg-cyan-900/40                │ bg-info-light text-info-dark dark:bg-info/20 dark:text-info                                           │
 │ (badge resolved)                │ dark:text-cyan-200                 │                                                                                                       │
 └─────────────────────────────────┴────────────────────────────────────┴───────────────────────────────────────────────────────────────────────────────────────────────────────┘

 Patrón read-only field (campo de solo lectura): sustituir el bloque completo:
 rounded-xl border border-slate-200 bg-white px-3 py-2.5 text-sm text-slate-800 shadow-inner dark:border-slate-600 dark:bg-slate-950 dark:text-slate-200
 →
 rounded-lg border border-ui-border bg-ui-body px-3 py-2.5 text-sm text-text-primary dark:border-ui-dark-border dark:bg-ui-dark-bg dark:text-text-dark-primary

 Patrón sección filtros / contenedor principal:
 rounded-2xl border border-slate-200 bg-slate-50 p-4 dark:border-ui-dark-border dark:bg-ui-dark-card
 →
 rounded-lg border border-ui-border bg-ui-body p-4 dark:border-ui-dark-border dark:bg-ui-dark-card

 ---
 Archivos a modificar — por prioridad

 PRIORIDAD 1 — Componentes reutilizables (impactan toda la app)

 ---
 resources/views/components/confirm-action-modal.blade.php

 Problema: Usa hardcoded slate-* en todo el modal.

 Cambios en línea 11:
 rounded-2xl border border-slate-200 bg-white p-5 shadow-xl dark:border-slate-700 dark:bg-slate-900
 →
 rounded-lg border border-ui-border bg-ui-card p-5 shadow-dropdown dark:border-ui-dark-border dark:bg-ui-dark-card

 Cambio en línea 12:
 text-slate-900 dark:text-slate-100
 →
 text-text-primary dark:text-text-dark-primary

 Cambio en línea 13:
 text-slate-600 dark:text-slate-300
 →
 text-text-secondary dark:text-text-dark-secondary

 Cambio en línea 19 (botón cancelar): Aplicar tabla estándar cancel btn.

 Cambio en línea 33 (botón danger):
 border border-red-200 bg-red-50 text-red-700 hover:bg-red-100
 →
 border border-danger/20 bg-danger-light text-danger-dark hover:bg-danger/20

 ---
 resources/views/components/date-range-filter.blade.php

 Problema: Mezcla border-slate-300 con tokens del sistema.

 Cambios:
 - Label: text-slate-700 dark:text-slate-200 → estándar text-text-secondary dark:text-text-dark-secondary
 - Inputs border-slate-300: → border-ui-border
 - rounded-xl en inputs → rounded-lg

 ---
 resources/views/components/filters/severity-filter-checkboxes.blade.php

 Problema: Contenedor y textos en slate-*, bg-white, rounded-xl.

 Cambios:
 - Contenedor <details>: rounded-xl border border-slate-300 bg-white → rounded-lg border border-ui-border bg-ui-card dark:border-ui-dark-border dark:bg-ui-dark-card
 - <summary> label text: text-slate-700 dark:text-slate-200 → estándar
 - Checkbox labels: text-slate-700 dark:text-slate-200 → estándar

 ---
 resources/views/components/filters/application-select.blade.php

 Cambios:
 - rounded-xl → rounded-lg
 - border-slate-300 → border-ui-border
 - Label text: text-slate-700 → text-text-secondary dark:text-text-dark-secondary

 ---
 resources/views/components/dashboard-application-link.blade.php

 Problema: Usa gradiente y colores slate-* en lugar del sistema.

 Cambios:
 - Contenedor: bg-gradient-to-br from-slate-50/90 via-white to-[#faf8fc] → bg-ui-card dark:bg-ui-dark-card
 - border-slate-200/90 dark:border-slate-700 → border-ui-border dark:border-ui-dark-border
 - ring-1 ring-slate-900/[0.04] → shadow-card
 - Texto nombre aplicación text-slate-900 dark:text-slate-100 → estándar text-text-primary
 - Texto total text-slate-600 dark:text-slate-400 → text-text-secondary dark:text-text-dark-secondary

 ---
 PRIORIDAD 2 — Páginas de listado (views principales)

 ---
 resources/views/dashboard.blade.php

 Cambios:
 - <h1>: text-slate-900 dark:text-slate-50 → text-text-primary dark:text-text-dark-primary
 - <p> subtítulo: text-slate-600 dark:text-slate-400 → estándar
 - Sección "By application":
   - rounded-2xl border border-slate-200/90 bg-gradient-to-br from-slate-50/90 via-white to-[#faf8fc] → rounded-lg border border-ui-border bg-ui-card dark:border-ui-dark-border
 dark:bg-ui-dark-card
   - dark:border-slate-700 dark:from-slate-900/80 dark:via-slate-950 dark:to-slate-900/60 → reemplazado por arriba
   - <h2>: text-slate-900 dark:text-slate-100 → estándar
   - <p> hint: text-slate-600 dark:text-slate-400 → estándar

 ---
 resources/views/logs/index.blade.php

 Cambios:
 - <h1> si usa text-slate-900 → estándar
 - <p> si usa text-slate-600 → estándar

 ---
 resources/views/logs/show.blade.php

 Cambios:
 - Botón back amber bg-[#f7a736] → patrón secondary de la tabla estándar (inline-flex items-center rounded-lg border border-ui-border bg-ui-card px-4 py-2 text-sm font-semibold
 text-text-primary shadow-card hover:bg-ui-body dark:...)
 - <h1> page title: estándar
 - Botones de acción secundarios (cancelar): estándar

 ---
 resources/views/error-codes/index.blade.php

 Cambios:
 - <h1> si usa text-slate-900 → estándar
 - Cualquier border-slate-* o bg-slate-* → estándar

 ---
 resources/views/archived-logs/index.blade.php

 Cambios:
 - Mismos que logs/index.blade.php

 ---
 PRIORIDAD 3 — Componentes Livewire (vistas complejas)

 ---
 resources/views/livewire/logs-table.blade.php

 Cambios:

 1. Contenedor filtros (línea 2):
 rounded-2xl border border-slate-200 bg-slate-50 p-4 dark:border-ui-dark-border dark:bg-ui-dark-card
 → rounded-lg border border-ui-border bg-ui-body p-4 dark:border-ui-dark-border dark:bg-ui-dark-card
 2. Label "Buscar" (línea 6): text-slate-700 dark:text-slate-200 → estándar secondary
 3. Input búsqueda (línea 9): rounded-xl → rounded-lg
 4. <summary> resolved group (línea 53): text-slate-700 dark:text-slate-200 → estándar secondary
 5. Radio labels (líneas 59, 69, 79): text-slate-700 dark:text-slate-200 → estándar secondary
 6. Botón Reset (línea 106-109):
 border border-slate-300 bg-white px-4 py-2 text-base font-semibold text-slate-800 hover:bg-slate-50
 → border border-ui-border bg-ui-card px-4 py-2 text-sm font-semibold text-text-primary hover:bg-ui-body dark:border-ui-dark-border dark:bg-ui-dark-card dark:text-text-dark-primary
  dark:hover:bg-ui-dark-border
 6. (También unificar tamaño: text-base → text-sm)
 7. Botón Apply (línea 98): text-base → text-sm (consistencia de tamaño)
 8. Rows de tabla (línea 174):
 hover:bg-slate-50 dark:hover:bg-slate-800
 → hover:bg-ui-body dark:hover:bg-ui-dark-card
 9. Celdas td (líneas 178, 182, 185, 186, 189):
 text-slate-700 dark:text-slate-200
 → text-text-primary dark:text-text-dark-primary
 10. Badge "resolved" (línea 192-195):
 bg-cyan-100 px-2 py-0.5 text-xs font-semibold text-cyan-800
 → bg-info-light text-info-dark dark:bg-info/20 dark:text-info
 10. (añadir inline-flex items-center rounded-full)

 ---
 resources/views/livewire/archived-logs-table.blade.php

 Mismos cambios que logs-table.blade.php. Verificar si tiene badge de "archived" adicional y aplicar el patrón de badge del sistema.

 ---
 resources/views/livewire/error-codes-table.blade.php

 Mismos cambios que logs-table.blade.php en la sección de filtros y tabla.

 ---
 resources/views/livewire/log-detail.blade.php

 Cambios:

 1. Wrapper principal (línea 1): text-slate-900 dark:text-slate-100 → text-text-primary dark:text-text-dark-primary
 2. Contenedor principal (línea 5):
 rounded-xl border border-slate-200 bg-slate-50 p-4 shadow-sm dark:border-slate-600 dark:bg-slate-900
 → rounded-lg border border-ui-border bg-ui-card p-4 shadow-card dark:border-ui-dark-border dark:bg-ui-dark-card
 3. Alert archived match (línea 8):
 rounded-xl border border-amber-200 bg-amber-50 dark:border-amber-900/40 dark:bg-amber-950/25
 → rounded-lg border border-warning/30 bg-warning-light dark:border-warning/30 dark:bg-warning/10
   - Texto dentro: text-slate-800 dark:text-slate-200 → estándar
 4. Labels de campos (todas las ocurrencias):
 text-sm font-medium text-slate-700 dark:text-slate-200
 → text-sm font-medium text-text-secondary dark:text-text-dark-secondary
 5. Todos los read-only fields (líneas 31, 36, 61, 66, 73, 78, 84, 94, 97): Aplicar patrón read-only field de la tabla estándar.
 6. Badge "resolved" en status field (línea 50-53):
 bg-cyan-100 px-2 py-0.5 text-xs font-semibold text-cyan-800 dark:bg-cyan-900/40 dark:text-cyan-200
 → inline-flex items-center rounded-full bg-info-light px-2 py-0.5 text-xs font-semibold text-info-dark dark:bg-info/20 dark:text-info
 7. Texto "unresolved" (línea 54): text-slate-700 dark:text-slate-300 → estándar secondary

 ---
 resources/views/livewire/archived-log-detail.blade.php

 Cambios:

 1. Wrapper principal (línea 1): estándar text-text-primary
 2. Botón back amber (línea 8): aplicar patrón secondary de la tabla estándar.
 3. <h1> título (línea 14): text-slate-900 md:text-2xl dark:text-slate-100 → text-text-primary dark:text-text-dark-primary
 4. <p> subtítulo (línea 17): text-slate-600 dark:text-slate-400 → estándar secondary
 5. Span "archived by" (línea 18): text-slate-700 dark:text-slate-200 → estándar secondary
 6. Botón cancelar edición (línea 51):
 border border-slate-300 bg-white ... text-slate-800 hover:bg-slate-50 dark:border-slate-600 dark:bg-slate-800 dark:text-slate-200 dark:hover:bg-slate-700
 → border border-ui-border bg-ui-card text-text-primary hover:bg-ui-body dark:border-ui-dark-border dark:bg-ui-dark-card dark:text-text-dark-primary dark:hover:bg-ui-dark-border
 7. Contenedor principal detalle (línea 78):
 rounded-xl border border-slate-200 bg-slate-50 p-4 shadow-sm dark:border-slate-600 dark:bg-slate-900
 → rounded-lg border border-ui-border bg-ui-card p-4 shadow-card dark:border-ui-dark-border dark:bg-ui-dark-card
 8. Labels de campos (todas): estándar secondary.
 9. Read-only fields (líneas 82, 87, 92, 99): patrón read-only field estándar.
 10. Sección inactiva description/url (líneas 106, 159):
 border border-slate-200 bg-slate-50 text-slate-900 dark:border-slate-600 dark:bg-slate-900 dark:text-slate-100
 → border border-ui-border bg-ui-body text-text-primary dark:border-ui-dark-border dark:bg-ui-dark-bg dark:text-text-dark-primary
 11. Divisor sección (línea 110, 163):
 border-b border-slate-200 dark:border-slate-500/80
 → border-b border-ui-border dark:border-ui-dark-border
 12. Título sección (líneas 111, 164):
 text-slate-900 dark:text-slate-50
 → text-text-primary dark:text-text-dark-primary
 13. Display fields de description/url (líneas 130, 143, 196, 211, 222, 237):
 rounded-xl border border-odoo-purple/40 bg-white px-3 py-2.5 text-sm shadow-inner dark:border-slate-500 dark:bg-slate-950
 → rounded-lg border border-odoo-purple/40 bg-ui-body px-3 py-2.5 text-sm dark:border-ui-dark-border dark:bg-ui-dark-bg
 13. (mantener border-odoo-purple/40 cuando el campo es "editable/resaltado"; usar border-ui-border cuando es puramente display)
 14. Textos read-only de description/url (líneas 134-136, 148-149):
 text-slate-900 dark:text-slate-100
 → text-text-primary dark:text-text-dark-primary
 text-slate-500 italic dark:text-slate-400
 → text-text-muted italic dark:text-text-dark-muted
 15. Textarea edición description (línea 121):
 dark:bg-slate-950 dark:text-slate-100 dark:placeholder:text-slate-500
 → dark:bg-ui-dark-bg dark:text-text-dark-primary dark:placeholder:text-text-dark-muted
 16. Read-only message y metadata (líneas 248, 258): aplicar patrón read-only field estándar.

 ---
 resources/views/livewire/error-code-form.blade.php

 Cambios:

 1. Botón back amber: patrón secondary estándar.
 2. Contenedor formulario inactivo:
 border border-slate-200 bg-slate-50
 → border border-ui-border bg-ui-body dark:border-ui-dark-border dark:bg-ui-dark-bg
 3. Inputs del formulario border-slate-200:
 → border-ui-border dark:border-ui-dark-border
 4. Labels: text-slate-700 → text-text-secondary dark:text-text-dark-secondary
 5. rounded-xl en inputs y contenedor → rounded-lg

 ---
 resources/views/livewire/comment-thread.blade.php

 Cambios:

 1. Editor wrapper:
 rounded-2xl border border-slate-200 bg-slate-50
 → rounded-lg border border-ui-border bg-ui-card dark:border-ui-dark-border dark:bg-ui-dark-card
 2. Artículos de comentario:
 rounded-2xl border border-slate-200 bg-white
 → rounded-lg border border-ui-border bg-ui-card dark:border-ui-dark-border dark:bg-ui-dark-card
 3. Empty state:
 rounded-2xl border border-dashed border-slate-300 bg-slate-50
 → rounded-lg border border-dashed border-ui-border bg-ui-body dark:border-ui-dark-border dark:bg-ui-dark-bg
 4. Textos de comentarios y metadatos: estándar text-text-*.

 ---
 PRIORIDAD 4 — Componentes secundarios

 ---
 resources/views/vendor/livewire/table-pagination.blade.php

 Cambios:
 - Botones de número de página bg-white text-slate-700 hover:bg-slate-50 → bg-ui-card text-text-primary hover:bg-ui-body dark:bg-ui-dark-card dark:text-text-dark-primary
 - Página activa bg-slate-200 → bg-ui-body dark:bg-ui-dark-border

 ---
 Verificación tras implementación

 1. Abrir http://logs.localhost en modo claro y oscuro
 2. Navegar por cada sección: Dashboard, Logs, Archived Logs, Error Codes
 3. Abrir un log → verificar detail page
 4. Abrir un archived log → verificar detail + modo edición
 5. Usar los filtros → verificar estilo consistente
 6. Abrir modal de confirmación (resolver/eliminar) → verificar colores
 7. Abrir formulario error code → verificar estado activo/inactivo
 8. Verificar paginación en listas largas
 9. Comprobar que dark mode es coherente en todas las vistas

 ---
 Archivos críticos a modificar

 resources/views/components/confirm-action-modal.blade.php
 resources/views/components/date-range-filter.blade.php
 resources/views/components/filters/severity-filter-checkboxes.blade.php
 resources/views/components/filters/application-select.blade.php
 resources/views/components/dashboard-application-link.blade.php
 resources/views/dashboard.blade.php
 resources/views/logs/index.blade.php
 resources/views/logs/show.blade.php
 resources/views/error-codes/index.blade.php
 resources/views/archived-logs/index.blade.php
 resources/views/livewire/logs-table.blade.php
 resources/views/livewire/log-detail.blade.php
 resources/views/livewire/archived-logs-table.blade.php
 resources/views/livewire/archived-log-detail.blade.php
 resources/views/livewire/error-code-form.blade.php
 resources/views/livewire/comment-thread.blade.php
 resources/views/vendor/livewire/table-pagination.blade.php