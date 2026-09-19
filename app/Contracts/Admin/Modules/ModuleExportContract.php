<?php

namespace App\Contracts\Admin\Modules;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Symfony\Component\HttpFoundation\Response;

/**
 * SRP: rendering a module listing as a downloadable Excel or PDF file.
 */
interface ModuleExportContract
{
    /**
     * @param  array<string, mixed>  $module
     * @param  Collection<int, Model>  $records
     */
    public function download(string $format, string $title, array $module, Collection $records): Response;
}
