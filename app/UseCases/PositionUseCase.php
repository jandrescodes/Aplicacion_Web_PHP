<?php

namespace App\UseCases;

use App\Domain\Models\Position;
use App\Http\Requests\Positions\StorePositionRequest;
use App\Http\Requests\Positions\UpdatePositionRequest;
use App\Services\AuditService;
use App\Services\PositionService;
use App\UseCases\DTOs\OperationResult;

class PositionUseCase
{
    private PositionService $positionService;
    private AuditService $auditService;

    public function __construct(PositionService $positionService, AuditService $auditService)
    {
        $this->positionService = $positionService;
        $this->auditService    = $auditService;
    }

    public function listPositions(): array
    {
        return array_map(fn(Position $p) => $p->toArray(), $this->positionService->listPositions());
    }

    public function getPosition(int $id): ?array
    {
        $position = $this->positionService->getPosition($id);
        return $position?->toArray();
    }

    public function createPosition(StorePositionRequest $req, ?int $actorId = null): OperationResult
    {
        $result = $this->positionService->createPosition(['nombredelpuesto' => $req->nombre]);
        $operationResult = new OperationResult(
            (bool)($result['success'] ?? false),
            (string)($result['message'] ?? '')
        );
        if ($operationResult->success) {
            $this->auditService->logCreate($actorId, 'position', null);
        }
        return $operationResult;
    }

    public function updatePosition(UpdatePositionRequest $req, ?int $actorId = null): OperationResult
    {
        $result = $this->positionService->updatePosition($req->id, ['nombredelpuesto' => $req->nombre]);
        $operationResult = new OperationResult(
            (bool)($result['success'] ?? false),
            (string)($result['message'] ?? '')
        );
        if ($operationResult->success) {
            $this->auditService->logUpdate($actorId, 'position', $req->id);
        }
        return $operationResult;
    }

    public function deletePosition(int $id, ?int $actorId = null): bool
    {
        $deleted = $this->positionService->deletePosition($id);
        if ($deleted) {
            $this->auditService->logDelete($actorId, 'position', $id);
        }
        return $deleted;
    }
}
