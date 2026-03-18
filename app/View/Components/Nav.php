<?php

namespace App\View\Components;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class Nav extends Component
{
    /**
     * Create a new component instance.
     */
    public function __construct()
    {
        //
    }

    public function linkClasses(string $pattern): string
    {
        $base = 'px-3 py-2 rounded-full text-base font-medium transition-colors';
        $active = 'bg-white/15 text-white';
        $inactive = 'text-white/80 hover:bg-white/10';
        
        return request()->routeIs($pattern) ? "$base $active" : "$base $inactive";
    }

    /**
     * Get the view / contents that represent the component.
     */
    public function render(): View|Closure|string
    {
        return view('components.nav');
    }
}
