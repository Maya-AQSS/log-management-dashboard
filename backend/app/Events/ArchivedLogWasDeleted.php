<?php

namespace App\Events;

use Illuminate\Foundation\Events\Dispatchable;

/**
 * Un {@see \App\Models\ArchivedLog} acaba de eliminarse (soft delete) en base de datos.
 */
final class ArchivedLogWasDeleted
{
    use Dispatchable;

    public function __construct(
        public readonly int $archivedLogId,
        public readonly string $archivedByUserId,
    ) {}
}
