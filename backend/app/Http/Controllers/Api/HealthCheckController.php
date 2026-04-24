<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Throwable;

class HealthCheckController extends Controller
{
    /**
     * Estado completo de todos los servicios dependientes.
     * HTTP 200 si todos ok, HTTP 503 si alguno falla.
     */
    public function index(): JsonResponse
    {
        $checks = [
            'database' => $this->checkDatabase(),
        ];

        $allOk = collect($checks)->every(fn (array $c): bool => $c['status'] === 'ok');
        $result = [
            'status' => $allOk ? 'ok' : 'degraded',
            'checks' => $checks,
        ];

        return response()->json($result, $allOk ? 200 : 503);
    }

    /**
     * Liveness probe: confirma que el proceso Laravel está vivo.
     * No verifica dependencias externas. Siempre HTTP 200.
     */
    public function live(): JsonResponse
    {
        return response()->json(['status' => 'ok']);
    }

    /**
     * Readiness probe: verifica dependencias críticas (BD).
     * HTTP 200 cuando está listo, HTTP 503 mientras no lo esté.
     */
    public function ready(): JsonResponse
    {
        $db = $this->checkDatabase();
        $ok = $db['status'] === 'ok';

        return response()->json([
            'status' => $ok ? 'ok' : 'degraded',
            'checks' => ['database' => $db],
        ], $ok ? 200 : 503);
    }

    /**
     * @return array{status: string, message?: string}
     */
    private function checkDatabase(): array
    {
        try {
            DB::connection()->getPdo();

            return ['status' => 'ok'];
        } catch (Throwable $e) {
            return ['status' => 'error', 'message' => $e->getMessage()];
        }
    }
}
