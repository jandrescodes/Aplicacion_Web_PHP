<?php

namespace App\Services;

use App\Domain\Contracts\AuditRepositoryInterface;
use App\Domain\Models\AuditEntry;
use Config\AppLogger;
use Throwable;

class AuditService
{
    private const VALID_ENTITIES = ['employee', 'position', 'user'];

    private AuditRepositoryInterface $auditRepository;

    public function __construct(AuditRepositoryInterface $auditRepository)
    {
        $this->auditRepository = $auditRepository;
    }

    public function logCreate(?int $userId, string $entity, ?int $entityId): void
    {
        $this->log($userId, 'create', $entity, $entityId);
    }

    public function logUpdate(?int $userId, string $entity, ?int $entityId): void
    {
        $this->log($userId, 'update', $entity, $entityId);
    }

    public function logDelete(?int $userId, string $entity, ?int $entityId): void
    {
        $this->log($userId, 'delete', $entity, $entityId);
    }

    /** @return array<AuditEntry> */
    public function entries(): array
    {
        return $this->auditRepository->listAll();
    }

    private function log(?int $userId, string $action, string $entity, ?int $entityId): void
    {
        if (!in_array($entity, self::VALID_ENTITIES, true)) {
            AppLogger::getInstance()->warning("AuditService: entidad inválida '{$entity}' — registro omitido.");
            return;
        }

        try {
            $this->auditRepository->record($userId, $action, $entity, $entityId);
        } catch (Throwable $e) {
            AppLogger::getInstance()->warning("AuditService: no se pudo registrar la auditoría — {$e->getMessage()}");
        }
    }
}
