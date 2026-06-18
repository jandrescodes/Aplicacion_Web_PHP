<?php

namespace App\Domain\Models;

class AuditEntry
{
    public function __construct(
        public readonly int $id,
        public readonly ?int $userId,
        public readonly string $action,
        public readonly string $entity,
        public readonly ?int $entityId,
        public readonly string $createdAt,
    ) {}

    public static function fromRow(array $row): self
    {
        return new self(
            id:        (int)($row['id'] ?? 0),
            userId:    isset($row['user_id']) ? (int)$row['user_id'] : null,
            action:    (string)($row['action'] ?? ''),
            entity:    (string)($row['entity'] ?? ''),
            entityId:  isset($row['entity_id']) ? (int)$row['entity_id'] : null,
            createdAt: (string)($row['created_at'] ?? ''),
        );
    }

    public function toArray(): array
    {
        return [
            'id'         => $this->id,
            'user_id'    => $this->userId,
            'action'     => $this->action,
            'entity'     => $this->entity,
            'entity_id'  => $this->entityId,
            'created_at' => $this->createdAt,
        ];
    }
}
