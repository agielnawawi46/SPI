<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\Department;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Spatie\Permission\Models\Role;

class ProfileCompletionController extends Controller
{
    /**
     * Roles selectable during self-onboarding (excludes 'admin' unless the user already holds it).
     */
    protected function getSelectableRoles(User $user): Collection
    {
        $orderedNames = ['manager', 'asman', 'staff', 'kspi', 'auditor', 'direktur', 'komisaris'];

        if ($user->hasRole('admin')) {
            $orderedNames[] = 'admin';
        }

        $rolesByName = Role::whereIn('name', $orderedNames)->get()->keyBy('name');

        return collect($orderedNames)
            ->map(fn (string $name) => $rolesByName->get($name))
            ->filter()
            ->values();
    }

    /**
     * Display the profile completion onboarding form.
     */
    public function show(): View|RedirectResponse
    {
        $user = Auth::user();

        // If already completed, go directly to dashboard
        if ($user->isProfileComplete()) {
            return redirect()->route('dashboard');
        }

        $roles = $this->getSelectableRoles($user);
        $departments = Department::getStructuredForSelector();
        $lainDeptId = Department::where('code', 'LAIN')->first()?->id ?? 8;

        // Only pre-select existing role if the user was pre-configured with a department or executive/admin role.
        // Brand-new self-registered users start with an empty role so they choose Role first, then Bidang.
        $initialRole = (!empty($user->department_id) || $user->hasAnyRole(['direktur', 'komisaris', 'admin']))
            ? ($user->roles->first()?->name ?? '')
            : '';

        return view('auth.complete-profile', compact('user', 'roles', 'departments', 'initialRole', 'lainDeptId'));
    }

    /**
     * Save the completed profile data.
     */
    public function store(Request $request): RedirectResponse
    {
        $user = Auth::user();

        $allowedRoleNames = $this->getSelectableRoles($user)->pluck('name')->all();
        $selectedRole = strtolower(trim((string) $request->input('role', '')));
        $isStandaloneRole = in_array($selectedRole, ['direktur', 'komisaris'], true);
        $lainDept = Department::where('code', 'LAIN')->first();

        $rules = [
            'nip' => [
                'required',
                'string',
                'max:50',
                Rule::unique('users', 'nip')->ignore($user->id),
            ],
            'role' => [
                'required',
                'string',
                Rule::in($allowedRoleNames),
            ],
            'department_id' => $isStandaloneRole
                ? ['nullable']
                : ['required', 'exists:departments,id'],
            'custom_department' => [
                'nullable',
                'string',
                'max:255',
                Rule::requiredIf(
                    fn () => !$isStandaloneRole
                        && $lainDept
                        && (string) $request->input('department_id') === (string) $lainDept->id
                ),
            ],
            'position' => [
                'nullable',
                'string',
                'max:150',
            ],
        ];

        $validated = $request->validate($rules, [
            'nip.required'               => 'Nomor Induk Pegawai (NIP) wajib diisi.',
            'nip.unique'                 => 'NIP tersebut sudah digunakan oleh pengguna lain.',
            'role.required'              => 'Silakan pilih Role / Peran Anda terlebih dahulu.',
            'role.in'                    => 'Role yang dipilih tidak valid.',
            'department_id.required'     => 'Silakan pilih Bidang / Departemen sesuai Role Anda.',
            'department_id.exists'       => 'Bidang / Departemen yang dipilih tidak valid.',
            'custom_department.required' => 'Silakan masukkan nama bidang / unit kerja baru.',
        ]);

        $departmentId = $isStandaloneRole ? null : ($validated['department_id'] ?? null);

        if (!$isStandaloneRole && $lainDept && (string) $departmentId === (string) $lainDept->id && $request->filled('custom_department')) {
            $customDeptName = trim($validated['custom_department']);
            $code = strtoupper(substr(preg_replace('/[^A-Za-z0-9]/', '', $customDeptName), 0, 4));
            if (empty($code)) {
                $code = 'CUST';
            }

            $roleLevel = in_array($validated['role'], ['manager', 'asman', 'staff'], true) ? $validated['role'] : 'staff';
            $newDept = Department::firstOrCreate(
                ['name' => $customDeptName],
                ['code' => $code, 'description' => $customDeptName, 'level' => $roleLevel, 'is_active' => true]
            );
            $departmentId = $newDept->id;
        }

        DB::transaction(function () use ($user, $validated, $departmentId) {
            $user->update([
                'nip'           => trim($validated['nip']),
                'department_id' => $departmentId,
                'position'      => !empty($validated['position']) ? trim($validated['position']) : null,
            ]);

            $user->syncRoles([$validated['role']]);
        });

        // Record Audit Log safely
        try {
            AuditLog::create([
                'user_id'        => $user->id,
                'action'         => 'update',
                'description'    => "User melengkapi profil identitas mandiri (NIP: {$validated['nip']}, Role: {$validated['role']})",
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
