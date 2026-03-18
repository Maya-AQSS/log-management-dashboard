<?php

namespace App\Http\Controllers;

use App\Models\ArchivedLog;
use App\Models\ErrorCode;
use App\Models\Log;
use Illuminate\Contracts\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        return view('dashboard', [
            'stats' => [
                'activeLogs' => Log::query()->count(),
                'archivedLogs' => ArchivedLog::query()->count(),
                'errorCodes' => ErrorCode::query()->count(),
            ],
        ]);
    }
}