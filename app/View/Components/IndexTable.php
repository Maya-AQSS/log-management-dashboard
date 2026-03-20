<?php

namespace App\View\Components;

use Closure;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Collection;
use Illuminate\View\Component;

class IndexTable extends Component
{
    /** @var array<int,string> */
    public array $headers;

    public string $emptyText;

    public bool $hasItems;

    public LengthAwarePaginator|Collection|null $paginator;

    /**
     * @param  array<int,string>  $headers
     */
    public function __construct(
        array $headers = [],
        string $emptyText = '',
        bool $hasItems = false,
        LengthAwarePaginator|Collection|null $paginator = null
    ) {
        $this->headers = $headers;
        $this->emptyText = $emptyText;
        $this->hasItems = $hasItems;
        $this->paginator = $paginator;
    }

    public function render(): View|Closure|string
    {
        return view('components.index-table');
    }
}
