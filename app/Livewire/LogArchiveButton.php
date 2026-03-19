<?php

namespace App\Livewire;

use App\Services\Contracts\ArchivedLogServiceInterface;
use App\Services\Contracts\LogServiceInterface;
use Illuminate\Contracts\View\View;
use Throwable;
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

        try {
            $archivedLogService = app(ArchivedLogServiceInterface::class);
            $archivedLog = $archivedLogService->archiveFromLogId(
                $this->logId,
                (int) auth()->id()
            );

            $this->archivedLogId = $archivedLog->id;
            $this->archived = true;

            session()->flash('status', __('logs.archived_success'));
            $this->redirect(route('archived-logs.show', $archivedLog->id));
        } catch (Throwable $e) {
            report($e);
            session()->flash('status', __('logs.archived_error'));
        }
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

