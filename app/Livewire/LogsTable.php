<?php

namespace App\Livewire;

use App\Services\Contracts\LogServiceInterface;
use Illuminate\Contracts\View\View;
use Livewire\Component;
use Livewire\WithPagination;

class LogsTable extends Component
{
    use WithPagination;

    public string $searchInput = '';
    public ?string $severityInput = null;
    public ?string $archivedInput = null;
    public ?string $resolvedInput = null;

    // filtros aplicados (ya validados)
    public ?string $search = null;
    public ?string $severity = null;
    public ?string $archived = null;
    public ?string $resolved = null;

    public function resetFilters(): void
    {
        $this->searchInput = '';
        $this->severityInput = null;
        $this->archivedInput = null;
        $this->resolvedInput = null;

        $this->search = null;
        $this->severity = null;
        $this->archived = null;
        $this->resolved = null;

        $this->resetPage();
    }

    public function applyFilters(): void
    {
        if ($this->severityInput === '') {
            $this->severityInput = null;
        }

        if ($this->archivedInput === '') {
            $this->archivedInput = null;
        }

        if ($this->resolvedInput === '') {
            $this->resolvedInput = null;
        }

        $validated = $this->validate([
            'searchInput' => ['nullable', 'string', 'max:255'],
            'severityInput' => ['nullable', 'in:critical,high,medium,low,other'],
            'archivedInput' => ['nullable', 'in:archived,not_archived'],
            'resolvedInput' => ['nullable', 'in:resolved,unresolved'],
        ]);

        $this->search = $validated['searchInput'] !== null && $validated['searchInput'] !== ''
            ? trim($validated['searchInput'])
            : null;

        $this->severity = $validated['severityInput'] ?: null;
        $this->archived = $validated['archivedInput'] ?: null;
        $this->resolved = $validated['resolvedInput'] ?: null;

        $this->resetPage();
    }

    public function render(): View
    {
        $logs = app(LogServiceInterface::class)->searchAndFilter(
            $this->search,
            $this->severity,
            $this->archived,
            $this->resolved,
            15
        );

        return view('livewire.logs-table', ['logs' => $logs]);
    }
}
