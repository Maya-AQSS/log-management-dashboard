<?php

namespace App\Livewire;

use App\Models\Log;
use App\Services\Contracts\LogServiceInterface;
use Illuminate\Contracts\View\View;
use Livewire\Component;

class LogDetail extends Component
{
    public int $logId;

    public function mount(int $logId): void
    {
        $this->logId = $logId;
    }

    public function render(): View
    {
        $log = Log::query()
            ->with(['application', 'errorCode'])
            ->findOrFail($this->logId);

        $metadataJson = null;
        if (is_array($log->metadata) && $log->metadata !== []) {
            $metadataJson = json_encode($log->metadata, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        }

        $archivedLogId = app(LogServiceInterface::class)->archivedLogIdFor($log->id);

        return view('livewire.log-detail', [
            'log' => $log,
            'metadataJson' => $metadataJson,
            'archivedLogId' => $archivedLogId,
        ]);
    }
}
