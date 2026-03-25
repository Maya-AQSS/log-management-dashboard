<?php

namespace App\Livewire;

use App\Enums\Severity;
use App\Services\Contracts\LogServiceInterface;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

class LogsTable extends Component
{
    use WithPagination;

    public string $searchInput = '';
    public ?string $severityInput = null;
    public ?string $resolvedInput = null;

    // filtros aplicados (ya validados)
    #[Url(as: 'search', except: '')]
    public string $search = '';

    #[Url(as: 'severity', except: null)]
    public ?string $severity = null;

    #[Url(as: 'resolved', except: null)]
    public ?string $resolved = null;

    public function mount(): void
    {
        if ($this->severity !== null && !in_array($this->severity, Severity::values(), true)) {
            $this->severity = null;
        }

        if ($this->resolved !== null && !in_array($this->resolved, ['resolved', 'unresolved'], true)) {
            $this->resolved = null;
        }

        $this->searchInput = $this->search;
        $this->severityInput = $this->severity;
        $this->resolvedInput = $this->resolved;
    }

    public function resetFilters(): void
    {
        $this->searchInput = '';
        $this->severityInput = null;
        $this->resolvedInput = null;

        $this->search = '';
        $this->severity = null;
        $this->resolved = null;

        $this->resetPage();
    }

    public function applyFilters(): void
    {
        if ($this->severityInput === '') {
            $this->severityInput = null;
        }

        if ($this->resolvedInput === '') {
            $this->resolvedInput = null;
        }

        $validated = $this->validate([
            'searchInput' => ['nullable', 'string', 'max:255'],
            'severityInput' => ['nullable', Severity::validationRule()],
            'resolvedInput' => ['nullable', 'in:resolved,unresolved'],
        ]);

        $this->search = $validated['searchInput'] !== null && $validated['searchInput'] !== ''
            ? trim($validated['searchInput'])
            : '';

        $this->severity = $validated['severityInput'] ?: null;
        $this->resolved = $validated['resolvedInput'] ?: null;

        $this->resetPage();
    }

    public function render(): View
    {
        $logs = app(LogServiceInterface::class)->searchAndFilter(
            $this->search !== '' ? $this->search : null,
            $this->severity,
            null,
            $this->resolved,
        );

        return view('livewire.logs-table', ['logs' => $logs]);
    }

}
