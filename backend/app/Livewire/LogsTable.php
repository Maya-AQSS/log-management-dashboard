<?php

namespace App\Livewire;

use App\Enums\ApplicationPluckScope;
use App\Filters\DateRangeFilter;
use App\Filters\SeverityFilter;
use App\Http\Requests\DateRangeFilterRequest;
use App\Services\Contracts\ApplicationServiceInterface;
use App\Services\Contracts\LogServiceInterface;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

class LogsTable extends Component
{
    use WithPagination;

    private ApplicationServiceInterface $applicationService;

    private LogServiceInterface $logService;

    /**
     * Livewire 4: el contenedor inyecta dependencias en hooks como boot(); no usar el constructor.
     *
     * @see https://livewire.laravel.com/docs/4.x/lifecycle-hooks
     */
    public function boot(
        ApplicationServiceInterface $applicationService,
        LogServiceInterface $logService,
    ): void {
        $this->applicationService = $applicationService;
        $this->logService = $logService;
    }

    private const SORTABLE_COLUMNS = ['created_at', 'severity', 'application'];

    private const SORT_DIRECTIONS = ['asc', 'desc'];

    public string $searchInput = '';

    public array $severityInput = [];

    public ?string $resolvedInput = null;

    public ?string $dateFromInput = null;

    public ?string $dateToInput = null;

    public ?string $selectedApplicationIdInput = null;

    // filtros aplicados (ya validados)
    #[Url(as: 'search', except: '')]
    public string $search = '';

    #[Url(as: 'severity', except: [])]
    public $severity = [];

    #[Url(as: 'resolved', except: null)]
    public ?string $resolved = null;

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

        if ($this->resolved !== null && ! in_array($this->resolved, ['resolved', 'unresolved'], true)) {
            $this->resolved = null;
        }

        $this->normalizeSortState();

        $this->searchInput = $this->search;
        $this->severityInput = $this->severity;
        $this->resolvedInput = $this->resolved;

        $this->dateFromInput = $this->dateFrom;
        $this->dateToInput = $this->dateTo;

        $this->selectedApplicationIdInput = $this->selectedApplicationId !== null
            ? (string) $this->selectedApplicationId
            : null;
    }

    public function resetFilters(): void
    {
        $this->searchInput = '';
        $this->severityInput = [];
        $this->resolvedInput = null;

        $this->dateFromInput = null;
        $this->dateToInput = null;

        $this->selectedApplicationIdInput = null;

        $this->search = '';
        $this->severity = [];
        $this->resolved = null;

        $this->dateFrom = null;
        $this->dateTo = null;

        $this->selectedApplicationId = null;

        $this->resetPage();
        $this->dispatch('date-range-reset');
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
            ...DateRangeFilterRequest::rulesFor('dateFromInput', 'dateToInput'),
            'selectedApplicationIdInput' => ['nullable', 'integer', 'exists:applications,id'],
        ]);

        $this->search = $validated['searchInput'] !== null && $validated['searchInput'] !== ''
            ? trim($validated['searchInput'])
            : '';

        $this->severity = SeverityFilter::normalize($this->severityInput);
        $this->resolved = $validated['resolvedInput'] ?: null;

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
        if (! in_array($column, self::SORTABLE_COLUMNS, true)) {
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

    public function updatedSearchInput(string $value): void
    {
        $this->search = trim(mb_substr($value, 0, 255));
        $this->resetPage();
    }

    public function render(): View
    {
        $this->normalizeSortState();

        $applications = $this->applicationService->pluckForFilter(ApplicationPluckScope::WithLogs);

        $logs = $this->logService->searchAndFilter(
            $this->search !== '' ? $this->search : null,
            $this->severity !== [] ? $this->severity : null,
            $this->selectedApplicationId,
            null,
            $this->resolved,
            $this->dateFrom,
            $this->dateTo,
            $this->sortBy,
            $this->sortDir,
        );

        return view('livewire.logs-table', [
            'logs' => $logs,
            'applications' => $applications,
        ]);
    }

    private function normalizeSortState(): void
    {
        if (! in_array($this->sortBy, self::SORTABLE_COLUMNS, true)) {
            $this->sortBy = null;
        }

        if (! in_array($this->sortDir, self::SORT_DIRECTIONS, true)) {
            $this->sortDir = 'asc';
        }
    }
}
