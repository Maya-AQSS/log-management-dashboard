<?php

namespace App\View\Components;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class SeverityBadge extends Component
{
    public string $label;
    public string $classes;

    public function __construct(?string $severity = null)
    {
        $this->label = $severity ? strtoupper($severity) : '-';

        $this->classes = match ($severity) {
            'critical' => 'inline-flex items-center rounded-full bg-rose-100 px-2 py-0.5 text-xs font-semibold text-rose-800',
            'high' => 'inline-flex items-center rounded-full bg-orange-100 px-2 py-0.5 text-xs font-semibold text-orange-800',
            'medium' => 'inline-flex items-center rounded-full bg-yellow-100 px-2 py-0.5 text-xs font-semibold text-yellow-800',
            'low' => 'inline-flex items-center rounded-full bg-emerald-100 px-2 py-0.5 text-xs font-semibold text-emerald-800',
            'other' => 'inline-flex items-center rounded-full bg-slate-100 px-2 py-0.5 text-xs font-semibold text-slate-700',
            default => 'text-slate-500',
        };
    }

    public function render(): View|Closure|string
    {
        return view('components.severity-badge');
    }
}
