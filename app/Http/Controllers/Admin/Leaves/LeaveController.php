<?php

namespace App\Http\Controllers\Admin\Leaves;

use App\Http\Controllers\Admin\Concerns\AdminModuleController;
use App\Models\Field\LeaveApplication;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class LeaveController extends AdminModuleController
{
    protected string $moduleKey = 'leaves';

    public function decision(Request $request, int|string $id): RedirectResponse
    {
        $data = $request->validate(['status' => ['required', Rule::in(['approved', 'rejected'])]]);
        LeaveApplication::findOrFail($id)->update(['status' => $data['status'], 'approved_by' => auth()->id(), 'approved_at' => now()]);

        return back()->with('success', 'Leave '.$data['status'].'.');
    }
}
