<?php

namespace App\Livewire;

use App\Models\ArchivedLog;
use App\Models\Log;
use App\Services\Contracts\ArchivedLogServiceInterface;
use App\Services\Contracts\LogServiceInterface;
use Illuminate\Contracts\View\View;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Livewire\Component;

class LogDetail extends Component
{
    use AuthorizesRequests;

    private ArchivedLogServiceInterface $archivedLogService;

    private LogServiceInterface $logService;

    /**
     * Livewire 4: el contenedor inyecta dependencias en hooks como boot(); no usar el constructor.
     *
     * @see https://livewire.laravel.com/docs/4.x/lifecycle-hooks
     */
    public function boot(
        ArchivedLogServiceInterface $archivedLogService,
        LogServiceInterface $logService,
    ): void {
        $this->archivedLogService = $archivedLogService;
        $this->logService = $logService;
    }

    public string $source = 'log';

    public int $recordId;

    public ?string $backHref = null;

    public string $urlTutorialInput = '';

    public bool $editingUrlTutorial = false;

    public string $descriptionInput = '';

    /** closed | editing */
    public string $descriptionPanelMode = 'closed';

    public function mount(string $source, int $recordId, ?string $backHref = null): void
    {
        abort_if(! in_array($source, ['log', 'archived_log'], true), 404);

        $this->source = $source;
        $this->recordId = $recordId;
        $this->backHref = $backHref;

        if ($source === 'archived_log') {
            $archivedLog = ArchivedLog::query()->findOrFail($recordId);
            $this->urlTutorialInput = $archivedLog->url_tutorial ?? '';
            $this->editingUrlTutorial = false;
            $this->descriptionInput = $archivedLog->description ?? '';
            $this->descriptionPanelMode = 'closed';
        }
    }

    /**
     * Abre edición de descripción y URL a la vez (solo quien archivó: policy update).
     */
    public function startEditingArchivedFields(): void
    {
        abort_unless($this->source === 'archived_log', 403);

        $archivedLog = ArchivedLog::query()->findOrFail($this->recordId);
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
        abort_unless($this->source === 'archived_log', 403);

        $archivedLog = ArchivedLog::query()->findOrFail($this->recordId);
        $this->authorize('update', $archivedLog);

        $this->descriptionInput = $archivedLog->description ?? '';
        $this->urlTutorialInput = $archivedLog->url_tutorial ?? '';
        $this->descriptionPanelMode = 'closed';
        $this->editingUrlTutorial = false;
        $this->resetValidation(['descriptionInput', 'urlTutorialInput']);
    }

    public function saveArchivedDetailChanges(): void
    {
        abort_unless($this->source === 'archived_log', 403);

        $archivedLog = ArchivedLog::query()->findOrFail($this->recordId);
        $this->authorize('update', $archivedLog);

        $this->validate(
            [
                'descriptionInput' => ['nullable', 'string', 'max:5000'],
                'urlTutorialInput' => [
                    'nullable',
                    'string',
                    'max:500',
                    function (string $attribute, mixed $value, \Closure $fail): void {
                        $v = trim((string) $value);
                        if ($v === '') {
                            return;
                        }
                        if (! $this->isAcceptableTutorialUrl($v)) {
                            $fail(__('archived_logs.validation.url_tutorial'));
                        }
                    },
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

        $urlForValidation = trim($this->urlTutorialInput);
        $urlForValidation = $urlForValidation === '' ? null : $urlForValidation;

        $archivedLog = $this->archivedLogService->updateDescription($archivedLog, $text);
        $archivedLog = $this->archivedLogService->updateUrlTutorial($archivedLog, $urlForValidation);

        $this->descriptionInput = $archivedLog->description ?? '';
        $this->urlTutorialInput = $archivedLog->url_tutorial ?? '';
        $this->descriptionPanelMode = 'closed';
        $this->editingUrlTutorial = false;
        $this->resetValidation(['descriptionInput', 'urlTutorialInput']);
    }

    public function updateDescription(): void
    {
        abort_unless($this->source === 'archived_log', 403);

        $archivedLog = ArchivedLog::query()->findOrFail($this->recordId);
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
        abort_unless($this->source === 'archived_log', 403);

        $archivedLog = ArchivedLog::query()->findOrFail($this->recordId);
        $this->authorize('update', $archivedLog);

        $this->validate(
            [
                'urlTutorialInput' => [
                    'nullable',
                    'string',
                    'max:500',
                    function (string $attribute, mixed $value, \Closure $fail): void {
                        $v = trim((string) $value);
                        if ($v === '') {
                            return;
                        }
                        if (! $this->isAcceptableTutorialUrl($v)) {
                            $fail(__('archived_logs.validation.url_tutorial'));
                        }
                    },
                ],
            ],
            [
                'urlTutorialInput.max' => __('validation.max.string', ['attribute' => __('logs.table.url_tutorial'), 'max' => 500]),
            ]
        );

        $urlForValidation = trim($this->urlTutorialInput);
        $urlForValidation = $urlForValidation === '' ? null : $urlForValidation;

        $fresh = $this->archivedLogService->updateUrlTutorial($archivedLog, $urlForValidation);
        $this->urlTutorialInput = $fresh->url_tutorial ?? '';
        $this->editingUrlTutorial = false;
        $this->descriptionPanelMode = 'closed';
    }

    public function render(): View
    {
        $metadataJson = null;
        $archivedLogId = null;
        $archivedDetailUrl = null;
        $archivedLog = null;
        $log = null;
        if ($this->source === 'archived_log') {
            $archivedLog = ArchivedLog::query()
                ->with(['application', 'errorCode', 'archivedBy'])
                ->findOrFail($this->recordId);

            if (is_array($archivedLog->metadata) && $archivedLog->metadata !== []) {
                $metadataJson = json_encode(
                    $archivedLog->metadata,
                    JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES
                );
            }
        } else {
            $log = Log::query()
                ->with(['application', 'errorCode'])
                ->findOrFail($this->recordId);

            if (is_array($log->metadata) && $log->metadata !== []) {
                $metadataJson = json_encode(
                    $log->metadata,
                    JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES
                );
            }

            $archivedLogId = $this->logService->archivedLogIdFor($log->id);
            if ($archivedLogId !== null) {
                $archivedDetailUrl = route('archived-logs.show', $archivedLogId);
            }
        }

        return view('livewire.log-detail', [
            'source' => $this->source,
            'backHref' => $this->backHref,
            'log' => $log,
            'archivedLog' => $archivedLog,
            'metadataJson' => $metadataJson,
            'archivedLogId' => $archivedLogId,
            'archivedDetailUrl' => $archivedDetailUrl,
            'descriptionPlaceholder' => $this->source === 'archived_log'
                ? __('archived_logs.description.placeholder')
                : '',
            'urlTutorialPlaceholder' => $this->source === 'archived_log'
                ? __('archived_logs.url_tutorial.placeholder')
                : '',
        ]);
    }

    /**
     * filter_var acepta hosts de una sola etiqueta (p. ej. https://example).
     * Para documentación externa exigimos http(s) con host "usable": dominio con punto,
     * localhost, IPv4 o IPv6 entre corchetes.
     */
    private function isAcceptableTutorialUrl(string $url): bool
    {
        if (filter_var($url, FILTER_VALIDATE_URL) === false) {
            return false;
        }

        $parts = parse_url($url);
        $scheme = strtolower((string) ($parts['scheme'] ?? ''));
        if (! in_array($scheme, ['http', 'https'], true)) {
            return false;
        }

        $host = (string) ($parts['host'] ?? '');
        if ($host === '') {
            return false;
        }

        $hostLower = strtolower($host);
        if ($hostLower === 'localhost') {
            return true;
        }

        if (preg_match('/^\d{1,3}(\.\d{1,3}){3}$/', $host) === 1) {
            return true;
        }

        if (str_starts_with($host, '[') && str_contains($host, ':')) {
            return true;
        }

        return str_contains($host, '.');
    }
}
