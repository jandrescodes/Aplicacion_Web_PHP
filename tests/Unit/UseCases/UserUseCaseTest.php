<?php

namespace Tests\Unit\UseCases;

use App\Domain\Models\User;
use App\Http\Requests\Users\StoreUserRequest;
use App\Http\Requests\Users\UpdateUserRequest;
use App\Services\AuditService;
use App\Services\UserService;
use App\UseCases\DTOs\OperationResult;
use App\UseCases\UserUseCase;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class UserUseCaseTest extends TestCase
{
    private UserService&MockObject $service;
    private AuditService&MockObject $audit;
    private UserUseCase $useCase;

    protected function setUp(): void
    {
        $this->service = $this->createMock(UserService::class);
        $this->audit   = $this->createMock(AuditService::class);
        $this->useCase = new UserUseCase($this->service, $this->audit);
    }

    private function makeUser(int $id = 1, string $usuario = 'admin'): User
    {
        return User::fromRow([
            'ID' => $id, 'Nombreusuario' => $usuario,
            'Password' => null, 'Correo' => 'admin@example.com',
        ]);
    }

    // --- listUsers ---

    public function test_listUsers_maps_models_to_arrays(): void
    {
        $this->service->method('listUsers')->willReturn([$this->makeUser(1, 'admin'), $this->makeUser(2, 'jdoe')]);

        $result = $this->useCase->listUsers();

        $this->assertCount(2, $result);
        $this->assertSame('admin', $result[0]['Nombreusuario']);
        $this->assertSame('jdoe', $result[1]['Nombreusuario']);
    }

    public function test_listUsers_omits_password_in_output(): void
    {
        $this->service->method('listUsers')->willReturn([$this->makeUser()]);

        $result = $this->useCase->listUsers();

        $this->assertArrayNotHasKey('Password', $result[0]);
    }

    public function test_listUsers_returns_empty_array_when_no_users(): void
    {
        $this->service->method('listUsers')->willReturn([]);

        $this->assertSame([], $this->useCase->listUsers());
    }

    // --- getUser ---

    public function test_getUser_returns_array_when_found(): void
    {
        $this->service->method('getUser')->with(2)->willReturn($this->makeUser(2, 'jdoe'));

        $result = $this->useCase->getUser(2);

        $this->assertIsArray($result);
        $this->assertSame(2, $result['ID']);
        $this->assertSame('jdoe', $result['Nombreusuario']);
    }

    public function test_getUser_returns_null_when_not_found(): void
    {
        $this->service->method('getUser')->willReturn(null);

        $this->assertNull($this->useCase->getUser(99));
    }

    // --- createUser ---

    public function test_createUser_passes_request_fields_to_service(): void
    {
        $req = StoreUserRequest::fromArray([
            'usuario' => 'jdoe', 'password' => 'secret123', 'correo' => 'jdoe@example.com',
        ]);

        $this->service->expects($this->once())
            ->method('createUser')
            ->with($this->callback(fn($data) =>
                $data['usuario'] === 'jdoe' &&
                $data['password'] === 'secret123' &&
                $data['correo'] === 'jdoe@example.com'
            ))
            ->willReturn(['success' => true, 'message' => 'Usuario creado exitosamente.']);

        $result = $this->useCase->createUser($req);

        $this->assertInstanceOf(OperationResult::class, $result);
        $this->assertTrue($result->success);
    }

    public function test_createUser_wraps_service_failure_in_OperationResult(): void
    {
        $req = StoreUserRequest::fromArray([
            'usuario' => 'jdoe', 'password' => 'secret123', 'correo' => 'jdoe@example.com',
        ]);
        $this->service->method('createUser')
            ->willReturn(['success' => false, 'message' => 'No se pudo crear el usuario.']);

        $result = $this->useCase->createUser($req);

        $this->assertFalse($result->success);
        $this->assertSame('No se pudo crear el usuario.', $result->message);
    }

    // --- updateUser ---

    public function test_updateUser_passes_id_and_request_fields_to_service(): void
    {
        $req = UpdateUserRequest::fromArray([
            'txtID' => '3', 'usuario' => 'jdoe', 'password' => '', 'correo' => 'jdoe@example.com',
        ]);

        $this->service->expects($this->once())
            ->method('updateUser')
            ->with(3, $this->callback(fn($data) =>
                $data['usuario'] === 'jdoe' && $data['correo'] === 'jdoe@example.com'
            ))
            ->willReturn(['success' => true, 'message' => 'Usuario actualizado exitosamente.']);

        $result = $this->useCase->updateUser($req);

        $this->assertTrue($result->success);
        $this->assertSame('Usuario actualizado exitosamente.', $result->message);
    }

    public function test_updateUser_wraps_service_failure_in_OperationResult(): void
    {
        $req = UpdateUserRequest::fromArray([
            'txtID' => '3', 'usuario' => 'jdoe', 'password' => '', 'correo' => 'jdoe@example.com',
        ]);
        $this->service->method('updateUser')
            ->willReturn(['success' => false, 'message' => 'No se encontró el usuario a editar.']);

        $result = $this->useCase->updateUser($req);

        $this->assertFalse($result->success);
    }

    // --- deleteUser ---

    public function test_deleteUser_delegates_to_service_and_returns_true(): void
    {
        $this->service->expects($this->once())->method('deleteUser')->with(4)->willReturn(true);

        $this->assertTrue($this->useCase->deleteUser(4));
    }

    public function test_deleteUser_returns_false_when_service_returns_false(): void
    {
        $this->service->method('deleteUser')->willReturn(false);

        $this->assertFalse($this->useCase->deleteUser(99));
    }

    // --- auditoría ---

    public function test_createUser_calls_audit_logCreate_on_success(): void
    {
        $this->service->method('createUser')->willReturn(['success' => true, 'message' => '']);
        $this->audit->expects($this->once())->method('logCreate')->with(1, 'user', null);

        $req = StoreUserRequest::fromArray(['usuario' => 'jdoe', 'password' => 'secret123', 'correo' => 'jdoe@example.com']);
        $this->useCase->createUser($req, 1);
    }

    public function test_createUser_does_not_call_audit_on_failure(): void
    {
        $this->service->method('createUser')->willReturn(['success' => false, 'message' => '']);
        $this->audit->expects($this->never())->method('logCreate');

        $req = StoreUserRequest::fromArray(['usuario' => 'jdoe', 'password' => 'secret123', 'correo' => 'jdoe@example.com']);
        $this->useCase->createUser($req, 1);
    }

    public function test_updateUser_calls_audit_logUpdate_on_success(): void
    {
        $this->service->method('updateUser')->willReturn(['success' => true, 'message' => '']);
        $this->audit->expects($this->once())->method('logUpdate')->with(1, 'user', 3);

        $req = UpdateUserRequest::fromArray(['txtID' => '3', 'usuario' => 'jdoe', 'password' => '', 'correo' => 'jdoe@example.com']);
        $this->useCase->updateUser($req, 1);
    }

    public function test_deleteUser_calls_audit_logDelete_on_success(): void
    {
        $this->service->method('deleteUser')->willReturn(true);
        $this->audit->expects($this->once())->method('logDelete')->with(1, 'user', 4);

        $this->useCase->deleteUser(4, 1);
    }

    public function test_deleteUser_does_not_call_audit_on_failure(): void
    {
        $this->service->method('deleteUser')->willReturn(false);
        $this->audit->expects($this->never())->method('logDelete');

        $this->useCase->deleteUser(4, 1);
    }
}
