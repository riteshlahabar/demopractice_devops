<?php

namespace App\Http\Controllers\Admin\Hr;

use App\Http\Controllers\Admin\Concerns\AdminModuleController;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * Aadhaar, PAN and bank proofs are kept on the private disk, never under
 * public/, so a file can only be fetched through the admin download action.
 */
class EmployeeDocumentController extends AdminModuleController
{
    protected string $moduleKey = 'employee-documents';

    private const DISK = 'local';

    private const DIRECTORY = 'employee-documents';

    protected function prepareData(array $validated, Request $request, array $module): array
    {
        unset($validated['file_path']);

        if ($request->hasFile('file_path')) {
            $validated['file_path'] = $request->file('file_path')->store(self::DIRECTORY, self::DISK);
        }

        return parent::prepareData($validated, $request, $module);
    }

    protected function persist(array $data, ?Model $record): Model
    {
        $previous = $record?->file_path;
        $saved = parent::persist($data, $record);

        if ($previous && isset($data['file_path']) && $previous !== $data['file_path']) {
            Storage::disk(self::DISK)->delete($previous);
        }

        return $saved;
    }

    public function download(int|string $id): StreamedResponse
    {
        $document = $this->findRecord($id);
        $disk = Storage::disk(self::DISK);
        abort_unless($document->file_path && $disk->exists($document->file_path), 404);

        $name = $document->document_type.'-'.$document->salesman_id.'.'.pathinfo($document->file_path, PATHINFO_EXTENSION);

        return $disk->download($document->file_path, $name);
    }
}
