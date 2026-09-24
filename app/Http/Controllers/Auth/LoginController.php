<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class LoginController extends Controller
{
    public function showLoginForm(): View
    {
        if (Auth::check()) {
            return redirect()->route('dashboard');
        }
        return view('auth.login');
    }

    public function login(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'email'    => ['required', 'email'],
            'password' => ['required'],
        ]);

        // Check if user is active before attempting login
        $user = \App\Models\User::where('email', $credentials['email'])->first();
        if ($user && !$user->is_active) {
            return back()
                ->withInput($request->only('email'))
                ->withErrors(['email' => 'Akun Anda tidak aktif. Hubungi Administrator.']);
        }

        if (Auth::attempt($credentials, $request->boolean('remember'))) {
            $request->session()->regenerate();

            // Log login action
            AuditLog::create([
                'user_id'     => Auth::id(),
                'action'      => 'login',
                'description' => 'User berhasil login',
                'ip_address'  => $request->ip(),
                'user_agent'  => $request->userAgent(),
                'url'         => $request->fullUrl(),
            ]);

            return redirect()->intended(route('dashboard'));
        }

        return back()
            ->withInput($request->only('email'))
            ->withErrors(['email' => 'Email atau password tidak sesuai.']);
    }

    public function logout(Request $request): RedirectResponse
    {
        AuditLog::create([
            'user_id'     => Auth::id(),
            'action'      => 'logout',
            'description' => 'User logout',
            'ip_address'  => $request->ip(),
            'user_agent'  => $request->userAgent(),
            'url'         => $request->fullUrl(),
        ]);

        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}
