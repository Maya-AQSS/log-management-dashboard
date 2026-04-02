<?php

namespace App\View\Components;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class SeverityBadge extends Component
{
    /**
     * Shared palette for severity badges.
     *
     * @var array<string, string>
     */
    public const BADGE_CLASSES = [
        'critical' => 'inline-flex items-center rounded-full bg-rose-100 px-2 py-0.5 text-xs font-semibold text-rose-800',
        'high' => 'inline-flex items-center rounded-full bg-orange-100 px-2 py-0.5 text-xs font-semibold text-orange-800',
        'medium' => 'inline-flex items-center rounded-full bg-yellow-100 px-2 py-0.5 text-xs font-semibold text-yellow-800',
        'low' => 'inline-flex items-center rounded-full bg-emerald-100 px-2 py-0.5 text-xs font-semibold text-emerald-800',
        'other' => 'inline-flex items-center rounded-full bg-slate-100 px-2 py-0.5 text-xs font-semibold text-slate-700',
    ];

    /**
     * Shared palette for dashboard cards.
     *
     * @var array<string, array{background:string,text:string,border:string}>
     */
    public const CARD_CLASSES = [
        'critical' => [
            'background' => 'bg-rose-100 dark:bg-rose-700/35',
            'text' => 'text-rose-900 dark:text-rose-100',
            'border' => 'border-rose-200 dark:border-rose-500/40',
        ],
        'high' => [
            'background' => 'bg-orange-100 dark:bg-orange-700/35',
            'text' => 'text-orange-900 dark:text-orange-100',
            'border' => 'border-orange-200 dark:border-orange-500/40',
        ],
        'medium' => [
            'background' => 'bg-yellow-100 dark:bg-yellow-600/35',
            'text' => 'text-yellow-900 dark:text-yellow-100',
            'border' => 'border-yellow-200 dark:border-yellow-400/40',
        ],
        'low' => [
            'background' => 'bg-emerald-100 dark:bg-emerald-700/35',
            'text' => 'text-emerald-900 dark:text-emerald-100',
            'border' => 'border-emerald-200 dark:border-emerald-500/40',
        ],
        'other' => [
            'background' => 'bg-slate-100 dark:bg-slate-700/45',
            'text' => 'text-slate-800 dark:text-slate-100',
            'border' => 'border-slate-200 dark:border-slate-500/40',
        ],
    ];

    /**
     * Fallback card palette for non-severity cards (all).
     */
    public const CARD_DEFAULT = [
        'background' => 'bg-[#ede7ef] dark:bg-[#5b3853]/45',
        'text' => 'text-[#5b3853] dark:text-[#f7eaf2]',
        'border' => 'border-[#d9c8df] dark:border-[#8f6f87]/50',
    ];

    public string $label;

    public string $classes;

    public function __construct(?string $severity = null)
    {
        $this->label = $severity ? strtoupper($severity) : '-';
        $this->classes = self::BADGE_CLASSES[$severity] ?? 'text-slate-500';
    }

    /**
     * @return array{background:string,text:string,border:string}
     */
    public static function dashboardPaletteFor(string $severity): array
    {
        return self::CARD_CLASSES[$severity] ?? self::CARD_DEFAULT;
    }

    public function render(): View|Closure|string
    {
        return view('components.severity-badge');
    }
}
