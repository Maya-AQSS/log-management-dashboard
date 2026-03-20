<?php

namespace App\Http\Controllers;

use App\Services\Contracts\ArchivedLogServiceInterface;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class ArchivedLogController extends Controller
{
    public function __construct(private ArchivedLogServiceInterface $archivedLogService) {}

    public function index(Request $request): View
    {
        $validated = $request->validate([
            'severity' => ['nullable', 'in:critical,high,medium,low,other'],
            'tutorial' => ['nullable', 'in:with_tutorial,without_tutorial'],
        ]);

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

        return view('archived-logs.show', [
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
