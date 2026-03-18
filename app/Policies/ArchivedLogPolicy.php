<?php

namespace App\Policies;

use App\Models\ArchivedLog;
use App\Models\User;

class ArchivedLogPolicy
{
    public function delete(User $user, ArchivedLog $archivedLog): bool
    {
        return $user->id === $archivedLog->archived_by_id;
    }
}