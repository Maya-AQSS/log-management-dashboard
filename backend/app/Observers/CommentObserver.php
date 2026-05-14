<?php
namespace App\Observers;

use App\Models\Comment;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Support\Facades\DB;
use Maya\Messaging\Publishers\AuditPublisher;

class CommentObserver
{
    public function __construct(private readonly AuditPublisher $publisher) {}

    public function created(Comment $comment): void
    {
        DB::afterCommit(fn () => $this->publish('created', $comment, null, $comment->getAttributes()));
    }

    public function updated(Comment $comment): void
    {
        $previous= array_intersect_key($comment->getOriginal(), $comment->getChanges());
        DB::afterCommit(fn() => $this->publish('updated', $comment, $previous, $comment->getChanges()));
    }

    public function deleted(Comment $comment): void
    {
        DB::afterCommit(fn() => $this->publish('deleted', $comment, $comment->getAttributes(), null));
    }


    private function publish(string $action, Comment $comment, ?array $previous, ?array $new): void
    {
        $this->publisher->publish(
            applicationSlug: 'maya-logs',
            entityType: 'comment',
            entityId:  (string) $comment->getKey(),
            action: $action,
            userId: (string) (auth()->id() ?? 'system'),
            previousValue:  $previous,
            newValue:  $new,
        );
    }
}
