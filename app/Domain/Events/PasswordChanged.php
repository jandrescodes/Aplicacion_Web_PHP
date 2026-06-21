<?php

namespace App\Domain\Events;

class PasswordChanged
{
    public function __construct(
        public readonly ?int $actorId,
        public readonly ?int $entityId,
    ) {}
}
