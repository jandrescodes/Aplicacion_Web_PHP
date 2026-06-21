<?php

namespace App\UseCases;

use App\Domain\Contracts\EventDispatcherInterface;
use App\Domain\Events\PasswordChanged;
use App\Domain\Events\ProfileUpdated;
use App\Http\Requests\Profile\ChangePasswordRequest;
use App\Http\Requests\Profile\UpdateProfileRequest;
use App\Services\UserService;
use App\UseCases\DTOs\OperationResult;
use Core\Security;

class ProfileUseCase
{
    public function __construct(
        private UserService $userService,
        private EventDispatcherInterface $dispatcher,
    ) {}

    public function getProfile(int $userId): ?array
    {
        return $this->userService->getUser($userId)?->toArray();
    }

    public function updateData(UpdateProfileRequest $req): OperationResult
    {
        $result = $this->userService->updateProfile($req->userId, [
            'usuario' => $req->usuario,
            'correo'  => $req->correo,
        ]);

        if ($result['success'] && $result['usuarioCambiado']) {
            Security::startSession();
            $_SESSION['usuario'] = $result['nuevoUsuario'];
        }

        $operationResult = new OperationResult((bool)$result['success'], (string)$result['message']);
        if ($operationResult->success) {
            $this->dispatcher->dispatch(new ProfileUpdated($req->userId, $req->userId));
        }
        return $operationResult;
    }

    public function changePassword(ChangePasswordRequest $req): OperationResult
    {
        $valid = $this->userService->verifyCurrentPassword($req->userId, $req->currentPassword);
        if (!$valid) {
            return OperationResult::fail('La contraseña actual es incorrecta.');
        }

        $result = $this->userService->changePassword($req->userId, $req->newPassword);
        $operationResult = new OperationResult((bool)$result['success'], (string)$result['message']);
        if ($operationResult->success) {
            $this->dispatcher->dispatch(new PasswordChanged($req->userId, $req->userId));
        }
        return $operationResult;
    }
}
