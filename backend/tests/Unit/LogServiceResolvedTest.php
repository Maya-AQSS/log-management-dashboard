<?php

namespace Tests\Unit;

use App\Models\Log;
use App\Repositories\Contracts\LogRepositoryInterface;
use App\Services\LogService;
use Maya\Messaging\Publishers\AuditPublisher;
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

        $service = new LogService($repository, $publisher);
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

        $service = new LogService($repository, $publisher);
        $service->resolved(7, 'sub-2');
    }
}
