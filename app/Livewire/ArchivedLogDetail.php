<?php

namespace App\Livewire;

use App\Rules\AcceptableTutorialUrl;
use App\Services\Contracts\ArchivedLogServiceInterface;
use Illuminate\Contracts\View\View;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
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

    public string $urlTutorialInput = '';

    public bool $editingUrlTutorial = false;

    public string $descriptionInput = '';

    /** closed | editing */
    public string $descriptionPanelMode = 'closed';

    public function mount(int $archivedLogId, ?string $backHref = null): void
    {
        $archivedLog = $this->archivedLogService->findOrFail($archivedLogId);

        $this->archivedLogId = $archivedLogId;
        $this->backHref = $backHref;
        $this->urlTutorialInput = $archivedLog->url_tutorial ?? '';
        $this->descriptionInput = $archivedLog->description ?? '';
    }

    /**
     * Abre edición de descripción y URL a la vez (solo quien archivó: policy update).
     */
    public function startEditingArchivedFields(): void
    {
        $archivedLog = $this->archivedLogService->findOrFail($this->archivedLogId);
        $this->authorize('update', $archivedLog);

        $this->descriptionInput = $archivedLog->description ?? '';
        $this->urlTutorialInput = $archivedLog->url_tutorial ?? '';
        $this->descriptionPanelMode = 'editing';
        $this->editingUrlTutorial = true;
    }

    public function startEditingDescription(): void
    {
        $this->startEditingArchivedFields();
    }

    public function cancelEditingDescription(): void
    {
        $this->cancelEditingArchivedFields();
    }

    public function cancelEditingArchivedFields(): void
    {
        $archivedLog = $this->archivedLogService->findOrFail($this->archivedLogId);
        $this->authorize('update', $archivedLog);

        $this->descriptionInput = $archivedLog->description ?? '';
        $this->urlTutorialInput = $archivedLog->url_tutorial ?? '';
        $this->descriptionPanelMode = 'closed';
        $this->editingUrlTutorial = false;
        $this->resetValidation(['descriptionInput', 'urlTutorialInput']);
    }

    public function saveArchivedDetailChanges(): void
    {
        $archivedLog = $this->archivedLogService->findOrFail($this->archivedLogId);
        $this->authorize('update', $archivedLog);

        $this->validate(
            [
                'descriptionInput' => ['nullable', 'string', 'max:5000'],
                'urlTutorialInput' => [
                    'nullable',
                    'string',
                    'max:500',
                    new AcceptableTutorialUrl(),
                ],
            ],
            [
                'descriptionInput.max' => __('validation.max.string', [
                    'attribute' => __('archived_logs.description.field_label'),
                    'max' => 5000,
                ]),
                'urlTutorialInput.max' => __('validation.max.string', ['attribute' => __('logs.table.url_tutorial'), 'max' => 500]),
            ]
        );

        $text = trim($this->descriptionInput);
        $text = $text === '' ? null : $text;

        $url = trim($this->urlTutorialInput);
        $url = $url === '' ? null : $url;

        $archivedLog = $this->archivedLogService->updateDescription($archivedLog, $text);
        $archivedLog = $this->archivedLogService->updateUrlTutorial($archivedLog, $url);

        $this->descriptionInput = $archivedLog->description ?? '';
        $this->urlTutorialInput = $archivedLog->url_tutorial ?? '';
        $this->descriptionPanelMode = 'closed';
        $this->editingUrlTutorial = false;
        $this->resetValidation(['descriptionInput', 'urlTutorialInput']);
    }

    public function updateDescription(): void
    {
        $archivedLog = $this->archivedLogService->findOrFail($this->archivedLogId);
        $this->authorize('update', $archivedLog);

        $this->validate(
            [
                'descriptionInput' => ['nullable', 'string', 'max:5000'],
            ],
            [
                'descriptionInput.max' => __('validation.max.string', [
                    'attribute' => __('archived_logs.description.field_label'),
                    'max' => 5000,
                ]),
            ]
        );

        $text = trim($this->descriptionInput);
        $text = $text === '' ? null : $text;

        $fresh = $this->archivedLogService->updateDescription($archivedLog, $text);
        $this->descriptionInput = $fresh->description ?? '';
        $this->descriptionPanelMode = 'closed';
        $this->editingUrlTutorial = false;
    }

    public function startEditingUrlTutorial(): void
    {
        $this->startEditingArchivedFields();
    }

    public function cancelEditingUrlTutorial(): void
    {
        $this->cancelEditingArchivedFields();
    }

    public function updateUrlTutorial(): void
    {
        $archivedLog = $this->archivedLogService->findOrFail($this->archivedLogId);
        $this->authorize('update', $archivedLog);

        $this->validate(
            [
                'urlTutorialInput' => [
                    'nullable',
                    'string',
                    'max:500',
                    new AcceptableTutorialUrl(),
                ],
            ],
            [
                'urlTutorialInput.max' => __('validation.max.string', ['attribute' => __('logs.table.url_tutorial'), 'max' => 500]),
            ]
        );

        $url = trim($this->urlTutorialInput);
        $url = $url === '' ? null : $url;

        $fresh = $this->archivedLogService->updateUrlTutorial($archivedLog, $url);
        $this->urlTutorialInput = $fresh->url_tutorial ?? '';
        $this->editingUrlTutorial = false;
        $this->descriptionPanelMode = 'closed';
    }

    public function render(): View
    {
        $archivedLog = $this->archivedLogService->findOrFail($this->archivedLogId);

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
            'descriptionPlaceholder' => __('archived_logs.description.placeholder'),
            'urlTutorialPlaceholder' => __('archived_logs.url_tutorial.placeholder'),
            'isEditable' => $this->descriptionPanelMode === 'editing',
        ]);
    }
}
