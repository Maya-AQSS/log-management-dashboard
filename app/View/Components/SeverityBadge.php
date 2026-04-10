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
        'critical' => 'inline-flex items-center rounded-full bg-danger-light px-2 py-0.5 text-xs font-semibold text-danger-dark',
        'high' => 'inline-flex items-center rounded-full bg-warning-light px-2 py-0.5 text-xs font-semibold text-warning-dark',
        'medium' => 'inline-flex items-center rounded-full bg-warning-light px-2 py-0.5 text-xs font-semibold text-warning-dark opacity-80',
        'low' => 'inline-flex items-center rounded-full bg-success-light px-2 py-0.5 text-xs font-semibold text-success-dark',
        'other' => 'inline-flex items-center rounded-full bg-ui-body px-2 py-0.5 text-xs font-semibold text-text-secondary',
    ];

    /**
     * Shared palette for dashboard cards.
     *
     * @var array<string, array{background:string,text:string,border:string}>
     */
    public const CARD_CLASSES = [
        'critical' => [
            'background' => 'bg-danger-light dark:bg-danger-dark/50',
            'text' => 'text-danger-dark dark:text-white',
            'border' => 'border-danger/20 dark:border-danger/50',
        ],
        'high' => [
            'background' => 'bg-warning-light dark:bg-warning-dark/50',
            'text' => 'text-warning-dark dark:text-white',
            'border' => 'border-warning/20 dark:border-warning/50',
        ],
        'medium' => [
            'background' => 'bg-orange-100 dark:bg-orange-800/50',
            'text' => 'text-orange-800 dark:text-white',
            'border' => 'border-orange-300 dark:border-orange-500/50',
        ],
        'low' => [
            'background' => 'bg-odoo-teal/10 dark:bg-odoo-teal/40',
            'text' => 'text-odoo-teal-d dark:text-white',
            'border' => 'border-odoo-teal/20 dark:border-odoo-teal/50',
        ],
        'other' => [
            'background' => 'bg-ui-card dark:bg-ui-dark-card',
            'text' => 'text-text-primary dark:text-white',
            'border' => 'border-ui-border dark:border-ui-dark-border',
        ],
    ];

    /**
     * Fallback card palette for non-severity cards (all).
     */
    public const CARD_DEFAULT = [
        'background' => 'bg-odoo-purple/10 dark:bg-odoo-purple/40',
        'text' => 'text-odoo-purple-d dark:text-white',
        'border' => 'border-odoo-purple/20 dark:border-odoo-purple/50',
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
