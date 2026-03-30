<?php

namespace App\Http\Controllers;

use App\Services\Contracts\ArchivedLogServiceInterface;
use App\Support\BackUrlResolver;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class ArchivedLogController extends Controller
{
    public function __construct(
        private ArchivedLogServiceInterface $archivedLogService,
        private BackUrlResolver $backUrlResolver,
    ) {}

    public function index(): View
    {
        return view('archived-logs.index');
    }

    public function show(Request $request, int $id): View
    {
        $archivedLog = $this->archivedLogService->findOrFail($id);
        $backHref = $this->backUrlResolver->resolveForArchivedShow($request, $id);

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
}
