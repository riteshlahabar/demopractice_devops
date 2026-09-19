<?php

namespace App\Http\Controllers\Api\Admin;

use App\Models\Field\SalesmanAsset;
use App\Models\SalesmanProfile;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

/**
 * Dealer approval and assignment, plus salesman onboarding.
 */
final class AdminPeopleController extends AdminApiController
{
    public function createSalesman(Request $request): JsonResponse
    {
        $this->admin($request);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'unique:users,email'],
            'mobile' => ['nullable', 'string', 'max:20', 'unique:users,mobile'],
            'password' => ['required', 'string', 'min:8'],
            'employee_code' => ['required', 'string', 'max:50', 'unique:salesman_profiles,employee_code'],
            'territory' => ['nullable', 'string', 'max:255'],
            'basic_salary' => ['nullable', 'numeric'],
            'target_amount' => ['nullable', 'numeric'],
        ]);

        $salesman = User::query()->create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'mobile' => $validated['mobile'] ?? null,
            'password' => Hash::make($validated['password']),
            'role' => User::ROLE_SALESMAN,
            'status' => 'active',
        ]);

        SalesmanProfile::query()->create([
            'user_id' => $salesman->id,
            'employee_code' => $validated['employee_code'],
            'territory' => $validated['territory'] ?? null,
            'basic_salary' => $validated['basic_salary'] ?? 0,
            'target_amount' => $validated['target_amount'] ?? 0,
        ]);

        return $this->success(['salesman' => $salesman->load('salesmanProfile')], 'Salesman created.', 201);
    }

    public function dealers(Request $request): JsonResponse
    {
        $this->admin($request);

        $dealers = User::query()
            ->with('dealerProfile.salesman')
            ->where('role', User::ROLE_DEALER)
            ->latest()
            ->paginate(20);

        return $this->success(['dealers' => $dealers]);
    }

    public function approveDealer(Request $request, User $dealer): JsonResponse
    {
        $admin = $this->admin($request);

        $validated = $request->validate([
            'salesman_id' => ['required', 'integer', 'exists:users,id'],
            'credit_limit' => ['nullable', 'numeric'],
        ]);

        $salesman = $this->salesmanOrFail($validated['salesman_id']);

        if ($dealer->role !== User::ROLE_DEALER) {
            return $this->fail('Selected user is not a dealer.');
        }

        $dealer->forceFill(['status' => 'active'])->save();
        $dealer->dealerProfile()->update([
            'salesman_id' => $salesman->id,
            'credit_limit' => $validated['credit_limit'] ?? $dealer->dealerProfile?->credit_limit ?? 0,
            'approved_at' => now(),
            'approved_by' => $admin->id,
        ]);

        return $this->success(['dealer' => $dealer->fresh('dealerProfile.salesman')], 'Dealer approved and assigned.');
    }

    public function assignDealer(Request $request, User $dealer): JsonResponse
    {
        $this->admin($request);

        $validated = $request->validate(['salesman_id' => ['required', 'integer', 'exists:users,id']]);
        $salesman = $this->salesmanOrFail($validated['salesman_id']);

        if ($dealer->role !== User::ROLE_DEALER) {
            return $this->fail('Selected user is not a dealer.');
        }

        $dealer->dealerProfile()->update(['salesman_id' => $salesman->id]);

        return $this->success(['dealer' => $dealer->fresh('dealerProfile.salesman')], 'Dealer assigned.');
    }

    public function assignAsset(Request $request, User $salesman): JsonResponse
    {
        $this->admin($request);

        if ($salesman->role !== User::ROLE_SALESMAN) {
            return $this->fail('Selected user is not a salesman.');
        }

        $validated = $request->validate([
            'asset_type' => ['required', 'string', 'max:40'],
            'asset_name' => ['required', 'string', 'max:255'],
            'serial_no' => ['nullable', 'string', 'max:255'],
            'issued_on' => ['nullable', 'date'],
            'condition' => ['nullable', 'string', 'max:255'],
        ]);

        $asset = SalesmanAsset::query()->create($validated + ['salesman_id' => $salesman->id]);

        return $this->success(['asset' => $asset], 'Salesman asset assigned.', 201);
    }

    private function salesmanOrFail(int|string $id): User
    {
        return User::query()->where('role', User::ROLE_SALESMAN)->findOrFail($id);
    }
}
