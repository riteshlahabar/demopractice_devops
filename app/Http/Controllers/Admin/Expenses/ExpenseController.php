<?php

namespace App\Http\Controllers\Admin\Expenses;

use App\Http\Controllers\Admin\Concerns\AdminModuleController;
use App\Models\Field\Expense;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ExpenseController extends AdminModuleController
{
    protected string $moduleKey = 'expenses';

    public function decision(Request $request, int|string $id): RedirectResponse
    {
        $data = $request->validate(['status' => ['required', Rule::in(['approved', 'rejected'])]]);
        Expense::findOrFail($id)->update(['status' => $data['status'], 'approved_by' => auth()->id(), 'approved_at' => now()]);

        return back()->with('success', 'Expense '.$data['status'].'.');
    }
}
