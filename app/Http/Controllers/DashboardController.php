<?php

namespace App\Http\Controllers;

use App\Middleware\AuthMiddleware;
use App\UseCases\DashboardUseCase;

class DashboardController extends Controller
{
    private DashboardUseCase $dashboardUseCase;

    public function __construct(AuthMiddleware $authMiddleware, DashboardUseCase $dashboardUseCase)
    {
        parent::__construct($authMiddleware);
        $this->dashboardUseCase = $dashboardUseCase;
    }

    public function index(): void
    {
        $this->requireLogin();
        $metrics = $this->dashboardUseCase->getMetrics();
        if (empty($_SESSION['is_admin'])) {
            unset($metrics['total_usuarios']);
        }
        $this->renderWithLayout('dashboard/index.php', $metrics);
    }
}
