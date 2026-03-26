<?php

namespace App\Livewire;

use App\Models\ArchivedLog;
use App\Models\Log;
use App\Services\Contracts\LogServiceInterface;
use Illuminate\Contracts\View\View;
use Livewire\Component;

class LogDetail extends Component
{
    public string $source = 'log';

    public int $recordId;

    public function mount(string $source = 'log', int $recordId): void
    {
        abort_if(!in_array($source, ['log', 'archived_log'], true), 404);

        $this->source = $source;
        $this->recordId = $recordId;
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
            'log' => $log,
            'archivedLog' => $archivedLog,
            'metadataJson' => $metadataJson,
            'archivedLogId' => $archivedLogId,
            'archivedDetailUrl' => $archivedDetailUrl,
        ]);
    }

}
