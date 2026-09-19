<?php

namespace App\Http\Controllers\Admin\Imports;

use App\Contracts\Admin\Imports\ImportFileReaderContract;
use App\Contracts\Admin\Imports\ImportImageArchiveContract;
use App\Contracts\Admin\Imports\ImportRowReaderContract;
use App\Contracts\Admin\Imports\ImportRunnerContract;
use App\Contracts\Admin\Imports\ImportSampleContract;
use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * SRP: HTTP entry point for spreadsheet imports. Parsing, mapping, image
 * handling and the import loop each live in their own service.
 */
class CommonImportController extends Controller
{
    private const ALLOWED_MODULES = [
        'products', 'categories', 'brands', 'units', 'inventory', 'warehouses', 'batches',
        'storefront-banners', 'storefront-sections', 'storefront-section-products',
        'storefront-service-blocks', 'storefront-footer-links',
    ];

    public function __construct(
        private readonly ImportFileReaderContract $files,
        private readonly ImportImageArchiveContract $images,
        private readonly ImportRowReaderContract $reader,
        private readonly ImportRunnerContract $runner,
        private readonly ImportSampleContract $samples,
    ) {}

    public function store(Request $request, string $module): RedirectResponse
    {
        $moduleConfig = $this->moduleConfig($module);
        abort_unless(! empty($moduleConfig['model']), 404);

        $request->validate([
            'import_file' => ['required', 'file', 'mimes:csv,txt,xlsx', 'max:10240'],
            'images_zip' => ['nullable', 'file', 'mimes:zip', 'max:51200'],
        ]);

        $file = $request->file('import_file');
        $rows = $this->files->rows($file->getRealPath(), $file->getClientOriginalExtension());

        if (count($rows) < 2) {
            return back()->with('error', 'Import file is empty or headers are missing. File: '.$file->getClientOriginalName().', Extension: '.$file->getClientOriginalExtension().', Size: '.$file->getSize().' bytes, Rows found: '.count($rows));
        }

        $headers = array_map(fn ($header) => $this->reader->header((string) $header), array_shift($rows));

        $this->images->extract($request->file('images_zip'), $headers, $rows, $module);

        $result = $this->runner->run($module, $moduleConfig, $headers, $rows, $this->forcedValues($request, $module));

        return $result->hasFailures()
            ? back()->with('warning', $result->summary().' First error: '.$result->firstError)
            : back()->with('success', $result->summary());
    }

    public function sample(string $module): Response
    {
        $moduleConfig = $this->moduleConfig($module);

        $sampleFile = public_path('excel/'.$module.'/'.$module.'_sample.csv');
        $fileName = str_replace('-', '_', $module).'_sample.csv';

        if (is_file($sampleFile)) {
            return response()->download($sampleFile, $fileName, ['Content-Type' => 'text/csv']);
        }

        $headers = $this->samples->headers($module, $moduleConfig);
        $row = $this->samples->row($module, $headers);

        return response()->streamDownload(function () use ($headers, $row): void {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, $headers);
            fputcsv($handle, $row);
            fclose($handle);
        }, $fileName, ['Content-Type' => 'text/csv']);
    }

    /**
     * @return array<string, mixed>
     */
    private function moduleConfig(string $module): array
    {
        abort_unless(in_array($module, self::ALLOWED_MODULES, true), 404);

        $moduleConfig = config('admin.modules.'.$module);
        abort_unless($moduleConfig, 404);

        return $moduleConfig;
    }

    /**
     * Values pinned by the submenu the import was started from, applied to
     * every row so the file does not have to repeat them.
     *
     * @return array<string, mixed>
     */
    private function forcedValues(Request $request, string $module): array
    {
        $forced = [];

        if ($request->filled('placement') && $module === 'storefront-banners') {
            $forced['placement'] = $request->query('placement');
        }

        if ($request->filled('section_key') && in_array($module, ['storefront-sections', 'storefront-section-products'], true)) {
            $forced['section_key'] = $request->query('section_key');
        }

        return $forced;
    }
}
