<?php

namespace App\Livewire;

use App\Enums\ApplicationPluckScope;
use App\Services\Contracts\ApplicationServiceInterface;
use App\Services\Contracts\ErrorCodeServiceInterface;
use Illuminate\Contracts\View\View;
use Livewire\Component;

class ErrorCodeForm extends Component
{
    private ApplicationServiceInterface $applicationService;

    private ErrorCodeServiceInterface $errorCodeService;

    /**
     * Livewire 4: el contenedor inyecta dependencias en hooks como boot(); no usar el constructor.
     *
     * @see https://livewire.laravel.com/docs/4.x/lifecycle-hooks
     */
    public function boot(
        ApplicationServiceInterface $applicationService,
        ErrorCodeServiceInterface $errorCodeService,
    ): void {
        $this->applicationService = $applicationService;
        $this->errorCodeService = $errorCodeService;
    }

    public string $mode = 'create';

    public ?int $errorCodeId = null;

    public bool $isEditing = false;

    public function mount(string $mode = 'create', ?int $errorCodeId = null): void
    {
        if (! in_array($mode, ['create', 'edit'], true)) {
            abort(404);
        }

        $this->mode = $mode;
        $this->errorCodeId = $errorCodeId;

        if ($this->mode === 'edit' && $this->errorCodeId === null) {
            abort(404);
        }

        if ($this->mode === 'edit') {
            app(ErrorCodeServiceInterface::class)->findOrFail($this->errorCodeId);
        }
    }

    public function enableEdit(): void
    {
        if ($this->mode !== 'edit') {
            return;
        }

        $this->isEditing = true;
    }

    public function cancelEdit(): void
    {
        $this->isEditing = false;
    }

    public function render(): View
    {
        $applications = $this->applicationService->pluckForFilter(ApplicationPluckScope::All);

        $errorCode = null;

        if ($this->mode === 'edit' && $this->errorCodeId !== null) {
            $errorCode = $this->errorCodeService->findOrFail($this->errorCodeId);
        }

        return view('livewire.error-code-form', [
            'applications' => $applications,
            'errorCode' => $errorCode,
            'isEditable' => $this->mode === 'create' || $this->isEditing,
        ]);
    }
}
