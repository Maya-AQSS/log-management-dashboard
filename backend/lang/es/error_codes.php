<?php

return [
    'menu' => 'Errores',
    'title' => 'Errores',
    'detail_title' => 'Detalles del Código de Error',
    'welcome' => 'Bienvenido a los errores',
    'create_title' => 'Crear Error Code',
    'create_subtitle' => 'Completa los campos para registrar un nuevo error code',
    'edit_title' => 'Editar Error Code',
    'edit_subtitle' => 'Actualiza los campos del error code',
    'detail_subtitle' => 'Pulsa editar para modificar este error code',
    'empty' => 'No hay códigos de error para mostrar',
    'deleted' => 'Error Code eliminado correctamente',
    'created' => 'Error Code creado correctamente',
    'updated' => 'Error Code actualizado correctamente',

    'table' => [
        'application' => 'Aplicación',
        'code' => 'Código de error',
        'severity' => 'Severidad',
        'name' => 'Nombre',
        'description' => 'Descripción',
        'file' => 'Fichero',
        'line' => 'Línea',
        'actions' => 'Acciones',
    ],

    'buttons' => [
        'apply' => 'Aplicar',
        'reset' => 'Limpiar',
        'back' => 'Volver',
        'create' => '+ Nuevo Error Code',
        'edit' => 'Editar',
        'delete' => 'Borrar',
        'save' => 'Guardar',
        'cancel' => 'Cancelar',
    ],

    'form' => [
        'application' => 'Aplicación',
    ],

    'messages' => [
        'delete_confirm' => '¿Seguro que quieres borrar este Error Code?',
    ],

    'filters' => [
        'severity' => 'Severidad',
        'search' => 'Buscar',
        'search_placeholder' => 'Buscar por código o nombre',
        'app' => 'App',
        'app_all' => 'Todas las aplicaciones',
    ],

    'validation' => [
        'application_id_required' => 'La aplicación es obligatoria.',
        'application_id_invalid' => 'La aplicación seleccionada no es válida.',
        'code_required' => 'El código de error es obligatorio.',
        'code_max' => 'El código de error no puede superar los 50 caracteres.',
        'code_unique' => 'Ya existe un código con este ID para esta aplicación.',
        'name_required' => 'El nombre es obligatorio.',
        'name_max' => 'El nombre no puede superar los 200 caracteres.',
        'description_max' => 'La descripción no puede superar los 5000 caracteres.',
        'file_max' => 'El fichero no puede superar los 255 caracteres.',
        'line_integer' => 'La línea debe ser un número entero.',
        'line_min' => 'La línea debe ser mayor que 0.',
        'severity_invalid' => 'La severidad seleccionada no es válida.',
    ],
];
