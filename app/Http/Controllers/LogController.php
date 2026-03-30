<?php

namespace App\Http\Controllers;

use App\Services\Contracts\ArchivedLogServiceInterface;
use App\Services\Contracts\LogServiceInterface;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Throwable;

class LogController extends Controller
{
    public function __construct(
        private LogServiceInterface $logService,
        private ArchivedLogServiceInterface $archivedLogService,
    ) {}

    public function index(): View
    {
        return view('logs.index');
    }

    public function show(Request $request, int $id): View
    {
        $log = $this->logService->findOrFail($id);
        $backHref = $this->resolveBackUrl($request, $id);
        $archivedLogId = $this->logService->archivedLogIdFor($id);

        return view('logs.show', [
            'source' => 'log',
            'log' => $log,
            'backHref' => $backHref,
            'archivedLogId' => $archivedLogId,
        ]);
    }

    public function archive(int $id): RedirectResponse
    {
        $matchedId = $this->logService->archivedLogIdFor($id);
        if ($matchedId !== null) {
            return redirect()->route('archived-logs.show', $matchedId);
        }

        try {
            $archivedLog = $this->archivedLogService->archiveFromLogId($id, (int) auth()->id());
            session()->flash('status', __('logs.archived_success'));

            return redirect()->route('archived-logs.show', $archivedLog->id);
        } catch (Throwable $e) {
            report($e);
            session()->flash('status', __('logs.archived_error'));

            return redirect()->route('logs.show', $id);
        }
    }

    public function resolve(int $id): RedirectResponse
    {
        $this->logService->resolved($id);

        return redirect()->route('logs.show', $id)->with('status', __('logs.status.resolved_success'));
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

    private function resolveBackUrl(Request $request, int $id): string
    {
        $fallback = route('logs.index');
        $sessionKey = "navigation.logs.show.$id.back";
        $referer = $request->headers->get('referer');

        if (is_string($referer) && Str::startsWith($referer, url('/')) && $this->isLogsIndexUrl($referer)) {
            $request->session()->put($sessionKey, $referer);
        }

        $stored = $request->session()->get($sessionKey);
        return is_string($stored) && Str::startsWith($stored, url('/')) ? $stored : $fallback;
    }

    private function isLogsIndexUrl(string $url): bool
    {
        $indexPrefix = route('logs.index');
        $showPrefix = route('logs.index') . '/';

        return Str::startsWith($url, $indexPrefix) && !Str::startsWith($url, $showPrefix);
    }
}
