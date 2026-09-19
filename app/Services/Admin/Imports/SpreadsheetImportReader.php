<?php

namespace App\Services\Admin\Imports;

use App\Contracts\Admin\Imports\ImportFileReaderContract;
use RuntimeException;
use ZipArchive;

final class SpreadsheetImportReader implements ImportFileReaderContract
{
    public function rows(string $path, string $extension): array
    {
        return strtolower($extension) === 'xlsx'
            ? $this->readXlsx($path)
            : $this->readCsv($path);
    }

    /**
     * @return array<int, array<int, string|null>>
     */
    private function readCsv(string $path): array
    {
        if (! is_file($path) || filesize($path) <= 0) {
            return [];
        }

        $content = file_get_contents($path);

        if ($content === false || trim($content) === '') {
            return [];
        }

        $content = preg_replace('/^\xEF\xBB\xBF/', '', $content);

        if (str_starts_with($content, "\xFF\xFE") || str_starts_with($content, "\xFE\xFF")) {
            $content = mb_convert_encoding($content, 'UTF-8', 'UTF-16');
        }

        $tmp = tmpfile();

        if (! $tmp) {
            return [];
        }

        fwrite($tmp, $content);
        rewind($tmp);

        $rows = [];

        while (($row = fgetcsv($tmp, 0, ',')) !== false) {
            if ($row === [null] || $row === false) {
                continue;
            }

            $rows[] = array_map(fn ($value) => is_string($value) ? trim($value) : $value, $row);
        }

        fclose($tmp);

        return array_values(array_filter($rows, function (array $row) {
            return collect($row)->filter(fn ($value) => trim((string) $value) !== '')->isNotEmpty();
        }));
    }

    /**
     * Reads the first worksheet directly from the XLSX package so the import
     * needs no spreadsheet library.
     *
     * @return array<int, array<int, string>>
     */
    private function readXlsx(string $path): array
    {
        if (! class_exists(ZipArchive::class)) {
            throw new RuntimeException('ZipArchive extension is required for XLSX import. Save the Excel file as CSV and import again.');
        }

        $zip = new ZipArchive;

        if ($zip->open($path) !== true) {
            throw new RuntimeException('Unable to open XLSX file.');
        }

        $sharedStrings = $this->sharedStrings($zip);
        $sheetXml = $zip->getFromName('xl/worksheets/sheet1.xml');
        $zip->close();

        if ($sheetXml === false) {
            throw new RuntimeException('First worksheet not found in XLSX file.');
        }

        return $this->sheetRows(simplexml_load_string($sheetXml), $sharedStrings);
    }

    /**
     * @return array<int, string>
     */
    private function sharedStrings(ZipArchive $zip): array
    {
        $sharedXml = $zip->getFromName('xl/sharedStrings.xml');

        if ($sharedXml === false) {
            return [];
        }

        $xml = simplexml_load_string($sharedXml);
        $strings = [];

        foreach ($xml->si ?? [] as $si) {
            $text = '';

            if (isset($si->t)) {
                $text = (string) $si->t;
            } elseif (isset($si->r)) {
                foreach ($si->r as $run) {
                    $text .= (string) $run->t;
                }
            }

            $strings[] = $text;
        }

        return $strings;
    }

    /**
     * @param  array<int, string>  $sharedStrings
     * @return array<int, array<int, string>>
     */
    private function sheetRows(mixed $sheet, array $sharedStrings): array
    {
        $rows = [];

        foreach ($sheet->sheetData->row ?? [] as $rowNode) {
            $row = [];

            foreach ($rowNode->c as $cell) {
                $index = $this->columnIndex((string) $cell['r']);
                $type = (string) $cell['t'];

                $row[$index] = match ($type) {
                    's' => $sharedStrings[(int) $cell->v] ?? '',
                    'inlineStr' => (string) ($cell->is->t ?? ''),
                    default => (string) ($cell->v ?? ''),
                };
            }

            if ($row === []) {
                continue;
            }

            ksort($row);
            $maxIndex = max(array_keys($row));
            $filledRow = [];

            for ($i = 0; $i <= $maxIndex; $i++) {
                $filledRow[] = $row[$i] ?? '';
            }

            $rows[] = $filledRow;
        }

        return $rows;
    }

    private function columnIndex(string $cellReference): int
    {
        preg_match('/^[A-Z]+/', strtoupper($cellReference), $matches);
        $letters = $matches[0] ?? 'A';
        $index = 0;

        for ($i = 0; $i < strlen($letters); $i++) {
            $index = ($index * 26) + (ord($letters[$i]) - 64);
        }

        return $index - 1;
    }
}
