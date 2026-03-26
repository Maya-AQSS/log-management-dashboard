<?php

namespace App\Livewire;

use App\Filters\DateRangeFilter;
use App\Filters\SeverityFilter;
use App\Http\Requests\DateRangeFilterRequest;
use App\Models\Application;
use App\Services\Contracts\ArchivedLogServiceInterface;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

class ArchivedLogsTable extends Component
{
    use WithPagination;

    private const SORTABLE_COLUMNS = ['archived_at', 'severity'];

    private const SORT_DIRECTIONS = ['asc', 'desc'];

    public array $severityInput = [];
    public ?string $dateFromInput = null;
    public ?string $dateToInput = null;
    public ?string $selectedApplicationIdInput = null;

    #[Url(as: 'severity', except: [])]
    public $severity = [];

    #[Url(as: 'date_from', except: null)]
    public ?string $dateFrom = null;

    #[Url(as: 'date_to', except: null)]
    public ?string $dateTo = null;

    #[Url(as: 'application', except: null)]
    public ?int $selectedApplicationId = null;

    #[Url(as: 'sort_by', except: null)]
    public ?string $sortBy = null;

    #[Url(as: 'sort_dir', except: 'asc')]
    public string $sortDir = 'asc';

    public function mount(): void
    {
        $this->severity = SeverityFilter::normalize($this->severity);
        [$this->dateFrom, $this->dateTo] = DateRangeFilter::normalize($this->dateFrom, $this->dateTo, 'date_from', 'date_to');

        $this->normalizeSortState();

        $this->severityInput = $this->severity;
        $this->dateFromInput = $this->dateFrom;
        $this->dateToInput = $this->dateTo;
        $this->selectedApplicationIdInput = $this->selectedApplicationId !== null
            ? (string) $this->selectedApplicationId
            : null;
    }

    public function applyFilters(): void
    {
        if ($this->selectedApplicationIdInput === '') {
            $this->selectedApplicationIdInput = null;
        }

        $validated = $this->validate([
            ...DateRangeFilterRequest::rulesFor('dateFromInput', 'dateToInput'),
            'selectedApplicationIdInput' => ['nullable', 'integer', 'exists:applications,id'],
        ]);

        $this->severity = SeverityFilter::normalize($this->severityInput);
        [$this->dateFrom, $this->dateTo] = DateRangeFilter::normalize(
            $this->dateFromInput,
            $this->dateToInput,
            'dateFromInput',
            'dateToInput'
        );

        $this->selectedApplicationId = $validated['selectedApplicationIdInput'] !== null
            ? (int) $validated['selectedApplicationIdInput']
            : null;

        $this->normalizeSortState();

        $this->resetPage();
    }

    public function sortByColumn(string $column): void
    {
        if (!in_array($column, self::SORTABLE_COLUMNS, true)) {
            return;
        }

        if ($this->sortBy === $column) {
            $this->sortDir = $this->sortDir === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortBy = $column;
            $this->sortDir = 'asc';
        }

        $this->resetPage();
    }

    public function resetFilters(): void
    {
        $this->severityInput = [];
        $this->dateFromInput = null;
        $this->dateToInput = null;
        $this->selectedApplicationIdInput = null;

        $this->severity = [];
        $this->dateFrom = null;
        $this->dateTo = null;
        $this->selectedApplicationId = null;

        $this->resetPage();
        $this->dispatch('date-range-reset');
    }

    public function render(): View
    {
        $this->normalizeSortState();

        $applications = Application::query()
            ->whereHas('archivedLogs')
            ->orderBy('name')
            ->pluck('name', 'id');

        $archivedLogs = app(ArchivedLogServiceInterface::class)->searchAndFilter(
            $this->severity !== [] ? $this->severity : null,
            $this->selectedApplicationId,
            $this->dateFrom,
            $this->dateTo,
            $this->sortBy,
            $this->sortDir,
            15
        );

        return view('livewire.archived-logs-table', [
            'archivedLogs' => $archivedLogs,
            'applications' => $applications,
        ]);
    }

    private function normalizeSortState(): void
    {
        if (!in_array($this->sortBy, self::SORTABLE_COLUMNS, true)) {
            $this->sortBy = null;
        }

        if (!in_array($this->sortDir, self::SORT_DIRECTIONS, true)) {
            $this->sortDir = 'asc';
        }
    }
}
