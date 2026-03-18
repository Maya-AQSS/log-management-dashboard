<?php

namespace App\Http\Controllers;


use App\Models\Log;
use Illuminate\Contracts\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class LogController extends Controller
{
    public function index(): View
    {
        $logs = Log::query()
            ->with(['application', 'errorCode', 'archivedLog'])
            ->latest('created_at')
            ->paginate(15);

        return view('logs.index', [
            'logs' => $logs,
        ]);
    }

    public function show(int $id): View
    {
        Log::query()->findOrFail($id);

        return view('logs.index');
    }

    public function stream(): StreamedResponse
    {
        return response()->stream(function (): void {
            $payload = Log::query()
                ->with(['application', 'errorCode'])
                ->latest('created_at')
                ->limit(10)
                ->get()
                ->map(fn (Log $log) => [
                    'id' => $log->id,
                    'severity' => $log->severity,
                    'message' => $log->message,
                    'application' => $log->application?->name,
                    'error_code' => $log->errorCode?->code,
                    'created_at' => optional($log->created_at)->toIso8601String(),
                ]);

            echo "event: logs\n";
            echo 'data: ' . json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) . "\n\n";

            if (function_exists('ob_flush')) {
                ob_flush();
            }

            flush();
        }, 200, [
            'Cache-Control' => 'no-cache',
            'Content-Type' => 'text/event-stream',
            'X-Accel-Buffering' => 'no',
        ]);
    }
}
