<?php

namespace App\Http\Controllers;

use App\Http\Requests\ErrorCodeIndexRequest;
use App\Services\Contracts\ErrorCodeServiceInterface;
use Illuminate\Contracts\View\View;

class ErrorCodeController extends Controller
{
    public function __construct(private ErrorCodeServiceInterface $errorCodeService) {}

    public function index(ErrorCodeIndexRequest $request): View
    {
        $validated = $request->validated();

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
