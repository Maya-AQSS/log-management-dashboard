<?php

return [
    'menu' => 'Logs',
    'title' => 'Listado de logs',
    'welcome' => 'Bienvenido a los logs',

    'empty' => 'No hay logs que coincidan con los filtros aplicados',

    'archived_success' => 'Log archivado correctamente',
    'archived_error' => 'No ha podido archivarse el log',

    'detail' => [
        'title' => 'Detalle del log',
        'id' => 'ID',
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
        'url_tutorial' => 'URL Tutorial',
    ],

    'status' => [
        'archived' => 'Archivado',
        'resolved' => 'Resuelto',
        'unresolved' => 'No resuelto',
    ],

    'filters' => [
        'search' => 'Buscar',
        'search_placeholder' => 'p.ej. mensaje del error',
        'severity' => 'Severidad',

        'archived' => 'Archivado',
        'archived_all' => 'Todos',
        'archived_archived' => 'Archivados',
        'archived_not_archived' => 'No archivados',

        'resolved' => 'Resuelto',
        'resolved_all' => 'Todos',
        'resolved_resolved' => 'Resueltos',
        'resolved_unresolved' => 'No resueltos',
    ],

    'buttons' => [
        'back' => 'Volver',
        'apply' => 'Aplicar',
        'reset' => 'Limpiar',
        'archive' => 'Guardar en Histórico',
        'view_archived' => 'Ver archivado',
        'solved' => 'Solucionado',
    ],
];
