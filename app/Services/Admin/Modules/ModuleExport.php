<?php

namespace App\Services\Admin\Modules;

use App\Contracts\Admin\Modules\ModuleExportContract;
use App\Models\User;
use App\Support\Admin\SimplePdfExporter;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;

final class ModuleExport implements ModuleExportContract
{
    public function download(string $format, string $title, array $module, Collection $records): Response
    {
        $columns = $module['columns'] ?? [];
        $headers = array_map(fn (array $column) => $column['label'], $columns);
        $rows = $records
            ->map(fn (Model $record) => array_map(fn (array $column) => $this->value($record, $column), $columns))
            ->all();

        $filename = Str::slug($title).'-'.now()->format('Ymd-His');

        return $format === 'pdf'
            ? $this->pdf($title, $headers, $rows, $filename)
            : $this->excel($headers, $rows, $filename);
    }

    /**
     * @param  array<int, string>  $headers
     * @param  array<int, array<int, string>>  $rows
     */
    private function pdf(string $title, array $headers, array $rows, string $filename): Response
    {
        return response(SimplePdfExporter::table($title, $headers, $rows), 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="'.$filename.'.pdf"',
        ]);
    }

    /**
     * Excel opens an HTML table saved as .xls, which avoids a spreadsheet
     * dependency for what is only a listing dump.
     *
     * @param  array<int, string>  $headers
     * @param  array<int, array<int, string>>  $rows
     */
    private function excel(array $headers, array $rows, string $filename): Response
    {
        return response()->streamDownload(function () use ($headers, $rows): void {
            echo '<table border="1"><thead><tr>';

            foreach ($headers as $header) {
                echo '<th>'.e($header).'</th>';
            }

            echo '</tr></thead><tbody>';

            foreach ($rows as $row) {
                echo '<tr>';

                foreach ($row as $value) {
                    echo '<td>'.e($value).'</td>';
                }

                echo '</tr>';
            }

            echo '</tbody></table>';
        }, $filename.'.xls', ['Content-Type' => 'application/vnd.ms-excel; charset=UTF-8']);
    }

    /**
     * @param  array<string, mixed>  $column
     */
    private function value(Model $record, array $column): string
    {
        $value = data_get($record, $column['key']);

        return match ($column['type'] ?? 'text') {
            'boolean' => $value ? 'Active' : 'Inactive',
            'status' => Str::of((string) $value)->replace('_', ' ')->title()->toString(),
            'money' => number_format((float) $value, 2),
            'email' => (string) User::displayEmail(is_string($value) ? $value : null),
            'date' => $value ? Carbon::parse($value)->format('d-m-Y') : '',
            'datetime' => $value ? Carbon::parse($value)->format('d-m-Y h:i A') : '',
            default => (string) ($value ?? ''),
        };
    }
}
