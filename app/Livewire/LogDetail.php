<?php

namespace App\Livewire;

use App\Services\Contracts\LogServiceInterface;
use Illuminate\Contracts\View\View;
use Livewire\Component;

class LogDetail extends Component
{
    private LogServiceInterface $logService;

    /**
     * Livewire 4: el contenedor inyecta dependencias en hooks como boot(); no usar el constructor.
     *
     * @see https://livewire.laravel.com/docs/4.x/lifecycle-hooks
     */
    public function boot(LogServiceInterface $logService): void
    {
        $this->logService = $logService;
    }

    public string $source = 'log';

    public int $recordId;

    public ?string $backHref = null;

    public function mount(string $source, int $recordId, ?string $backHref = null): void
    {
        abort_if(! in_array($source, ['log', 'archived_log'], true), 404);

        $this->source = $source;
        $this->recordId = $recordId;
        $this->backHref = $backHref;
    }

    public function render(): View
    {
        $metadataJson = null;
        $archivedLogId = null;
        $archivedDetailUrl = null;
        $log = null;

        if ($this->source === 'log') {
            $log = $this->logService->findOrFail($this->recordId);

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
            'log' => $log,
            'metadataJson' => $metadataJson,
            'archivedLogId' => $archivedLogId,
            'archivedDetailUrl' => $archivedDetailUrl,
        ]);
    }
}
