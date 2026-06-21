<?php

namespace App\UseCases;

use App\Domain\Contracts\EventDispatcherInterface;
use App\Domain\Events\UserCreated;
use App\Domain\Events\UserDeleted;
use App\Domain\Events\UserUpdated;
use App\Domain\Models\User;
use App\Http\Requests\Users\StoreUserRequest;
use App\Http\Requests\Users\UpdateUserRequest;
use App\Services\UserService;
use App\UseCases\DTOs\OperationResult;

class UserUseCase
{
    public function __construct(
        private UserService $userService,
        private EventDispatcherInterface $dispatcher,
    ) {}

    public function listUsers(): array
    {
        return array_map(fn(User $u) => $u->toArray(), $this->userService->listUsers());
    }

    public function getUser(int $id): ?array
    {
        $user = $this->userService->getUser($id);
        return $user?->toArray();
    }

    public function createUser(StoreUserRequest $req, ?int $actorId = null): OperationResult
    {
        $result = $this->userService->createUser([
            'usuario'  => $req->usuario,
            'password' => $req->password,
            'correo'   => $req->correo,
        ]);
        $operationResult = new OperationResult(
            (bool)($result['success'] ?? false),
            (string)($result['message'] ?? '')
        );
        if ($operationResult->success) {
            $this->dispatcher->dispatch(new UserCreated($actorId));
        }
        return $operationResult;
    }

    public function updateUser(UpdateUserRequest $req, ?int $actorId = null): OperationResult
    {
        $result = $this->userService->updateUser($req->id, [
            'usuario'  => $req->usuario,
            'password' => $req->password,
            'correo'   => $req->correo,
        ]);
        $operationResult = new OperationResult(
            (bool)($result['success'] ?? false),
            (string)($result['message'] ?? '')
        );
        if ($operationResult->success) {
            $this->dispatcher->dispatch(new UserUpdated($actorId, $req->id));
        }
        return $operationResult;
    }

    public function deleteUser(int $id, ?int $actorId = null): bool
    {
        $deleted = $this->userService->deleteUser($id);
        if ($deleted) {
            $this->dispatcher->dispatch(new UserDeleted($actorId, $id));
        }
        return $deleted;
    }
}
