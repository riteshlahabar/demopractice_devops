<?php

namespace App\Contracts\System;

use App\Models\System\Backup;
use App\Models\User;

interface DatabaseBackupContract
{
    /**
     * Dump every table to a .sql file on the private disk and return the
     * finished Backup row. A failed run is still returned, with status
     * "failed" and the error recorded, rather than throwing.
     */
    public function create(?User $actor = null): Backup;

    /**
     * Absolute path of a backup file, or null when the file is gone.
     */
    public function pathFor(Backup $backup): ?string;

    /**
     * Delete the file and the row together.
     */
    public function delete(Backup $backup): void;

    /**
     * Restore the database from a backup file. Returns the number of
     * statements executed.
     */
    public function restore(Backup $backup): int;
}
