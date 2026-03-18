<?php

namespace App\Livewire;

use App\Models\Log;
use App\Services\Contracts\ArchivedLogServiceInterface;
use App\Services\Contracts\LogServiceInterface;
use Illuminate\Contracts\View\View;
use Livewire\Component;

class LogArchiveButton extends Component
{
    public int $logId;

    public bool $archived = false;

    public ?int $archivedLogId = null;

    public function mount(int $logId): void
    {
        $this->logId = $logId;
        $this->refreshStatus();
    }

    public function archive(): void
    {
        $this->validate([
            'logId' => ['required', 'integer', 'exists:logs,id'],
        ]);

        if ($this->archived) return;

        $archivedLogService = app(ArchivedLogServiceInterface::class);
        $archivedLog = $archivedLogService->archiveFromLogId(
            $this->logId,
            (int) auth()->id()
        );

        $this->archivedLogId = $archivedLog->id;
        $this->archived = true;

        $this->redirect(route('archived-logs.show', $archivedLog->id));
    }

    private function refreshStatus(): void
    {
        $matchedId = app(LogServiceInterface::class)->archivedLogIdFor($this->logId);

        $this->archivedLogId = $matchedId !== null ? (int) $matchedId : null;
        $this->archived = $this->archivedLogId !== null;
    }

    public function render(): View
    {
        return view('livewire.log-archive-button');
    }
}

