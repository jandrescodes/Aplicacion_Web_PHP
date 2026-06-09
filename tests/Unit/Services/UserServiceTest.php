<?php

namespace Tests\Unit\Services;

use App\Domain\Contracts\UserRepositoryInterface;
use App\Domain\Models\User;
use App\Services\UserService;
use PDOException;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class UserServiceTest extends TestCase
{
    private UserRepositoryInterface&MockObject $repo;
    private UserService $service;

    protected function setUp(): void
    {
        $this->repo    = $this->createMock(UserRepositoryInterface::class);
        $this->service = new UserService($this->repo);
    }

    private function makeUser(array $overrides = []): User
    {
        return User::fromRow(array_merge([
            'ID'             => '2',
            'Nombreusuario'  => 'jdoe',
            'Password'       => password_hash('secret123', PASSWORD_DEFAULT),
            'Correo'         => 'jdoe@example.com',
        ], $overrides));
    }

    private function validCreateData(): array
    {
        return ['usuario' => 'jdoe', 'password' => 'secret123', 'correo' => 'jdoe@example.com'];
    }

    private function validUpdateData(): array
    {
        return ['usuario' => 'jdoe', 'password' => '', 'correo' => 'jdoe@example.com'];
    }

    // --- listUsers ---

    public function test_listUsers_delegates_to_repository(): void
    {
        $users = [$this->makeUser()];
        $this->repo->expects($this->once())->method('listAll')->willReturn($users);

        $this->assertSame($users, $this->service->listUsers());
    }

    // --- getUser ---

    public function test_getUser_returns_null_when_id_is_zero(): void
    {
        $this->repo->expects($this->never())->method('findById');

        $this->assertNull($this->service->getUser(0));
    }

    public function test_getUser_returns_user_from_repository(): void
    {
        $user = $this->makeUser();
        $this->repo->method('findById')->with(2)->willReturn($user);

        $this->assertSame($user, $this->service->getUser(2));
    }

    // --- createUser: validation ---

    public function test_create_returns_failure_when_usuario_is_empty(): void
    {
        $this->repo->expects($this->never())->method('create');

        $result = $this->service->createUser(array_merge($this->validCreateData(), ['usuario' => '']));

        $this->assertFalse($result['success']);
    }

    public function test_create_returns_failure_when_password_is_empty(): void
    {
        $this->repo->expects($this->never())->method('create');

        $result = $this->service->createUser(array_merge($this->validCreateData(), ['password' => '']));

        $this->assertFalse($result['success']);
    }

    public function test_create_returns_failure_when_password_shorter_than_8(): void
    {
        $this->repo->expects($this->never())->method('create');

        $result = $this->service->createUser(array_merge($this->validCreateData(), ['password' => '1234567']));

        $this->assertFalse($result['success']);
    }

    public function test_create_returns_failure_when_correo_is_empty(): void
    {
        $this->repo->expects($this->never())->method('create');

        $result = $this->service->createUser(array_merge($this->validCreateData(), ['correo' => '']));

        $this->assertFalse($result['success']);
    }

    public function test_create_returns_failure_when_correo_is_invalid(): void
    {
        $this->repo->expects($this->never())->method('create');

        $result = $this->service->createUser(array_merge($this->validCreateData(), ['correo' => 'no-es-email']));

        $this->assertFalse($result['success']);
    }

    // --- createUser: email único ---

    public function test_create_returns_failure_when_email_already_exists(): void
    {
        $this->repo->method('emailExists')->with('jdoe@example.com', null)->willReturn(true);
        $this->repo->expects($this->never())->method('create');

        $result = $this->service->createUser($this->validCreateData());

        $this->assertFalse($result['success']);
        $this->assertSame('El correo electrónico ya está registrado.', $result['message']);
    }

    public function test_create_proceeds_when_email_does_not_exist(): void
    {
        $this->repo->method('emailExists')->willReturn(false);
        $this->repo->method('create')->willReturn(true);

        $result = $this->service->createUser($this->validCreateData());

        $this->assertTrue($result['success']);
    }

    // --- createUser: repository ---

    public function test_create_hashes_password_before_persisting(): void
    {
        $this->repo->expects($this->once())
            ->method('create')
            ->with($this->callback(function (array $data) {
                return isset($data['Password']) && password_verify('secret123', $data['Password']);
            }))
            ->willReturn(true);

        $this->service->createUser($this->validCreateData());
    }

    public function test_create_returns_success_when_repository_succeeds(): void
    {
        $this->repo->method('create')->willReturn(true);

        $result = $this->service->createUser($this->validCreateData());

        $this->assertTrue($result['success']);
        $this->assertSame('Usuario creado exitosamente.', $result['message']);
    }

    public function test_create_returns_failure_when_repository_returns_false(): void
    {
        $this->repo->method('create')->willReturn(false);

        $result = $this->service->createUser($this->validCreateData());

        $this->assertFalse($result['success']);
    }

    public function test_create_returns_failure_when_repository_throws_PDOException(): void
    {
        $this->repo->method('create')->willThrowException(new PDOException('DB error'));

        $result = $this->service->createUser($this->validCreateData());

        $this->assertFalse($result['success']);
        $this->assertSame('No se pudo crear el usuario.', $result['message']);
    }

    // --- updateUser ---

    public function test_update_returns_failure_when_id_is_zero(): void
    {
        $this->repo->expects($this->never())->method('findById');

        $result = $this->service->updateUser(0, $this->validUpdateData());

        $this->assertFalse($result['success']);
        $this->assertSame('El ID del usuario no es válido.', $result['message']);
    }

    public function test_update_returns_failure_when_email_already_taken_by_another_user(): void
    {
        $this->repo->method('findById')->willReturn($this->makeUser());
        $this->repo->method('emailExists')->with('jdoe@example.com', 2)->willReturn(true);
        $this->repo->expects($this->never())->method('update');

        $result = $this->service->updateUser(2, $this->validUpdateData());

        $this->assertFalse($result['success']);
        $this->assertSame('El correo electrónico ya está registrado.', $result['message']);
    }

    public function test_update_allows_keeping_same_email(): void
    {
        $this->repo->method('findById')->willReturn($this->makeUser());
        $this->repo->method('emailExists')->with('jdoe@example.com', 2)->willReturn(false);
        $this->repo->method('update')->willReturn(true);

        $result = $this->service->updateUser(2, $this->validUpdateData());

        $this->assertTrue($result['success']);
    }

    public function test_update_returns_failure_when_user_not_found(): void
    {
        $this->repo->method('findById')->willReturn(null);

        $result = $this->service->updateUser(99, $this->validUpdateData());

        $this->assertFalse($result['success']);
        $this->assertSame('No se encontró el usuario a editar.', $result['message']);
    }

    public function test_update_keeps_existing_password_when_empty_provided(): void
    {
        $existingHash = password_hash('original', PASSWORD_DEFAULT);
        $existing = $this->makeUser(['Password' => $existingHash]);
        $this->repo->method('findById')->willReturn($existing);

        $this->repo->expects($this->once())
            ->method('update')
            ->with($this->anything(), $this->callback(function (array $data) use ($existingHash) {
                return $data['Password'] === $existingHash;
            }))
            ->willReturn(true);

        $this->service->updateUser(2, $this->validUpdateData());
    }

    public function test_update_hashes_new_password_when_provided(): void
    {
        $existing = $this->makeUser();
        $this->repo->method('findById')->willReturn($existing);

        $this->repo->expects($this->once())
            ->method('update')
            ->with($this->anything(), $this->callback(function (array $data) {
                return password_verify('nuevaclave123', $data['Password']);
            }))
            ->willReturn(true);

        $this->service->updateUser(2, array_merge($this->validUpdateData(), ['password' => 'nuevaclave123']));
    }

    public function test_update_returns_success_when_repository_succeeds(): void
    {
        $this->repo->method('findById')->willReturn($this->makeUser());
        $this->repo->method('update')->willReturn(true);

        $result = $this->service->updateUser(2, $this->validUpdateData());

        $this->assertTrue($result['success']);
        $this->assertSame('Usuario actualizado exitosamente.', $result['message']);
    }

    public function test_update_returns_failure_when_repository_throws_PDOException(): void
    {
        $this->repo->method('findById')->willReturn($this->makeUser());
        $this->repo->method('update')->willThrowException(new PDOException('DB error'));

        $result = $this->service->updateUser(2, $this->validUpdateData());

        $this->assertFalse($result['success']);
        $this->assertSame('No se pudo actualizar el usuario.', $result['message']);
    }

    // --- deleteUser ---

    public function test_delete_returns_false_when_id_is_zero(): void
    {
        $this->repo->expects($this->never())->method('deleteById');

        $this->assertFalse($this->service->deleteUser(0));
    }

    public function test_delete_delegates_to_repository(): void
    {
        $this->repo->expects($this->once())->method('deleteById')->with(4)->willReturn(true);

        $this->assertTrue($this->service->deleteUser(4));
    }
}
