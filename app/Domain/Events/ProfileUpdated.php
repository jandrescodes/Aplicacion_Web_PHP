<?php

namespace App\Domain\Events;

class ProfileUpdated
{
    public function __construct(
        public readonly ?int $actorId,
        public readonly ?int $entityId,
    ) {}
}
