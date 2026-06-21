<?php

namespace App\Domain\Events;

class EmployeeDeleted
{
    public function __construct(
        public readonly ?int $actorId,
        public readonly ?int $entityId,
    ) {}
}
