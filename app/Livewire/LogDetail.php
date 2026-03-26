<?php

namespace App\Livewire;

use App\Models\ArchivedLog;
use App\Models\Log;
use App\Services\Contracts\ArchivedLogServiceInterface;
use App\Services\Contracts\LogServiceInterface;
use Illuminate\Contracts\View\View;
use Livewire\Component;

class LogDetail extends Component
{
    public string $source = 'log';

    public int $recordId;

    public ?string $backHref = null;

    public string $urlTutorialInput = '';

    public bool $editingUrlTutorial = false;

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
        }
    }

    public function startEditingUrlTutorial(): void
    {
        abort_unless($this->source === 'archived_log', 403);

        $archivedLog = ArchivedLog::query()->findOrFail($this->recordId);
        $this->authorize('update', $archivedLog);

        $this->urlTutorialInput = $archivedLog->url_tutorial ?? '';
        $this->editingUrlTutorial = true;
    }

    public function cancelEditingUrlTutorial(): void
    {
        abort_unless($this->source === 'archived_log', 403);

        $archivedLog = ArchivedLog::query()->findOrFail($this->recordId);
        $this->authorize('update', $archivedLog);

        $this->urlTutorialInput = $archivedLog->url_tutorial ?? '';
        $this->editingUrlTutorial = false;
        $this->resetValidation(['urlTutorialInput']);
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

        $fresh = app(ArchivedLogServiceInterface::class)->updateUrlTutorial($archivedLog, $urlForValidation);
        $this->urlTutorialInput = $fresh->url_tutorial ?? '';
        $this->editingUrlTutorial = false;
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

            $archivedLogId = app(LogServiceInterface::class)->archivedLogIdFor($log->id);
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
