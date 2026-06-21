<?php

namespace App\UseCases;

use App\Domain\Contracts\EventDispatcherInterface;
use App\Domain\Events\PositionCreated;
use App\Domain\Events\PositionDeleted;
use App\Domain\Events\PositionUpdated;
use App\Domain\Models\Position;
use App\Http\Requests\Positions\StorePositionRequest;
use App\Http\Requests\Positions\UpdatePositionRequest;
use App\Services\PositionService;
use App\UseCases\DTOs\OperationResult;

class PositionUseCase
{
    public function __construct(
        private PositionService $positionService,
        private EventDispatcherInterface $dispatcher,
    ) {}

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
            $this->dispatcher->dispatch(new PositionCreated($actorId));
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
            $this->dispatcher->dispatch(new PositionUpdated($actorId, $req->id));
        }
        return $operationResult;
    }

    public function deletePosition(int $id, ?int $actorId = null): bool
    {
        $deleted = $this->positionService->deletePosition($id);
        if ($deleted) {
            $this->dispatcher->dispatch(new PositionDeleted($actorId, $id));
        }
        return $deleted;
    }
}
