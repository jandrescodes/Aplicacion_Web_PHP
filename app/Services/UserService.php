<?php

namespace App\Services;

use App\Domain\Contracts\UserRepositoryInterface;
use App\Domain\Models\User;
use PDOException;

class UserService
{
    private UserRepositoryInterface $userRepository;

    public function __construct(UserRepositoryInterface $userRepository)
    {
        $this->userRepository = $userRepository;
    }

    /** @return array<User> */
    public function listUsers(): array
    {
        return $this->userRepository->listAll();
    }

    public function getUser($id): ?User
    {
        $userId = (int)$id;
        if ($userId <= 0) {
            return null;
        }
        return $this->userRepository->findById($userId);
    }

    public function createUser($data)
    {
        $validationError = $this->validateUserData($data, false);
        if ($validationError !== null) {
            return ['success' => false, 'message' => $validationError];
        }

        $correo = trim((string)($data['correo'] ?? ''));
        if ($this->userRepository->emailExists($correo)) {
            return ['success' => false, 'message' => 'El correo electrónico ya está registrado.'];
        }

        $rawPassword = trim((string)($data['password'] ?? ''));
        $passwordHash = password_hash($rawPassword, PASSWORD_DEFAULT);
        if (!is_string($passwordHash) || $passwordHash === '') {
            return ['success' => false, 'message' => 'No se pudo procesar la contraseña.'];
        }

        try {
            $created = $this->userRepository->create([
                'Nombreusuario' => trim((string)($data['usuario'] ?? '')),
                'Password' => $passwordHash,
                'Correo' => $correo,
            ]);
        } catch (PDOException $exception) {
            return ['success' => false, 'message' => 'No se pudo crear el usuario.'];
        }

        if (!$created) {
            return ['success' => false, 'message' => 'No se pudo crear el usuario.'];
        }

        return ['success' => true, 'message' => 'Usuario creado exitosamente.'];
    }

    public function updateUser($id, $data)
    {
        $userId = (int)$id;
        if ($userId <= 0) {
            return ['success' => false, 'message' => 'El ID del usuario no es válido.'];
        }

        $validationError = $this->validateUserData($data, true);
        if ($validationError !== null) {
            return ['success' => false, 'message' => $validationError];
        }

        $existingUser = $this->userRepository->findById($userId);
        if ($existingUser === null) {
            return ['success' => false, 'message' => 'No se encontró el usuario a editar.'];
        }

        $correo = trim((string)($data['correo'] ?? ''));
        if ($this->userRepository->emailExists($correo, $userId)) {
            return ['success' => false, 'message' => 'El correo electrónico ya está registrado.'];
        }

        $rawPassword = trim((string)($data['password'] ?? ''));
        $passwordToPersist = $existingUser->password ?? '';
        if ($rawPassword !== '') {
            $passwordHash = password_hash($rawPassword, PASSWORD_DEFAULT);
            if (!is_string($passwordHash) || $passwordHash === '') {
                return ['success' => false, 'message' => 'No se pudo procesar la contraseña.'];
            }
            $passwordToPersist = $passwordHash;
        }

        try {
            $updated = $this->userRepository->update($userId, [
                'Nombreusuario' => trim((string)($data['usuario'] ?? '')),
                'Password' => $passwordToPersist,
                'Correo' => trim((string)($data['correo'] ?? ''))
            ]);
        } catch (PDOException $exception) {
            return ['success' => false, 'message' => 'No se pudo actualizar el usuario.'];
        }

        if (!$updated) {
            return ['success' => false, 'message' => 'No se pudo actualizar el usuario.'];
        }

        return ['success' => true, 'message' => 'Usuario actualizado exitosamente.'];
    }

    public function verifyCurrentPassword(int $userId, string $plainPassword): bool
    {
        if ($userId <= 0 || $plainPassword === '') {
            return false;
        }
        $user = $this->userRepository->findById($userId);
        if ($user === null || $user->password === null || $user->password === '') {
            return false;
        }
        return password_verify($plainPassword, $user->password);
    }

    public function updateProfile(int $userId, array $data): array
    {
        if ($userId <= 0) {
            return ['success' => false, 'message' => 'El ID del usuario no es válido.', 'usuarioCambiado' => false, 'nuevoUsuario' => ''];
        }

        $existingUser = $this->userRepository->findById($userId);
        if ($existingUser === null) {
            return ['success' => false, 'message' => 'No se encontró el usuario.', 'usuarioCambiado' => false, 'nuevoUsuario' => ''];
        }

        $usuario = trim((string)($data['usuario'] ?? ''));
        $correo  = trim((string)($data['correo'] ?? ''));

        if ($usuario === '') {
            return ['success' => false, 'message' => 'El usuario es obligatorio.', 'usuarioCambiado' => false, 'nuevoUsuario' => ''];
        }
        if ($correo === '' || !filter_var($correo, FILTER_VALIDATE_EMAIL)) {
            return ['success' => false, 'message' => 'Debe ingresar un correo válido.', 'usuarioCambiado' => false, 'nuevoUsuario' => ''];
        }
        if ($this->userRepository->usernameExistsExcluding($usuario, $userId)) {
            return ['success' => false, 'message' => 'El nombre de usuario ya está en uso.', 'usuarioCambiado' => false, 'nuevoUsuario' => ''];
        }
        if ($this->userRepository->emailExists($correo, $userId)) {
            return ['success' => false, 'message' => 'El correo electrónico ya está registrado.', 'usuarioCambiado' => false, 'nuevoUsuario' => ''];
        }

        $usuarioCambiado = $usuario !== $existingUser->usuario;

        try {
            $updated = $this->userRepository->update($userId, [
                'Nombreusuario' => $usuario,
                'Password'      => $existingUser->password ?? '',
                'Correo'        => $correo,
            ]);
        } catch (PDOException $exception) {
            return ['success' => false, 'message' => 'No se pudo actualizar el perfil.', 'usuarioCambiado' => false, 'nuevoUsuario' => ''];
        }

        if (!$updated) {
            return ['success' => false, 'message' => 'No se pudo actualizar el perfil.', 'usuarioCambiado' => false, 'nuevoUsuario' => ''];
        }

        return ['success' => true, 'message' => 'Perfil actualizado exitosamente.', 'usuarioCambiado' => $usuarioCambiado, 'nuevoUsuario' => $usuario];
    }

    public function changePassword(int $userId, string $newPassword): array
    {
        if ($userId <= 0) {
            return ['success' => false, 'message' => 'El ID del usuario no es válido.'];
        }

        $hash = password_hash($newPassword, PASSWORD_DEFAULT);
        if (!is_string($hash) || $hash === '') {
            return ['success' => false, 'message' => 'No se pudo procesar la contraseña.'];
        }

        try {
            $updated = $this->userRepository->updatePasswordHash($userId, $hash);
        } catch (PDOException $exception) {
            return ['success' => false, 'message' => 'No se pudo cambiar la contraseña.'];
        }

        if (!$updated) {
            return ['success' => false, 'message' => 'No se pudo cambiar la contraseña.'];
        }

        return ['success' => true, 'message' => 'Contraseña actualizada exitosamente.'];
    }

    public function deleteUser($id)
    {
        $userId = (int)$id;
        if ($userId <= 0) {
            return false;
        }
        return $this->userRepository->deleteById($userId);
    }

    private function validateUserData($data, $isUpdate = false)
    {
        $usuario = trim((string)($data['usuario'] ?? ''));
        $password = trim((string)($data['password'] ?? ''));
        $correo = trim((string)($data['correo'] ?? ''));

        if ($usuario === '' || $correo === '') {
            return 'Debe completar los campos obligatorios del usuario y correo.';
        }

        if (!$isUpdate && $password === '') {
            return 'Debe completar la contraseña del usuario.';
        }

        if ($password !== '' && strlen($password) < 8) {
            return 'La contraseña debe tener al menos 8 caracteres.';
        }

        if (!filter_var($correo, FILTER_VALIDATE_EMAIL)) {
            return 'Debe ingresar un correo válido.';
        }

        return null;
    }
}
