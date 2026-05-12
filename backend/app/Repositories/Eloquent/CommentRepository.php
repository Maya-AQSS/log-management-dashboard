<?php

namespace App\Repositories\Eloquent;

use App\Models\Comment;
use App\Repositories\Contracts\CommentRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

final class CommentRepository implements CommentRepositoryInterface
{
    public function listForCommentable(Model $commentable): Collection
    {
        return $commentable->comments()
            ->with('user')
            ->latest()
            ->get();
    }

    public function createFor(Model $commentable, string $userId, string $content): Comment
    {
        /** @var Comment $comment */
        $comment = $commentable->comments()->create([
            'user_id' => $userId,
            'content' => $content,
        ]);

        $comment->loadMissing('user');

        return $comment;
    }

    public function findOrFail(int $id): Comment
    {
        return Comment::query()->findOrFail($id);
    }

    public function updateContent(Comment $comment, string $content): Comment
    {
        $comment->update(['content' => $content]);
        $comment->refresh()->loadMissing('user');

        return $comment;
    }

    public function delete(Comment $comment): void
    {
        $comment->delete();
    }
}
