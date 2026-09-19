<?php

namespace App\Services\System;

use App\Contracts\System\DatabaseBackupContract;
use App\Models\System\Backup;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use PDO;
use Throwable;

/**
 * Writes the dump in PHP rather than shelling out to mysqldump, because shared
 * cPanel hosting usually disables exec(). Rows are streamed in chunks and
 * appended straight to the file, so a large table never has to fit in memory.
 *
 * Files go to the private `local` disk — a database dump must never be
 * reachable over the web.
 */
class SqlDatabaseBackupService implements DatabaseBackupContract
{
    private const DIRECTORY = 'backups';

    private const CHUNK = 500;

    public function create(?User $actor = null): Backup
    {
        $filename = 'backup-'.now()->format('Y-m-d-His').'-'.bin2hex(random_bytes(3)).'.sql';
        $relative = self::DIRECTORY.'/'.$filename;

        $backup = Backup::query()->create([
            'filename' => $filename,
            'disk' => 'local',
            'path' => $relative,
            'type' => 'database',
            'status' => 'running',
            'created_by' => $actor?->id,
        ]);

        try {
            $disk = Storage::disk('local');
            $disk->put($relative, $this->header());
            $absolute = $disk->path($relative);

            $tables = $this->tables();
            foreach ($tables as $table) {
                $this->dumpTable($absolute, $table);
            }

            file_put_contents($absolute, "\nSET FOREIGN_KEY_CHECKS=1;\n", FILE_APPEND);

            $backup->update([
                'status' => 'completed',
                'size_bytes' => (int) @filesize($absolute),
                'table_count' => count($tables),
                'completed_at' => now(),
            ]);
        } catch (Throwable $e) {
            $backup->update([
                'status' => 'failed',
                'error' => mb_substr($e->getMessage(), 0, 2000),
                'completed_at' => now(),
            ]);
        }

        return $backup->refresh();
    }

    public function pathFor(Backup $backup): ?string
    {
        $disk = Storage::disk($backup->disk ?: 'local');

        return $disk->exists($backup->path) ? $disk->path($backup->path) : null;
    }

    public function delete(Backup $backup): void
    {
        Storage::disk($backup->disk ?: 'local')->delete($backup->path);
        $backup->delete();
    }

    public function restore(Backup $backup): int
    {
        $path = $this->pathFor($backup);

        if ($path === null) {
            throw new \RuntimeException('The backup file is missing, so it cannot be restored.');
        }

        $pdo = DB::connection()->getPdo();
        $executed = 0;
        $statement = '';
        $handle = fopen($path, 'r');

        try {
            while (($line = fgets($handle)) !== false) {
                $trimmed = trim($line);

                if ($trimmed === '' || str_starts_with($trimmed, '--')) {
                    continue;
                }

                $statement .= $line;

                // Statements are written one per line by dumpTable, always
                // terminated with a semicolon at end of line.
                if (str_ends_with($trimmed, ';')) {
                    $pdo->exec($statement);
                    $executed++;
                    $statement = '';
                }
            }
        } finally {
            fclose($handle);
        }

        return $executed;
    }

    private function header(): string
    {
        return "-- Bawaskar ERP database backup\n"
            .'-- Taken: '.now()->toDateTimeString()."\n"
            ."SET FOREIGN_KEY_CHECKS=0;\n";
    }

    /**
     * @return array<int, string>
     */
    private function tables(): array
    {
        $database = DB::connection()->getDatabaseName();
        $rows = DB::select('SHOW FULL TABLES WHERE Table_type = ?', ['BASE TABLE']);
        $key = 'Tables_in_'.$database;

        return array_values(array_map(static fn ($row): string => (array) $row ? ((array) $row)[$key] : '', $rows));
    }

    private function dumpTable(string $absolute, string $table): void
    {
        $create = (array) DB::selectOne('SHOW CREATE TABLE `'.$table.'`');
        $sql = "\n-- Table: {$table}\n"
            ."DROP TABLE IF EXISTS `{$table}`;\n"
            .str_replace("\n", ' ', (string) ($create['Create Table'] ?? '')).";\n";

        file_put_contents($absolute, $sql, FILE_APPEND);

        DB::table($table)->orderByRaw('1')->chunk(self::CHUNK, function ($rows) use ($absolute, $table): void {
            $lines = '';

            foreach ($rows as $row) {
                $values = array_map(fn ($value): string => $this->quote($value), array_values((array) $row));
                $columns = implode('`, `', array_keys((array) $row));
                $lines .= "INSERT INTO `{$table}` (`{$columns}`) VALUES (".implode(', ', $values).");\n";
            }

            if ($lines !== '') {
                file_put_contents($absolute, $lines, FILE_APPEND);
            }
        });
    }

    private function quote(mixed $value): string
    {
        if ($value === null) {
            return 'NULL';
        }

        if (is_bool($value)) {
            return $value ? '1' : '0';
        }

        if (is_int($value) || is_float($value)) {
            return (string) $value;
        }

        return DB::connection()->getPdo()->quote((string) $value, PDO::PARAM_STR);
    }
}
