<?php

namespace Tests\Unit\UseCases;

use App\Domain\Contracts\EventDispatcherInterface;
use App\Domain\Events\PasswordChanged;
use App\Domain\Events\ProfileUpdated;
use App\Http\Requests\Profile\ChangePasswordRequest;
use App\Http\Requests\Profile\UpdateProfileRequest;
use App\Services\UserService;
use App\UseCases\DTOs\OperationResult;
use App\UseCases\ProfileUseCase;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class ProfileUseCaseTest extends TestCase
{
    private UserService&MockObject $userService;
    private EventDispatcherInterface&MockObject $dispatcher;
    private ProfileUseCase $useCase;

    protected function setUp(): void
    {
        $this->userService  = $this->createMock(UserService::class);
        $this->dispatcher   = $this->createMock(EventDispatcherInterface::class);
        $this->useCase      = new ProfileUseCase($this->userService, $this->dispatcher);
    }

    private function makeUpdateReq(int $userId = 5): UpdateProfileRequest
    {
        return UpdateProfileRequest::forUser($userId, ['usuario' => 'jdoe', 'correo' => 'jdoe@example.com']);
    }

    private function makeChangePasswordReq(int $userId = 5): ChangePasswordRequest
    {
        return ChangePasswordRequest::forUser($userId, [
            'currentPassword'    => 'oldpass1',
            'newPassword'        => 'newpass1',
            'confirmNewPassword' => 'newpass1',
        ]);
    }

    // --- updateData ---

    public function test_updateData_returns_success_when_service_succeeds(): void
    {
        $this->userService->method('updateProfile')->willReturn([
            'success' => true, 'message' => 'Datos actualizados.', 'usuarioCambiado' => false, 'nuevoUsuario' => 'jdoe',
        ]);

        $result = $this->useCase->updateData($this->makeUpdateReq());

        $this->assertInstanceOf(OperationResult::class, $result);
        $this->assertTrue($result->success);
    }

    public function test_updateData_returns_failure_when_service_fails(): void
    {
        $this->userService->method('updateProfile')->willReturn([
            'success' => false, 'message' => 'Correo en uso.', 'usuarioCambiado' => false, 'nuevoUsuario' => '',
        ]);

        $result = $this->useCase->updateData($this->makeUpdateReq());

        $this->assertFalse($result->success);
        $this->assertSame('Correo en uso.', $result->message);
    }

    public function test_updateData_dispatches_ProfileUpdated_on_success(): void
    {
        $this->userService->method('updateProfile')->willReturn([
            'success' => true, 'message' => '', 'usuarioCambiado' => false, 'nuevoUsuario' => '',
        ]);
        $this->dispatcher->expects($this->once())->method('dispatch')
            ->with($this->callback(
                fn($e) => $e instanceof ProfileUpdated && $e->actorId === 5 && $e->entityId === 5
            ));

        $this->useCase->updateData($this->makeUpdateReq(5));
    }

    public function test_updateData_does_not_dispatch_on_failure(): void
    {
        $this->userService->method('updateProfile')->willReturn([
            'success' => false, 'message' => '', 'usuarioCambiado' => false, 'nuevoUsuario' => '',
        ]);
        $this->dispatcher->expects($this->never())->method('dispatch');

        $this->useCase->updateData($this->makeUpdateReq(5));
    }

    // --- changePassword ---

    public function test_changePassword_returns_failure_when_current_password_is_wrong(): void
    {
        $this->userService->method('verifyCurrentPassword')->willReturn(false);
        $this->dispatcher->expects($this->never())->method('dispatch');

        $result = $this->useCase->changePassword($this->makeChangePasswordReq());

        $this->assertFalse($result->success);
        $this->assertSame('La contraseña actual es incorrecta.', $result->message);
    }

    public function test_changePassword_returns_success_when_service_succeeds(): void
    {
        $this->userService->method('verifyCurrentPassword')->willReturn(true);
        $this->userService->method('changePassword')->willReturn(['success' => true, 'message' => 'Contraseña actualizada.']);

        $result = $this->useCase->changePassword($this->makeChangePasswordReq());

        $this->assertTrue($result->success);
    }

    public function test_changePassword_dispatches_PasswordChanged_on_success(): void
    {
        $this->userService->method('verifyCurrentPassword')->willReturn(true);
        $this->userService->method('changePassword')->willReturn(['success' => true, 'message' => '']);
        $this->dispatcher->expects($this->once())->method('dispatch')
            ->with($this->callback(
                fn($e) => $e instanceof PasswordChanged && $e->actorId === 5 && $e->entityId === 5
            ));

        $this->useCase->changePassword($this->makeChangePasswordReq(5));
    }

    public function test_changePassword_does_not_dispatch_on_failure(): void
    {
        $this->userService->method('verifyCurrentPassword')->willReturn(true);
        $this->userService->method('changePassword')->willReturn(['success' => false, 'message' => '']);
        $this->dispatcher->expects($this->never())->method('dispatch');

        $this->useCase->changePassword($this->makeChangePasswordReq(5));
    }
}
