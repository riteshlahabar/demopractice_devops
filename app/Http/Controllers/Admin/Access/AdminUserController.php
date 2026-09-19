<?php

namespace App\Http\Controllers\Admin\Access;

use App\Contracts\Admin\Access\AdminAccountGuardContract;
use App\Http\Controllers\Admin\Concerns\AdminModuleController;
use App\Models\User;
use App\Support\Admin\Modules\AdminModuleServices;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

/**
 * Admin panel staff accounts. Always saved with role `admin`; what they can
 * open comes from their admin role.
 */
class AdminUserController extends AdminModuleController
{
    protected string $moduleKey = 'admin-users';

    public function __construct(AdminModuleServices $modules, private readonly AdminAccountGuardContract $guard)
    {
        parent::__construct($modules);
    }

    protected function prepareData(array $validated, Request $request, array $module): array
    {
        return ['role' => User::ROLE_ADMIN] + parent::prepareData($validated, $request, $module);
    }

    protected function persist(array $data, ?Model $record): Model
    {
        $error = $this->guard->checkSave($record instanceof User ? $record : null, $data, $this->actor());
        if ($error) {
            throw ValidationException::withMessages(['admin_role_id' => $error]);
        }

        return parent::persist($data, $record);
    }

    public function destroy(int|string $id): RedirectResponse
    {
        $error = $this->guard->checkDelete($this->findRecord($id), $this->actor());

        return $error ? back()->with('error', $error) : parent::destroy($id);
    }

    public function bulkDestroy(Request $request): RedirectResponse
    {
        $ids = array_map('intval', (array) $request->input('selected_ids', []));
        foreach ($this->recordsQuery($this->module())->whereKey($ids)->get() as $record) {
            if ($error = $this->guard->checkDelete($record, $this->actor())) {
                return back()->with('error', $record->name.': '.$error);
            }
        }

        return parent::bulkDestroy($request);
    }

    private function actor(): User
    {
        $user = auth()->user();
        abort_unless($user instanceof User, 403);

        return $user;
    }
}
