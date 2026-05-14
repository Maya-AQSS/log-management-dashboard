<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Models\ArchivedLog;
use App\Repositories\Contracts\ArchivedLogRepositoryInterface;
use App\Services\ArchivedLogService;
use App\Support\ResilientLogPublisher;
use Maya\Messaging\Publishers\LogPublisher;
use Mockery;
use PHPUnit\Framework\Attributes\DoesNotPerformAssertions;
use Tests\TestCase;

class ArchivedLogServiceTest extends TestCase
{
    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    public function test_archive_devuelve_existente_sin_depender_de_eventos(): void
    {
        $existing = new ArchivedLog;
        $existing->exists = true;
        $existing->id = 42;
        $existing->wasRecentlyCreated = false;

        $repo = Mockery::mock(ArchivedLogRepositoryInterface::class);
        $repo->shouldReceive('archiveFromLogId')
            ->once()
            ->with(7, 'user-subject')
            ->andReturn($existing);

        $publisher = new ResilientLogPublisher($this->createMock(LogPublisher::class));
        $sut = new ArchivedLogService($repo, $publisher);
        $out = $sut->archiveFromLogId(7, 'user-subject');

        $this->assertSame(42, $out->id);
    }

    #[DoesNotPerformAssertions]
    public function test_update_no_persiste_si_los_valores_coinciden(): void
    {
        $log = new ArchivedLog(['description' => 'igual', 'archived_by_id' => 'sub-1']);
        $log->id = 10;
        $log->exists = true;

        $repo = Mockery::mock(ArchivedLogRepositoryInterface::class);
        $repo->shouldReceive('updateArchivedFields')->never();

        $publisher = new ResilientLogPublisher($this->createMock(LogPublisher::class));
        $sut = new ArchivedLogService($repo, $publisher);

        $sut->updateArchivedFields($log, ['description' => 'igual']);
    }

    #[DoesNotPerformAssertions]
    public function test_update_persiste_cuando_hay_cambio(): void
    {
        $log = new ArchivedLog(['description' => 'viejo', 'archived_by_id' => 'sub-1']);
        $log->id = 11;
        $log->exists = true;

        $repo = Mockery::mock(ArchivedLogRepositoryInterface::class);
        $repo->shouldReceive('updateArchivedFields')
            ->once()
            ->with($log, ['description' => 'nuevo']);

        $publisher = new ResilientLogPublisher($this->createMock(LogPublisher::class));
        $sut = new ArchivedLogService($repo, $publisher);

        $sut->updateArchivedFields($log, ['description' => 'nuevo']);
    }

    #[DoesNotPerformAssertions]
    public function test_delete_no_llama_al_repositorio_cuando_devuelve_false(): void
    {
        $log = new ArchivedLog;
        $log->id = 3;
        $log->archived_by_id = 'u';
        $log->exists = true;

        $repo = Mockery::mock(ArchivedLogRepositoryInterface::class);
        $repo->shouldReceive('delete')->once()->with($log)->andReturn(false);

        $publisher = new ResilientLogPublisher($this->createMock(LogPublisher::class));
        $sut = new ArchivedLogService($repo, $publisher);

        $sut->delete($log);
    }

    #[DoesNotPerformAssertions]
    public function test_delete_llama_al_repositorio_cuando_devuelve_true(): void
    {
        $log = new ArchivedLog;
        $log->id = 4;
        $log->archived_by_id = 'u2';
        $log->exists = true;

        $repo = Mockery::mock(ArchivedLogRepositoryInterface::class);
        $repo->shouldReceive('delete')->once()->with($log)->andReturn(true);

        $publisher = new ResilientLogPublisher($this->createMock(LogPublisher::class));
        $sut = new ArchivedLogService($repo, $publisher);

        $sut->delete($log);
    }
}
