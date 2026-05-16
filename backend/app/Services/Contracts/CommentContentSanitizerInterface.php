<?php
declare(strict_types=1);

namespace App\Services\Contracts;

/**
 * Sanea el HTML rico de un comentario (Purifier) y valida invariantes de seguridad:
 * tamaño total, no-blanco, imágenes embebidas (magic bytes + límite por imagen).
 */
interface CommentContentSanitizerInterface
{
    /**
     * Devuelve el contenido saneado listo para persistir.
     *
     * @throws \Illuminate\Validation\ValidationException si alguna regla falla.
     */
    public function sanitize(string $rawContent): string;
}
