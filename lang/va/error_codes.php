<?php

return [
    'menu' => 'Errors',
    'title' => 'Errors',
    'welcome' => 'Benvingut als errors',
    'create_title' => 'Crear Error Code',
    'create_subtitle' => 'Completa els camps per registrar un nou error code',
    'edit_title' => 'Editar Error Code',
    'edit_subtitle' => 'Actualitza els camps del error code',
    'empty' => 'No hi ha codis d\'error per mostrar',
    'deleted' => 'Error Code eliminat correctament',
    'created' => 'Error Code creat correctament',
    'updated' => 'Error Code actualitzat correctament',

    'table' => [
        'application' => 'Aplicació',
        'code' => 'Codi d\'error',
        'severity' => 'Severitat',
        'name' => 'Nom',
        'description' => 'Descripció',
        'file' => 'Fitxer',
        'line' => 'Línia',
        'actions' => 'Accions',
    ],

    'buttons' => [
        'apply' => 'Aplicar',
        'reset' => 'Netejar',
        'back' => 'Enrere',
        'create' => '+ Nou Error Code',
        'edit' => 'Editar',
        'delete' => 'Esborrar',
        'save' => 'Guardar',
        'cancel' => 'Cancelar',
    ],

    'form' => [
        'application' => 'Aplicació',
    ],

    'messages' => [
        'delete_confirm' => 'Segur que vols esborrar este Error Code?',
    ],

    'filters' => [
        'severity' => 'Severitat',
        'search' => 'Buscar',
        'search_placeholder' => 'Buscar per codi o nom',
        'app' => 'App',
        'app_all' => 'Totes les aplicacions',
    ],

    'validation' => [
        'application_id_required' => 'L\'aplicació és obligatòria.',
        'application_id_invalid' => 'L\'aplicació seleccionada no és vàlida.',
        'code_required' => 'El codi d\'error és obligatori.',
        'code_max' => 'El codi d\'error no pot superar els 255 caràcters.',
        'code_unique' => 'Ja existix un codi en este ID per a esta aplicació.',
        'name_required' => 'El nom és obligatori.',
        'name_max' => 'El nom no pot superar els 255 caràcters.',
        'file_max' => 'El fitxer no pot superar els 255 caràcters.',
        'line_integer' => 'La línia ha de ser un número sancer.',
        'line_min' => 'La línia ha de ser major que 0.',
        'severity_invalid' => 'La severitat seleccionada no és vàlida.',
    ],
];
