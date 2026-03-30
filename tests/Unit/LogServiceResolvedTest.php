<?php

namespace Tests\Unit;

use App\Models\Log;
use App\Repositories\Contracts\LogRepositoryInterface;
use App\Services\LogService;
use PHPUnit\Framework\TestCase;

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
            ->with(42);

        $service = new LogService($repository);
        $service->resolved(42);
    }
}
