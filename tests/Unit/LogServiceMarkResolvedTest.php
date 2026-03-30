<?php

namespace Tests\Unit;

use App\Repositories\Contracts\LogRepositoryInterface;
use App\Services\LogService;
use PHPUnit\Framework\TestCase;

class LogServiceMarkResolvedTest extends TestCase
{
    public function test_mark_resolved_delegates_to_repository(): void
    {
        $repository = $this->createMock(LogRepositoryInterface::class);
        $repository->expects($this->once())
            ->method('markResolved')
            ->with(42);

        $service = new LogService($repository);
        $service->markResolved(42);
    }
}
