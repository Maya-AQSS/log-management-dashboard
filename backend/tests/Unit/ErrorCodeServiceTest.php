<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Models\ErrorCode;
use App\Repositories\Contracts\ErrorCodeRepositoryInterface;
use App\Services\ErrorCodeService;
use App\Support\ResilientLogPublisher;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Maya\Messaging\Publishers\LogPublisher;
use Mockery;
use PHPUnit\Framework\Attributes\DoesNotPerformAssertions;
use Tests\TestCase;

class ErrorCodeServiceTest extends TestCase
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
                'LAR-LOG-010',
                $this->anything(),
                $this->anything(),
                ['error_code_id' => 99],
                $this->anything(),
            );

        $repo = Mockery::mock(ErrorCodeRepositoryInterface::class);
        $repo->shouldReceive('findOrFail')
            ->once()
            ->with(99)
            ->andThrow(new ModelNotFoundException);

        $sut = new ErrorCodeService($repo, new ResilientLogPublisher($logPublisher));

        $this->expectException(ModelNotFoundException::class);
        $sut->findOrFail(99);
    }

    #[DoesNotPerformAssertions]
    public function test_create_delega_en_repositorio_sin_error(): void
    {
        $model = new ErrorCode(['code' => 'X', 'application_id' => 1, 'name' => 'X']);
        $model->id = 1;

        $repo = Mockery::mock(ErrorCodeRepositoryInterface::class);
        $repo->shouldReceive('create')
            ->once()
            ->with(['code' => 'X', 'application_id' => 1])
            ->andReturn($model);

        $publisher = new ResilientLogPublisher($this->createMock(LogPublisher::class));
        $sut = new ErrorCodeService($repo, $publisher);

        $sut->create(['code' => 'X', 'application_id' => 1]);
    }
}
