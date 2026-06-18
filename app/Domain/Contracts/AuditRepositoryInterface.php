<?php

namespace App\Domain\Contracts;

use App\Domain\Models\AuditEntry;

interface AuditRepositoryInterface
{
    public function record(?int $userId, string $action, string $entity, ?int $entityId): bool;

    /** @return array<AuditEntry> */
    public function listAll(): array;

    public function countAll(): int;
}
