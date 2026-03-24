<?php

namespace App\View\Components;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class DashboardCard extends Component
{
    public string $backgroundClass;
    public string $accentTextClass;
    public string $borderClass;

    public function __construct(
        public string $title,
        public string $href,
        public int $unresolvedCount,
        public int $resolvedCount,
        public string $unresolvedLabel,
        public string $resolvedLabel,
        public string $severityKey = 'all'
    ) {
        [$this->backgroundClass, $this->accentTextClass, $this->borderClass] = match ($this->severityKey) {
            'critical' => ['bg-rose-100 dark:bg-rose-700/35', 'text-rose-900 dark:text-rose-100', 'border-rose-200 dark:border-rose-500/40'],
            'high' => ['bg-orange-100 dark:bg-orange-700/35', 'text-orange-900 dark:text-orange-100', 'border-orange-200 dark:border-orange-500/40'],
            'medium' => ['bg-yellow-100 dark:bg-yellow-600/35', 'text-yellow-900 dark:text-yellow-100', 'border-yellow-200 dark:border-yellow-400/40'],
            'low' => ['bg-emerald-100 dark:bg-emerald-700/35', 'text-emerald-900 dark:text-emerald-100', 'border-emerald-200 dark:border-emerald-500/40'],
            'other' => ['bg-slate-100 dark:bg-slate-700/45', 'text-slate-800 dark:text-slate-100', 'border-slate-200 dark:border-slate-500/40'],
            default => ['bg-[#ede7ef] dark:bg-[#5b3853]/45', 'text-[#5b3853] dark:text-[#f7eaf2]', 'border-[#d9c8df] dark:border-[#8f6f87]/50'],
        };
    }

    public function render(): View|Closure|string
    {
        return view('components.dashboard-card');
    }
}
