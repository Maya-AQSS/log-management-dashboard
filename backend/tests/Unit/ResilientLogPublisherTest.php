<?php

namespace Tests\Unit;

use App\Support\ResilientLogPublisher;
use Illuminate\Log\Events\MessageLogged;
use Illuminate\Support\Facades\Event;
use Maya\Messaging\Publishers\LogPublisher;
use PHPUnit\Framework\Attributes\Test;
use RuntimeException;
use Tests\TestCase;

class ResilientLogPublisherTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        config(['logging.default' => 'null']);
        $this->app->forgetInstance('log');
    }

    #[Test]
    public function registra_warning_y_no_relanca_si_log_publisher_falla(): void
    {
        Event::fake([MessageLogged::class]);

        $logPublisher = $this->createMock(LogPublisher::class);
        $logPublisher->expects($this->once())
            ->method('publish')
            ->willThrowException(new RuntimeException('broker caído'));

        $original = new RuntimeException('error de negocio');

        $sut = new ResilientLogPublisher($logPublisher);
        $sut->publishFromThrowable($original, 'medium', 'test_error_code', ['k' => 1], 'maya-logs');

        Event::assertDispatched(MessageLogged::class, function (MessageLogged $event): bool {
            return $event->level === 'warning'
                && $event->message === 'maya.logs.publish_failed_after_operation_failure'
                && ($event->context['app'] ?? null) === 'maya-logs'
                && ($event->context['error_code'] ?? null) === 'test_error_code'
                && ($event->context['original_message'] ?? null) === 'error de negocio'
                && ($event->context['publish_error'] ?? null) === 'broker caído';
        });
    }

    #[Test]
    public function delega_en_log_publisher_sin_evento_de_warning_resiliente(): void
    {
        Event::fake([MessageLogged::class]);

        $logPublisher = $this->createMock(LogPublisher::class);
        $logPublisher->expects($this->once())
            ->method('publish');

        $sut = new ResilientLogPublisher($logPublisher);
        $sut->publishFromThrowable(new RuntimeException('x'), 'low', 'code', [], 'app');

        Event::assertNotDispatched(MessageLogged::class, function (MessageLogged $event): bool {
            return $event->message === 'maya.logs.publish_failed_after_operation_failure';
        });
    }

    #[Test]
    public function publish_structured_registra_warning_si_log_publisher_falla(): void
    {
        Event::fake([MessageLogged::class]);

        $logPublisher = $this->createMock(LogPublisher::class);
        $logPublisher->expects($this->once())
            ->method('publish')
            ->willThrowException(new RuntimeException('broker down'));

        $sut = new ResilientLogPublisher($logPublisher);
        $sut->publishStructured('low', 'mensaje', 'LAR-LOG-099', ['k' => 1], 'maya-logs');

        Event::assertDispatched(MessageLogged::class, function (MessageLogged $event): bool {
            return $event->level === 'warning'
                && $event->message === 'maya.logs.publish_failed_after_operation_failure'
                && ($event->context['error_code'] ?? null) === 'LAR-LOG-099'
                && ($event->context['original_message'] ?? null) === 'mensaje'
                && ($event->context['publish_error'] ?? null) === 'broker down';
        });
    }
}
