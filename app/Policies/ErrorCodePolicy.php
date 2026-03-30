<?php
namespace App\Policies;
use App\Models\ErrorCode;
use App\Models\User;

/**
 * Authorization policy for ErrorCode CRUD operations.
 *
 * Decision: any authenticated user may create, update, and delete error codes.
 * The 'auth' route middleware already ensures the user is authenticated before
 * these policy methods are called.
 */

class ErrorCodePolicy
{
    public function create(User $user): bool
    {
        return true;
    }
    public function update(User $user, ErrorCode $errorCode): bool
    {
        return true;
    }

    public function delete(User $user, ErrorCode $errorCode): bool
    {
        return true;
    }
}