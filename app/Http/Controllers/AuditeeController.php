<?php

namespace App\Http\Controllers;

use App\Models\FollowUp;
use App\Models\FollowUpEvidence;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuditeeController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();

        // Auditee sees follow-ups for their own department OR any child/parent department OR explicitly assigned to them
        $query = FollowUp::with(['document.documentType', 'auditeeDepartment'])->withCount('evidences')
            ->when($user->hasRole(['manager', 'asman', 'staff']), function ($q) use ($user) {
                $deptId = $user->department_id;
                abort_if(!$deptId, 403, 'Akun Anda belum ditetapkan ke departemen. Hubungi administrator.');
                $dept = $user->department;
                $allowedIds = $dept ? array_unique(array_merge($dept->getAllChildIds(), $dept->getAllParentIds())) : [$deptId];

                $q->where(function($q2) use ($allowedIds, $user) {
                    $q2->whereIn('auditee_department_id', $allowedIds)
                       ->orWhere('assigned_to', $user->id);
                });
            });

        if ($search = $request->get('search')) {
            $query->where(function($q) use ($search) {
                $q->where('temuan', 'like', "{$search}%")
                  ->orWhere('rekomendasi', 'like', "{$search}%");
            });
        }

        $followUps = $query->orderBy('due_date')->paginate(10);
        $stats = [
            'open'        => $followUps->where('status', 'open')->count(),
            'in_progress' => $followUps->where('status', 'in_progress')->count(),
            'completed'   => $followUps->where('status', 'closed')->count(),
            'overdue'     => $followUps->where('status', 'overdue')->count(),
        ];

        return view('auditee.index', compact('followUps', 'stats'));
    }

    public function show(FollowUp $followUp)
    {
        $user = Auth::user();

        // Access control: Auditee sees their department's follow-ups (including hierarchy) or explicitly assigned to them
        if ($user->hasRole(['manager', 'asman', 'staff'])) {
            $deptId = $user->department_id;
            abort_if(!$deptId, 403, 'Akun Anda belum ditetapkan ke departemen. Hubungi administrator.');
            $dept = $user->department;
            $allowedIds = $dept ? array_unique(array_merge($dept->getAllChildIds(), $dept->getAllParentIds())) : [$deptId];
            abort_if(!in_array($followUp->auditee_department_id, $allowedIds) && $followUp->assigned_to != $user->id, 403);
        }

        $followUp->load(['document.documentType', 'evidences.uploader', 'approvals.approver']);
        return view('auditee.show', compact('followUp'));
    }

    public function uploadEvidence(Request $request, FollowUp $followUp)
    {
        $user = Auth::user();
        
        $canUpload = false;
        if ($user->hasRole(['staff', 'admin']) && in_array($followUp->approval_status, ['pending', 'rejected'])) {
            $canUpload = true;
        } elseif ($user->hasRole('asman') && $followUp->approval_status === 'pending_asman') {
            $canUpload = true;
        } elseif ($user->hasRole('manager') && $followUp->approval_status === 'pending_manager') {
            $canUpload = true;
        } elseif ($user->hasRole('auditor') && $followUp->approval_status === 'pending_auditor') {
            $canUpload = true;
        } elseif ($user->hasRole('kspi') && $followUp->approval_status === 'pending_kspi') {
            $canUpload = true;
        }

        if (!$canUpload) {
            abort(403, 'Status saat ini ('.$followUp->approval_status.') tidak mengizinkan unggah bukti.');
        }

        if ($user->hasRole(['manager', 'asman', 'staff'])) {
            $deptId = $user->department_id;
            abort_if(!$deptId, 403, 'Akun Anda belum ditetapkan ke departemen. Hubungi administrator.');
            $dept = $user->department;
            $allowedIds = $dept ? array_unique(array_merge($dept->getAllChildIds(), $dept->getAllParentIds())) : [$deptId];
            abort_if(!in_array($followUp->auditee_department_id, $allowedIds) && $followUp->assigned_to != $user->id, 403, 'Anda tidak memiliki akses ke tindak lanjut ini.');
        }

        $request->validate([
            'files'       => 'nullable|array',
            'files.*'     => 'file|mimes:pdf,doc,docx,xls,xlsx,png,jpg,jpeg',
            'file'        => 'nullable|file|mimes:pdf,doc,docx,xls,xlsx,png,jpg,jpeg',
            'description' => 'nullable|string',
        ]);

        $rawFiles = $request->file('files') ?? $request->file('file');
        if (!$rawFiles) {
            return back()->withErrors(['files' => 'Berkas bukti wajib diunggah.'])->withInput();
        }

        $files = is_array($rawFiles) ? $rawFiles : [$rawFiles];
        $savedCount = 0;

        foreach ($files as $file) {
            if (!$file || !$file->isValid()) continue;

            $originalName = $file->getClientOriginalName();
            $uniqueId = uniqid();
            $path = $file->storeAs('evidences', time() . '_' . $uniqueId . '_' . $originalName, 'public');

            $followUp->evidences()->create([
                'uploaded_by' => Auth::id(),
                'file_name'   => $originalName,
                'file_path'   => $path,
                'pdf_path'    => null,
                'file_type'   => $file->getClientMimeType(),
                'file_size'   => $file->getSize(),
                'description' => $request->description,
            ]);
            $savedCount++;
        }

        // Update follow-up status to in_progress (draft mode)
        $followUp->update([
            'status' => 'in_progress'
        ]);

        $message = "{$savedCount} berkas bukti berhasil diunggah (Disimpan sebagai Draf).";
        return redirect()->back()->with('success', $message);
    }

    public function submitEvidence(Request $request, FollowUp $followUp)
    {
        $user = Auth::user();
        if (!$user->hasRole(['staff', 'admin'])) {
            abort(403);
        }
        if ($user->hasRole(['manager', 'asman', 'staff'])) {
            $deptId = $user->department_id;
            abort_if(!$deptId, 403, 'Akun Anda belum ditetapkan ke departemen.');
            $dept = $user->department;
            $allowedIds = $dept ? array_unique(array_merge($dept->getAllChildIds(), $dept->getAllParentIds())) : [$deptId];
            abort_if(!in_array($followUp->auditee_department_id, $allowedIds) && $followUp->assigned_to != $user->id, 403);
        }

        if ($followUp->evidences()->count() === 0) {
            return redirect()->back()->withErrors('Anda harus mengunggah setidaknya satu file bukti sebelum mengirim tindak lanjut.');
        }

        $followUp->update([
            'approval_status' => 'pending_asman'
        ]);

        // Notify Asman & Manager in the department hierarchy
        $parentDeptIds = $followUp->auditeeDepartment ? $followUp->auditeeDepartment->getAllParentIds() : [$followUp->auditee_department_id];
        $targetUsers = \App\Models\User::role(['asman', 'manager'])
            ->whereIn('department_id', $parentDeptIds)
            ->get();
        
        foreach($targetUsers as $target) {
            \App\Models\SpiNotification::create([
                'user_id'         => $target->id,
                'title'           => 'Persetujuan Bukti Tindak Lanjut',
                'message'         => 'Bukti tindak lanjut untuk "' . \Illuminate\Support\Str::limit($followUp->finding, 30) . '" telah dikirim oleh '.$user->name.' dan menunggu persetujuan Anda.',
                'type'            => 'approval',
                'link'            => route('auditee.show', $followUp),
                'notifiable_type' => get_class($followUp),
                'notifiable_id'   => $followUp->id,
            ]);
        }

        return redirect()->back()->with('success', 'Tindak Lanjut berhasil dikirim. Menunggu persetujuan Assistant Manager.');
    }

    public function deleteEvidence(Request $request, FollowUpEvidence $evidence)
    {
        $user = Auth::user();
        if (!$user->hasRole(['staff', 'admin'])) {
            abort(403);
        }

        $followUp = $evidence->followUp;
        if ($user->hasRole(['manager', 'asman', 'staff'])) {
            $deptId = $user->department_id;
            abort_if(!$deptId, 403, 'Akun Anda belum ditetapkan ke departemen.');
            $dept = $user->department;
            $allowedIds = $dept ? array_unique(array_merge($dept->getAllChildIds(), $dept->getAllParentIds())) : [$deptId];
            abort_if(!in_array($followUp->auditee_department_id, $allowedIds) && $followUp->assigned_to != $user->id, 403);
        }
        if (!in_array($followUp->approval_status, ['pending', 'rejected'])) {
            return redirect()->back()->withErrors('Anda tidak dapat menghapus bukti karena status saat ini tidak mengizinkan perubahan.');
        }

        \Illuminate\Support\Facades\Storage::disk('public')->delete($evidence->file_path);
        if ($evidence->pdf_path) {
            \Illuminate\Support\Facades\Storage::disk('public')->delete($evidence->pdf_path);
        }
        $evidence->delete();

        return redirect()->back()->with('success', 'File bukti berhasil dihapus.');
    }

    public function approveEvidence(Request $request, FollowUp $followUp)
    {
        $user = Auth::user();
        
        if ($followUp->approval_status === 'pending_asman' && $user->hasRole(['asman', 'manager'])) {
            // Allow manager to bypass asman if they want to approve directly
            $nextStatus = $user->hasRole('manager') ? 'pending_auditor' : 'pending_manager';
            $nextRole = $user->hasRole('manager') ? 'Manager' : 'Manager';
            if($user->hasRole('manager')) {
                 $followUp->update(['approval_status' => 'pending_auditor']);
                 $level = 2; // Treat manager's approval at asman step as step 2 complete for auditee
            } else {
                 $followUp->update(['approval_status' => 'pending_manager']);
                 $level = 1;
            }

            \App\Models\Approval::create([
                'approvable_type' => get_class($followUp),
                'approvable_id'   => $followUp->id,
                'approver_id'     => $user->id,
                'approval_level'  => $level,
                'approval_role'   => $user->hasRole('manager') ? 'manager' : 'asman',
                'action'          => 'approve',
                'acted_at'        => now(),
            ]);

            if ($nextStatus === 'pending_manager') {
                $parentDeptIds = $followUp->auditeeDepartment ? $followUp->auditeeDepartment->getAllParentIds() : [$followUp->auditee_department_id];
                $managers = \App\Models\User::role('manager')->whereIn('department_id', $parentDeptIds)->get();
                foreach($managers as $m) {
                    \App\Models\SpiNotification::create([
                        'user_id'         => $m->id,
                        'title'           => 'Persetujuan Bukti Tindak Lanjut',
                        'message'         => 'Bukti "' . \Illuminate\Support\Str::limit($followUp->finding, 30) . '" disetujui Asman dan menunggu final review Anda.',
                        'type'            => 'approval',
                        'link'            => route('auditee.show', $followUp),
                        'notifiable_type' => get_class($followUp),
                        'notifiable_id'   => $followUp->id,
                    ]);
                }
            } else {
                $auditors = \App\Models\User::role('auditor')->get();
                foreach($auditors as $auditor) {
                    \App\Models\SpiNotification::create([
                        'user_id'         => $auditor->id,
                        'title'           => 'Review Bukti Tindak Lanjut',
                        'message'         => 'Bukti tindak lanjut "' . \Illuminate\Support\Str::limit($followUp->finding, 30) . '" telah disetujui Auditee dan siap di-review.',
                        'type'            => 'approval',
                        'link'            => route('other-activities.tl.show', $followUp->document_id),
                        'notifiable_type' => get_class($followUp),
                        'notifiable_id'   => $followUp->id,
                    ]);
                }
            }

            return redirect()->back()->with('success', 'Bukti disetujui. Diteruskan ke tahap selanjutnya.');
        }
        elseif ($followUp->approval_status === 'pending_manager' && $user->hasRole('manager')) {
            $followUp->update(['approval_status' => 'pending_auditor']);
            
            \App\Models\Approval::create([
                'approvable_type' => get_class($followUp),
                'approvable_id'   => $followUp->id,
                'approver_id'     => $user->id,
                'approval_level'  => 2,
                'approval_role'   => 'manager',
                'action'          => 'approve',
                'acted_at'        => now(),
            ]);

            $auditors = \App\Models\User::role('auditor')->get();
            foreach($auditors as $auditor) {
                \App\Models\SpiNotification::create([
                    'user_id'         => $auditor->id,
                    'title'           => 'Review Bukti Tindak Lanjut',
                    'message'         => 'Bukti tindak lanjut "' . \Illuminate\Support\Str::limit($followUp->finding, 30) . '" telah disetujui Auditee dan siap di-review.',
                    'type'            => 'approval',
                    'link'            => route('other-activities.tl.show', $followUp->document_id),
                    'notifiable_type' => get_class($followUp),
                    'notifiable_id'   => $followUp->id,
                ]);
            }

            return redirect()->back()->with('success', 'Bukti disetujui. Diteruskan ke Auditor.');
        } 
        elseif ($followUp->approval_status === 'pending_auditor' && $user->hasRole('auditor')) {
            $followUp->update(['approval_status' => 'pending_kspi']);

            \App\Models\Approval::create([
                'approvable_type' => get_class($followUp),
                'approvable_id'   => $followUp->id,
                'approver_id'     => $user->id,
                'approval_level'  => 3,
                'approval_role'   => 'auditor',
                'action'          => 'approve',
                'acted_at'        => now(),
            ]);

            $kspis = \App\Models\User::role('kspi')->get();
            foreach($kspis as $kspi) {
                \App\Models\SpiNotification::create([
                    'user_id'         => $kspi->id,
                    'title'           => 'Persetujuan Final Tindak Lanjut',
                    'message'         => 'Bukti tindak lanjut "' . \Illuminate\Support\Str::limit($followUp->finding, 30) . '" telah disetujui Auditor dan menunggu pengesahan Anda.',
                    'type'            => 'approval',
                    'link'            => route('other-activities.tl.show', $followUp->document_id),
                    'notifiable_type' => get_class($followUp),
                    'notifiable_id'   => $followUp->id,
                ]);
            }

            return redirect()->back()->with('success', 'Bukti disetujui. Diteruskan ke KSPI.');
        }
        elseif ($followUp->approval_status === 'pending_kspi' && $user->hasRole('kspi')) {
            $followUp->update([
                'approval_status' => 'approved',
                'status' => 'closed'
            ]);

            \App\Models\Approval::create([
                'approvable_type' => get_class($followUp),
                'approvable_id'   => $followUp->id,
                'approver_id'     => $user->id,
                'approval_level'  => 4,
                'approval_role'   => 'kspi',
                'action'          => 'approve',
                'acted_at'        => now(),
            ]);

            $parentDeptIds = $followUp->auditeeDepartment ? $followUp->auditeeDepartment->getAllParentIds() : [$followUp->auditee_department_id];
            $auditees = \App\Models\User::where('id', $followUp->assigned_to)
                ->orWhere(function($q) use ($parentDeptIds) {
                    $q->whereHas('roles', fn($r) => $r->whereIn('name', ['manager', 'asman']))
                      ->whereIn('department_id', $parentDeptIds);
                })->get();
            $auditors = \App\Models\User::role('auditor')->get();
            $allToNotify = $auditees->merge($auditors)->unique('id');

            foreach($allToNotify as $u) {
                \App\Models\SpiNotification::create([
                    'user_id'         => $u->id,
                    'title'           => 'Tindak Lanjut Closed',
                    'message'         => 'Tindak lanjut "' . \Illuminate\Support\Str::limit($followUp->finding, 30) . '" telah sah disetujui oleh KSPI.',
                    'type'            => 'success',
                    'link'            => $u->hasRole(['manager','asman','staff']) ? route('auditee.show', $followUp) : route('other-activities.tl.show', $followUp->document_id),
                    'notifiable_type' => get_class($followUp),
                    'notifiable_id'   => $followUp->id,
                ]);
            }

            return redirect()->back()->with('success', 'Bukti final disetujui. Tindak lanjut selesai.');
        }

        abort(403, 'Anda tidak berhak melakukan persetujuan pada tahap ini.');
    }

    public function rejectEvidence(Request $request, FollowUp $followUp)
    {
        $request->validate(['notes' => 'required|string']);

        $user = Auth::user();

        $canReject = false;
        $level = 0;
        $role = '';
        if ($followUp->approval_status === 'pending_asman' && $user->hasRole(['asman', 'manager'])) { $canReject = true; $level = 1; $role = $user->hasRole('manager') ? 'manager' : 'asman'; }
        if ($followUp->approval_status === 'pending_manager' && $user->hasRole('manager')) { $canReject = true; $level = 2; $role = 'manager'; }
        if ($followUp->approval_status === 'pending_auditor' && $user->hasRole('auditor')) { $canReject = true; $level = 3; $role = 'auditor'; }
        if ($followUp->approval_status === 'pending_kspi' && $user->hasRole('kspi')) { $canReject = true; $level = 4; $role = 'kspi'; }

        if (!$canReject) {
            abort(403, 'Anda tidak berhak menolak pada tahap ini.');
        }

        $followUp->update([
            'approval_status' => 'rejected',
            'notes' => $request->notes
        ]);

        \App\Models\Approval::create([
            'approvable_type' => get_class($followUp),
            'approvable_id'   => $followUp->id,
            'approver_id'     => $user->id,
            'approval_level'  => $level,
            'approval_role'   => $role,
            'action'          => 'reject',
            'comments'        => $request->notes,
            'acted_at'        => now(),
        ]);

        $parentDeptIds = $followUp->auditeeDepartment ? $followUp->auditeeDepartment->getAllParentIds() : [$followUp->auditee_department_id];
        $auditees = \App\Models\User::where('id', $followUp->assigned_to)
            ->orWhere(function($q) use ($parentDeptIds) {
                $q->whereHas('roles', fn($r) => $r->whereIn('name', ['manager', 'asman', 'staff']))
                  ->whereIn('department_id', $parentDeptIds);
            })->get();
            
        foreach($auditees as $u) {
            \App\Models\SpiNotification::create([
                'user_id'         => $u->id,
                'title'           => 'Tindak Lanjut Ditolak',
                'message'         => 'Tindak lanjut "' . \Illuminate\Support\Str::limit($followUp->finding, 30) . '" ditolak oleh '. strtoupper($role) .'. Catatan: ' . \Illuminate\Support\Str::limit($request->notes, 30),
                'type'            => 'error',
                'link'            => route('auditee.show', $followUp),
                'notifiable_type' => get_class($followUp),
                'notifiable_id'   => $followUp->id,
            ]);
        }

        return redirect()->back()->with('success', 'Bukti ditolak dan dikembalikan ke Auditee Staff.');
    }
}
