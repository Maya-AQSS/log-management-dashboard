<?php

return [
    /*
    |--------------------------------------------------------------------------
    | API — mensajes para el cliente (403, roles, etc.)
    |--------------------------------------------------------------------------
    */

    'auth' => [
        'forbidden' => 'No tienes permiso para realizar esta acción.',
    ],

    'require_role' => [
        'forbidden' => 'No tienes el rol necesario para esta acción.',
    ],

    'comments' => [
        'actor_not_in_directory' => 'Tu usuario no aparece en el directorio del panel; no puedes comentar.',
    ],
];
