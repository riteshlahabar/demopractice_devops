<?php

namespace App\Http\Controllers\Storefront;

use App\Contracts\Location\UserLocationContract;
use App\Contracts\Storefront\Session\StorefrontIdentitySessionContract;
use App\Http\Controllers\Controller;
use App\Models\CustomerProfile;
use App\Models\DealerProfile;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class StorefrontAuthController extends Controller
{
    public function __construct(
        private readonly StorefrontIdentitySessionContract $identity,
        private readonly UserLocationContract $location,
    ) {}

    public function login(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'role' => ['required', Rule::in([User::ROLE_CUSTOMER, User::ROLE_DEALER])],
            'login' => ['required', 'string', 'max:255'],
            'password' => ['required', 'string'],
            'redirect_to' => ['nullable', 'string', 'max:2048'],
        ], [
            'role.required' => 'Please select account type.',
            'role.in' => 'Please choose a valid account type.',
            'login.required' => 'Please enter email or mobile number.',
            'password.required' => 'Please enter password.',
        ]);

        $loginValue = trim((string) $validated['login']);
        $normalizedEmail = str_contains($loginValue, '@') ? strtolower($loginValue) : null;
        $normalizedMobile = preg_replace('/\D+/', '', $loginValue) ?: $loginValue;

        $user = User::query()
            ->with(['dealerProfile.salesman', 'customerProfile'])
            ->where('role', $validated['role'])
            ->where(function ($query) use ($normalizedEmail, $normalizedMobile): void {
                if ($normalizedEmail) {
                    $query->where('email', $normalizedEmail);
                }

                $query->orWhere('mobile', $normalizedMobile);
            })
            ->first();

        if (! $user || ! Hash::check($validated['password'], $user->password)) {
            return back()
                ->withInput($request->except('password'))
                ->withErrors(['login' => 'Invalid login credentials.']);
        }

        if ($user->role === User::ROLE_DEALER && $user->status !== 'active') {
            return back()
                ->withInput($request->except('password'))
                ->withErrors(['login' => 'Dealer account is pending admin approval.']);
        }

        if ($user->status !== 'active') {
            return back()
                ->withInput($request->except('password'))
                ->withErrors(['login' => 'This account is not active.']);
        }

        $user->forceFill(['last_login_at' => now()])->save();
        $this->identity->login($request, $user);

        return redirect()->to($request->input('redirect_to', route('store.page', ['page' => 'user-dashboard'])))
            ->with('success', 'Welcome back, '.$user->name.'.');
    }

    public function register(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'role' => ['required', Rule::in([User::ROLE_CUSTOMER, User::ROLE_DEALER])],
            'name' => ['required', 'string', 'min:3', 'max:255'],
            'email' => ['required', 'email:rfc', 'max:255', 'unique:users,email'],
            'mobile' => ['required', 'regex:/^[0-9]{10}$/', 'unique:users,mobile'],
            'password' => ['required', 'string', 'min:8', 'max:64', 'confirmed'],
            'password_confirmation' => ['required_with:password'],
            'firm_name' => ['nullable', 'string', 'max:255', 'required_if:role,dealer'],
            'gst_number' => ['nullable', 'string', 'max:30'],
            'accept_terms' => ['accepted'],
            'redirect_to' => ['nullable', 'string', 'max:2048'],
        ] + $this->location->rules(), [
            'role.required' => 'Please select account type.',
            'role.in' => 'Please choose a valid account type.',
            'state_code.required' => 'Please select state.',
            'district_code.required' => 'Please select district.',
            'subdistrict_code.required' => 'Please select taluka.',
            'subdistrict_name.required_if' => 'Please type the taluka name.',
            'city_village.required' => 'Please enter city or village.',
            'pincode.required' => 'Please enter pincode.',
            'pincode.regex' => 'Pincode must be 6 digits.',
            'name.required' => 'Please enter full name.',
            'name.min' => 'Full name must be at least 3 characters.',
            'email.required' => 'Please enter email address.',
            'email.email' => 'Please enter a valid email address.',
            'email.unique' => 'This email is already registered.',
            'mobile.required' => 'Please enter mobile number.',
            'mobile.regex' => 'Mobile number must be 10 digits.',
            'mobile.unique' => 'This mobile number is already registered.',
            'password.required' => 'Please enter password.',
            'password.min' => 'Password must be at least 8 characters.',
            'password.confirmed' => 'Password and confirm password must match.',
            'password_confirmation.required_with' => 'Please confirm password.',
            'firm_name.required_if' => 'Firm name is required for dealer signup.',
            'accept_terms.accepted' => 'Please accept terms and privacy policy.',
        ]);

        $validated['email'] = strtolower(trim((string) $validated['email']));
        $validated['mobile'] = preg_replace('/\D+/', '', (string) $validated['mobile']);
        $validated['name'] = trim((string) $validated['name']);
        $validated['firm_name'] = isset($validated['firm_name']) ? trim((string) $validated['firm_name']) : null;
        $validated['gst_number'] = isset($validated['gst_number']) ? strtoupper(trim((string) $validated['gst_number'])) : null;

        $isDealer = $validated['role'] === User::ROLE_DEALER;
        $location = $this->location->attributes($validated);

        $user = User::query()->create($location + [
            'name' => $validated['name'],
            'email' => $validated['email'],
            'mobile' => $validated['mobile'],
            'password' => $validated['password'],
            'role' => $validated['role'],
            'status' => $isDealer ? 'pending_approval' : 'active',
            'mobile_verified_at' => now(),
        ]);

        if ($isDealer) {
            DealerProfile::query()->create([
                'user_id' => $user->id,
                'dealer_code' => 'DLR'.str_pad((string) $user->id, 6, '0', STR_PAD_LEFT),
                'firm_name' => $validated['firm_name'],
                'gst_number' => $validated['gst_number'] ?? null,
            ]);
        } else {
            CustomerProfile::query()->firstOrCreate(['user_id' => $user->id]);
        }

        $redirectTo = (string) ($request->input('redirect_to') ?: route('store.page', ['page' => 'user-dashboard']));
        $successMessage = $isDealer
            ? 'Dealer registration submitted successfully. You can log in after admin approval.'
            : 'Customer account created successfully. Please log in to continue.';

        return redirect()->route('store.page', [
            'page' => 'login',
            'role' => $validated['role'],
            'redirect_to' => $redirectTo,
        ])->with('success', $successMessage);
    }

    public function logout(Request $request): RedirectResponse
    {
        $this->identity->logout($request);

        return redirect()->route('store.home')->with('success', 'You have been logged out.');
    }
}
