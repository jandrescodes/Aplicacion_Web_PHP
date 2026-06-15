<?php

namespace App\Domain\Contracts;

use App\Domain\Models\User;

interface UserRepositoryInterface
{
    public function findByUsername(string $username): ?User;

    /** @return array<User> */
    public function listAll(): array;

    public function findById(int $id): ?User;

    public function create(array $data): bool;

    public function update(int $id, array $data): bool;

    public function deleteById(int $id): bool;

    public function updatePasswordHash(int $id, string $passwordHash): bool;

    public function findByIdWithRememberToken(int $id): ?User;

    public function setRememberToken(int $id, string $tokenHash, string $expiresAt): bool;

    public function clearRememberToken(int $id): bool;

    public function emailExists(string $email, ?int $excludeId = null): bool;

    public function usernameExistsExcluding(string $username, int $excludeId): bool;

    public function countAll(): int;
}
