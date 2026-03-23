<?php

namespace App\Http\Controllers;

use App\Services\Contracts\LogServiceInterface;
use Illuminate\Contracts\View\View;

class DashboardController extends Controller
{
    public function __construct(private LogServiceInterface $logService) {}

    public function index(): View
    {
        return view('dashboard', [
            'severityCounts' => $this->logService->severityCounts(),
        ]);
    }
}
