<?php

namespace App\View\Components\Filters;

use App\Enums\Severity;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class SeverityFilterCheckboxes extends Component
{
    /**
     * Wire model property name inside the Livewire component.
     * Example: `severityInput`.
     */
    public function __construct(
        public string $wireModel,
        public ?string $title = null,
        /** @var array<int,string> $selected */
        public array $selected = [],
    ) {}

    public function render(): View
    {
        return view('components.filters.severity-filter-checkboxes', [
            'title' => $this->title,
            'wireModel' => $this->wireModel,
            'selected' => $this->selected,
            'severities' => Severity::values(),
        ]);
    }
}
