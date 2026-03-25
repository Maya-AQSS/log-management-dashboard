<?php

namespace App\Http\Controllers;

use App\Models\Application;
use App\Models\ErrorCode;
use App\Services\Contracts\ErrorCodeServiceInterface;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Contracts\View\View;
use Illuminate\Validation\Rule;

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

    public function store(Request $request): RedirectResponse
    {
        $validated = $this->validateErrorCode($request);

        /** @var ErrorCode $errorCode */
        $errorCode = $this->errorCodeService->create($validated);

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

    public function update(Request $request, int $id): RedirectResponse
    {
        $errorCode = $this->errorCodeService->findOrFail($id);
        $validated = $this->validateErrorCode($request, $errorCode);

        $this->errorCodeService->update($errorCode, $validated);

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

    private function validateErrorCode(Request $request, ?ErrorCode $errorCode = null): array
    {
        $idToIgnore = $errorCode?->id;

        return $request->validate([
            'application_id' => ['required', 'integer', 'exists:applications,id'],
            'code' => [
                'required',
                'string',
                'max:255',
                Rule::unique('error_codes', 'code')
                    ->ignore($idToIgnore)
                    ->where(fn ($query) => $query->where('application_id', $request->integer('application_id'))),
            ],
            'name' => ['required', 'string', 'max:255'],
            'file' => ['nullable', 'string', 'max:255'],
            'line' => ['nullable', 'integer', 'min:1'],
            'severity' => ['nullable', Rule::in(['critical', 'high', 'medium', 'low', 'other'])],
            'description' => ['nullable', 'string'],
        ]);
    }
}
