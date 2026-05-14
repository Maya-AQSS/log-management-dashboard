<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Models\ArchivedLog;
use App\Observers\ArchivedLogObserver;
use Maya\Messaging\Publishers\AuditPublisher;
use Mockery;
use PHPUnit\Framework\Attributes\DoesNotPerformAssertions;
use Tests\TestCase;

/**
 * Las rutas `updated` del observer se validan en integración (save real + syncChanges de Eloquent).
 * Aquí se cubren `created` y `deleted` invocados como en los eventos de modelo.
 */
class ArchivedLogObserverTest extends TestCase
{
    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    #[DoesNotPerformAssertions]
    public function test_created_publica_audit(): void
    {
        $publisher = Mockery::mock(AuditPublisher::class);
        $publisher->shouldReceive('publish')
            ->once()
            ->withArgs(static function (
                string $applicationSlug,
                string $entityType,
                string $entityId,
                string $action,
                string $userId,
                ?string $blockId,
                ?array $previousValue,
                ?array $newValue,
            ): bool {
                return $entityType === 'archived_log'
                    && $entityId === '9'
                    && $action === 'created'
                    && $userId === 'actor-1'
                    && $blockId === null
                    && $previousValue === null
                    && is_array($newValue)
                    && ($newValue['severity'] ?? null) === 'low';
            });

        $observer = new ArchivedLogObserver($publisher);
        $model = new ArchivedLog([
            'archived_by_id' => 'actor-1',
            'application_id' => 1,
            'severity' => 'low',
            'message' => 'm',
        ]);
        $model->id = 9;
        $model->exists = false;
        $model->syncOriginal();
        $observer->created($model);
    }

    #[DoesNotPerformAssertions]
    public function test_deleted_publica_audit_con_atributos_previos(): void
    {
        $publisher = Mockery::mock(AuditPublisher::class);
        $publisher->shouldReceive('publish')
            ->once()
            ->withArgs(static function (
                string $applicationSlug,
                string $entityType,
                string $entityId,
                string $action,
                string $userId,
                ?string $blockId,
                ?array $previousValue,
                ?array $newValue,
            ): bool {
                return $entityType === 'archived_log'
                    && $entityId === '7'
                    && $action === 'deleted'
                    && $userId === 'del-user'
                    && $blockId === null
                    && is_array($previousValue)
                    && ($previousValue['message'] ?? null) === 'x'
                    && $newValue === null;
            });

        $observer = new ArchivedLogObserver($publisher);
        $model = new ArchivedLog([
            'archived_by_id' => 'del-user',
            'message' => 'x',
        ]);
        $model->id = 7;
        $model->exists = true;
        $model->syncOriginal();

        $observer->deleted($model);
    }
}
