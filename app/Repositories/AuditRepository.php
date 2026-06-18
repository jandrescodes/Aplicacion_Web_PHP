<?php

namespace App\Repositories;

use App\Domain\Contracts\AuditRepositoryInterface;
use App\Domain\Models\AuditEntry;
use PDO;

class AuditRepository implements AuditRepositoryInterface
{
    private $connection;

    public function __construct(PDO $connection)
    {
        $this->connection = $connection;
    }

    public function record(?int $userId, string $action, string $entity, ?int $entityId): bool
    {
        $statement = $this->connection->prepare(
            "INSERT INTO `audit_log` (user_id, action, entity, entity_id)
             VALUES (:user_id, :action, :entity, :entity_id)"
        );
        $statement->bindValue(':user_id',   $userId,   $userId === null   ? PDO::PARAM_NULL : PDO::PARAM_INT);
        $statement->bindValue(':action',    $action,   PDO::PARAM_STR);
        $statement->bindValue(':entity',    $entity,   PDO::PARAM_STR);
        $statement->bindValue(':entity_id', $entityId, $entityId === null ? PDO::PARAM_NULL : PDO::PARAM_INT);
        return $statement->execute();
    }

    /** @return array<AuditEntry> */
    public function listAll(): array
    {
        $statement = $this->connection->prepare(
            "SELECT id, user_id, action, entity, entity_id, created_at
             FROM `audit_log`
             ORDER BY created_at DESC"
        );
        $statement->execute();
        return array_map(
            fn(array $row) => AuditEntry::fromRow($row),
            $statement->fetchAll(PDO::FETCH_ASSOC)
        );
    }

    public function countAll(): int
    {
        $statement = $this->connection->prepare(
            "SELECT COUNT(*) FROM `audit_log`"
        );
        $statement->execute();
        return (int)$statement->fetchColumn();
    }
}
