<?php
namespace App\Observers;

use App\Models\ErrorCode;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Support\Facades\DB;
use Maya\Messaging\Publishers\AuditPublisher;

class ErrorCodeObserver
{
    public function __construct(private readonly AuditPublisher $publisher) {}

    public function created(ErrorCode $errorCode): void
    {
        DB::afterCommit(fn () => $this->publish('created', $errorCode, null, $errorCode->getAttributes()));
    }

    public function updated(ErrorCode $errorCode): void
    {
        $previous= array_intersect_key($errorCode->getOriginal(), $errorCode->getChanges());
        DB::afterCommit(fn() => $this->publish('updated', $errorCode, $previous, $errorCode->getChanges()));
    }

    public function deleted(ErrorCode $errorCode): void
    {
        DB::afterCommit(fn() => $this->publish('deleted', $errorCode, $errorCode->getAttributes(), null));
    }


    private function publish(string $action, ErrorCode $errorCode, ?array $previous, ?array $new): void
    {
        $this->publisher->publish(
            applicationSlug: 'maya-logs',
            entityType: 'error_code',
            entityId:  (string) $errorCode->getKey(),
            action: $action,
            userId: (string) (auth()->id() ?? 'system'),
            previousValue:  $previous,
            newValue:  $new,
        );
    }
}
