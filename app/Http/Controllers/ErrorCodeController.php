<?php

namespace App\Http\Controllers;

use App\Services\Contracts\ErrorCodeServiceInterface;
use Illuminate\Contracts\View\View;

class ErrorCodeController extends Controller
{
    public function __construct(private ErrorCodeServiceInterface $errorCodeService) {}

    public function index(): View
    {
        $errorCodes = $this->errorCodeService->paginate(15);

        return view('error-codes.index', [
            'errorCodes' => $errorCodes,
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
