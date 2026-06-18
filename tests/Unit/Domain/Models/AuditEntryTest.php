<?php

namespace Tests\Unit\Domain\Models;

use App\Domain\Models\AuditEntry;
use PHPUnit\Framework\TestCase;

class AuditEntryTest extends TestCase
{
    public function test_fromRow_maps_all_fields_correctly(): void
    {
        $entry = AuditEntry::fromRow([
            'id'         => '5',
            'user_id'    => '3',
            'action'     => 'create',
            'entity'     => 'employee',
            'entity_id'  => '12',
            'created_at' => '2026-06-18 10:00:00',
        ]);

        $this->assertSame(5, $entry->id);
        $this->assertSame(3, $entry->userId);
        $this->assertSame('create', $entry->action);
        $this->assertSame('employee', $entry->entity);
        $this->assertSame(12, $entry->entityId);
        $this->assertSame('2026-06-18 10:00:00', $entry->createdAt);
    }

    public function test_fromRow_nullable_fields_are_null_when_absent(): void
    {
        $entry = AuditEntry::fromRow([
            'id'         => '1',
            'action'     => 'delete',
            'entity'     => 'position',
            'created_at' => '2026-06-18 11:00:00',
        ]);

        $this->assertNull($entry->userId);
        $this->assertNull($entry->entityId);
    }

    public function test_fromRow_nullable_fields_are_null_when_explicitly_null(): void
    {
        $entry = AuditEntry::fromRow([
            'id'         => '2',
            'user_id'    => null,
            'action'     => 'update',
            'entity'     => 'user',
            'entity_id'  => null,
            'created_at' => '2026-06-18 12:00:00',
        ]);

        $this->assertNull($entry->userId);
        $this->assertNull($entry->entityId);
    }

    public function test_fromRow_casts_ids_to_int(): void
    {
        $entry = AuditEntry::fromRow([
            'id'         => '99',
            'user_id'    => '7',
            'action'     => 'create',
            'entity'     => 'user',
            'entity_id'  => '42',
            'created_at' => '2026-06-18 09:00:00',
        ]);

        $this->assertIsInt($entry->id);
        $this->assertIsInt($entry->userId);
        $this->assertIsInt($entry->entityId);
    }

    public function test_toArray_returns_correct_keys_and_values(): void
    {
        $entry = AuditEntry::fromRow([
            'id'         => '3',
            'user_id'    => '1',
            'action'     => 'update',
            'entity'     => 'position',
            'entity_id'  => '8',
            'created_at' => '2026-06-18 13:00:00',
        ]);

        $array = $entry->toArray();

        $this->assertSame(3, $array['id']);
        $this->assertSame(1, $array['user_id']);
        $this->assertSame('update', $array['action']);
        $this->assertSame('position', $array['entity']);
        $this->assertSame(8, $array['entity_id']);
        $this->assertSame('2026-06-18 13:00:00', $array['created_at']);
    }

    public function test_toArray_roundtrip_preserves_nullable_fields(): void
    {
        $entry = AuditEntry::fromRow([
            'id'         => '10',
            'user_id'    => null,
            'action'     => 'delete',
            'entity'     => 'employee',
            'entity_id'  => null,
            'created_at' => '2026-06-18 14:00:00',
        ]);

        $array = $entry->toArray();

        $this->assertNull($array['user_id']);
        $this->assertNull($array['entity_id']);
    }
}
