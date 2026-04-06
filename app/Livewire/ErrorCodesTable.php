<?php

namespace App\Livewire;

use App\Enums\ApplicationPluckScope;
use App\Services\Contracts\ApplicationServiceInterface;
use App\Services\Contracts\ErrorCodeServiceInterface;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

class ErrorCodesTable extends Component
{
    use WithPagination;

    private ApplicationServiceInterface $applicationService;

    private ErrorCodeServiceInterface $errorCodeService;

    /**
     * Livewire 4: el contenedor inyecta dependencias en hooks como boot(); no usar el constructor.
     *
     * @see https://livewire.laravel.com/docs/4.x/lifecycle-hooks
     */
    public function boot(
        ApplicationServiceInterface $applicationService,
        ErrorCodeServiceInterface $errorCodeService,
    ): void {
        $this->applicationService = $applicationService;
        $this->errorCodeService = $errorCodeService;
    }

    public string $searchInput = '';

    public ?string $filterAppInput = null;

    #[Url(as: 'search', except: '')]
    public string $search = '';

    #[Url(as: 'filterApp', except: null)]
    public ?int $filterApp = null;

    public function mount(): void
    {
        if ($this->filterApp !== null && $this->filterApp <= 0) {
            $this->filterApp = null;
        }

        $this->searchInput = $this->search;
        $this->filterAppInput = $this->filterApp !== null ? (string) $this->filterApp : null;
    }

    public function resetFilters(): void
    {
        $this->searchInput = '';
        $this->filterAppInput = null;

        $this->search = '';
        $this->filterApp = null;

        $this->resetPage();
    }

    public function applyFilters(): void
    {
        if ($this->filterAppInput === '') {
            $this->filterAppInput = null;
        }

        $validated = $this->validate([
            'searchInput' => ['nullable', 'string', 'max:255'],
            'filterAppInput' => ['nullable', 'integer', 'exists:applications,id'],
        ]);

        $this->search = $validated['searchInput'] !== null && $validated['searchInput'] !== ''
            ? trim($validated['searchInput'])
            : '';

        $this->filterApp = $validated['filterAppInput'] !== null
            ? (int) $validated['filterAppInput']
            : null;

        $this->resetPage();
    }

    public function updatedSearchInput(string $value): void
    {
        $this->search = trim(substr($value, 0, 255));
        $this->searchInput = $this->search;

        $this->resetPage();
    }

    public function render(): View
    {
        $applications = $this->applicationService->pluckForFilter(ApplicationPluckScope::All);

        $errorCodes = $this->errorCodeService->searchAndFilter(
            $this->search !== '' ? $this->search : null,
            $this->filterApp,
            15
        );

        return view('livewire.error-codes-table', [
            'applications' => $applications,
            'errorCodes' => $errorCodes,
        ]);
    }
}
