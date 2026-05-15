<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Models\Comment;
use App\Models\ErrorCode;
use App\Repositories\Contracts\CommentRepositoryInterface;
use App\Services\CommentContentSanitizer;
use App\Services\CommentService;
use App\Support\ResilientLogPublisher;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Validation\ValidationException;
use Maya\Messaging\Publishers\LogPublisher;
use Mockery;
use PHPUnit\Framework\Attributes\DoesNotPerformAssertions;
use Tests\TestCase;

class CommentServiceTest extends TestCase
{
    private function makeSut(
        CommentRepositoryInterface $repo,
        ResilientLogPublisher $publisher,
    ): CommentService {
        return new CommentService($repo, $publisher, new CommentContentSanitizer);
    }

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
                'LAR-LOG-014',
                $this->anything(),
                $this->anything(),
                ['comment_id' => 7],
                $this->anything(),
            );

        $repo = Mockery::mock(CommentRepositoryInterface::class);
        $repo->shouldReceive('findOrFail')
            ->once()
            ->with(7)
            ->andThrow(new ModelNotFoundException);

        $sut = $this->makeSut($repo, new ResilientLogPublisher($logPublisher));

        $this->expectException(ModelNotFoundException::class);
        $sut->findOrFail(7);
    }

    #[DoesNotPerformAssertions]
    public function test_delete_delega_en_repositorio_sin_error(): void
    {
        $comment = new Comment;
        $comment->id = 3;
        $comment->exists = true;

        $repo = Mockery::mock(CommentRepositoryInterface::class);
        $repo->shouldReceive('delete')->once()->with($comment);

        $publisher = new ResilientLogPublisher($this->createMock(LogPublisher::class));
        $sut = $this->makeSut($repo, $publisher);

        $sut->delete($comment);
    }

    public function test_create_no_publica_si_falla_validacion_de_contenido(): void
    {
        $commentable = new ErrorCode;
        $commentable->id = 1;
        $commentable->exists = true;

        $repo = Mockery::mock(CommentRepositoryInterface::class);
        $repo->shouldReceive('createForCommentable')->never();

        $logPublisher = $this->createMock(LogPublisher::class);
        $logPublisher->expects($this->never())->method('publish');

        $sut = $this->makeSut($repo, new ResilientLogPublisher($logPublisher));

        $this->expectException(ValidationException::class);
        $sut->createForCommentable($commentable, 'user-1', '   ');
    }
}
