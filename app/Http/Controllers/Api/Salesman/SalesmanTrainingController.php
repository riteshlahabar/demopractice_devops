<?php

namespace App\Http\Controllers\Api\Salesman;

use App\Models\Hr\TrainingAttendance;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * The salesman's own training enrolments. Attendance and certificates are
 * recorded by HR/admin; the salesman only views them and downloads a
 * certificate once issued. The file path is never exposed in the JSON — same
 * rule as employee documents — only a boolean and the download route.
 */
final class SalesmanTrainingController extends SalesmanApiController
{
    private const DISK = 'local';

    public function index(Request $request): JsonResponse
    {
        $attendances = TrainingAttendance::query()
            ->with('program')
            ->where('salesman_id', $this->salesman($request)->id)
            ->whereHas('program')
            ->get();

        return $this->success([
            'trainings' => $attendances->map(fn (TrainingAttendance $attendance): array => [
                'id' => $attendance->id,
                'title' => $attendance->program->title,
                'trainer' => $attendance->program->trainer,
                'mode' => $attendance->program->mode,
                'venue' => $attendance->program->venue,
                'starts_on' => $attendance->program->starts_on?->toDateString(),
                'ends_on' => $attendance->program->ends_on?->toDateString(),
                'status' => $attendance->status,
                'score' => $attendance->score !== null ? (float) $attendance->score : null,
                'certificate_issued' => (bool) $attendance->certificate_issued,
                'remarks' => $attendance->remarks,
            ])->all(),
        ]);
    }

    public function certificate(Request $request, TrainingAttendance $attendance): StreamedResponse
    {
        $user = $this->salesman($request);

        abort_unless((int) $attendance->salesman_id === (int) $user->id, 403);

        $disk = Storage::disk(self::DISK);
        abort_unless($attendance->certificate_path && $disk->exists($attendance->certificate_path), 404);

        $name = 'certificate-'.$attendance->training_program_id.'.'.pathinfo($attendance->certificate_path, PATHINFO_EXTENSION);

        return $disk->download($attendance->certificate_path, $name);
    }
}
