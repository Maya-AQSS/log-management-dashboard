<?php

return [
    'menu' => 'Logs',
    'title' => 'Llistat de logs',
    'welcome' => 'Benvingut als logs',

    'empty' => 'No hi ha logs que coincideixin amb els filtres aplicats',

    'archived_success' => 'Log arxivat correctament',
    'archived_error' => "No s'ha pogut arxivar el log",
    'confirm_archive' => 'Segur que vols arxivar aquest log?',

    'detail' => [
        'title' => 'Detall del log',
        'archived_title' => 'Detall del log arxivat',
        'id' => 'ID',
        'by' => 'per',
        'archived_by' => 'Arxivat per',
        'file' => 'Fitxer',
        'line' => 'Línia',
        'metadata' => 'Metadades (JSON)',
        'no_metadata' => 'Sense metadades',
        'archived_match' => "Aquest log coincideix amb una entrada de l'històric arxivat (mateixa aplicació, codi d'error, severitat i missatge).",
    ],

    'table' => [
        'application' => 'Aplicació',
        'severity' => 'Severitat',
        'message' => 'Missatge',
        'error_code' => "Codi d'error",
        'created_at' => 'Creat',
        'status' => 'Estat',
        'url_tutorial' => 'URL del tutorial',
        'sortable_hint' => 'Clic per ordenar per aquesta columna',
    ],

    'status' => [
        'archived' => 'Arxivat',
        'resolved' => 'Resolt',
        'unresolved' => 'No resolt',
    ],

    'filters' => [
        'search' => 'Cercar',
        'search_placeholder' => "p. ex. missatge de l'error",
        'severity' => 'Severitat',

        'date_range' => 'Rang de dates',
        'date_from' => 'Data inici',
        'date_to' => 'Data fi',
        'date_range_invalid' => 'La data fi no pot ser anterior a la data inici.',

        'application' => 'Aplicació',
        'application_all' => 'Totes les aplicacions',

        'archived' => 'Arxivat',
        'archived_all' => 'Tots',
        'archived_archived' => 'Arxivats',
        'archived_not_archived' => 'No arxivats',

        'resolved' => 'Resolt',
        'resolved_group' => 'Resolts / No resolts',
        'resolved_all' => 'Tots',
        'resolved_resolved' => 'Resolts',
        'resolved_unresolved' => 'No resolts',
    ],

    'buttons' => [
        'back' => 'Tornar',
        'apply' => 'Aplicar',
        'reset' => 'Netejar',
        'cancel' => 'Cancel·lar',
        'archive' => "Desar a l'històric",
        'view_archived' => 'Veure arxivat',
        'solved' => 'Marcar com a resolt',
    ],
];
