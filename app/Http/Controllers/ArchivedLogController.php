<?php

namespace App\Http\Controllers;

use App\Models\ArchivedLog;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;

class ArchivedLogController extends Controller
{
    public function index(): View
    {
        $archivedLogs = ArchivedLog::query()
            ->with(['application', 'archivedBy', 'errorCode'])
            ->withCount('comments')
            ->latest('archived_at')
            ->paginate(15);

        return view('archived-logs.index', [
            'archivedLogs' => $archivedLogs,
        ]);
    }

    public function show(int $id): View
    {
        ArchivedLog::query()->findOrFail($id);

        return view('archived-logs.index');
    }

    public function destroy(int $id): RedirectResponse
    {
        $archivedLog = ArchivedLog::query()->findOrFail($id);

        $this->authorize('delete', $archivedLog);

        DB::transaction(function () use ($archivedLog): void {
            $archivedLog->logs()->update(['matched_archived_log_id' => null]);
            $archivedLog->delete();
        });

        return redirect()
            ->route('archived-logs.index')
            ->with('status', __('archived_logs.deleted'));
    }
}