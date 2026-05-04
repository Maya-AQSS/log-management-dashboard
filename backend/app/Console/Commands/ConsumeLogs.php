<?php

namespace App\Console\Commands;

use App\Models\Application;
use App\Models\ErrorCode;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Maya\Messaging\Support\AmqpConsumer;

class ConsumeLogs extends Command
{
    protected $signature = 'logs:consume {--queue=logs.ingest}';

    protected $description = 'Consume logs.ingest from RabbitMQ and persist each log in the logs table';

    public function handle(AmqpConsumer $consumer): int
    {
        $queue = (string) $this->option('queue');
        $this->info("Consuming from queue: {$queue}");

        $consumer->consume($queue, function (array $payload) {
            $applicationId = $this->resolveApplicationId($payload['app'] ?? null);
            if ($applicationId === null) {
                return; // drop payload silently — app is unknown, already logged by consumer
            }

            $errorCodeId = $this->resolveErrorCodeId(
                code: $payload['error_code'] ?? null,
                applicationId: $applicationId,
                file: $payload['file'] ?? null,
                line: $payload['line'] ?? null,
            );

            // Bypass the model's write-protection (Log::saving => false) by using the
            // query builder directly. The model remains read-only for HTTP/Livewire
            // consumers; only this worker writes via raw DB.
            DB::table('logs')->insert([
                'error_code_id'  => $errorCodeId,
                'application_id' => $applicationId,
                'severity'       => $payload['severity'] ?? 'other',
                'message'        => $payload['message'] ?? '',
                'file'           => $payload['file'] ?? null,
                'line'           => $payload['line'] ?? null,
                'metadata'       => isset($payload['metadata'])
                    ? json_encode($payload['metadata'])
                    : null,
                'resolved'       => false,
                'created_at'     => isset($payload['occurred_at'])
                    ? Carbon::parse($payload['occurred_at'])
                    : now(),
            ]);
        });

        return self::SUCCESS;
    }

    private function resolveApplicationId(?string $name): ?int
    {
        if ($name === null || $name === '') {
            return null;
        }

        // applications es ahora una vista sobre FDW → maya_auth.applications (read-only).
        // Solo se aceptan logs de apps registradas en maya_auth (por slug).
        return Application::where('name', $name)->value('id');
    }

    private function resolveErrorCodeId(?string $code, int $applicationId, ?string $file, ?int $line): ?int
    {
        if ($code === null || $code === '') {
            return null;
        }

        $errorCode = ErrorCode::firstOrCreate(
            ['code' => $code, 'application_id' => $applicationId],
            ['file' => $file, 'line' => $line],
        );

        return $errorCode->id;
    }
}
