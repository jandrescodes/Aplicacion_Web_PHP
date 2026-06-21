<?php

namespace App\Listeners;

use App\Domain\Events\EmployeeCreated;
use App\Domain\Events\EmployeeDeleted;
use App\Domain\Events\EmployeeUpdated;
use App\Domain\Events\PositionCreated;
use App\Domain\Events\PositionDeleted;
use App\Domain\Events\PositionUpdated;
use App\Domain\Events\UserCreated;
use App\Domain\Events\UserDeleted;
use App\Domain\Events\UserUpdated;
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

    public function onPositionCreated(PositionCreated $event): void
    {
        $this->auditService->logCreate($event->actorId, 'position', $event->entityId);
    }

    public function onPositionUpdated(PositionUpdated $event): void
    {
        $this->auditService->logUpdate($event->actorId, 'position', $event->entityId);
    }

    public function onPositionDeleted(PositionDeleted $event): void
    {
        $this->auditService->logDelete($event->actorId, 'position', $event->entityId);
    }

    public function onUserCreated(UserCreated $event): void
    {
        $this->auditService->logCreate($event->actorId, 'user', $event->entityId);
    }

    public function onUserUpdated(UserUpdated $event): void
    {
        $this->auditService->logUpdate($event->actorId, 'user', $event->entityId);
    }

    public function onUserDeleted(UserDeleted $event): void
    {
        $this->auditService->logDelete($event->actorId, 'user', $event->entityId);
    }
}
