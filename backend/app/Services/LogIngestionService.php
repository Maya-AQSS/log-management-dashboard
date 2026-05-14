<?php

namespace App\Services;

use App\Models\Application;
use App\Support\ResilientLogPublisher;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class LogIngestionService
{
    protected array $slugToId = [];

    private array $errorCodeIdCache = [];

    private array $logBuffer = [];

    private const MAX_ERROR_CODE_CACHE = 10_000;

    private const DEFAULT_BATCH_SIZE = 100;

    public function __construct(
        private readonly ResilientLogPublisher $resilientLogPublisher,
        private readonly int $batchSize = self::DEFAULT_BATCH_SIZE,
    ) {
        if ($this->batchSize < 1) {
            throw new \InvalidArgumentException("batchSize must be >= 1, got {$this->batchSize}");
        }
    }

    /**
     * Obtiene el slug de la aplicación en la mensajería.
     *
     * @return string El slug de la aplicación en la mensajería.
     */
    private function messagingAppSlug(): string
    {
        return (string) config('messaging.app');
    }

    /**
     * Carga el mapa de slugs de aplicaciones a sus IDs.
     */
    public function loadApplicationMap(): void
    {
        // Pre-loads application slug→id once per worker startup.
        // Applications are a small, stable set; the map is refreshed on restart.
        $this->setApplicationMap(Application::pluck('id', 'slug')->all());
    }

    /**
     * Establece el mapa de slugs de aplicaciones a sus IDs.
     *
     * @param  array<string, int>  $map  El mapa de slugs de aplicaciones a sus IDs.
     */
    public function setApplicationMap(array $map): void
    {
        $this->slugToId = $map;
    }

    /**
     * Ingresa un log en la tabla `logs`.
     *
     * @param  array<string, mixed>  $payload  El payload del log.
     */
    public function ingest(array $payload): void
    {
        $log = LogPayload::fromArray($payload);

        $applicationId = $this->resolveApplicationId($log->app);
        if ($applicationId === null) {
            return;
        }

        try {
            $errorCodeId = $this->resolveErrorCodeId(
                code: $log->errorCode,
                applicationId: $applicationId,
                file: $log->file,
                line: $log->line,
            );
        } catch (\Throwable $e) {
            $this->resilientLogPublisher->publishFromThrowable(
                $e,
                'high',
                'LAR-LOG-005',
                ['component' => 'log_ingestion', 'stage' => 'resolve_error_code'],
                $this->messagingAppSlug(),
            );
            throw $e;
        }

        $this->logBuffer[] = [
            'error_code_id' => $errorCodeId,
            'application_id' => $applicationId,
            'severity' => $log->severity,
            'message' => $log->message,
            'file' => $log->file,
            'line' => $log->line,
            'metadata' => is_array($log->metadata)
                ? (json_encode($log->metadata, JSON_UNESCAPED_UNICODE) ?: null)
                : $log->metadata,
            'resolved' => false,
            'created_at' => $log->occurredAt !== null
                ? $this->parseTimestamp($log->occurredAt)
                : now(),
        ];

        if (count($this->logBuffer) >= $this->batchSize) {
            $this->flush();
        }
    }

    /**
     * Escribe todas las filas de log en una sola instrucción INSERT.
     * Llama después del bucle de consumo RabbitMQ para vaciar cualquier lote parcial.
     */
    public function flush(): void
    {
        if (empty($this->logBuffer)) {
            return;
        }

        try {
            // Bypass the model's write-protection (saving => false) via query builder.
            // The model remains read-only for HTTP consumers; only this worker writes via raw DB.
            DB::table('logs')->insert($this->logBuffer);
        } catch (\Throwable $e) {
            $this->resilientLogPublisher->publishFromThrowable(
                $e,
                'high',
                'LAR-LOG-006',
                [
                    'component' => 'log_ingestion',
                    'stage' => 'flush_batch',
                    'buffer_count' => count($this->logBuffer),
                ],
                $this->messagingAppSlug(),
            );
            throw $e;
        } finally {
            $this->logBuffer = [];
        }
    }

    /**
     * Resuelve el ID de la aplicación a partir del slug.
     *
     * @param  string  $slug  El slug de la aplicación.
     * @return int|null El ID de la aplicación o null si no se encuentra.
     */
    private function resolveApplicationId(string $slug): ?int
    {
        if ($slug === '') {
            return null;
        }

        if (! array_key_exists($slug, $this->slugToId)) {
            $this->resilientLogPublisher->publishStructured(
                'medium',
                'ConsumeLogs: descartar payload — slug de aplicación no registrado en maya_auth',
                'LAR-LOG-007',
                ['slug' => $slug, 'component' => 'log_ingestion'],
                $this->messagingAppSlug(),
            );

            return null;
        }

        $id = (int) $this->slugToId[$slug];

        return $id > 0 ? $id : null;
    }

    /**
     * Resuelve el ID del código de error a partir del código y la aplicación.
     *
     * @param  string|null  $code  El código de error.
     * @param  int  $applicationId  El ID de la aplicación.
     * @param  string|null  $file  El archivo donde ocurrió el error.
     * @param  int|null  $line  La línea donde ocurrió el error.
     * @return int|null El ID del código de error o null si no se encuentra.
     */
    private function resolveErrorCodeId(?string $code, int $applicationId, ?string $file, ?int $line): ?int
    {
        if ($code === null) {
            return null;
        }

        // Null byte separator avoids collisions between codes containing ':' and numeric appIds.
        $cacheKey = $code."\0".$applicationId;
        if (isset($this->errorCodeIdCache[$cacheKey])) {
            return $this->errorCodeIdCache[$cacheKey];
        }

        // INSERT ON CONFLICT DO NOTHING — atomic under concurrent workers.
        // 'name' defaults to the code string; users can set a readable name via HTTP API.
        DB::table('error_codes')->insertOrIgnore([
            'code' => $code,
            'application_id' => $applicationId,
            'name' => $code,
            'file' => $file,
            'line' => $line,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $id = DB::table('error_codes')
            ->where('code', $code)
            ->where('application_id', $applicationId)
            ->value('id');

        if ($id === null) {
            $this->resilientLogPublisher->publishStructured(
                'high',
                'ConsumeLogs: no se pudo resolver el ID para el código de error',
                'LAR-LOG-008',
                ['code' => $code, 'application_id' => $applicationId, 'component' => 'log_ingestion'],
                $this->messagingAppSlug(),
            );

            return null;
        }

        // Full reset when threshold reached: O(1), no hot-entry bias from partial eviction.
        if (count($this->errorCodeIdCache) >= self::MAX_ERROR_CODE_CACHE) {
            $this->errorCodeIdCache = [];
        }

        $this->errorCodeIdCache[$cacheKey] = $id;

        return $id;
    }

    /**
     * Analiza una cadena de tiempo y la convierte en un objeto Carbon.
     *
     * @param  string  $value  La cadena de tiempo.
     * @return Carbon El objeto Carbon.
     */
    private function parseTimestamp(string $value): Carbon
    {
        // strtotime() guards against Carbon::parse()'s permissiveness with relative strings
        // ("next year", "tomorrow"). Reusing the result avoids parsing the string twice.
        $unix = strtotime($value);
        if ($unix === false) {
            $this->resilientLogPublisher->publishStructured(
                'medium',
                'ConsumeLogs: formato de occurred_at inválido, se usa now()',
                'LAR-LOG-009',
                ['value' => $value, 'component' => 'log_ingestion'],
                $this->messagingAppSlug(),
            );

            return now();
        }

        return Carbon::createFromTimestamp($unix);
    }
}
