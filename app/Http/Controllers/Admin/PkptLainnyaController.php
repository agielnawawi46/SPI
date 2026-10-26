<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Pkpt;
use App\Models\PkptItem;
use App\Models\Department;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class PkptLainnyaController extends Controller
{
    public function index(Request $request)
    {
        $query = PkptItem::with(['pkpt', 'auditeeDepartment']);

        if ($request->has('search')) {
            $search = $request->search;
            $query->where('audit_object', 'like', "{$search}%")
                  ->orWhereHas('pkpt', function ($q) use ($search) {
                      $q->where('title', 'like', "{$search}%")
                        ->orWhere('period_year', 'like', "{$search}%");
                  });
        }

        $items = $query->latest()->paginate(10);
        return view('admin.pkpt.lainnya.index', compact('items'));
    }

    public function create()
    {
        $pkpts = Pkpt::orderBy('period_year', 'desc')->get();
        $departments = Department::orderBy('name')->get();
        return view('admin.pkpt.lainnya.create', compact('pkpts', 'departments'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'pkpt_id' => 'required|exists:pkpts,id',
            'audit_object' => 'required|string|max:255',
            'auditee_department_id' => 'nullable',
            'custom_auditee_name' => 'nullable|string|max:255',
            'planned_start' => 'nullable|date',
            'planned_end' => 'nullable|date|after_or_equal:planned_start',
        ]);

        if ($validated['auditee_department_id'] === 'other') {
            $validated['auditee_department_id'] = null;
        } else {
            $validated['custom_auditee_name'] = null;
        }

        PkptItem::create($validated);

        return redirect()->route('pkpt.lainnya.index')->with('success', 'Data Objek Audit (Lainnya) berhasil ditambahkan.');
    }

    public function show(PkptItem $lainnya)
    {
        $lainnya->load(['pkpt', 'auditeeDepartment']);
        return view('admin.pkpt.lainnya.show', compact('lainnya'));
    }

    public function edit(PkptItem $lainnya)
    {
        $pkpts = Pkpt::withTrashed()->orderBy('period_year', 'desc')->get();
        $departments = Department::orderBy('name')->get();
        return view('admin.pkpt.lainnya.edit', compact('lainnya', 'pkpts', 'departments'));
    }

    public function update(Request $request, PkptItem $lainnya)
    {
        $validated = $request->validate([
            'pkpt_id' => 'required|exists:pkpts,id',
            'audit_object' => 'required|string|max:255',
            'auditee_department_id' => 'nullable',
            'custom_auditee_name' => 'nullable|string|max:255',
            'planned_start' => 'nullable|date',
            'planned_end' => 'nullable|date|after_or_equal:planned_start',
        ]);

        if ($validated['auditee_department_id'] === 'other') {
            $validated['auditee_department_id'] = null;
        } else {
            $validated['custom_auditee_name'] = null;
        }

        $lainnya->update($validated);

        return redirect()->route('pkpt.lainnya.index')->with('success', 'Data Objek Audit (Lainnya) berhasil diperbarui.');
    }

    public function destroy(PkptItem $lainnya)
    {
        if ($lainnya->attachment_path) {
            Storage::disk('public')->delete($lainnya->attachment_path);
        }
        $lainnya->delete();
        return redirect()->route('pkpt.lainnya.index')->with('success', 'Data Objek Audit (Lainnya) berhasil dihapus.');
    }
}
