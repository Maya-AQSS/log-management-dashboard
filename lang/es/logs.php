<?php

return [
    'menu' => 'Logs',
    'title' => 'Listado de logs',
    'welcome' => 'Bienvenido a los logs',

    'empty' => 'No hay logs que coincidan con los filtros aplicados',

    'archived_success' => 'Log archivado correctamente',
    'archived_error' => 'No ha podido archivarse el log',

    'table' => [
        'application' => 'Aplicación',
        'severity' => 'Severidad',
        'message' => 'Mensaje',
        'error_code' => 'Código de error',
        'created_at' => 'Creado',
        'status' => 'Estado',
    ],

    'status' => [
        'archived' => 'Archivado',
        'resolved' => 'Resuelto',
    ],

    'filters' => [
        'search' => 'Buscar',
        'search_placeholder' => 'p.ej. mensaje del error',
        'severity' => 'Severidad',
        'date_range' => 'Rango de fechas',
        'date_from' => 'Fecha inicio',
        'date_to' => 'Fecha fin',

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
        'apply' => 'Aplicar',
        'reset' => 'Limpiar',
        'archive' => 'Archivar',
        'view_archived' => 'Ver archivado',
    ],
];
