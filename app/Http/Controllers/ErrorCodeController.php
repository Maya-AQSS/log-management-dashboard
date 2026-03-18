<?php

namespace App\Http\Controllers;

use App\Models\ErrorCode;
use Illuminate\Contracts\View\View;

class ErrorCodeController extends Controller
{
    public function index(): View
    {
        $errorCodes = ErrorCode::query()
            ->with('application')
            ->withCount(['logs', 'archivedLogs', 'comments'])
            ->orderBy('code')
            ->paginate(15);

        return view('error-codes.index', [
            'errorCodes' => $errorCodes,
        ]);
    }

    public function show(int $id): View
    {
        ErrorCode::query()->findOrFail($id);

        return view('error-codes.index');
    }
}