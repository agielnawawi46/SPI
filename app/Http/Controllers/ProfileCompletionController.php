<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\Department;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class ProfileCompletionController extends Controller
{
    /**
     * Display the profile completion onboarding form.
     */
    public function show(): View|RedirectResponse
    {
        $user = Auth::user();

        // If already completed, go directly to dashboard
        if (!empty($user->nip) && !empty($user->department_id)) {
            return redirect()->route('dashboard');
        }

        $departments = Department::orderBy('name')->get();

        return view('auth.complete-profile', compact('user', 'departments'));
    }

    /**
     * Save the completed profile data.
     */
    public function store(Request $request): RedirectResponse
    {
        $user = Auth::user();

        $validated = $request->validate([
            'nip' => [
                'required',
                'string',
                'max:50',
                Rule::unique('users', 'nip')->ignore($user->id),
            ],
            'department_id' => [
                'required',
                'exists:departments,id',
            ],
            'position' => [
                'nullable',
                'string',
                'max:150',
            ],
        ], [
            'nip.required'           => 'Nomor Induk Pegawai (NIP) wajib diisi.',
            'nip.unique'             => 'NIP tersebut sudah digunakan oleh pengguna lain.',
            'department_id.required' => 'Silakan pilih Bidang / Departemen Anda.',
            'department_id.exists'   => 'Bidang / Departemen yang dipilih tidak valid.',
        ]);

        $user->update([
            'nip'           => trim($validated['nip']),
            'department_id' => $validated['department_id'],
            'position'      => !empty($validated['position']) ? trim($validated['position']) : null,
        ]);

        // Option 1: Ensure user has at least 'staff' role
        if ($user->roles()->count() === 0) {
            try {
                $user->assignRole('staff');
            } catch (\Throwable $e) {}
        }

        // Record Audit Log safely
        try {
            AuditLog::create([
                'user_id'        => $user->id,
                'action'         => 'update',
                'description'    => "User melengkapi profil identitas mandiri (NIP: {$validated['nip']})",
                'auditable_type' => get_class($user),
                'auditable_id'   => $user->id,
                'ip_address'     => $request->ip(),
                'user_agent'     => substr($request->userAgent() ?? '', 0, 500),
                'url'            => substr($request->url(), 0, 255),
            ]);
        } catch (\Throwable $e) {}

        return redirect()->route('dashboard')->with(
            'success',
            'Profil Anda berhasil dilengkapi! Selamat datang di Sistem Informasi Manajemen Audit & Kepatuhan.'
        );
    }
}

