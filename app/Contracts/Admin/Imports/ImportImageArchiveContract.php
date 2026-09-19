<?php

namespace App\Contracts\Admin\Imports;

use Illuminate\Http\UploadedFile;

/**
 * SRP: unpacking the optional images ZIP that accompanies an import file.
 */
interface ImportImageArchiveContract
{
    /**
     * @param  array<int, string>  $headers
     * @param  array<int, array<int, string|null>>  $rows
     */
    public function extract(?UploadedFile $archive, array $headers, array $rows, string $module): void;
}
