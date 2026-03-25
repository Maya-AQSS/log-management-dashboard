<?php

namespace App\Livewire;

use App\Models\Application;
use App\Services\Contracts\ErrorCodeServiceInterface;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

class ErrorCodesTable extends Component
{
    use WithPagination;

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
        $applications = Application::query()
            ->orderBy('name')
            ->pluck('name', 'id');

        $errorCodes = app(ErrorCodeServiceInterface::class)->searchAndFilter(
            $this->search !== '' ? $this->search : null,
            $this->filterApp,
            null,
            15
        );

        return view('livewire.error-codes-table', [
            'applications' => $applications,
            'errorCodes' => $errorCodes,
        ]);
    }
}