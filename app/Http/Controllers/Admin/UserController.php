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

    public function create()
    {
        $roles = Role::all();
        $departments = Department::where('is_active', true)->get();
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
            
            $newDept = Department::firstOrCreate(
                ['name' => $customDeptName],
                ['code' => $code, 'description' => $customDeptName, 'is_active' => true]
            );
            $validated['department_id'] = $newDept->id;
        }

        DB::transaction(function () use ($validated) {
            $user = User::create([
                'name'          => $validated['name'],
                'email'         => $validated['email'],
                'password'      => Hash::make($validated['password']),
                'nip'           => $validated['nip'],
                'position'      => $validated['position'],
                'department_id' => $validated['department_id'],
                'is_active'     => $validated['is_active'] ?? true,
            ]);

            $user->assignRole($validated['role']);
        });

        return redirect()->route('admin.users.index')->with('success', 'User berhasil ditambahkan.');
    }

    public function edit(User $user)
    {
        $roles = Role::all();
        $departments = Department::where('is_active', true)->get();
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
            
            $newDept = Department::firstOrCreate(
                ['name' => $customDeptName],
                ['code' => $code, 'description' => $customDeptName, 'is_active' => true]
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
            $updateData = [
                'name'          => $validated['name'],
                'email'         => $validated['email'],
                'nip'           => $validated['nip'],
                'position'      => $validated['position'],
                'department_id' => $validated['department_id'],
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
        return view('admin.users.import');
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
                        $dept = Department::where('code', trim($row['department_code']))->first();
                        if ($dept) {
                            $departmentId = $dept->id;
                        }
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

                    // Assign role
                    $roleName = trim($row['role']);
                    if (Role::where('name', $roleName)->exists()) {
                        $user->syncRoles([$roleName]);
                    } else {
                        throw new \Exception("Role '$roleName' tidak valid.");
                    }

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

        $columns = ['name', 'email', 'password', 'role', 'nip', 'position', 'department_code'];
        $exampleRow = ['John Doe', 'john.doe@example.com', 'password123', 'staff', '12345678', 'Staf IT', 'HC'];

        $callback = function() use ($columns, $exampleRow) {
            $file = fopen('php://output', 'w');
            fputcsv($file, $columns);
            fputcsv($file, $exampleRow);
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}
