<?php

namespace App\Http\Controllers;


use App\Services\Contracts\ArchivedLogServiceInterface;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;

class ArchivedLogController extends Controller
{
    public function __construct(private ArchivedLogServiceInterface $archivedLogService) {}

    public function index(): View
    {
        $archivedLogs = $this->archivedLogService->paginate(15);

        return view('archived-logs.index', [
            'archivedLogs' => $archivedLogs,
        ]);
    }

    public function show(int $id): View
    {
        $archivedLog = $this->archivedLogService->findOrFail($id);

        return view('archived-logs.index', [
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
