<?php

namespace App\Events;

use Illuminate\Foundation\Events\Dispatchable;

/**
 * Los campos editables de un {@see \App\Models\ArchivedLog} acaban de persistirse.
 */
final class ArchivedLogFieldsWereUpdated
{
    use Dispatchable;

    /**
     * @param  array<string, mixed>  $previousValue
     * @param  array<string, mixed>  $newValue
     */
    public function __construct(
        public readonly int $archivedLogId,
        public readonly string $archivedByUserId,
        public readonly array $previousValue,
        public readonly array $newValue,
    ) {}
}
