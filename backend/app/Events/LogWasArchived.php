<?php

namespace App\Events;

use App\Models\ArchivedLog;
use Illuminate\Foundation\Events\Dispatchable;

/**
 * Un log activo acaba de persistirse como {@see ArchivedLog} (transacción del repositorio ya cerrada).
 */
final class LogWasArchived
{
    use Dispatchable;

    public function __construct(
        public readonly ArchivedLog $archivedLog,
        public readonly string $archivedByUserId,
    ) {}
}
