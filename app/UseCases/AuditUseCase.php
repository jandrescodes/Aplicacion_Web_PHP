<?php

namespace App\UseCases;

use App\Services\AuditService;

class AuditUseCase
{
    public function __construct(private AuditService $auditService) {}

    public function listEntries(): array
    {
        return array_map(fn($entry) => $entry->toArray(), $this->auditService->entries());
    }
}
