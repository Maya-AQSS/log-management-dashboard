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

    // filtros aplicados (ya validados)
    public ?string $search = null;
    public ?string $severity = null;

    public function resetFilters(): void
    {
        $this->searchInput = '';
        $this->severityInput = null;

        $this->search = null;
        $this->severity = null;

        $this->resetPage();
    }

    public function applyFilters(): void
    {
        if ($this->severityInput === '') {
            $this->severityInput = null;
        }

        $validated = $this->validate([
            'searchInput' => ['nullable', 'string', 'max:255'],
            'severityInput' => ['nullable', 'in:critical,high,medium,low,other'],
        ]);

        $this->search = $validated['searchInput'] !== null && $validated['searchInput'] !== ''
            ? trim($validated['searchInput'])
            : null;

        $this->severity = $validated['severityInput'] ?: null;

        $this->resetPage();
    }

    public function render(): View
    {
        $logs = app(LogServiceInterface::class)->searchAndFilter(
            $this->search,
            $this->severity,
            15
        );

        return view('livewire.logs-table', ['logs' => $logs]);
    }
}
