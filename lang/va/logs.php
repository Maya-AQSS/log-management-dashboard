<?php

return [
    'menu' => 'Logs',
    'title' => 'Llistat de logs',
    'welcome' => 'Benvingut als logs',

    'empty' => "No hi ha logs que coincideixin amb els filtres aplicats",

    'archived_success' => 'Log arxivat correctament',
    'archived_error' => "No s'ha pogut arxivar el log",

    'table' => [
        'application' => 'Aplicació',
        'severity' => 'Severitat',
        'message' => 'Missatge',
        'error_code' => "Codi d'error",
        'created_at' => 'Creat',
        'status' => 'Estat',
    ],

    'status' => [
        'archived' => 'Arxivat',
        'resolved' => 'Resolts',
    ],

    'filters' => [
        'search' => 'Cercar',
        'search_placeholder' => "p. ex. missatge de l'error",
        'severity' => 'Severitat',
        'application' => 'Aplicació',
        'application_all' => 'Totes les aplicacions',

        'archived' => 'Arxivat',
        'archived_all' => 'Tots',
        'archived_archived' => 'Arxivats',
        'archived_not_archived' => 'No arxivats',

        'resolved' => 'Resolts',
        'resolved_group' => 'Resolts / No resolts',
        'resolved_all' => 'Tots',
        'resolved_resolved' => 'Resolts',
        'resolved_unresolved' => 'No resolts',
    ],

    'buttons' => [
        'apply' => 'Aplicar',
        'reset' => 'Netejar',
        'archive' => 'Arxivar',
        'view_archived' => 'Veure arxivat',
    ],
];
