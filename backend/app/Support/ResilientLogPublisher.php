<?php

namespace App\Support;

use Illuminate\Support\Facades\Log;
use Maya\Messaging\Publishers\LogPublisher;
use Throwable;

/**
 * Envuelve {@see LogPublisher} para publicar fallos a maya.logs sin que un error
 * del broker enmascare la excepción original del flujo de negocio.
 *
 * Reutilizable desde cualquier servicio que quiera registrar un fallo estructurado
 * y seguir relanzando o devolviendo la excepción original.
 */
final class ResilientLogPublisher
{
    public function __construct(
        private readonly LogPublisher $logPublisher,
    ) {}

    /**
     * Publica un fallo a maya.logs sin que un error del broker enmascare la excepción original del flujo de negocio.
     *
     * @param  array<string, mixed>  $metadata
     */
    public function publishFromThrowable(Throwable $original, string $severity, string $errorCode, array $metadata, string $app): void
    {
        try {
            $this->logPublisher->publish(
                severity: $severity,
                message: $original->getMessage(),
                errorCode: $errorCode,
                file: $original->getFile(),
                line: $original->getLine(),
                metadata: $metadata,
                app: $app,
            );
        } catch (Throwable $publishError) {
            Log::warning('maya.logs.publish_failed_after_operation_failure', [
                'app' => $app,
                'error_code' => $errorCode,
                'original_class' => $original::class,
                'original_message' => $original->getMessage(),
                'publish_error_class' => $publishError::class,
                'publish_error' => $publishError->getMessage(),
            ]);
        }
    }

    /**
     * Incidencia sin {@see Throwable} (p. ej. slug desconocido): misma resiliencia que {@see publishFromThrowable}.
     *
     * @param  array<string, mixed>  $metadata
     */
    public function publishStructured(
        string $severity,
        string $message,
        string $errorCode,
        array $metadata,
        string $app,
        ?string $file = null,
        ?int $line = null,
    ): void {
        try {
            $this->logPublisher->publish(
                severity: $severity,
                message: $message,
                errorCode: $errorCode,
                file: $file,
                line: $line,
                metadata: $metadata,
                app: $app,
            );
        } catch (Throwable $publishError) {
            Log::warning('maya.logs.publish_failed_after_operation_failure', [
                'app' => $app,
                'error_code' => $errorCode,
                'original_message' => $message,
                'publish_error_class' => $publishError::class,
                'publish_error' => $publishError->getMessage(),
            ]);
        }
    }
}
