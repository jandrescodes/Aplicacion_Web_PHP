<?php

namespace Tests\Unit\Services;

use App\Domain\Contracts\AuditRepositoryInterface;
use App\Domain\Models\AuditEntry;
use App\Services\AuditService;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class AuditServiceTest extends TestCase
{
    private AuditRepositoryInterface&MockObject $repo;
    private AuditService $service;

    protected function setUp(): void
    {
        $this->repo    = $this->createMock(AuditRepositoryInterface::class);
        $this->service = new AuditService($this->repo);
    }

    // --- logCreate ---

    public function test_logCreate_calls_record_with_create_action(): void
    {
        $this->repo->expects($this->once())
            ->method('record')
            ->with(1, 'create', 'employee', 5);

        $this->service->logCreate(1, 'employee', 5);
    }

    public function test_logCreate_accepts_null_userId_and_entityId(): void
    {
        $this->repo->expects($this->once())
            ->method('record')
            ->with(null, 'create', 'position', null);

        $this->service->logCreate(null, 'position', null);
    }

    // --- logUpdate ---

    public function test_logUpdate_calls_record_with_update_action(): void
    {
        $this->repo->expects($this->once())
            ->method('record')
            ->with(2, 'update', 'user', 10);

        $this->service->logUpdate(2, 'user', 10);
    }

    // --- logDelete ---

    public function test_logDelete_calls_record_with_delete_action(): void
    {
        $this->repo->expects($this->once())
            ->method('record')
            ->with(3, 'delete', 'position', 7);

        $this->service->logDelete(3, 'position', 7);
    }

    // --- entidad inválida ---

    public function test_log_skips_record_when_entity_is_invalid(): void
    {
        $this->repo->expects($this->never())->method('record');

        $this->service->logCreate(1, 'invalid_entity', 1);
    }

    // --- fallo silencioso ---

    public function test_log_does_not_propagate_exception_when_record_throws(): void
    {
        $this->repo->method('record')->willThrowException(new \RuntimeException('DB down'));

        // No debe lanzar excepción
        $this->service->logCreate(1, 'employee', 1);
        $this->addToAssertionCount(1);
    }

    // --- entries ---

    public function test_entries_delegates_to_repository(): void
    {
        $entries = [
            AuditEntry::fromRow(['id' => 1, 'user_id' => 1, 'action' => 'create', 'entity' => 'employee', 'entity_id' => 5, 'created_at' => '2026-06-18 10:00:00']),
        ];
        $this->repo->expects($this->once())->method('listAll')->willReturn($entries);

        $this->assertSame($entries, $this->service->entries());
    }
}
