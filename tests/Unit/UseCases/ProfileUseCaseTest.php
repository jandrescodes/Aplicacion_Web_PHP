<?php

namespace Tests\Unit\UseCases;

use App\Http\Requests\Profile\ChangePasswordRequest;
use App\Http\Requests\Profile\UpdateProfileRequest;
use App\Services\AuditService;
use App\Services\UserService;
use App\UseCases\DTOs\OperationResult;
use App\UseCases\ProfileUseCase;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class ProfileUseCaseTest extends TestCase
{
    private UserService&MockObject $userService;
    private AuditService&MockObject $audit;
    private ProfileUseCase $useCase;

    protected function setUp(): void
    {
        $this->userService = $this->createMock(UserService::class);
        $this->audit       = $this->createMock(AuditService::class);
        $this->useCase     = new ProfileUseCase($this->userService, $this->audit);
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

    public function test_updateData_calls_audit_logUpdate_on_success(): void
    {
        $this->userService->method('updateProfile')->willReturn([
            'success' => true, 'message' => '', 'usuarioCambiado' => false, 'nuevoUsuario' => '',
        ]);
        $this->audit->expects($this->once())->method('logUpdate')->with(5, 'user', 5);

        $this->useCase->updateData($this->makeUpdateReq(5));
    }

    public function test_updateData_does_not_call_audit_on_failure(): void
    {
        $this->userService->method('updateProfile')->willReturn([
            'success' => false, 'message' => '', 'usuarioCambiado' => false, 'nuevoUsuario' => '',
        ]);
        $this->audit->expects($this->never())->method('logUpdate');

        $this->useCase->updateData($this->makeUpdateReq(5));
    }

    // --- changePassword ---

    public function test_changePassword_returns_failure_when_current_password_is_wrong(): void
    {
        $this->userService->method('verifyCurrentPassword')->willReturn(false);
        $this->audit->expects($this->never())->method('logUpdate');

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

    public function test_changePassword_calls_audit_logUpdate_on_success(): void
    {
        $this->userService->method('verifyCurrentPassword')->willReturn(true);
        $this->userService->method('changePassword')->willReturn(['success' => true, 'message' => '']);
        $this->audit->expects($this->once())->method('logUpdate')->with(5, 'user', 5);

        $this->useCase->changePassword($this->makeChangePasswordReq(5));
    }

    public function test_changePassword_does_not_call_audit_on_failure(): void
    {
        $this->userService->method('verifyCurrentPassword')->willReturn(true);
        $this->userService->method('changePassword')->willReturn(['success' => false, 'message' => '']);
        $this->audit->expects($this->never())->method('logUpdate');

        $this->useCase->changePassword($this->makeChangePasswordReq(5));
    }
}
