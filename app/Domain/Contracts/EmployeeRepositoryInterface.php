<?php

namespace App\Domain\Contracts;

use App\Domain\Models\Employee;
use App\Domain\Models\Position;

interface EmployeeRepositoryInterface
{
    /** @return array<Employee> */
    public function listAllWithPosition(): array;

    /** @return array<Position> */
    public function listPositions(): array;

    public function findById(int $id): ?Employee;

    public function findByIdWithPosition(int $id): ?Employee;

    public function findFilesById(int $id): ?array;

    public function create(array $data): bool;

    public function update(int $id, array $data): bool;

    public function deleteById(int $id): bool;

    public function countAll(): int;

    /** @return array<array{puesto: string, total: int}> */
    public function countByPosition(): array;
}
