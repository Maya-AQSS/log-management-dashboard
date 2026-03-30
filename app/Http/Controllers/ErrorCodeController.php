<?php

namespace App\Http\Controllers;

use App\Http\Requests\ErrorCodeRequest;
use App\Models\ErrorCode;
use App\Services\Contracts\ErrorCodeServiceInterface;
use Illuminate\Http\RedirectResponse;
use Illuminate\Contracts\View\View;

class ErrorCodeController extends Controller
{
    public function __construct(private ErrorCodeServiceInterface $errorCodeService) {}

    public function index(): View
    {
        return view('error-codes.index');
    }

    public function create(): RedirectResponse|View 
    {
        $this->authorize('create', ErrorCode::class);

        return view('error-codes.create');
    }

    public function store(ErrorCodeRequest $request): RedirectResponse
    {
        /** @var ErrorCode $errorCode */
        $this->authorize('create', ErrorCode::class);
        $errorCode = $this->errorCodeService->create($request->validated());

        return redirect()
            ->route('error-codes.show', $errorCode->id)
            ->with('status', __('error_codes.created'));
    }

    public function update(ErrorCodeRequest $request, int $id): RedirectResponse
    {
        $errorCode = $this->errorCodeService->findOrFail($id);
        $this->authorize('update', $errorCode);

        $this->errorCodeService->update($errorCode, $request->validated());

        return redirect()
            ->route('error-codes.show', $errorCode->id)
            ->with('status', __('error_codes.updated'));
    }

    public function show(int $id): View
    {
        $errorCode = $this->errorCodeService->findOrFail($id);

        return view('error-codes.show', [
            'errorCodeId' => $id,
        ]);
    }

    public function destroy(int $id): RedirectResponse
    {
        $errorCode = $this->errorCodeService->findOrFail($id);
        $this->authorize('delete', $errorCode);

        $this->errorCodeService->delete($errorCode);

        return redirect()
            ->route('error-codes.index')
            ->with('status', __('error_codes.deleted'));
    }
}
