<?php

namespace App\Console\Commands;

use App\Models\System\AuditLog;
use Illuminate\Console\Command;

class PruneAuditLogs extends Command
{
    protected $signature = 'audit:prune {--days= : Override config/audit.php retention_days}';

    protected $description = 'Delete audit trail rows older than the configured retention period.';

    public function handle(): int
    {
        $days = (int) ($this->option('days') ?? config('audit.retention_days', 365));

        if ($days <= 0) {
            $this->info('Retention is set to keep everything; nothing pruned.');

            return self::SUCCESS;
        }

        $cutoff = now()->subDays($days);
        $deleted = 0;

        // Deleted in batches so a long trail does not lock the table.
        do {
            $batch = AuditLog::query()->where('created_at', '<', $cutoff)->limit(1000)->delete();
            $deleted += $batch;
        } while ($batch > 0);

        $this->info("Pruned {$deleted} audit rows older than {$days} days.");

        return self::SUCCESS;
    }
}
