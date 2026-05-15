<?php

namespace Tests\Unit;

use App\Models\Log;
use App\Repositories\Contracts\LogRepositoryInterface;
use App\Services\LogService;
use App\Support\ResilientLogPublisher;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Maya\Messaging\Publishers\AuditPublisher;
use Maya\Messaging\Publishers\LogPublisher;
use Tests\TestCase;

class LogServiceResolvedTest extends TestCase
{
    public function test_resolved_calls_find_or_fail_then_repository_resolved(): void
    {
        $log = $this->createMock(Log::class);

        $repository = $this->createMock(LogRepositoryInterface::class);
        $repository->expects($this->once())
            ->method('findOrFail')
            ->with(42)
            ->willReturn($log);
        $repository->expects($this->once())
            ->method('resolved')
            ->with(42)
            ->willReturn(1);

        $publisher = $this->createMock(AuditPublisher::class);
        $publisher->expects($this->once())
            ->method('publish');

        $resilient = new ResilientLogPublisher($this->createMock(LogPublisher::class));

        $service = new LogService($repository, $publisher, $resilient);
        $service->resolved(42, 'sub-1');
    }

    public function test_resolved_no_audit_when_row_already_resolved(): void
    {
        $log = $this->createMock(Log::class);

        $repository = $this->createMock(LogRepositoryInterface::class);
        $repository->expects($this->once())
            ->method('findOrFail')
            ->with(7)
            ->willReturn($log);
        $repository->expects($this->once())
            ->method('resolved')
            ->with(7)
            ->willReturn(0);

        $publisher = $this->createMock(AuditPublisher::class);
        $publisher->expects($this->never())
            ->method('publish');

        $resilient = new ResilientLogPublisher($this->createMock(LogPublisher::class));

        $service = new LogService($repository, $publisher, $resilient);
        $service->resolved(7, 'sub-2');
    }

    public function test_resolved_publica_telemetria_si_el_log_no_existe(): void
    {
        $repository = $this->createMock(LogRepositoryInterface::class);
        $repository->expects($this->once())
            ->method('findOrFail')
            ->with(99)
            ->willThrowException(new ModelNotFoundException);
        $repository->expects($this->never())->method('resolved');

        $logPublisher = $this->createMock(LogPublisher::class);
        $logPublisher->expects($this->once())
            ->method('publish')
            ->with(
                'medium',
                $this->anything(),
                'LAR-LOG-018',
                $this->anything(),
                $this->anything(),
                ['log_id' => 99, 'actor_user_id' => 'sub-x'],
                $this->anything(),
            );

        $audit = $this->createMock(AuditPublisher::class);
        $audit->expects($this->never())->method('publish');

        $service = new LogService(
            $repository,
            $audit,
            new ResilientLogPublisher($logPublisher),
        );

        $this->expectException(ModelNotFoundException::class);
        $service->resolved(99, 'sub-x');
    }
}
