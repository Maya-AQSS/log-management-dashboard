<?php

namespace App\Http\Controllers;

use App\Services\Contracts\ErrorCodeServiceInterface;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class ErrorCodeController extends Controller
{
    public function __construct(private ErrorCodeServiceInterface $errorCodeService) {}

    public function index(Request $request): View
    {
        $validated = $request->validate([
            'severity' => ['nullable', 'in:critical,high,medium,low,other'],
        ]);

        $severity = $validated['severity'] ?? null;

        $errorCodes = $this->errorCodeService->searchAndFilter($severity, 15);

        return view('error-codes.index', [
            'errorCodes' => $errorCodes,
            'severity' => $severity,
        ]);
    }

    public function show(int $id): View
    {
        $errorCode = $this->errorCodeService->findOrFail($id);

        return view('error-codes.show', [
            'errorCode' => $errorCode,
        ]);
    }
}
