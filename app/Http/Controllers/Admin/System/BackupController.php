<?php

namespace App\Http\Controllers\Admin\System;

use App\Contracts\System\DatabaseBackupContract;
use App\Http\Controllers\Admin\Concerns\AdminModuleController;
use App\Models\System\Backup;
use App\Support\Admin\Modules\AdminModuleServices;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Throwable;

/**
 * Backups are taken and restored here, never created by hand, so the module
 * config switches off Add and Edit. Restore is deliberately awkward: it needs
 * the word RESTORE typed in, because it overwrites the live database.
 */
class BackupController extends AdminModuleController
{
    protected string $moduleKey = 'backups';

    public function __construct(
        AdminModuleServices $modules,
        private readonly DatabaseBackupContract $backups,
    ) {
        parent::__construct($modules);
    }

    public function run(Request $request): RedirectResponse
    {
        $backup = $this->backups->create($request->user());

        return $backup->status === 'completed'
            ? back()->with('success', "Backup created: {$backup->filename} ({$backup->size_label}, {$backup->table_count} tables).")
            : back()->with('error', 'Backup failed: '.($backup->error ?: 'unknown error.'));
    }

    public function download(int|string $id): BinaryFileResponse|RedirectResponse
    {
        $backup = Backup::query()->findOrFail($id);
        $path = $this->backups->pathFor($backup);

        if ($path === null) {
            return back()->with('error', 'That backup file is no longer on the server.');
        }

        return response()->download($path, $backup->filename);
    }

    public function destroy(int|string $id): RedirectResponse
    {
        $backup = Backup::query()->findOrFail($id);
        $this->backups->delete($backup);

        return redirect()->route('admin.backups.index')->with('success', 'Backup deleted.');
    }

    public function restore(Request $request, int|string $id): RedirectResponse
    {
        $request->validate(
            ['confirm' => ['required', 'string', 'in:RESTORE']],
            ['confirm.in' => 'Type RESTORE exactly to confirm overwriting the live database.']
        );

        $backup = Backup::query()->findOrFail($id);

        if ($backup->status !== 'completed') {
            return back()->with('error', 'Only a completed backup can be restored.');
        }

        try {
            $statements = $this->backups->restore($backup);
        } catch (Throwable $e) {
            return back()->with('error', 'Restore failed: '.$e->getMessage());
        }

        return back()->with('success', "Database restored from {$backup->filename} ({$statements} statements).");
    }
}
