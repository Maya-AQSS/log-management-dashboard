<?php

namespace App\Observers;

use App\Models\Comment;
use Illuminate\Database\Eloquent\Model;

final class CommentObserver extends AbstractAuditableModelObserver
{
    protected function auditEntityType(): string
    {
        return 'comment';
    }

    protected function auditTemporalKeys(): array
    {
        return self::AUDIT_ELOQUENT_TEMPORAL_KEYS;
    }

    protected function resolveAuditUserId(Model $model): string
    {
        /** @var Comment $model */
        return (string) ($model->user_id ?? 'system');
    }

    public function created(Comment $comment): void
    {
        $this->auditAfterCreate('Nuevo comentario creado', $comment);
    }

    public function updated(Comment $comment): void
    {
        $this->auditAfterUpdate('Comentario modificado', $comment);
    }

    public function deleted(Comment $comment): void
    {
        $this->auditAfterDelete('Comentario eliminado', $comment);
    }
}
