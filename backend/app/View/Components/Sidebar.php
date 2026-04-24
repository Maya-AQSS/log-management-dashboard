<?php

namespace App\View\Components;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class Sidebar extends Component
{
    public function linkClasses(string $pattern): string
    {
        $base = 'flex items-center gap-3 w-full px-4 py-2.5 rounded-xl text-sm font-medium transition-colors text-left';
        $active = 'bg-ui-sidebar-active dark:bg-odoo-dark-purple text-white';
        $inactive = 'text-white/60 hover:bg-ui-sidebar-hover dark:hover:bg-ui-dark-card hover:text-white/90';

        return request()->routeIs($pattern) ? "$base $active" : "$base $inactive";
    }

    public function render(): View|Closure|string
    {
        return view('components.sidebar');
    }
}
