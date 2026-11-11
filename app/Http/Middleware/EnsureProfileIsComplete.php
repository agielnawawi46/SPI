<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsureProfileIsComplete
{
    /**
     * Handle an incoming request.
     * Ensure that authenticated users have completed their profile (NIP and Department).
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (Auth::check()) {
            $user = Auth::user();

            $isProfileIncomplete = empty($user->nip) || empty($user->department_id);

            if ($isProfileIncomplete) {
                // Allowed routes during profile completion
                if (!$request->routeIs('profile.complete', 'profile.complete.store', 'logout')) {
                    if ($request->expectsJson()) {
                        return response()->json([
                            'message' => 'Silakan lengkapi NIP dan Bidang (Departemen) Anda terlebih dahulu.',
                            'redirect' => route('profile.complete'),
                        ], 403);
                    }

                    return redirect()->route('profile.complete')
                        ->with('info', 'Silakan lengkapi NIP dan Bidang (Departemen) Anda terlebih dahulu untuk dapat mengakses SIMAK.');
                }
            } else {
                // If profile is already complete, redirect away from the completion page
                if ($request->routeIs('profile.complete')) {
                    return redirect()->route('dashboard');
                }
            }
        }

        return $next($request);
    }
}

