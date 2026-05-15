<?php

namespace App\Observers;

use App\Observers\Concerns\NormalizesAuditTemporalPayload;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Maya\Messaging\Publishers\AuditPublisher;

/**
 * Plantilla CRUD → maya.audit para modelos del panel.
 *
 * Cada observer concreto define tipo de entidad, claves temporales y actor;
 * la publicación y el diferimiento al commit son compartidos.
 */
abstract class AbstractAuditableModelObserver
{
    use NormalizesAuditTemporalPayload;

    public function __construct(
        protected readonly AuditPublisher $publisher,
    ) {}

    abstract protected function auditEntityType(): string;

    /**
     * @return list<string>
     */
    abstract protected function auditTemporalKeys(): array;

    abstract protected function resolveAuditUserId(Model $model): string;

    protected function auditAfterCreate(string $action, Model $model): void
    {
        $this->afterCommit(fn () => $this->publishAudit(
            $action,
            $model,
            null,
            $model->getAttributes(),
        ));
    }

    protected function auditAfterUpdate(string $action, Model $model): void
    {
        [$previous, $new] = $this->auditUpdateDiff($model);

        $this->afterCommit(fn () => $this->publishAudit(
            $action,
            $model,
            $previous,
            $new,
        ));
    }

    protected function auditAfterDelete(string $action, Model $model): void
    {
        $this->afterCommit(fn () => $this->publishAudit(
            $action,
            $model,
            $model->getAttributes(),
            null,
        ));
    }

    /**
     * @return array{0: ?array<string, mixed>, 1: ?array<string, mixed>}
     */
    protected function auditUpdateDiff(Model $model): array
    {
        $previous = array_intersect_key($model->getOriginal(), $model->getChanges());
        $new = $model->getChanges();

        return [
            $previous !== [] ? $previous : null,
            $new !== [] ? $new : null,
        ];
    }

    protected function afterCommit(callable $callback): void
    {
        if (DB::transactionLevel() === 0) {
            $callback();

            return;
        }

        DB::afterCommit($callback);
    }

    /**
     * @param  array<string, mixed>|null  $previousValue
     * @param  array<string, mixed>|null  $newValue
     */
    protected function publishAudit(
        string $action,
        Model $model,
        ?array $previousValue,
        ?array $newValue,
    ): void {
        $this->publisher->publish(
            applicationSlug: 'maya-logs', //(string) config('messaging.app'),
            entityType: $this->auditEntityType(),
            entityId: (string) $model->getKey(),
            action: $action,
            userId: $this->resolveAuditUserId($model),
            previousValue: $this->normalizeAuditTemporalPayload($previousValue, $this->auditTemporalKeys()),
            newValue: $this->normalizeAuditTemporalPayload($newValue, $this->auditTemporalKeys()),
        );
    }
}
