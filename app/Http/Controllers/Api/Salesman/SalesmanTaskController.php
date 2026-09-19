<?php

namespace App\Http\Controllers\Api\Salesman;

use App\Models\Hr\Task;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Tasks assigned to the salesman. The salesman can move a task through its
 * own status but never reassign it or change who assigned it.
 */
final class SalesmanTaskController extends SalesmanApiController
{
    public function index(Request $request): JsonResponse
    {
        $tasks = Task::query()
            ->with('dealer:id,name')
            ->where('assigned_to', $this->salesman($request)->id)
            ->when($request->filled('status'), fn ($query) => $query->where('status', $request->string('status')))
            ->orderByRaw("field(status, 'pending', 'in_progress', 'completed', 'cancelled')")
            ->orderBy('due_date')
            ->get();

        return $this->success(['tasks' => $tasks]);
    }

    public function update(Request $request, Task $task): JsonResponse
    {
        $user = $this->salesman($request);

        if ((int) $task->assigned_to !== (int) $user->id) {
            return $this->fail('Task not assigned to this salesman.', 403);
        }

        $validated = $request->validate([
            'status' => ['required', 'in:pending,in_progress,completed,cancelled'],
            'completion_notes' => ['nullable', 'string'],
        ]);

        $task->status = $validated['status'];
        $task->completion_notes = $validated['completion_notes'] ?? $task->completion_notes;
        $task->completed_at = $validated['status'] === 'completed' ? now() : null;
        $task->save();

        return $this->success(['task' => $task->fresh('dealer:id,name')], 'Task updated.');
    }
}
