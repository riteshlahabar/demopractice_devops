<?php

namespace App\Http\Controllers\Admin\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class LoginController extends Controller
{
    public function create(): View
    {
        return view('admin.auth.login');
    }

    public function store(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        $remember = $request->boolean('remember');
        if (! Auth::attempt($credentials, $remember)) {
            return back()->withErrors(['email' => 'The email address or password is incorrect.'])->onlyInput('email');
        }

        $request->session()->regenerate();
        $user = $request->user();
        if (! $user || $user->role !== User::ROLE_ADMIN || $user->status !== 'active') {
            Auth::logout();

            return back()->withErrors(['email' => 'This account is not authorized for the admin panel.']);
        }

        $user->forceFill(['last_login_at' => now()])->save();

        return redirect()->intended(route('admin.dashboard'));
    }

    public function destroy(Request $request): RedirectResponse
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('admin.login')->with('success', 'You have been logged out.');
    }
}
