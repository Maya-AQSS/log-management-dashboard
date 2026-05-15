<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Repositories\Contracts\LogRepositoryInterface;
use App\Services\LogService;
use App\Support\ResilientLogPublisher;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Maya\Messaging\Publishers\AuditPublisher;
use Maya\Messaging\Publishers\LogPublisher;
use Mockery;
use Tests\TestCase;

class LogServiceFindOrFailTest extends TestCase
{
    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    public function test_find_or_fail_publica_telemetria_si_no_existe(): void
    {
        $logPublisher = $this->createMock(LogPublisher::class);
        $logPublisher->expects($this->once())
            ->method('publish')
            ->with(
                'medium',
                $this->anything(),
                'LAR-LOG-019',
                $this->anything(),
                $this->anything(),
                ['log_id' => 55],
                $this->anything(),
            );

        $repo = Mockery::mock(LogRepositoryInterface::class);
        $repo->shouldReceive('findOrFail')
            ->once()
            ->with(55)
            ->andThrow(new ModelNotFoundException);

        $sut = new LogService(
            $repo,
            $this->createMock(AuditPublisher::class),
            new ResilientLogPublisher($logPublisher),
        );

        $this->expectException(ModelNotFoundException::class);
        $sut->findOrFail(55);
    }
}
