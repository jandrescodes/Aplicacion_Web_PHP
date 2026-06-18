<?php

namespace App\Http\Controllers;

use App\Middleware\AuthMiddleware;
use App\UseCases\AuditUseCase;

class AuditController extends Controller
{
    private AuditUseCase $auditUseCase;

    public function __construct(AuthMiddleware $authMiddleware, AuditUseCase $auditUseCase)
    {
        parent::__construct($authMiddleware);
        $this->auditUseCase = $auditUseCase;
    }

    public function index(): void
    {
        $this->requireLogin();
        $this->requireAdmin();

        $entries = $this->auditUseCase->listEntries();

        $this->renderWithLayout(
            'audit/index.php',
            array_merge(
                compact('entries'),
                $this->pageHeaderData(
                    'Auditoría',
                    'fas fa-clipboard-list',
                    $this->moduleBreadcrumbs('Auditoría', 'auditoria', 'fas fa-clipboard-list')
                )
            )
        );
    }
}
