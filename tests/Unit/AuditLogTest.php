<?php

namespace Tests\Unit;

use App\Models\System\AuditLog;
use App\Models\System\Backup;
use Tests\TestCase;

class AuditLogTest extends TestCase
{
    public function test_record_type_is_the_class_name_without_its_namespace(): void
    {
        $log = new AuditLog(['auditable_type' => 'App\Models\Catalog\Product', 'auditable_id' => 4, 'event' => 'updated']);

        $this->assertSame('Product', $log->record_type);
    }

    public function test_an_update_summary_shows_old_and_new_values(): void
    {
        $log = new AuditLog([
            'event' => 'updated',
            'auditable_type' => 'App\Models\Catalog\Product',
            'auditable_id' => 4,
            'old_values' => ['name' => 'Old Name'],
            'new_values' => ['name' => 'New Name'],
        ]);

        $this->assertSame('name: Old Name → New Name', $log->change_summary);
    }

    public function test_a_create_summary_lists_values_without_an_arrow(): void
    {
        $log = new AuditLog([
            'event' => 'created',
            'auditable_type' => 'App\Models\Catalog\Brand',
            'auditable_id' => 9,
            'new_values' => ['name' => 'Bawaskar'],
        ]);

        $this->assertSame('name: Bawaskar', $log->change_summary);
    }

    public function test_a_summary_caps_the_fields_it_lists(): void
    {
        $log = new AuditLog([
            'event' => 'updated',
            'auditable_type' => 'App\Models\User',
            'auditable_id' => 1,
            'old_values' => array_fill_keys(['a', 'b', 'c', 'd', 'e', 'f'], '1'),
            'new_values' => array_fill_keys(['a', 'b', 'c', 'd', 'e', 'f'], '2'),
        ]);

        $this->assertStringContainsString('(+2 more)', $log->change_summary);
    }

    public function test_a_summary_with_nothing_recorded_reads_as_a_dash(): void
    {
        $log = new AuditLog(['event' => 'updated', 'auditable_type' => 'App\Models\User', 'auditable_id' => 1]);

        $this->assertSame('—', $log->change_summary);
    }

    public function test_backup_size_is_shown_in_readable_units(): void
    {
        $this->assertSame('512 B', (new Backup(['size_bytes' => 512]))->size_label);
        $this->assertSame('1.5 KB', (new Backup(['size_bytes' => 1536]))->size_label);
        $this->assertSame('2.5 MB', (new Backup(['size_bytes' => 2621440]))->size_label);
    }

    public function test_the_audit_tables_are_never_audited_themselves(): void
    {
        // Recording a change to an audit row would record that recording, and
        // so on; both system tables must stay on the ignore list.
        $ignored = config('audit.ignore', []);

        $this->assertContains(AuditLog::class, $ignored);
        $this->assertContains(Backup::class, $ignored);
    }

    public function test_password_like_columns_are_on_the_redact_list(): void
    {
        $redact = config('audit.redact', []);

        foreach (['password', 'remember_token', 'api_token'] as $column) {
            $this->assertContains($column, $redact);
        }
    }
}
