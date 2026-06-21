<?php

namespace App\UseCases;

use App\Domain\Contracts\EventDispatcherInterface;
use App\Domain\Events\EmployeeCreated;
use App\Domain\Events\EmployeeDeleted;
use App\Domain\Events\EmployeeUpdated;
use App\Domain\Models\Employee;
use App\Domain\Models\Position;
use App\Http\Requests\Employees\StoreEmployeeRequest;
use App\Http\Requests\Employees\UpdateEmployeeRequest;
use App\Services\EmployeeService;
use App\UseCases\DTOs\OperationResult;

class EmployeeUseCase
{
    public function __construct(
        private EmployeeService $employeeService,
        private EventDispatcherInterface $dispatcher,
    ) {}


    public function listEmployees(): array
    {
        return array_map(fn(Employee $e) => $e->toArray(), $this->employeeService->listEmployees());
    }

    public function listPositions(): array
    {
        return array_map(fn(Position $p) => $p->toArray(), $this->employeeService->listPositions());
    }

    public function getEmployee(int $id): ?array
    {
        $employee = $this->employeeService->getEmployee($id);
        return $employee?->toArray();
    }

    public function getEmployeeWithPosition(int $id): ?array
    {
        $employee = $this->employeeService->getEmployeeWithPosition($id);
        return $employee?->toArray();
    }

    public function createEmployee(StoreEmployeeRequest $req, array $files, string $baseDirectory, ?int $actorId = null): OperationResult
    {
        $result = $this->employeeService->createEmployee([
            'primernombre'    => $req->primerNombre,
            'segundonombre'   => $req->segundoNombre,
            'primerapellido'  => $req->primerApellido,
            'segundoapellido' => $req->segundoApellido,
            'idpuesto'        => $req->idPuesto,
            'fechadeingreso'  => $req->fechaIngreso,
        ], $files, $baseDirectory);

        $operationResult = new OperationResult(
            (bool)($result['success'] ?? false),
            (string)($result['message'] ?? '')
        );
        if ($operationResult->success) {
            $this->dispatcher->dispatch(new EmployeeCreated($actorId));
        }
        return $operationResult;
    }

    public function updateEmployee(UpdateEmployeeRequest $req, array $files, string $baseDirectory, ?int $actorId = null): OperationResult
    {
        $result = $this->employeeService->updateEmployee($req->id, [
            'primernombre'    => $req->primerNombre,
            'segundonombre'   => $req->segundoNombre,
            'primerapellido'  => $req->primerApellido,
            'segundoapellido' => $req->segundoApellido,
            'idpuesto'        => $req->idPuesto,
            'fechadeingreso'  => $req->fechaIngreso,
        ], $files, $baseDirectory);

        $operationResult = new OperationResult(
            (bool)($result['success'] ?? false),
            (string)($result['message'] ?? '')
        );
        if ($operationResult->success) {
            $this->dispatcher->dispatch(new EmployeeUpdated($actorId, $req->id));
        }
        return $operationResult;
    }

    public function deleteEmployee(int $id, string $baseDirectory, ?int $actorId = null): bool
    {
        $deleted = $this->employeeService->deleteEmployee($id, $baseDirectory);
        if ($deleted) {
            $this->dispatcher->dispatch(new EmployeeDeleted($actorId, $id));
        }
        return $deleted;
    }
}
