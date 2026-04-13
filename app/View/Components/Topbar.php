<?php

namespace App\View\Components;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class Topbar extends Component
{
    public string $pageTitle;

    public function __construct()
    {
        $this->pageTitle = $this->resolvePageTitle();
    }

    private function resolvePageTitle(): string
    {
        $request = request();

        return match (true) {
            $request->routeIs('dashboard*') => __('dashboard.menu'),
            $request->routeIs('logs*') => __('logs.menu'),
            $request->routeIs('archived-logs*') => __('archived_logs.menu'),
            $request->routeIs('error-codes*') => __('error_codes.menu'),
            default => __('app.app_name'),
        };
    }

    public function render(): View|Closure|string
    {
        return view('components.topbar');
    }
}
