<?php

namespace Tests\Unit\UseCases;

use App\Domain\Models\Position;
use App\Http\Requests\Positions\StorePositionRequest;
use App\Http\Requests\Positions\UpdatePositionRequest;
use App\Services\AuditService;
use App\Services\PositionService;
use App\UseCases\DTOs\OperationResult;
use App\UseCases\PositionUseCase;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class PositionUseCaseTest extends TestCase
{
    private PositionService&MockObject $service;
    private AuditService&MockObject $audit;
    private PositionUseCase $useCase;

    protected function setUp(): void
    {
        $this->service = $this->createMock(PositionService::class);
        $this->audit   = $this->createMock(AuditService::class);
        $this->useCase = new PositionUseCase($this->service, $this->audit);
    }

    private function makePosition(int $id = 1, string $nombre = 'Gerente'): Position
    {
        return Position::fromRow(['ID' => $id, 'Nombredelpuesto' => $nombre]);
    }

    // --- listPositions ---

    public function test_listPositions_maps_models_to_arrays(): void
    {
        $this->service->method('listPositions')
            ->willReturn([$this->makePosition(1, 'Gerente'), $this->makePosition(2, 'Analista')]);

        $result = $this->useCase->listPositions();

        $this->assertCount(2, $result);
        $this->assertSame('Gerente', $result[0]['Nombredelpuesto']);
        $this->assertSame('Analista', $result[1]['Nombredelpuesto']);
    }

    public function test_listPositions_returns_empty_array_when_no_positions(): void
    {
        $this->service->method('listPositions')->willReturn([]);

        $this->assertSame([], $this->useCase->listPositions());
    }

    // --- getPosition ---

    public function test_getPosition_returns_array_when_found(): void
    {
        $this->service->method('getPosition')->with(3)->willReturn($this->makePosition(3, 'Contador'));

        $result = $this->useCase->getPosition(3);

        $this->assertIsArray($result);
        $this->assertSame(3, $result['ID']);
        $this->assertSame('Contador', $result['Nombredelpuesto']);
    }

    public function test_getPosition_returns_null_when_not_found(): void
    {
        $this->service->method('getPosition')->willReturn(null);

        $this->assertNull($this->useCase->getPosition(99));
    }

    // --- createPosition ---

    public function test_createPosition_passes_nombre_to_service(): void
    {
        $req = StorePositionRequest::fromArray(['nombredelpuesto' => 'Director']);

        $this->service->expects($this->once())
            ->method('createPosition')
            ->with(['nombredelpuesto' => 'Director'])
            ->willReturn(['success' => true, 'message' => 'Puesto creado exitosamente.']);

        $result = $this->useCase->createPosition($req);

        $this->assertInstanceOf(OperationResult::class, $result);
        $this->assertTrue($result->success);
    }

    public function test_createPosition_wraps_service_failure_in_OperationResult(): void
    {
        $req = StorePositionRequest::fromArray(['nombredelpuesto' => 'Director']);
        $this->service->method('createPosition')
            ->willReturn(['success' => false, 'message' => 'No se pudo crear el puesto.']);

        $result = $this->useCase->createPosition($req);

        $this->assertFalse($result->success);
        $this->assertSame('No se pudo crear el puesto.', $result->message);
    }

    // --- updatePosition ---

    public function test_updatePosition_passes_id_and_nombre_to_service(): void
    {
        $req = UpdatePositionRequest::fromArray(['txtID' => '4', 'nombredelpuesto' => 'Supervisor']);

        $this->service->expects($this->once())
            ->method('updatePosition')
            ->with(4, ['nombredelpuesto' => 'Supervisor'])
            ->willReturn(['success' => true, 'message' => 'Puesto actualizado exitosamente.']);

        $result = $this->useCase->updatePosition($req);

        $this->assertTrue($result->success);
        $this->assertSame('Puesto actualizado exitosamente.', $result->message);
    }

    public function test_updatePosition_wraps_service_failure_in_OperationResult(): void
    {
        $req = UpdatePositionRequest::fromArray(['txtID' => '4', 'nombredelpuesto' => 'Supervisor']);
        $this->service->method('updatePosition')
            ->willReturn(['success' => false, 'message' => 'No se encontró el puesto a editar.']);

        $result = $this->useCase->updatePosition($req);

        $this->assertFalse($result->success);
    }

    // --- deletePosition ---

    public function test_deletePosition_delegates_to_service_and_returns_true(): void
    {
        $this->service->expects($this->once())->method('deletePosition')->with(5)->willReturn(true);

        $this->assertTrue($this->useCase->deletePosition(5));
    }

    public function test_deletePosition_returns_false_when_service_returns_false(): void
    {
        $this->service->method('deletePosition')->willReturn(false);

        $this->assertFalse($this->useCase->deletePosition(99));
    }

    // --- auditoría ---

    public function test_createPosition_calls_audit_logCreate_on_success(): void
    {
        $this->service->method('createPosition')->willReturn(['success' => true, 'message' => '']);
        $this->audit->expects($this->once())->method('logCreate')->with(7, 'position', null);

        $req = StorePositionRequest::fromArray(['nombredelpuesto' => 'Director']);
        $this->useCase->createPosition($req, 7);
    }

    public function test_createPosition_does_not_call_audit_on_failure(): void
    {
        $this->service->method('createPosition')->willReturn(['success' => false, 'message' => '']);
        $this->audit->expects($this->never())->method('logCreate');

        $req = StorePositionRequest::fromArray(['nombredelpuesto' => 'Director']);
        $this->useCase->createPosition($req, 7);
    }

    public function test_updatePosition_calls_audit_logUpdate_on_success(): void
    {
        $this->service->method('updatePosition')->willReturn(['success' => true, 'message' => '']);
        $this->audit->expects($this->once())->method('logUpdate')->with(7, 'position', 4);

        $req = UpdatePositionRequest::fromArray(['txtID' => '4', 'nombredelpuesto' => 'Supervisor']);
        $this->useCase->updatePosition($req, 7);
    }

    public function test_deletePosition_calls_audit_logDelete_on_success(): void
    {
        $this->service->method('deletePosition')->willReturn(true);
        $this->audit->expects($this->once())->method('logDelete')->with(7, 'position', 5);

        $this->useCase->deletePosition(5, 7);
    }

    public function test_deletePosition_does_not_call_audit_on_failure(): void
    {
        $this->service->method('deletePosition')->willReturn(false);
        $this->audit->expects($this->never())->method('logDelete');

        $this->useCase->deletePosition(5, 7);
    }
}
