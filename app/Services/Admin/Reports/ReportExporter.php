<?php

namespace App\Services\Admin\Reports;

use App\Contracts\Admin\Reports\ReportExporterContract;
use App\Data\Admin\Reports\ReportResult;
use App\Support\Admin\SimplePdfExporter;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;

/**
 * Same file formats as the module listings — an HTML table saved as .xls and
 * the plain PDF table — so reports need no extra server dependency.
 */
final class ReportExporter implements ReportExporterContract
{
    public function __construct(private readonly ReportValueFormatter $formatter) {}

    public function download(string $format, string $title, ReportResult $result): Response
    {
        $headers = array_map(fn (array $column): string => $column['label'], $result->columns);
        $rows = array_map(fn (array $row): array => array_map(
            fn (array $column): string => $this->formatter->format($row[$column['key']] ?? null, $column['type'] ?? 'text'),
            $result->columns,
        ), $result->rows);

        $filename = Str::slug($title).'-'.now()->format('Ymd-His');

        if ($format === 'pdf') {
            return response(SimplePdfExporter::table($title, $headers, $rows), 200, [
                'Content-Type' => 'application/pdf',
                'Content-Disposition' => 'attachment; filename="'.$filename.'.pdf"',
            ]);
        }

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
}
