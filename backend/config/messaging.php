<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Slug de aplicación en mensajería (audit, logs estructurados, etc.)
    |--------------------------------------------------------------------------
    |
    | Debe coincidir con MAYA_MESSAGING_APP y con el registro en maya_auth /
    | consumidores que enrutan por application_slug.
    |
    */
    'app' => env('MAYA_MESSAGING_APP', 'maya_logs'),

    /*
    |--------------------------------------------------------------------------
    | Zona horaria en mensajes de auditoría (occurred_at + fechas en payloads)
    |--------------------------------------------------------------------------
    |
    | IANA (p. ej. Europe/Madrid). ISO 8601 con offset en payloads para alinear
    | con paneles que muestran la «Fecha» del evento en hora local. Vacío o
    | ausente en otras apps → UTC con sufijo Z (solo maya_logs define aquí).
    |
    */
    // `env('…', default)` no aplica si la clave existe en .env con cadena vacía; `?:` sí.
    'audit_timestamp_timezone' => env('MAYA_AUDIT_TIMESTAMP_TIMEZONE') ?: 'Europe/Madrid',
];
