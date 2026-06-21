<?php

namespace App\Domain\Events;

class PositionCreated
{
    public function __construct(
        public readonly ?int $actorId,
        public readonly ?int $entityId = null,
    ) {}
}
