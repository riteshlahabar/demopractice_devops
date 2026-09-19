<?php

namespace App\Http\Controllers\Api\Salesman;

use App\Models\Hr\EmployeeSkill;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Read-only skill/certification records for the salesman. These are entered
 * by HR after an assessment or a training program, never by the salesman.
 */
final class SalesmanSkillController extends SalesmanApiController
{
    public function index(Request $request): JsonResponse
    {
        $skills = EmployeeSkill::query()
            ->where('salesman_id', $this->salesman($request)->id)
            ->orderBy('skill')
            ->get();

        return $this->success(['skills' => $skills]);
    }
}
