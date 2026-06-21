<?php

namespace App\Domain\Events;

class UserDeleted
{
    public function __construct(
        public readonly ?int $actorId,
        public readonly ?int $entityId = null,
    ) {}
}
