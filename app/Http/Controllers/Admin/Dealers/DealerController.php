<?php

namespace App\Http\Controllers\Admin\Dealers;

use App\Http\Controllers\Admin\Concerns\PeopleModuleController;
use App\Models\DealerProfile;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class DealerController extends PeopleModuleController
{
    protected string $moduleKey = 'dealers';

    protected string $role = User::ROLE_DEALER;

    protected string $profileRelation = 'dealerProfile';

    protected string $profileModel = DealerProfile::class;

    protected array $profileFields = ['salesman_id', 'dealer_code', 'firm_name', 'gst_number', 'credit_limit', 'outstanding_balance'];

    public function approve(Request $request, int|string $id): RedirectResponse
    {
        $dealer = User::query()->where('role', User::ROLE_DEALER)->with('dealerProfile')->findOrFail($id);
        $data = $request->validate(['salesman_id' => ['required', 'exists:users,id'], 'credit_limit' => ['nullable', 'numeric', 'min:0']]);
        User::query()->whereKey($data['salesman_id'])->where('role', User::ROLE_SALESMAN)->firstOrFail();
        $dealer->forceFill(['status' => 'active'])->save();
        $dealer->dealerProfile()->update([
            'salesman_id' => $data['salesman_id'],
            'credit_limit' => $data['credit_limit'] ?? $dealer->dealerProfile->credit_limit,
            'approved_at' => now(),
            'approved_by' => auth()->id(),
        ]);

        return back()->with('success', 'Dealer approved and assigned successfully.');
    }
}
