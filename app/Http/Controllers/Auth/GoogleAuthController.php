<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Laravel\Socialite\Facades\Socialite;

class GoogleAuthController extends Controller
{
    /**
     * Redirect user to Google OAuth consent page.
     */
    public function redirectToGoogle(): RedirectResponse
    {
        $clientId = config('services.google.client_id');
        $clientSecret = config('services.google.client_secret');

        if (empty($clientId) || empty($clientSecret)) {
            return redirect()->route('login')->with(
                'error',
                'Kredensial Google OAuth (Client ID & Client Secret) belum dikonfigurasi di file .env server. Silakan hubungi Administrator.'
            );
        }

        $driver = Socialite::driver('google');
        $driver->redirectUrl(url('/auth/google/callback'));

        // Force Google to always show the account chooser ("Pilih akun")
        $params = [
            'prompt' => 'select_account',
        ];

        // Optional domain restriction hint on Google consent screen
        $allowedDomain = config('services.google.allowed_domain');
        if (!empty($allowedDomain)) {
            $params['hd'] = $allowedDomain;
        }

        $driver->with($params);

        return $driver->redirect();
    }

    /**
     * Handle callback from Google OAuth.
     * Implementing Option B: Pre-registered / Role Matching.
     */
    public function handleGoogleCallback(Request $request): RedirectResponse
    {
        // Check for Google error response or user cancellation
        if ($request->has('error')) {
            return redirect()->route('login')->with('error', 'Login Google dibatalkan atau terjadi kesalahan: ' . $request->get('error'));
        }

        try {
            $googleUser = Socialite::driver('google')
                ->redirectUrl(url('/auth/google/callback'))
                ->user();
        } catch (\Throwable $e) {
            return redirect()->route('login')->with('error', 'Gagal memproses autentikasi Google: ' . $e->getMessage());
        }

        $email = strtolower(trim($googleUser->getEmail() ?? ''));

        if (empty($email)) {
            return redirect()->route('login')->with('error', 'Akun Google Anda tidak menyediakan alamat email yang valid.');
        }

        // Domain Validation: Ensure email matches company domain
        $allowedDomain = config('services.google.allowed_domain');
        if (!empty($allowedDomain) && !str_ends_with($email, '@' . $allowedDomain)) {
            return redirect()->route('login')->with(
                'error',
                "Hanya email resmi perusahaan (@{$allowedDomain}) yang diizinkan untuk login ke SIMAK. Akun ({$email}) ditolak."
            );
        }

        // User Lookup or Auto-Register for official company domain (@peb.co.id)
        $user = User::where('email', $email)->first();

        if (!$user) {
            // Auto-create new user with data from Google Workspace SSO
            $user = User::create([
                'name'          => $googleUser->getName() ?: explode('@', $email)[0],
                'email'         => $email,
                'google_id'     => $googleUser->getId(),
                'avatar'        => $googleUser->getAvatar(),
                'password'      => null,
                'is_active'     => true,
                'department_id' => null,
                'position'      => null,
                'nip'           => null,
            ]);

            // Assign default role 'staff' (Auditee)
            try {
                $user->assignRole('staff');
            } catch (\Throwable $e) {
                \Illuminate\Support\Facades\Log::warning('Role staff could not be assigned: ' . $e->getMessage());
            }
        } else {
            // Check if user account is active
            if (!$user->is_active) {
                return redirect()->route('login')->with(
                    'error',
                    'Akun SIMAK Anda sedang dinonaktifkan. Silakan hubungi Administrator SPI.'
                );
            }

            // Update Google ID and Avatar if provided
            $updates = [];
            if ($user->google_id !== $googleUser->getId()) {
                $updates['google_id'] = $googleUser->getId();
            }
            if ($googleUser->getAvatar() && $user->avatar !== $googleUser->getAvatar()) {
                $updates['avatar'] = $googleUser->getAvatar();
            }
            if (!empty($updates)) {
                $user->update($updates);
            }

            // Ensure user has at least 'staff' role if no role is currently assigned
            if ($user->roles()->count() === 0) {
                try {
                    $user->assignRole('staff');
                } catch (\Throwable $e) {}
            }
        }

        // Login user with Remember Me enabled
        Auth::login($user, true);
        $request->session()->regenerate();

        // Record Audit Log safely (use url() instead of fullUrl() to avoid truncating OAuth query params)
        try {
            AuditLog::create([
                'user_id'     => $user->id,
                'action'      => 'login',
                'description' => "User berhasil login via Google Workspace SSO ({$email})",
                'ip_address'  => $request->ip(),
                'user_agent'  => substr($request->userAgent() ?? '', 0, 500),
                'url'         => substr($request->url(), 0, 255),
            ]);
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::warning('Failed to write audit log on Google login: ' . $e->getMessage());
        }

        // Check if user profile is complete (NIP & Department required)
        if (empty($user->nip) || empty($user->department_id)) {
            return redirect()->route('profile.complete')->with(
                'info',
                'Selamat datang di SIMAK, ' . $user->name . '! Silakan lengkapi NIP dan Bidang (Departemen) Anda terlebih dahulu.'
            );
        }

        return redirect()->intended(route('dashboard'))->with('success', 'Selamat datang di SIMAK, ' . $user->name . '!');
    }
}

