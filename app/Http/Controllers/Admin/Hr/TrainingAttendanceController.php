<?php

namespace App\Http\Controllers\Admin\Hr;

use App\Http\Controllers\Admin\Concerns\AdminModuleController;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * Training certificates are kept on the private disk, never under public/, and
 * are fetched only through the admin download action — the same rule employee
 * documents follow.
 */
class TrainingAttendanceController extends AdminModuleController
{
    protected string $moduleKey = 'training-attendances';

    private const DISK = 'local';

    private const DIRECTORY = 'training-certificates';

    protected function prepareData(array $validated, Request $request, array $module): array
    {
        // A file input posts an UploadedFile, which is not a column value; the
        // stored path replaces it only when a new file actually arrived.
        unset($validated['certificate_path']);

        if ($request->hasFile('certificate_path')) {
            $validated['certificate_path'] = $request->file('certificate_path')->store(self::DIRECTORY, self::DISK);
            $validated['certificate_issued'] = true;
        }

        return parent::prepareData($validated, $request, $module);
    }

    protected function persist(array $data, ?Model $record): Model
    {
        $previous = $record?->certificate_path;
        $saved = parent::persist($data, $record);

        if ($previous && isset($data['certificate_path']) && $previous !== $data['certificate_path']) {
            Storage::disk(self::DISK)->delete($previous);
        }

        return $saved;
    }

    public function download(int|string $id): StreamedResponse
    {
        $attendance = $this->findRecord($id);
        $disk = Storage::disk(self::DISK);
        abort_unless($attendance->certificate_path && $disk->exists($attendance->certificate_path), 404);

        $name = 'certificate-'.$attendance->training_program_id.'-'.$attendance->salesman_id
            .'.'.pathinfo($attendance->certificate_path, PATHINFO_EXTENSION);

        return $disk->download($attendance->certificate_path, $name);
    }
}
