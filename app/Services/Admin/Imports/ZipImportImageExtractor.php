<?php

namespace App\Services\Admin\Imports;

use App\Contracts\Admin\Imports\ImportImageArchiveContract;
use App\Contracts\Admin\Imports\ImportImagePathContract;
use Illuminate\Http\UploadedFile;
use RuntimeException;
use ZipArchive;

final class ZipImportImageExtractor implements ImportImageArchiveContract
{
    /**
     * Only these land under public/. Executable types are excluded, and so is
     * SVG, which can carry script and would be served from our own origin.
     */
    private const ALLOWED_EXTENSIONS = ['jpg', 'jpeg', 'png', 'webp', 'gif'];

    private const IMAGE_COLUMNS = ['image_path', 'primary_image', 'product_image', 'gallery_images', 'icon_path'];

    public function __construct(private readonly ImportImagePathContract $paths) {}

    public function extract(?UploadedFile $archive, array $headers, array $rows, string $module): void
    {
        if (! $archive) {
            return;
        }

        if (! class_exists(ZipArchive::class)) {
            throw new RuntimeException('ZipArchive extension is required for image ZIP upload.');
        }

        $zipPath = $archive->getRealPath();
        $zip = new ZipArchive;

        if ($zip->open($zipPath) !== true) {
            throw new RuntimeException('Unable to open image ZIP file.');
        }

        $wanted = $this->wantedImages($headers, $rows, $module);

        for ($i = 0; $i < $zip->numFiles; $i++) {
            $entry = trim(str_replace('\\', '/', $zip->getNameIndex($i)), '/');

            if ($entry === '' || str_ends_with($entry, '/') || str_contains($entry, '..')) {
                continue;
            }

            $fileName = basename($entry);

            if (! in_array(strtolower(pathinfo($fileName, PATHINFO_EXTENSION)), self::ALLOWED_EXTENSIONS, true)) {
                continue;
            }

            if (! isset($wanted[$fileName])) {
                continue;
            }

            $targetFullPath = public_path($wanted[$fileName]);
            $targetDir = dirname($targetFullPath);

            if (! is_dir($targetDir)) {
                mkdir($targetDir, 0775, true);
            }

            copy('zip://'.$zipPath.'#'.$entry, $targetFullPath);
        }

        $zip->close();
    }

    /**
     * Only files actually referenced by the spreadsheet are unpacked, keyed by
     * base name so the ZIP's own folder layout does not matter.
     *
     * @return array<string, string>
     */
    private function wantedImages(array $headers, array $rows, string $module): array
    {
        $wanted = [];

        foreach ($rows as $values) {
            $values = array_pad($values, count($headers), null);
            $row = array_combine($headers, array_slice($values, 0, count($headers)));

            if (! $row) {
                continue;
            }

            foreach (self::IMAGE_COLUMNS as $column) {
                if (empty($row[$column])) {
                    continue;
                }

                $imageValues = $column === 'gallery_images'
                    ? (preg_split('/[|;]/', (string) $row[$column]) ?: [])
                    : [(string) $row[$column]];

                foreach ($imageValues as $imageValue) {
                    $targetPath = $this->paths->normalize((string) $imageValue, $module);

                    if ($targetPath !== '') {
                        $wanted[basename($targetPath)] = $targetPath;
                    }
                }
            }
        }

        return $wanted;
    }
}
