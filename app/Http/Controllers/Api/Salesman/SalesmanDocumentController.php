<?php

namespace App\Http\Controllers\Api\Salesman;

use App\Http\Controllers\Api\ApiController;
use App\Models\Hr\EmployeeDocument;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * The salesman's own HR documents (Aadhaar, PAN, bank details, letters).
 *
 * Identity numbers are returned masked and the stored file path is never
 * exposed, so a compromised token cannot be used to enumerate documents on
 * disk.
 */
class SalesmanDocumentController extends ApiController
{
    public function index(Request $request): JsonResponse
    {
        $user = $this->requireUser($request, User::ROLE_SALESMAN);
        if ($user instanceof JsonResponse) {
            return $user;
        }

        $documents = EmployeeDocument::query()
            ->where('salesman_id', $user->id)
            ->orderBy('document_type')
            ->get();

        return $this->success([
            'documents' => $documents->map(fn (EmployeeDocument $doc): array => [
                'id' => $doc->id,
                'document_type' => $doc->document_type,
                'document_no' => $doc->masked_document_no,
                'issued_on' => $doc->issued_on?->toDateString(),
                'expires_on' => $doc->expires_on?->toDateString(),
                'status' => $doc->status,
                'remarks' => $doc->remarks,
                'has_file' => $doc->file_path !== null,
            ])->all(),
        ]);
    }
}
