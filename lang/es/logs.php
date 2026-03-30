<?php

return [
    'menu' => 'Logs',
    'title' => 'Listado de logs',
    'welcome' => 'Bienvenido a los logs',

    'empty' => 'No hay logs que coincidan con los filtros aplicados',

    'archived_success' => 'Log archivado correctamente',
    'archived_error' => 'No ha podido archivarse el log',
    'confirm_archive' => '¿Seguro que quieres archivar este log?',

    'detail' => [
        'title' => 'Detalle del log',
        'archived_title' => 'Detalle del log archivado',
        'id' => 'ID',
        'by' => 'por',
        'archived_by' => 'Archivado por',
        'file' => 'Archivo',
        'line' => 'Línea',
        'metadata' => 'Metadatos (JSON)',
        'no_metadata' => 'Sin metadatos',
        'archived_match' => 'Este log coincide con una entrada del histórico archivado (misma aplicación, código de error, severidad y mensaje).',
    ],

    'table' => [
        'application' => 'Aplicación',
        'severity' => 'Severidad',
        'message' => 'Mensaje',
        'error_code' => 'Código de error',
        'created_at' => 'Creado',
        'status' => 'Estado',
        'url_tutorial' => 'URL del tutorial',
        'sortable_hint' => 'Clic para ordenar por esta columna',
    ],

    'status' => [
        'archived' => 'Archivado',
        'resolved' => 'Resuelto',
        'unresolved' => 'No resuelto',
        'resolved_success' => 'Marcado como resuelto correctamente',
    ],

    'filters' => [
        'search' => 'Buscar',
        'search_placeholder' => 'p.ej. mensaje del error',
        'severity' => 'Severidad',

        'date_range' => 'Rango de fechas',
        'date_from' => 'Fecha inicio',
        'date_to' => 'Fecha fin',
        'date_range_invalid' => 'La fecha fin no puede ser anterior a la fecha inicio.',

        'application' => 'Aplicación',
        'application_all' => 'Todas las aplicaciones',

        'archived' => 'Archivado',
        'archived_all' => 'Todos',
        'archived_archived' => 'Archivados',
        'archived_not_archived' => 'No archivados',

        'resolved' => 'Resuelto',
        'resolved_group' => 'Resuelto / No resuelto',
        'resolved_all' => 'Todos',
        'resolved_resolved' => 'Resueltos',
        'resolved_unresolved' => 'No resueltos',
    ],

    'buttons' => [
        'back' => 'Volver',
        'apply' => 'Aplicar',
        'reset' => 'Limpiar',
        'cancel' => 'Cancelar',
        'archive' => 'Guardar en Histórico',
        'view_archived' => 'Ver archivado',
        'solved' => 'Marcar como resuelto',
    ],
];
