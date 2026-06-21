<?php

namespace Tests\Unit\UseCases;

use App\Domain\Contracts\EventDispatcherInterface;
use App\Domain\Events\EmployeeCreated;
use App\Domain\Events\EmployeeDeleted;
use App\Domain\Events\EmployeeUpdated;
use App\Domain\Models\Employee;
use App\Domain\Models\Position;
use App\Http\Requests\Employees\StoreEmployeeRequest;
use App\Http\Requests\Employees\UpdateEmployeeRequest;
use App\Services\EmployeeService;
use App\UseCases\EmployeeUseCase;
use App\UseCases\DTOs\OperationResult;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class EmployeeUseCaseTest extends TestCase
{
    private EmployeeService&MockObject $service;
    private EventDispatcherInterface&MockObject $dispatcher;
    private EmployeeUseCase $useCase;

    protected function setUp(): void
    {
        $this->service    = $this->createMock(EmployeeService::class);
        $this->dispatcher = $this->createMock(EventDispatcherInterface::class);
        $this->useCase    = new EmployeeUseCase($this->service, $this->dispatcher);
    }

    private function makeEmployee(array $overrides = []): Employee
    {
        return Employee::fromRow(array_merge([
            'ID' => '1', 'Primernombre' => 'Juan', 'Segundonombre' => '',
            'Primerapellido' => 'Pérez', 'Segundoapellido' => 'López',
            'Foto' => 'foto.jpg', 'CV' => 'cv.pdf',
            'Idpuesto' => '3', 'Fecha' => '2024-01-15', 'puesto' => 'Gerente',
        ], $overrides));
    }

    private function makePosition(int $id = 1, string $nombre = 'Gerente'): Position
    {
        return Position::fromRow(['ID' => $id, 'Nombredelpuesto' => $nombre]);
    }

    // --- listEmployees ---

    public function test_listEmployees_maps_models_to_arrays(): void
    {
        $this->service->method('listEmployees')->willReturn([$this->makeEmployee()]);

        $result = $this->useCase->listEmployees();

        $this->assertIsArray($result);
        $this->assertCount(1, $result);
        $this->assertSame(1, $result[0]['ID']);
        $this->assertSame('Juan', $result[0]['Primernombre']);
    }

    public function test_listEmployees_returns_empty_array_when_no_employees(): void
    {
        $this->service->method('listEmployees')->willReturn([]);

        $this->assertSame([], $this->useCase->listEmployees());
    }

    // --- listPositions ---

    public function test_listPositions_maps_models_to_arrays(): void
    {
        $this->service->method('listPositions')->willReturn([$this->makePosition(3, 'Contador')]);

        $result = $this->useCase->listPositions();

        $this->assertCount(1, $result);
        $this->assertSame(3, $result[0]['ID']);
        $this->assertSame('Contador', $result[0]['Nombredelpuesto']);
    }

    // --- getEmployee ---

    public function test_getEmployee_returns_array_when_found(): void
    {
        $this->service->method('getEmployee')->with(1)->willReturn($this->makeEmployee());

        $result = $this->useCase->getEmployee(1);

        $this->assertIsArray($result);
        $this->assertSame(1, $result['ID']);
    }

    public function test_getEmployee_returns_null_when_not_found(): void
    {
        $this->service->method('getEmployee')->willReturn(null);

        $this->assertNull($this->useCase->getEmployee(99));
    }

    // --- createEmployee ---

    public function test_createEmployee_passes_request_fields_to_service(): void
    {
        $req = StoreEmployeeRequest::fromArray([
            'primernombre' => 'Juan', 'segundonombre' => '',
            'primerapellido' => 'Pérez', 'segundoapellido' => 'López',
            'idpuesto' => '3', 'fechadeingreso' => '2024-01-15',
        ]);

        $this->service->expects($this->once())
            ->method('createEmployee')
            ->with(
                $this->callback(fn($data) =>
                    $data['primernombre'] === 'Juan' &&
                    $data['idpuesto'] === 3 &&
                    $data['fechadeingreso'] === '2024-01-15'
                ),
                [],
                '/tmp'
            )
            ->willReturn(['success' => true, 'message' => 'Empleado creado exitosamente.']);

        $result = $this->useCase->createEmployee($req, [], '/tmp');

        $this->assertInstanceOf(OperationResult::class, $result);
        $this->assertTrue($result->success);
    }

    public function test_createEmployee_wraps_service_success_in_OperationResult(): void
    {
        $req = StoreEmployeeRequest::fromArray([
            'primernombre' => 'Ana', 'primerapellido' => 'Gómez', 'segundoapellido' => 'Ruiz',
            'idpuesto' => '2', 'fechadeingreso' => '2024-05-01',
        ]);
        $this->service->method('createEmployee')
            ->willReturn(['success' => true, 'message' => 'Empleado creado exitosamente.']);

        $result = $this->useCase->createEmployee($req, [], '/tmp');

        $this->assertTrue($result->success);
        $this->assertSame('Empleado creado exitosamente.', $result->message);
    }

    public function test_createEmployee_wraps_service_failure_in_OperationResult(): void
    {
        $req = StoreEmployeeRequest::fromArray([
            'primernombre' => 'Ana', 'primerapellido' => 'Gómez', 'segundoapellido' => 'Ruiz',
            'idpuesto' => '2', 'fechadeingreso' => '2024-05-01',
        ]);
        $this->service->method('createEmployee')
            ->willReturn(['success' => false, 'message' => 'No se pudo crear el empleado.']);

        $result = $this->useCase->createEmployee($req, [], '/tmp');

        $this->assertFalse($result->success);
        $this->assertSame('No se pudo crear el empleado.', $result->message);
    }

    // --- updateEmployee ---

    public function test_updateEmployee_includes_id_when_calling_service(): void
    {
        $req = UpdateEmployeeRequest::fromArray([
            'txtID' => '5', 'primernombre' => 'Juan', 'primerapellido' => 'Pérez',
            'segundoapellido' => 'López', 'idpuesto' => '3', 'fechadeingreso' => '2024-01-15',
        ]);

        $this->service->expects($this->once())
            ->method('updateEmployee')
            ->with(5, $this->isType('array'), [], '/tmp')
            ->willReturn(['success' => true, 'message' => 'Empleado actualizado exitosamente.']);

        $result = $this->useCase->updateEmployee($req, [], '/tmp');

        $this->assertTrue($result->success);
    }

    public function test_updateEmployee_wraps_service_failure_in_OperationResult(): void
    {
        $req = UpdateEmployeeRequest::fromArray([
            'txtID' => '5', 'primernombre' => 'Juan', 'primerapellido' => 'Pérez',
            'segundoapellido' => 'López', 'idpuesto' => '3', 'fechadeingreso' => '2024-01-15',
        ]);
        $this->service->method('updateEmployee')
            ->willReturn(['success' => false, 'message' => 'No se pudo actualizar el empleado.']);

        $result = $this->useCase->updateEmployee($req, [], '/tmp');

        $this->assertFalse($result->success);
    }

    // --- deleteEmployee ---

    public function test_deleteEmployee_delegates_to_service_and_returns_bool(): void
    {
        $this->service->expects($this->once())
            ->method('deleteEmployee')
            ->with(3, '/tmp')
            ->willReturn(true);

        $this->assertTrue($this->useCase->deleteEmployee(3, '/tmp'));
    }

    public function test_deleteEmployee_returns_false_when_service_returns_false(): void
    {
        $this->service->method('deleteEmployee')->willReturn(false);

        $this->assertFalse($this->useCase->deleteEmployee(0, '/tmp'));
    }

    // --- eventos ---

    public function test_createEmployee_dispatches_EmployeeCreated_on_success(): void
    {
        $this->service->method('createEmployee')->willReturn(['success' => true, 'message' => '']);
        $this->dispatcher->expects($this->once())
            ->method('dispatch')
            ->with($this->callback(
                fn($e) => $e instanceof EmployeeCreated && $e->actorId === 2
            ));

        $req = StoreEmployeeRequest::fromArray([
            'primernombre' => 'Ana', 'primerapellido' => 'Gómez', 'segundoapellido' => 'Ruiz',
            'idpuesto' => '1', 'fechadeingreso' => '2024-01-01',
        ]);
        $this->useCase->createEmployee($req, [], '/tmp', 2);
    }

    public function test_createEmployee_does_not_dispatch_on_failure(): void
    {
        $this->service->method('createEmployee')->willReturn(['success' => false, 'message' => '']);
        $this->dispatcher->expects($this->never())->method('dispatch');

        $req = StoreEmployeeRequest::fromArray([
            'primernombre' => 'Ana', 'primerapellido' => 'Gómez', 'segundoapellido' => 'Ruiz',
            'idpuesto' => '1', 'fechadeingreso' => '2024-01-01',
        ]);
        $this->useCase->createEmployee($req, [], '/tmp', 2);
    }

    public function test_updateEmployee_dispatches_EmployeeUpdated_on_success(): void
    {
        $this->service->method('updateEmployee')->willReturn(['success' => true, 'message' => '']);
        $this->dispatcher->expects($this->once())
            ->method('dispatch')
            ->with($this->callback(
                fn($e) => $e instanceof EmployeeUpdated && $e->actorId === 2 && $e->entityId === 5
            ));

        $req = UpdateEmployeeRequest::fromArray([
            'txtID' => '5', 'primernombre' => 'Juan', 'primerapellido' => 'Pérez',
            'segundoapellido' => 'López', 'idpuesto' => '3', 'fechadeingreso' => '2024-01-15',
        ]);
        $this->useCase->updateEmployee($req, [], '/tmp', 2);
    }

    public function test_deleteEmployee_dispatches_EmployeeDeleted_on_success(): void
    {
        $this->service->method('deleteEmployee')->willReturn(true);
        $this->dispatcher->expects($this->once())
            ->method('dispatch')
            ->with($this->callback(
                fn($e) => $e instanceof EmployeeDeleted && $e->actorId === 2 && $e->entityId === 3
            ));

        $this->useCase->deleteEmployee(3, '/tmp', 2);
    }

    public function test_deleteEmployee_does_not_dispatch_on_failure(): void
    {
        $this->service->method('deleteEmployee')->willReturn(false);
        $this->dispatcher->expects($this->never())->method('dispatch');

        $this->useCase->deleteEmployee(3, '/tmp', 2);
    }
}
