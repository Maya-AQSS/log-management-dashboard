<?php

namespace App\Http\Controllers;

use App\Http\Requests\ErrorCodeRequest;
use App\Models\Application;
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

    public function create(): View
    {
        $applications = Application::query()
            ->orderBy('name')
            ->pluck('name', 'id');

        return view('error-codes.create', [
            'applications' => $applications,
        ]);
    }

    public function store(ErrorCodeRequest $request): RedirectResponse
    {
        /** @var ErrorCode $errorCode */
        $errorCode = $this->errorCodeService->create($request->validated());

        return redirect()
            ->route('error-codes.show', $errorCode->id)
            ->with('status', __('error_codes.created'));
    }

    public function edit(int $id): View
    {
        $errorCode = $this->errorCodeService->findOrFail($id);
        $applications = Application::query()
            ->orderBy('name')
            ->pluck('name', 'id');

        return view('error-codes.edit', [
            'errorCode' => $errorCode,
            'applications' => $applications,
        ]);
    }

    public function update(ErrorCodeRequest $request, int $id): RedirectResponse
    {
        $errorCode = $this->errorCodeService->findOrFail($id);

        $this->errorCodeService->update($errorCode, $request->validated());

        return redirect()
            ->route('error-codes.show', $errorCode->id)
            ->with('status', __('error_codes.updated'));
    }

    public function show(int $id): View
    {
        $errorCode = $this->errorCodeService->findOrFail($id);

        return view('error-codes.show', [
            'errorCode' => $errorCode,
        ]);
    }

    public function destroy(int $id): RedirectResponse
    {
        $errorCode = $this->errorCodeService->findOrFail($id);

        $this->errorCodeService->delete($errorCode);

        return redirect()
            ->route('error-codes.index')
            ->with('status', __('error_codes.deleted'));
    }
}
