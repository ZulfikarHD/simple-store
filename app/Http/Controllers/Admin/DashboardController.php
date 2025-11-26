<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\DashboardService;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    /**
     * Constructor dengan dependency injection DashboardService
     * untuk memisahkan business logic dari controller layer
     *
     * @param  DashboardService  $dashboardService  Service untuk mengolah data dashboard
     */
    public function __construct(public DashboardService $dashboardService) {}

    /**
     * Menampilkan dashboard admin overview
     * dengan statistik dan metrics utama untuk monitoring performa toko, yaitu:
     * - Total orders hari ini
     * - Pending orders yang perlu diproses
     * - Total sales keseluruhan
     * - Recent orders list
     *
     * @return Response Inertia response dengan data dashboard stats
     */
    public function index(): Response
    {
        $stats = $this->dashboardService->getDashboardStats();

        return Inertia::render('Admin/Dashboard', [
            'stats' => $stats,
        ]);
    }
}
