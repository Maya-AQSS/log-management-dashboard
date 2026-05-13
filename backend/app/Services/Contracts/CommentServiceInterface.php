<?php

namespace App\Services\Contracts;

use App\Models\Comment;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

interface CommentServiceInterface
{
    public function findOrFail(int $id): Comment;

    /**
     * @return Collection<int, Comment>
     */
    public function listForCommentable(Model $commentable): Collection;

    public function createForCommentable(Model $commentable, string $userId, string $rawContent): Comment;

    public function updateContent(Comment $comment, string $rawContent): Comment;

    public function delete(Comment $comment): void;
}
