<?php

namespace App\Http\Controllers;

use App\Services\Contracts\ArchivedLogServiceInterface;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ArchivedLogController extends Controller
{
    public function __construct(private ArchivedLogServiceInterface $archivedLogService) {}

    public function index(): View
    {
        return view('archived-logs.index');
    }

    public function show(Request $request, int $id): View
    {
        $archivedLog = $this->archivedLogService->findOrFail($id);
        $backHref = $this->resolveBackUrl($request, $id);

        return view('logs.show', [
            'source' => 'archived_log',
            'archivedLog' => $archivedLog,
            'backHref' => $backHref,
        ]);
    }

    public function destroy(int $id): RedirectResponse
    {
        $archivedLog = $this->archivedLogService->findOrFail($id);

        $this->authorize('delete', $archivedLog);

        $this->archivedLogService->delete($archivedLog);

        return redirect()
            ->route('archived-logs.index')
            ->with('status', __('archived_logs.deleted'));
    }

    private function resolveBackUrl(Request $request, int $id): string
    {
        $fallback = route('archived-logs.index');
        $sessionKey = "navigation.archived.show.$id.back";
        $referer = $request->headers->get('referer');

        if (is_string($referer) && Str::startsWith($referer, url('/'))) {
            if ($this->isArchivedIndexUrl($referer)) {
                $request->session()->put($sessionKey, $referer);
            }

            if ($this->isLogDetailUrl($referer)) {
                return $referer;
            }
        }

        $stored = $request->session()->get($sessionKey);
        return is_string($stored) && Str::startsWith($stored, url('/')) ? $stored : $fallback;
    }

    private function isArchivedIndexUrl(string $url): bool
    {
        $indexPrefix = route('archived-logs.index');
        $showPrefix = route('archived-logs.index') . '/';

        return Str::startsWith($url, $indexPrefix) && !Str::startsWith($url, $showPrefix);
    }

    private function isLogDetailUrl(string $url): bool
    {
        $showPrefix = route('logs.index') . '/';

        return Str::startsWith($url, $showPrefix);
    }
}
