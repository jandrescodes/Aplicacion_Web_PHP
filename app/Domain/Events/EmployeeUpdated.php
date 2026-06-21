<?php

namespace App\Domain\Events;

class EmployeeUpdated
{
    public function __construct(
        public readonly ?int $actorId,
        public readonly ?int $entityId,
    ) {}
}
