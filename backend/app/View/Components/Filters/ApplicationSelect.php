<?php

namespace App\View\Components\Filters;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Collection;
use Illuminate\View\Component;

class ApplicationSelect extends Component
{
    /**
     * @param  array<int|string,string>|Arrayable<int|string,string>|Collection<int|string,string>  $applications
     */
    public function __construct(
        public ?string $label = null,
        public string $placeholder = '',
        public array|Arrayable|Collection $applications = [],
        public string|int|null $selected = null,
        public bool $hideLabel = false,
    ) {}

    public function render(): View
    {
        return view('components.filters.application-select');
    }
}
