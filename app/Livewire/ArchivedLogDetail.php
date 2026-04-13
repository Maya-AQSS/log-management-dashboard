<?php

namespace App\Livewire;

use App\Models\ArchivedLog;
use App\Models\ErrorCode;
use App\Rules\AcceptableTutorialUrl;
use App\Services\Contracts\ArchivedLogServiceInterface;
use Illuminate\Contracts\View\View;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Livewire\Attributes\Computed;
use Livewire\Component;

class ArchivedLogDetail extends Component
{
    use AuthorizesRequests;

    private ArchivedLogServiceInterface $archivedLogService;

    /**
     * Livewire 4: el contenedor inyecta dependencias en hooks como boot(); no usar el constructor.
     *
     * @see https://livewire.laravel.com/docs/4.x/lifecycle-hooks
     */
    public function boot(ArchivedLogServiceInterface $archivedLogService): void
    {
        $this->archivedLogService = $archivedLogService;
    }

    public int $archivedLogId;

    public ?string $backHref = null;

    public bool $isEditing = false;

    // Campos editables ligados a la vista
    public bool $resolved = false;

    public ?int $errorCodeId = null;

    public string $internalNotes = '';

    public string $descriptionInput = '';

    public string $urlTutorialInput = '';

    public function mount(int $archivedLogId, ?string $backHref = null): void
    {
        $this->archivedLogId = $archivedLogId;
        $this->backHref = $backHref;
        $this->syncFromModel($this->archivedLog);
    }

    #[Computed]
    public function archivedLog(): ArchivedLog
    {
        return $this->archivedLogService->findOrFail($this->archivedLogId);
    }

    /**
     * @return array<int|string, string>
     */
    #[Computed]
    public function errorCodes(): array
    {
        if (! $this->isEditing) {
            return [];
        }

        return ErrorCode::query()->orderBy('code')->pluck('code', 'id')->all();
    }

    public function enableEdit(): void
    {
        $this->authorize('update', $this->archivedLog);
        $this->syncFromModel($this->archivedLog);
        $this->isEditing = true;
    }

    public function cancelEdit(): void
    {
        $this->syncFromModel($this->archivedLog);
        $this->isEditing = false;
        $this->resetValidation();
    }

    public function save(): void
    {
        $archivedLog = $this->archivedLog;
        $this->authorize('update', $archivedLog);

        $this->validate([
            'descriptionInput' => ['nullable', 'string', 'max:5000'],
            'urlTutorialInput' => ['nullable', 'string', 'max:500', new AcceptableTutorialUrl],
            'internalNotes' => ['nullable', 'string', 'max:5000'],
            'errorCodeId' => ['nullable', 'integer', 'exists:error_codes,id'],
        ]);

        $this->archivedLogService->updateArchivedFields($archivedLog, [
            'resolved' => $this->resolved,
            'error_code_id' => $this->errorCodeId,
            'internal_notes' => blank($this->internalNotes) ? null : trim($this->internalNotes),
            'description' => blank($this->descriptionInput) ? null : trim($this->descriptionInput),
            'url_tutorial' => blank($this->urlTutorialInput) ? null : trim($this->urlTutorialInput),
        ]);

        // Invalida la cache del computed para que render() obtenga datos frescos
        unset($this->archivedLog);

        $this->isEditing = false;
        $this->resetValidation();
    }

    private function syncFromModel(ArchivedLog $archivedLog): void
    {
        $this->resolved = (bool) $archivedLog->resolved;
        $this->errorCodeId = $archivedLog->error_code_id;
        $this->internalNotes = $archivedLog->internal_notes ?? '';
        $this->descriptionInput = $archivedLog->description ?? '';
        $this->urlTutorialInput = $archivedLog->url_tutorial ?? '';
    }

    public function render(): View
    {
        $archivedLog = $this->archivedLog;

        $metadataJson = null;
        if (is_array($archivedLog->metadata) && $archivedLog->metadata !== []) {
            $metadataJson = json_encode(
                $archivedLog->metadata,
                JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES
            );
        }

        return view('livewire.archived-log-detail', [
            'archivedLog' => $archivedLog,
            'metadataJson' => $metadataJson,
            'isEditable' => $this->isEditing,
            'errorCodes' => $this->errorCodes,
        ]);
    }
}
