<?php

namespace App\Http\Controllers;

use App\Services\Contracts\LogServiceInterface;
use Illuminate\Contracts\View\View;

class DashboardController extends Controller
{
    public function __construct(private LogServiceInterface $logService) {}

    /**
     * Muestra el dashboard con las cards de resumen por severidad de error, incluyendo la card "Todos" (total de logs).
     */
    public function index(): View
    {
        $cards = collect($this->logService->dashboardSeverityCards())
            ->map(fn (array $card): array => [
                ...$card,
                'title' => __('severity.'.$card['key']),
                'href' => route('logs.index', $card['key'] === 'all'
                    ? []
                    : ['severity' => $card['key']]),
            ])
            ->all();

        $applicationTotals = collect($this->logService->dashboardApplicationTotals())
            ->map(fn (array $row): array => [
                ...$row,
                'href' => route('logs.index', ['application' => $row['application_id']]),
            ])
            ->all();

        return view('dashboard', [
            'cards' => $cards,
            'applicationTotals' => $applicationTotals,
            'unresolvedLabel' => __('logs.filters.resolved_unresolved'),
            'resolvedLabel' => __('logs.filters.resolved_resolved'),
        ]);
    }
}
