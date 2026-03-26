<?php

namespace App\View\Components;

use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class DateRangeFilter extends Component
{
    public function __construct(
        public string $wireModelFrom,
        public string $wireModelTo,
        public ?string $label = null,
        public ?string $fromLabel = null,
        public ?string $toLabel = null,
    ) {}

    public function render(): View
    {
        return view('components.date-range-filter');
    }
}

