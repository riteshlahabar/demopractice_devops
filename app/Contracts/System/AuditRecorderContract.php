<?php

namespace App\Contracts\System;

use Illuminate\Database\Eloquent\Model;

interface AuditRecorderContract
{
    /**
     * Record one model change. Implementations must never throw — an audit
     * failure may not stop the business action that caused it.
     */
    public function record(string $event, Model $model): void;

    /**
     * Run a callback with recording switched off, for bulk work that would
     * otherwise write thousands of rows.
     */
    public function withoutRecording(callable $callback): mixed;
}
