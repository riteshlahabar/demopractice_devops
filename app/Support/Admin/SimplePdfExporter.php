<?php

namespace App\Support\Admin;

class SimplePdfExporter
{
    public static function table(string $title, array $headers, array $rows): string
    {
        $pages = [];
        $lines = [];
        $lines[] = $title;
        $lines[] = str_repeat('-', min(96, max(24, strlen($title))));
        $lines[] = self::line($headers);
        $lines[] = str_repeat('-', 96);

        foreach ($rows as $row) {
            $lines[] = self::line($row);
        }

        foreach (array_chunk($lines, 42) as $chunk) {
            $pages[] = implode("\n", $chunk);
        }

        if ($pages === []) {
            $pages[] = $title."\nNo records found.";
        }

        return self::document($pages);
    }

    private static function line(array $values): string
    {
        $parts = [];

        foreach ($values as $value) {
            $parts[] = str_pad(self::clean((string) $value), 18);
        }

        return substr(implode(' ', $parts), 0, 112);
    }

    private static function clean(string $value): string
    {
        $value = preg_replace('/\s+/', ' ', strip_tags($value)) ?? '';

        return mb_strimwidth($value, 0, 18, '...');
    }

    private static function document(array $pages): string
    {
        $objects = [];
        $objects[] = '<< /Type /Catalog /Pages 2 0 R >>';

        $pageRefs = [];
        $contentObjectNumber = 3;
        $fontObjectNumber = 3 + (count($pages) * 2);

        foreach ($pages as $index => $page) {
            $pageNumber = $contentObjectNumber++;
            $streamNumber = $contentObjectNumber++;
            $pageRefs[] = $pageNumber.' 0 R';
            $objects[$pageNumber - 1] = '<< /Type /Page /Parent 2 0 R /MediaBox [0 0 842 595] /Resources << /Font << /F1 '.$fontObjectNumber.' 0 R >> >> /Contents '.$streamNumber.' 0 R >>';
            $stream = self::pageStream($page, $index + 1, count($pages));
            $objects[$streamNumber - 1] = '<< /Length '.strlen($stream).' >>'."\nstream\n".$stream."\nendstream";
        }

        $objects[1] = '<< /Type /Pages /Kids ['.implode(' ', $pageRefs).'] /Count '.count($pageRefs).' >>';
        $objects[$fontObjectNumber - 1] = '<< /Type /Font /Subtype /Type1 /BaseFont /Helvetica >>';
        ksort($objects);

        $pdf = "%PDF-1.4\n";
        $offsets = [0];

        foreach ($objects as $number => $object) {
            $objectNumber = $number + 1;
            $offsets[$objectNumber] = strlen($pdf);
            $pdf .= $objectNumber." 0 obj\n".$object."\nendobj\n";
        }

        $xrefOffset = strlen($pdf);
        $pdf .= "xref\n0 ".(count($objects) + 1)."\n0000000000 65535 f \n";
        for ($i = 1; $i <= count($objects); $i++) {
            $pdf .= sprintf('%010d 00000 n ', $offsets[$i])."\n";
        }

        $pdf .= "trailer\n<< /Size ".(count($objects) + 1)." /Root 1 0 R >>\nstartxref\n".$xrefOffset."\n%%EOF";

        return $pdf;
    }

    private static function pageStream(string $text, int $page, int $pages): string
    {
        $commands = [
            'BT',
            '/F1 9 Tf',
            '40 555 Td',
        ];

        foreach (explode("\n", $text) as $index => $line) {
            if ($index > 0) {
                $commands[] = '0 -12 Td';
            }

            $commands[] = '('.self::escape($line).') Tj';
        }

        $commands[] = '0 -20 Td';
        $commands[] = '(Page '.$page.' of '.$pages.') Tj';
        $commands[] = 'ET';

        return implode("\n", $commands);
    }

    private static function escape(string $value): string
    {
        return str_replace(['\\', '(', ')'], ['\\\\', '\\(', '\\)'], $value);
    }
}
