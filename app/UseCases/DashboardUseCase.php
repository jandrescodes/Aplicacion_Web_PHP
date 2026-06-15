<?php

namespace App\UseCases;

use App\Domain\Contracts\EmployeeRepositoryInterface;
use App\Domain\Contracts\PositionRepositoryInterface;
use App\Domain\Contracts\UserRepositoryInterface;

class DashboardUseCase
{
    public function __construct(
        private EmployeeRepositoryInterface $employeeRepo,
        private PositionRepositoryInterface $positionRepo,
        private UserRepositoryInterface $userRepo,
    ) {}

    public function getMetrics(): array
    {
        return [
            'total_empleados'         => $this->employeeRepo->countAll(),
            'total_puestos'           => $this->positionRepo->countAll(),
            'total_usuarios'          => $this->userRepo->countAll(),
            'distribucion_por_puesto' => $this->employeeRepo->countByPosition(),
        ];
    }
}
