<?php

namespace App\Services\Contracts;

use App\Models\ArchivedLog;
use App\Models\Comment;
use App\Models\ErrorCode;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;

interface CommentServiceInterface
{
    /**
     * @return Collection<int, Comment>
     */
    public function listForArchivedLog(int $archivedLogId): Collection;

    /**
     * @return Collection<int, Comment>
     */
    public function listForErrorCode(int $errorCodeId): Collection;

    public function storeForArchivedLog(int $archivedLogId, User $author, string $rawContent): Comment;

    public function storeForErrorCode(int $errorCodeId, User $author, string $rawContent): Comment;

    /**
     * Localiza el comentario; la autorización se aplica en el controlador via Policy.
     */
    public function findOrFail(int $commentId): Comment;

    public function update(Comment $comment, string $rawContent): Comment;

    public function delete(Comment $comment): void;
}
