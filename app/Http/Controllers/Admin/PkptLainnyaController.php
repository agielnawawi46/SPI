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
        $request->validate([
            'pkpt_id' => 'required|exists:pkpts,id',
            'audit_object' => 'required|string|max:255',
            'auditee_department_id' => 'nullable',
            'custom_auditee_name' => 'nullable|string|max:255',
            'planned_start' => 'nullable|date',
            'planned_end' => 'nullable|date|after_or_equal:planned_start',
            'attachment' => 'nullable|file|mimes:pdf,doc,docx,xls,xlsx|max:307200',
        ]);

        $data = $request->all();
        if ($data['auditee_department_id'] === 'other') {
            $data['auditee_department_id'] = null;
        } else {
            $data['custom_auditee_name'] = null;
        }

        if ($request->hasFile('attachment')) {
            $data['attachment_path'] = $request->file('attachment')->store('pkpt/lainnya', 'public');
        }

        PkptItem::create($data);

        return redirect()->route('pkpt.lainnya.index')->with('success', 'Data Objek Audit (Lainnya) berhasil ditambahkan.');
    }

    public function show(PkptItem $lainnya)
    {
        $lainnya->load(['pkpt', 'auditeeDepartment']);
        return view('admin.pkpt.lainnya.show', compact('lainnya'));
    }

    public function edit(PkptItem $lainnya)
    {
        $pkpts = Pkpt::orderBy('period_year', 'desc')->get();
        $departments = Department::orderBy('name')->get();
        return view('admin.pkpt.lainnya.edit', compact('lainnya', 'pkpts', 'departments'));
    }

    public function update(Request $request, PkptItem $lainnya)
    {
        $request->validate([
            'pkpt_id' => 'required|exists:pkpts,id',
            'audit_object' => 'required|string|max:255',
            'auditee_department_id' => 'nullable',
            'custom_auditee_name' => 'nullable|string|max:255',
            'planned_start' => 'nullable|date',
            'planned_end' => 'nullable|date|after_or_equal:planned_start',
            'attachment' => 'nullable|file|mimes:pdf,doc,docx,xls,xlsx|max:307200',
            'remove_attachment' => 'nullable|boolean',
        ]);

        $data = $request->all();
        if ($data['auditee_department_id'] === 'other') {
            $data['auditee_department_id'] = null;
        } else {
            $data['custom_auditee_name'] = null;
        }

        if ($request->has('remove_attachment') && $request->remove_attachment) {
            if ($lainnya->attachment_path) {
                Storage::disk('public')->delete($lainnya->attachment_path);
            }
            $data['attachment_path'] = null;
        } elseif ($request->hasFile('attachment')) {
            if ($lainnya->attachment_path) {
                Storage::disk('public')->delete($lainnya->attachment_path);
            }
            $data['attachment_path'] = $request->file('attachment')->store('pkpt/lainnya', 'public');
        }

        $lainnya->update($data);

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
