<?php

namespace App\Domain\Contracts;

use App\Domain\Models\Position;

interface PositionRepositoryInterface
{
    /** @return array<Position> */
    public function listAll(): array;

    public function findById(int $id): ?Position;

    public function create(string $name): bool;

    public function update(int $id, string $name): bool;

    public function deleteById(int $id): bool;

    public function countAll(): int;
}
