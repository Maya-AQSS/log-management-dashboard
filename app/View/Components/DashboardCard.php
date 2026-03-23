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
        $palette = SeverityBadge::dashboardPaletteFor($this->severityKey);
        $this->backgroundClass = $palette['background'];
        $this->accentTextClass = $palette['text'];
        $this->borderClass = $palette['border'];
    }

    public function render(): View|Closure|string
    {
        return view('components.dashboard-card');
    }
}
