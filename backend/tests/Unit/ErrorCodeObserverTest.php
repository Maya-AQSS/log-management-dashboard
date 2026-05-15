<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Models\ErrorCode;
use App\Observers\ErrorCodeObserver;
use Illuminate\Http\Request;
use Maya\Messaging\Publishers\AuditPublisher;
use Mockery;
use PHPUnit\Framework\Attributes\DoesNotPerformAssertions;
use Tests\TestCase;

class ErrorCodeObserverTest extends TestCase
{
    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    #[DoesNotPerformAssertions]
    public function test_created_usa_jwt_subject_como_actor(): void
    {
        config(['messaging.app' => 'maya-logs']);

        $request = Request::create('/api/v1/error-codes', 'POST');
        $request->attributes->set('jwt_user', ['id' => 'jwt-sub-42']);
        $this->app->instance('request', $request);

        $publisher = Mockery::mock(AuditPublisher::class);
        $publisher->shouldReceive('publish')
            ->once()
            ->withArgs(static function (
                string $applicationSlug,
                string $entityType,
                string $entityId,
                string $action,
                string $userId,
            ): bool {
                return $applicationSlug === 'maya-logs'
                    && $entityType === 'error_code'
                    && $entityId === '5'
                    && $action === 'Creado un código de error'
                    && $userId === 'jwt-sub-42';
            });

        $observer = new ErrorCodeObserver($publisher);
        $model = new ErrorCode(['code' => 'X', 'application_id' => 1, 'name' => 'X']);
        $model->id = 5;
        $model->exists = false;
        $model->syncOriginal();

        $observer->created($model);
    }

    #[DoesNotPerformAssertions]
    public function test_created_usa_system_sin_jwt_en_request(): void
    {
        config(['messaging.app' => 'maya-logs']);

        $request = Request::create('/api/v1/error-codes', 'POST');
        $this->app->instance('request', $request);

        $publisher = Mockery::mock(AuditPublisher::class);
        $publisher->shouldReceive('publish')
            ->once()
            ->withArgs(static function (
                string $applicationSlug,
                string $entityType,
                string $entityId,
                string $action,
                string $userId,
            ): bool {
                return $applicationSlug === 'maya-logs' && $userId === 'system';
            });

        $observer = new ErrorCodeObserver($publisher);
        $model = new ErrorCode(['code' => 'Y', 'application_id' => 1, 'name' => 'Y']);
        $model->id = 6;
        $model->exists = false;
        $model->syncOriginal();

        $observer->created($model);
    }
}
