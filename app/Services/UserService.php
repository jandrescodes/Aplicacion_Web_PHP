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
