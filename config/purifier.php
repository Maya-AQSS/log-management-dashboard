<?php

return [
    'encoding'      => 'UTF-8',
    'finalize'      => true,
    'ignoreNonStrings' => false,
    'cachePath'     => storage_path('app/purifier'),
    'cacheFileMode' => 0755,

    'settings' => [
        /*
         * Perfil usado por el editor rich text (TipTap 2).
         * Permite las etiquetas que genera StarterKit + Image + Link.
         * Los data: URIs de imágenes base64 están permitidos porque
         * el backend ya valida el MIME type real antes de llegar aquí
         * (STRIDE E-RTE-01).
         */
        'default' => [
            'HTML.Doctype'    => 'HTML 4.01 Transitional',
            'HTML.Allowed'    =>
                'p,br,b,strong,i,em,s,ul,ol,li,' .
                'h1,h2,h3,h4,h5,h6,' .
                'blockquote,code,pre,hr,' .
                'a[href|rel|target],' .
                'img[src|alt|width|height]',
            'CSS.AllowedProperties'  => '',
            'AutoFormat.AutoParagraph' => false,
            'AutoFormat.RemoveEmpty'   => false,
            // Permite data: URIs para imágenes base64 embebidas
            'URI.AllowedSchemes' => ['http' => true, 'https' => true, 'data' => true],
            'Attr.AllowedFrameTargets' => ['_blank'],
        ],
        'rich_comment' => [
            'HTML.Doctype'    => 'HTML 4.01 Transitional',
            'HTML.Allowed'    =>
                'p[style],br,b,strong,i,em,s,u,' .
                'ul[data-type],ol,' .
                'li[data-type|data-checked],' .
                'h1[style],h2[style],h3[style],h4[style],h5[style],h6[style],' .
                'blockquote,code,pre,hr,' .
                'a[href|rel|target],' .
                'img[src|alt|width|height],' .
                'mark[style|data-color],' .
                'span[style],' .
                'sup,sub,' .
                'div',
            'CSS.AllowedProperties'  => 'text-align,color,background-color',
            'AutoFormat.AutoParagraph' => false,
            'AutoFormat.RemoveEmpty'   => false,
            'URI.AllowedSchemes' => ['http' => true, 'https' => true, 'data' => true],
            'Attr.AllowedFrameTargets' => ['_blank'],
        ],

        /*
         * Definición personalizada para registrar los atributos data-* de Tiptap
         * en HTMLPurifier (que por defecto los elimina en HTML 4.01).
         * Se aplica a todos los perfiles. Rev debe incrementarse si se añaden atributos.
         */
        'custom_definition' => [
            'id'    => 'tiptap-tasklist',
            'rev'   => 2,
            'debug' => false,
            'attributes' => [
                ['ul',   'data-type',    'CDATA'],
                ['li',   'data-type',    'CDATA'],
                ['li',   'data-checked', 'CDATA'],
                ['mark', 'data-color',   'CDATA'],
            ],
        ],
    ],
];
