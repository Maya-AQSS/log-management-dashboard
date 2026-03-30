<?php
namespace App\Policies;
use App\Models\ErrorCode;
use App\Models\User;

class ErrorCodePolicy
{
    public function create(User $user): bool
    {
        return true;
    }
    public function update(User $user, ErrorCode $errorCode): bool
    {
        return $user->id === (int) $errorCode->created_by_id;
    }

    public function delete(User $user, ErrorCode $errorCode): bool
    {
        return $user->id === (int) $errorCode->created_by_id;
    }
}