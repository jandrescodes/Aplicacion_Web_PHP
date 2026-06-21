<?php

namespace App\Listeners;

use App\Domain\Events\EmployeeCreated;
use App\Domain\Events\EmployeeDeleted;
use App\Domain\Events\EmployeeUpdated;
use App\Services\AuditService;

class AuditListener
{
    public function __construct(private AuditService $auditService) {}

    public function onEmployeeCreated(EmployeeCreated $event): void
    {
        $this->auditService->logCreate($event->actorId, 'employee', $event->entityId);
    }

    public function onEmployeeUpdated(EmployeeUpdated $event): void
    {
        $this->auditService->logUpdate($event->actorId, 'employee', $event->entityId);
    }

    public function onEmployeeDeleted(EmployeeDeleted $event): void
    {
        $this->auditService->logDelete($event->actorId, 'employee', $event->entityId);
    }
}
