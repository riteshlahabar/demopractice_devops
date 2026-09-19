<?php

namespace App\Console\Commands;

use App\Repositories\Location\EloquentLocationDirectory;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

/**
 * Loads the three LGD CSVs (no header row) into lgd_states, lgd_districts and
 * lgd_local_bodies. Safe to re-run: rows are upserted by their primary key.
 *
 *   php artisan lgd:import /home/user/excel
 */
class ImportLgdLocations extends Command
{
    protected $signature = 'lgd:import {directory : Folder holding state_for_insert.csv, district_for_insert.csv and Muncipal_for_insert.csv}';

    protected $description = 'Import the India state / district / taluka directory from the LGD CSV files';

    /** File => [table, column names in file order, primary key]. */
    private const FILES = [
        'state_for_insert.csv' => ['lgd_states', ['sr_no', 'state_code', 'version', 'name', 'name_local', 'census_2001_code', 'census_2011_code', 'state_type'], 'state_code'],
        'district_for_insert.csv' => ['lgd_districts', ['state_code', 'state_name', 'district_code', 'name', 'census_2001_code', 'census_2011_code'], 'district_code'],
        'Muncipal_for_insert.csv' => ['lgd_local_bodies', ['sr_no', 'state_name', 'local_body_code', 'local_body_name', 'census_code', 'district_code', 'district_name', 'subdistrict_code', 'subdistrict_name', 'village_code', 'village_name'], 'sr_no'],
    ];

    public function handle(): int
    {
        $directory = rtrim((string) $this->argument('directory'), '/\\');

        foreach (array_keys(self::FILES) as $file) {
            if (! is_readable($directory.DIRECTORY_SEPARATOR.$file)) {
                $this->error('Missing or unreadable: '.$directory.DIRECTORY_SEPARATOR.$file);

                return self::FAILURE;
            }
        }

        foreach (self::FILES as $file => [$table, $columns, $key]) {
            $count = $this->importFile($directory.DIRECTORY_SEPARATOR.$file, $table, $columns, $key);
            $this->info(sprintf('%s: %d rows into %s', $file, $count, $table));
        }

        Cache::forever(EloquentLocationDirectory::VERSION_KEY, (int) Cache::get(EloquentLocationDirectory::VERSION_KEY, 1) + 1);

        return self::SUCCESS;
    }

    /**
     * @param  array<int, string>  $columns
     */
    private function importFile(string $path, string $table, array $columns, string $key): int
    {
        $handle = fopen($path, 'r');
        $batch = [];
        $count = 0;
        $first = true;

        while (($cells = fgetcsv($handle, 0, ',', '"', '')) !== false) {
            if ($cells === [null]) {
                continue;
            }

            if ($first) {
                // state_for_insert.csv starts with a UTF-8 byte-order mark.
                $cells[0] = preg_replace('/^\xEF\xBB\xBF/', '', (string) $cells[0]);
                $first = false;
            }

            $row = [];
            foreach ($columns as $index => $column) {
                $value = trim((string) ($cells[$index] ?? ''));
                $row[$column] = $value === '' ? null : $value;
            }

            if ($row[$key] === null) {
                continue;
            }

            $batch[] = $row;
            $count++;

            if (count($batch) === 500) {
                DB::table($table)->upsert($batch, [$key], array_values(array_diff($columns, [$key])));
                $batch = [];
            }
        }

        fclose($handle);

        if ($batch !== []) {
            DB::table($table)->upsert($batch, [$key], array_values(array_diff($columns, [$key])));
        }

        return $count;
    }
}
