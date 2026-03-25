<?php

namespace App\Livewire;

use App\Enums\Severity;
use App\Filters\SeverityFilter;
use App\Models\Application;
use App\Services\Contracts\LogServiceInterface;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

class LogsTable extends Component
{
    use WithPagination;

    public string $searchInput = '';

    public array $severityInput = [];
    public ?string $resolvedInput = null;
    public ?string $selectedApplicationIdInput = null;

    // filtros aplicados (ya validados)
    #[Url(as: 'search', except: '')]
    public string $search = '';

   
    #[Url(as: 'severity', except: [])]
    public $severity = [];

    #[Url(as: 'resolved', except: null)]
    public ?string $resolved = null;

    #[Url(as: 'application', except: null)]
    public ?int $selectedApplicationId = null;

    public function mount(): void
    {
        $this->severity = SeverityFilter::normalize($this->severity);

        if ($this->resolved !== null && !in_array($this->resolved, ['resolved', 'unresolved'], true)) {
            $this->resolved = null;
        }

        $this->searchInput = $this->search;
        $this->severityInput = $this->severity;
        $this->resolvedInput = $this->resolved;
        $this->selectedApplicationIdInput = $this->selectedApplicationId !== null
            ? (string) $this->selectedApplicationId
            : null;
    }

    public function resetFilters(): void
    {
        $this->searchInput = '';
        $this->severityInput = [];
        $this->resolvedInput = null;
        $this->selectedApplicationIdInput = null;

        $this->search = '';
        $this->severity = [];
        $this->resolved = null;
        $this->selectedApplicationId = null;

        $this->resetPage();
    }

    public function applyFilters(): void
    {
        if ($this->resolvedInput === '') {
            $this->resolvedInput = null;
        }
        if ($this->selectedApplicationIdInput === '') {
            $this->selectedApplicationIdInput = null;
        }

        $validated = $this->validate([
            'searchInput' => ['nullable', 'string', 'max:255'],
            'resolvedInput' => ['nullable', 'in:resolved,unresolved'],
            'selectedApplicationIdInput' => ['nullable', 'integer', 'exists:applications,id'],
        ]);

        $this->search = $validated['searchInput'] !== null && $validated['searchInput'] !== ''
            ? trim($validated['searchInput'])
            : '';

        $this->severity = SeverityFilter::normalize($this->severityInput);
        $this->resolved = $validated['resolvedInput'] ?: null;
        $this->selectedApplicationId = $validated['selectedApplicationIdInput'] !== null
            ? (int) $validated['selectedApplicationIdInput']
            : null;

        $this->resetPage();
    }

    public function render(): View
    {
        $applications = Application::query()
            ->whereHas('logs')
            ->orderBy('name')
            ->pluck('name', 'id');

        $logs = app(LogServiceInterface::class)->searchAndFilter(
            $this->search !== '' ? $this->search : null,
            $this->severity !== [] ? $this->severity : null,
            $this->selectedApplicationId,
            null,
            $this->resolved,
        );

        return view('livewire.logs-table', [
            'logs' => $logs,
            'applications' => $applications,
        ]);
    }
}
