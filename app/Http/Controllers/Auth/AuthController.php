<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;

class AuthController extends Controller
{
    public function create(): Response
    {
        return Inertia::render('Auth/Login');
    }

    public function store(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        if (! Auth::attempt($credentials, $request->boolean('remember'))) {
            throw ValidationException::withMessages(['email' => 'Email atau kata sandi salah.']);
        }

        $request->session()->regenerate();

        $user = Auth::user();

        if (! $user->isPdAdmin() && ! $user->isVerified()) {
            Auth::logout();
            throw ValidationException::withMessages(['email' => 'Akun tidak aktif. Hubungi administrator.']);
        }

        return match (true) {
            $user->canAccessAdminPortal() => redirect()->intended(route('admin.dashboard')),
            $user->isPdAdmin() && ! $user->isVerified() => redirect()->route('registration.status'),
            $user->isPdAdmin() => redirect()->intended(route('pd.dashboard')),
            in_array($user->role, ['scorekeeper', 'sport_coordinator'], true) => redirect()->intended(route('staff.matches.index')),
            default => redirect()->intended('/'),
        };
    }

    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/')->with('success', 'Berhasil keluar.');
    }
}
