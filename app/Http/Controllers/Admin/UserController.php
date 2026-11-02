<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Department;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use League\Csv\Reader;
use Spatie\Permission\Models\Role;

class UserController extends Controller
{
    public function index(Request $request)
    {
        $query = User::with(['department', 'roles']);

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "{$search}%")
                  ->orWhere('email', 'like', "{$search}%")
                  ->orWhere('nip', 'like', "{$search}%");
            });
        }

        $users = $query->latest()->paginate(15)->withQueryString();

        return view('admin.users.index', compact('users'));
    }

    protected function getStructuredDepartments()
    {
        return Department::with(['parent.parent'])
            ->where('is_active', true)
            ->get()
            ->map(function ($dept) {
                $groupLabel = 'Lainnya';
                if ($dept->level === 'manager') {
                    $groupLabel = 'Tingkat Manager / Bidang Utama';
                } elseif ($dept->level === 'asman') {
                    $groupLabel = $dept->parent ? $dept->parent->name : 'Unit Khusus (Langsung Direktur)';
                } elseif ($dept->level === 'staff') {
                    if ($dept->parent && $dept->parent->parent) {
                        $groupLabel = $dept->parent->parent->name . ' ➔ ' . $dept->parent->name;
                    } elseif ($dept->parent) {
                        $groupLabel = $dept->parent->name;
                    } else {
                        $groupLabel = 'Unit Staff';
                    }
                } elseif ($dept->level === 'kspi' || $dept->level === 'auditor') {
                    $groupLabel = 'Satuan Pengawasan Intern (SPI)';
                }

                return [
                    'id'             => $dept->id,
                    'name'           => $dept->name,
                    'code'           => $dept->code,
                    'level'          => $dept->level,
                    'parent_id'      => $dept->parent_id,
                    'parent_name'    => $dept->parent?->name ?? '',
                    'hierarchy_path' => $dept->hierarchy_path,
                    'group_label'    => $groupLabel,
                ];
            });
    }

    public function create()
    {
        $roles = Role::all();
        $departments = $this->getStructuredDepartments();
        return view('admin.users.create', compact('roles', 'departments'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'          => 'required|string|max:255',
            'email'         => 'required|string|email|max:255|unique:users',
            'password'      => 'required|string|min:8|confirmed',
            'nip'           => 'nullable|string|max:50|unique:users',
            'position'      => 'nullable|string|max:255',
            'department_id' => 'nullable|exists:departments,id',
            'custom_department' => 'nullable|string|max:255|required_if:department_id,' . (Department::where('code', 'LAIN')->first()?->id ?? -1),
            'role'          => 'required|exists:roles,name',
            'is_active'     => 'boolean',
        ]);

        $lainDept = Department::where('code', 'LAIN')->first();
        if ($lainDept && $request->department_id == $lainDept->id && $request->filled('custom_department')) {
            $customDeptName = $request->custom_department;
            $code = strtoupper(substr(preg_replace('/[^A-Za-z0-9]/', '', $customDeptName), 0, 4));
            if (empty($code)) $code = 'CUST';
            
            $roleLevel = in_array($request->role, ['manager', 'asman', 'staff']) ? $request->role : 'staff';
            $newDept = Department::firstOrCreate(
                ['name' => $customDeptName],
                ['code' => $code, 'description' => $customDeptName, 'level' => $roleLevel, 'is_active' => true]
            );
            $validated['department_id'] = $newDept->id;
        }

        DB::transaction(function () use ($validated) {
            $role = $validated['role'];
            $departmentId = in_array($role, ['direktur', 'komisaris']) ? null : ($validated['department_id'] ?? null);

            $user = User::create([
                'name'          => $validated['name'],
                'email'         => $validated['email'],
                'password'      => Hash::make($validated['password']),
                'nip'           => $validated['nip'] ?? null,
                'position'      => $validated['position'] ?? null,
                'department_id' => $departmentId,
                'is_active'     => $validated['is_active'] ?? true,
            ]);

            $user->assignRole($validated['role']);
        });

        return redirect()->route('admin.users.index')->with('success', 'User berhasil ditambahkan.');
    }

    public function edit(User $user)
    {
        $roles = Role::all();
        $departments = $this->getStructuredDepartments();
        return view('admin.users.edit', compact('user', 'roles', 'departments'));
    }

    public function update(Request $request, User $user)
    {
        $validated = $request->validate([
            'name'          => 'required|string|max:255',
            'email'         => ['required', 'string', 'email', 'max:255', Rule::unique('users')->ignore($user->id)],
            'password'      => 'nullable|string|min:8|confirmed',
            'nip'           => ['nullable', 'string', 'max:50', Rule::unique('users')->ignore($user->id)],
            'position'      => 'nullable|string|max:255',
            'department_id' => 'nullable|exists:departments,id',
            'custom_department' => 'nullable|string|max:255|required_if:department_id,' . (Department::where('code', 'LAIN')->first()?->id ?? -1),
            'role'          => 'required|exists:roles,name',
            'is_active'     => 'boolean',
        ]);

        $lainDept = Department::where('code', 'LAIN')->first();
        if ($lainDept && $request->department_id == $lainDept->id && $request->filled('custom_department')) {
            $customDeptName = $request->custom_department;
            $code = strtoupper(substr(preg_replace('/[^A-Za-z0-9]/', '', $customDeptName), 0, 4));
            if (empty($code)) $code = 'CUST';
            
            $roleLevel = in_array($request->role, ['manager', 'asman', 'staff']) ? $request->role : 'staff';
            $newDept = Department::firstOrCreate(
                ['name' => $customDeptName],
                ['code' => $code, 'description' => $customDeptName, 'level' => $roleLevel, 'is_active' => true]
            );
            $validated['department_id'] = $newDept->id;
        }

        // Safeguard for permanent super admin
        if ($user->email === 'it.infra@peb.co.id') {
            if (!$request->has('is_active')) {
                return back()->with('error', 'Akun Super Admin utama tidak dapat dinonaktifkan.');
            }
            if ($validated['role'] !== 'admin') {
                return back()->with('error', 'Role akun Super Admin utama harus tetap Admin.');
            }
        }

        DB::transaction(function () use ($request, $validated, $user) {
            $role = $validated['role'];
            $departmentId = in_array($role, ['direktur', 'komisaris']) ? null : ($validated['department_id'] ?? null);

            $updateData = [
                'name'          => $validated['name'],
                'email'         => $validated['email'],
                'nip'           => $validated['nip'] ?? null,
                'position'      => $validated['position'] ?? null,
                'department_id' => $departmentId,
                'is_active'     => $request->has('is_active'),
            ];

            if ($request->filled('password')) {
                $updateData['password'] = Hash::make($validated['password']);
            }

            $user->update($updateData);
            $user->syncRoles([$validated['role']]);
        });

        return redirect()->route('admin.users.index')->with('success', 'User berhasil diperbarui.');
    }

    public function destroy(User $user)
    {
        if ($user->email === 'it.infra@peb.co.id') {
            return back()->with('error', 'Akun Super Admin utama (it.infra@peb.co.id) adalah akun tetap sistem dan tidak dapat dihapus.');
        }

        if ($user->id === auth()->id()) {
            return back()->with('error', 'Tidak dapat menghapus akun Anda sendiri.');
        }

        $user->delete();
        return redirect()->route('admin.users.index')->with('success', 'User berhasil dihapus.');
    }

    public function importForm()
    {
        $departments = Department::with('parent')
            ->where('is_active', true)
            ->orderByRaw("CASE 
                WHEN level = 'manager' THEN 1 
                WHEN level = 'asman' THEN 2 
                WHEN level = 'staff' THEN 3 
                WHEN level IN ('kspi', 'auditor') THEN 4 
                ELSE 5 
            END")
            ->orderBy('code')
            ->get();

        return view('admin.users.import', compact('departments'));
    }

    public function import(Request $request)
    {
        $request->validate([
            'csv_file' => 'required|file|mimes:csv,txt|max:2048',
        ]);

        $file = $request->file('csv_file');
        
        try {
            $csv = Reader::createFromPath($file->getRealPath(), 'r');
            $csv->setHeaderOffset(0);
            $records = $csv->getRecords();

            $successCount = 0;
            $errorCount = 0;
            $errors = [];

            DB::beginTransaction();

            foreach ($records as $index => $row) {
                try {
                    // Check required fields
                    if (empty($row['name']) || empty($row['email']) || empty($row['password']) || empty($row['role'])) {
                        throw new \Exception("Kolom name, email, password, role wajib diisi.");
                    }

                    // Map Department code to ID
                    $departmentId = null;
                    if (!empty($row['department_code'])) {
                        $code = strtoupper(trim($row['department_code']));
                        $dept = Department::where('code', $code)->first();
                        if ($dept) {
                            $departmentId = $dept->id;
                        } else {
                            throw new \Exception("Kode bidang '{$code}' tidak ditemukan di sistem.");
                        }
                    }

                    // Assign role
                    $roleName = strtolower(trim($row['role']));
                    if (!Role::where('name', $roleName)->exists()) {
                        throw new \Exception("Role '$roleName' tidak valid.");
                    }

                    // Validate department requirement for auditee roles
                    if (in_array($roleName, ['manager', 'asman', 'staff']) && !$departmentId) {
                        throw new \Exception("Role '$roleName' wajib menyertakan department_code yang valid.");
                    }

                    // Direktur and Komisaris stand alone without department
                    if (in_array($roleName, ['direktur', 'komisaris'])) {
                        $departmentId = null;
                    }

                    // Create or update user by email
                    $user = User::updateOrCreate(
                        ['email' => trim($row['email'])],
                        [
                            'name'          => trim($row['name']),
                            'password'      => Hash::make(trim($row['password'])),
                            'nip'           => !empty($row['nip']) ? trim($row['nip']) : null,
                            'position'      => !empty($row['position']) ? trim($row['position']) : null,
                            'department_id' => $departmentId,
                            'is_active'     => true,
                        ]
                    );

                    $user->syncRoles([$roleName]);

                    $successCount++;
                } catch (\Exception $e) {
                    $errorCount++;
                    $errors[] = "Baris " . ($index + 2) . ": " . $e->getMessage();
                }
            }

            if ($errorCount > 0) {
                DB::rollBack();
                return back()->with('error', "Gagal mengimpor. $successCount berhasil, $errorCount gagal. Error: " . implode(', ', array_slice($errors, 0, 3)));
            }

            DB::commit();
            return redirect()->route('admin.users.index')->with('success', "Berhasil mengimpor $successCount user.");

        } catch (\Exception $e) {
            return back()->with('error', 'Gagal membaca file CSV: ' . $e->getMessage());
        }
    }

    public function downloadTemplate()
    {
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="template_import_user.csv"',
        ];

        $columns = ['name', 'email', 'password', 'role', 'nip', 'department_code'];
        $exampleRows = [
            ['Ahmad Fauzi', 'ahmad.manager@peb.co.id', 'Password123!', 'manager', '198001012005011001', 'KEU'],
            ['Budi Setiawan', 'budi.asman@peb.co.id', 'Password123!', 'asman', '198502022010021002', 'AM_KMR'],
            ['Citra Dewi', 'citra.staff@peb.co.id', 'Password123!', 'staff', '199203032018032003', 'ST_KPK'],
            ['Doni Pratama', 'doni.auditor@peb.co.id', 'Password123!', 'auditor', '199004042015041004', 'ST_AUDIT'],
        ];

        $callback = function() use ($columns, $exampleRows) {
            $file = fopen('php://output', 'w');
            fputcsv($file, $columns);
            foreach ($exampleRows as $row) {
                fputcsv($file, $row);
            }
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}
