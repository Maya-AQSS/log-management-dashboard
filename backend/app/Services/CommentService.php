<?php

namespace App\Services;

use App\Models\ArchivedLog;
use App\Models\Comment;
use App\Models\ErrorCode;
use App\Models\User;
use App\Repositories\Contracts\CommentRepositoryInterface;
use App\Services\Contracts\CommentContentSanitizerInterface;
use App\Services\Contracts\CommentServiceInterface;
use Illuminate\Database\Eloquent\Collection;

final class CommentService implements CommentServiceInterface
{
    public function __construct(
        private readonly CommentRepositoryInterface $comments,
        private readonly CommentContentSanitizerInterface $sanitizer,
    ) {}

    public function listForArchivedLog(int $archivedLogId): Collection
    {
        return $this->comments->listForCommentable(
            ArchivedLog::query()->findOrFail($archivedLogId),
        );
    }

    public function listForErrorCode(int $errorCodeId): Collection
    {
        return $this->comments->listForCommentable(
            ErrorCode::query()->findOrFail($errorCodeId),
        );
    }

    public function storeForArchivedLog(int $archivedLogId, User $author, string $rawContent): Comment
    {
        $archivedLog = ArchivedLog::query()->findOrFail($archivedLogId);

        return $this->comments->createFor(
            $archivedLog,
            (string) $author->id,
            $this->sanitizer->sanitize($rawContent),
        );
    }

    public function storeForErrorCode(int $errorCodeId, User $author, string $rawContent): Comment
    {
        $errorCode = ErrorCode::query()->findOrFail($errorCodeId);

        return $this->comments->createFor(
            $errorCode,
            (string) $author->id,
            $this->sanitizer->sanitize($rawContent),
        );
    }

    public function findOrFail(int $commentId): Comment
    {
        return $this->comments->findOrFail($commentId);
    }

    public function update(Comment $comment, string $rawContent): Comment
    {
        return $this->comments->updateContent(
            $comment,
            $this->sanitizer->sanitize($rawContent),
        );
    }

    public function delete(Comment $comment): void
    {
        $this->comments->delete($comment);
    }
}
