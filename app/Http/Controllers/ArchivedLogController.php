<?php

namespace App\Http\Controllers;

use App\Http\Requests\ArchivedLogIndexRequest;
use App\Services\Contracts\ArchivedLogServiceInterface;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;

class ArchivedLogController extends Controller
{
    public function __construct(private ArchivedLogServiceInterface $archivedLogService) {}

    public function index(ArchivedLogIndexRequest $request): View
    {
        $validated = $request->validated();

        $severity = $validated['severity'] ?? null;
        $tutorial = $validated['tutorial'] ?? null;

        $archivedLogs = $this->archivedLogService->searchAndFilter($severity, $tutorial, 15);

        return view('archived-logs.index', [
            'archivedLogs' => $archivedLogs,
            'severity' => $severity,
            'tutorial' => $tutorial,
        ]);
    }

    public function show(int $id): View
    {
        $archivedLog = $this->archivedLogService->findOrFail($id);

        return view('logs.show', [
            'source' => 'archived_log',
            'archivedLog' => $archivedLog,
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
