<?php

namespace App\Http\Controllers;

use App\Services\Contracts\LogServiceInterface;
use Illuminate\Contracts\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class LogController extends Controller
{
    public function __construct(private LogServiceInterface $logService) {}

    public function index(): View
    {
        $logs = $this->logService->paginate(15);

        return view('logs.index', ['logs' => $logs]);
    }

    public function show(int $id): View
    {
        $log = $this->logService->findOrFail($id);

        return view('logs.show', ['log' => $log]);
    }

    public function stream(): StreamedResponse
    {
        return response()->stream(function (): void {
            $payload = $this->logService->streamPayload(10);

            echo "event: logs\n";
            echo 'data: ' . json_encode(
                $payload,
                JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES
            ) . "\n\n";

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
