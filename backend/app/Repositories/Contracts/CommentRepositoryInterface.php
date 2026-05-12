<?php

namespace App\Repositories\Contracts;

use App\Models\Comment;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

interface CommentRepositoryInterface
{
    /**
     * Lista los comentarios de un commentable (ArchivedLog | ErrorCode) con `user` eager-cargado.
     *
     * @return Collection<int, Comment>
     */
    public function listForCommentable(Model $commentable): Collection;

    /**
     * Crea un comentario asociado al commentable y al usuario indicados.
     */
    public function createFor(Model $commentable, string $userId, string $content): Comment;

    /**
     * Lanza 404 si el comentario no existe.
     */
    public function findOrFail(int $id): Comment;

    /**
     * Sustituye el contenido del comentario.
     */
    public function updateContent(Comment $comment, string $content): Comment;

    public function delete(Comment $comment): void;
}
