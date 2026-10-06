<?php

namespace App\Http\Controllers;

use App\Models\Approval;
use App\Models\Document;
use App\Models\SpiNotification;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class ApprovalController extends Controller
{
    /**
     * Submit a document for approval.
     */
    public function submit(Request $request, $type, $id)
    {
        // Currently we only support 'document' type.
        if ($type !== 'document') {
            abort(404);
        }

        $document = Document::findOrFail($id);

        // Verify if user is creator or admin
        if ($document->created_by !== Auth::id() && !Auth::user()->hasRole('admin')) {
            abort(403, 'Unauthorized action.');
        }

        // Verify if document is in valid state for submission
        if (!in_array($document->status, ['draft', 'revised'])) {
            return redirect()->back()->withErrors('Dokumen tidak dapat di-submit pada status ini.');
        }

        DB::transaction(function () use ($document) {
            // Bypass approval if the document type doesn't require it
            if ($document->documentType && !$document->documentType->requires_approval) {
                $document->update([
                    'status' => 'approved',
                ]);
                return;
            }

            if ($document->documentType?->slug === 'bak') {
                $document->update([
                    'status' => 'pending_kspi',
                ]);
            } elseif ($document->documentType?->slug === 'monitoring-tl') {
                $qrUrl     = route('document.verify', $document->uuid);
                $qrContent = QrCode::format('svg')->size(150)->generate($qrUrl);
                $qrPath    = 'qrcodes_auditor/' . $document->uuid . '.svg';
                Storage::disk('public')->put($qrPath, $qrContent);

                $document->update([
                    'status' => 'pending_kspi',
                    'qr_code_path_auditor' => $qrPath,
                ]);

                Approval::create([
                    'approvable_type' => Document::class,
                    'approvable_id'   => $document->id,
                    'approver_id'     => Auth::id(),
                    'approval_level'  => 0,
                    'approval_role'   => 'auditor',
                    'action'          => 'approve',
                    'comments'        => 'Dokumen disusun dan disubmit oleh Auditor.',
                    'acted_at'        => now(),
                ]);
            } else {
                // Change document status
                $document->update(['status' => 'pending_kspi']);
            }

            // Create pending approval for KSPI (Level 1)
            Approval::create([
                'approvable_type' => Document::class,
                'approvable_id'   => $document->id,
                'approver_id'     => null,
                'approval_level'  => 1,
                'approval_role'   => 'kspi',
                'action'          => null,
            ]);

            // Notify KSPI and Admin users
            $kspiUsers = User::role('kspi')->get();
            $adminUsers = User::role('admin')->get();
            $usersToNotify = $kspiUsers->merge($adminUsers)->unique('id');

            foreach ($usersToNotify as $userToNotify) {
                SpiNotification::create([
                    'user_id'          => $userToNotify->id,
                    'title'            => 'Dokumen Menunggu Review',
                    'message'          => 'Dokumen "' . $document->title . '" telah diajukan oleh ' . Auth::user()->name . ' dan menunggu review KSPI.',
                    'type'             => 'approval',
                    'link'             => route('assurance.show', $document),
                    'notifiable_type'  => Document::class,
                    'notifiable_id'    => $document->id,
                ]);
            }
        });

        return redirect()->back()->with('success', 'Dokumen berhasil di-submit untuk direview oleh KSPI.');
    }

    /**
     * Generate QR code for auditee signature.
     */
    public function signAuditee(Request $request, $id)
    {
        $document = Document::findOrFail($id);

        if ($document->documentType?->slug === 'bak') {
            if ($document->status !== 'pending_auditee') {
                return redirect()->back()->withErrors('Dokumen belum masuk ke tahap persetujuan Auditee.');
            }
            if ($document->auditee_id !== Auth::id() && !Auth::user()->hasRole('admin')) {
                abort(403, 'Unauthorized action.');
            }

            $qrUrl     = route('document.verify', $document->uuid);
            $qrContent = QrCode::format('svg')->size(150)->generate($qrUrl);
            $qrPath    = 'qrcodes_auditee/' . $document->uuid . '.svg';
            Storage::disk('public')->put($qrPath, $qrContent);

            DB::transaction(function () use ($document, $qrPath) {
                $document->update([
                    'status' => 'approved',
                    'approved_at' => now(), // Final approval timestamp
                    'qr_code_path_auditee' => $qrPath,
                ]);

                $approval = $document->approvals()->where('approval_level', 2)->where('approver_id', Auth::id())->whereNull('action')->first();
                if ($approval) {
                    $approval->update([
                        'action'   => 'approve',
                        'acted_at' => now(),
                    ]);
                }

                $usersToNotify = collect([$document->creator])->merge(User::role('admin')->get())->unique('id');
                foreach ($usersToNotify as $userToNotify) {
                    if ($userToNotify) {
                        SpiNotification::create([
                            'user_id'         => $userToNotify->id,
                            'title'           => 'Berita Acara Konsultasi Disetujui Auditee',
                            'message'         => 'Dokumen "' . $document->title . '" telah disetujui dan ditandatangani oleh Auditee.',
                            'type'            => 'success',
                            'link'            => route('advisory.show', $document),
                            'notifiable_type' => Document::class,
                            'notifiable_id'   => $document->id,
                        ]);
                    }
                }
            });

            return redirect()->back()->with('success', 'Dokumen berhasil disetujui dan ditandatangani.');
        } else {
            // Original logic for other document types
            if ($document->created_by !== Auth::id() && !Auth::user()->hasRole('admin')) {
                abort(403, 'Unauthorized action.');
            }

            if (!in_array($document->status, ['draft', 'revised'])) {
                return redirect()->back()->withErrors('Dokumen tidak dapat ditandatangani pada status ini.');
            }

            $qrUrl     = route('document.verify', $document->uuid);
            $qrContent = QrCode::format('svg')->size(150)->generate($qrUrl);
            $qrPath    = 'qrcodes_auditee/' . $document->uuid . '.svg';
            Storage::disk('public')->put($qrPath, $qrContent);

            $document->update(['qr_code_path_auditee' => $qrPath]);

            return redirect()->back()->with('success', 'Tanda tangan auditee berhasil dibubuhkan.');
        }
    }

    /**
     * Process an approval action (Approve/Reject).
     */
    public function action(Request $request, Approval $approval)
    {
        $request->validate([
            'action'            => 'required|in:approve,reject',
            'comments'          => 'nullable|string',
            'review_permohonan' => 'nullable|string',
            'kesimpulan'        => 'nullable|string',
            'alasan'            => 'nullable|string',
        ]);

        $user = Auth::user();

        // Verify role matches the required approval role
        if (!$user->hasRole($approval->approval_role)) {
            abort(403, 'Anda tidak memiliki hak untuk melakukan persetujuan ini.');
        }

        // Verify it is still pending
        if (!$approval->isPending()) {
            return redirect()->back()->withErrors('Persetujuan ini sudah diproses sebelumnya.');
        }

        DB::transaction(function () use ($request, $approval, $user) {
            // Update the approval record
            $approval->update([
                'approver_id' => $user->id,
                'action'      => $request->action,
                'comments'    => $request->comments,
                'acted_at'    => now(),
            ]);

            /** @var Document $model */
            $model = $approval->approvable;
            $creator = $model->creator;

            if ($request->action === 'reject') {
                // If rejected, set status to revised
                $model->update(['status' => 'revised']);

                // Notify the document creator
                if ($creator) {
                    SpiNotification::create([
                        'user_id'         => $creator->id,
                        'title'           => 'Dokumen Perlu Direvisi',
                        'message'         => 'Dokumen "' . $model->title . '" dikembalikan oleh ' . $user->name . ($request->comments ? ': ' . $request->comments : '.'),
                        'type'            => 'revision',
                        'link'            => $this->resolveDocumentRoute($model),
                        'notifiable_type' => Document::class,
                        'notifiable_id'   => $model->id,
                    ]);
                }
            } elseif ($request->action === 'approve') {
                if ($approval->approval_level === 1) {
                    if ($model->documentType?->slug === 'bak') {
                        // For BAK, KSPI approves then escalates to Auditee Manager
                        $qrUrl     = route('document.verify', $model->uuid);
                        $qrContent = QrCode::format('svg')->size(150)->generate($qrUrl);
                        $qrPath    = 'qrcodes/' . $model->uuid . '.svg';
                        Storage::disk('public')->put($qrPath, $qrContent);

                        $model->update([
                            'status'            => 'pending_auditee',
                            'qr_code_path'      => $qrPath,
                            'review_permohonan' => $request->input('review_permohonan', $model->review_permohonan),
                            'kesimpulan'        => 'Terima (dilanjutkan)',
                            'alasan'            => $request->input('alasan', $model->alasan),
                        ]);

                        Approval::create([
                            'approvable_type' => get_class($model),
                            'approvable_id'   => $model->id,
                            'approver_id'     => $model->auditee_id,
                            'approval_level'  => 2,
                            'approval_role'   => 'manager',
                            'action'          => null,
                        ]);

                        // Notify Auditee and Admin users
                        $auditeeUser = $model->auditeeUser;
                        $adminUsers = User::role('admin')->get();
                        $usersToNotify = collect($adminUsers);
                        if ($auditeeUser) {
                            $usersToNotify->push($auditeeUser);
                        }
                        $usersToNotify = $usersToNotify->unique('id');

                        foreach ($usersToNotify as $userToNotify) {
                            SpiNotification::create([
                                'user_id'         => $userToNotify->id,
                                'title'           => 'Berita Acara Konsultasi Menunggu Persetujuan Anda',
                                'message'         => 'Dokumen "' . $model->title . '" telah di-review KSPI dan menunggu persetujuan Anda.',
                                'type'            => 'approval',
                                'link'            => route('advisory.show', $model),
                                'notifiable_type' => Document::class,
                                'notifiable_id'   => $model->id,
                            ]);
                        }
                    } elseif ($model->documentType?->slug === 'monitoring-tl') {
                        // For Monitoring Tindak Lanjut, KSPI approval is the final step
                        $qrUrl     = route('document.verify', $model->uuid);
                        $qrContent = QrCode::format('svg')->size(150)->generate($qrUrl);
                        $qrPath    = 'qrcodes/' . $model->uuid . '.svg';
                        Storage::disk('public')->put($qrPath, $qrContent);

                        $model->update([
                            'status'       => 'approved',
                            'approved_at'  => now(),
                            'qr_code_path' => $qrPath,
                        ]);

                        // Generate FollowUp records based on document content
                        $items = json_decode($model->content, true);
                        if (is_array($items)) {
                            foreach ($items as $group) {
                                if (isset($group['rows']) && is_array($group['rows'])) {
                                    foreach ($group['rows'] as $row) {
                                        if (!empty(trim($row['lha'] ?? ''))) {
                                            $auditeeUser = null;
                                            if (!empty(trim($row['pic_email'] ?? ''))) {
                                                $auditeeUser = \App\Models\User::where('email', trim($row['pic_email']))->first();
                                            }

                                            $dueDate = !empty($row['jatuh_tempo']) ? \Carbon\Carbon::parse($row['jatuh_tempo']) : now()->addDays(7);

                                            $followUp = \App\Models\FollowUp::create([
                                                'document_id' => $model->id,
                                                'auditee_department_id' => $auditeeUser ? ($auditeeUser->department_id ?? 1) : ($model->creator->department_id ?? 1),
                                                'finding' => $row['lha'] ?? '',
                                                'sebab' => $row['sebab'] ?? null,
                                                'akibat' => $row['akibat'] ?? null,
                                                'recommendation' => $row['rekomendasi'] ?? '',
                                                'evaluasi_tl' => $row['evaluasi_tl'] ?? null,
                                                'due_date' => $dueDate,
                                                'status' => 'open',
                                                'assigned_to' => $auditeeUser ? $auditeeUser->id : null,
                                                'pic_name' => $row['pic_tl'] ?? null,
                                                'pic_email' => $row['pic_email'] ?? null,
                                                'approval_status' => 'pending',
                                                'created_by' => $user->id,
                                            ]);

                                            // Determine recipients for in-app and email
                                            $departmentId = $auditeeUser ? ($auditeeUser->department_id ?? 1) : ($model->creator->department_id ?? 1);
                                            $managers = \App\Models\User::role(['manager', 'asman'])->where('department_id', $departmentId)->get();
                                            $recipients = collect();
                                            
                                            if ($auditeeUser) {
                                                $recipients->push($auditeeUser);
                                            }
                                            $recipients = $recipients->merge($managers)->unique('id');

                                            foreach ($recipients as $recipient) {
                                                SpiNotification::create([
                                                    'user_id'         => $recipient->id,
                                                    'title'           => 'Tugas Tindak Lanjut Baru',
                                                    'message'         => 'Ada tugas unggah bukti tindak lanjut baru: ' . \Illuminate\Support\Str::limit($followUp->finding, 50),
                                                    'type'            => 'warning',
                                                    'link'            => route('auditee.show', $followUp),
                                                    'notifiable_type' => \App\Models\FollowUp::class,
                                                    'notifiable_id'   => $followUp->id,
                                                ]);
                                            }
                                        }
                                    }
                                }
                            }
                        }

                        // Notify creator and Admin users
                        $adminUsers = User::role('admin')->get();
                        $usersToNotify = collect($adminUsers);
                        if ($creator) {
                            $usersToNotify->push($creator);
                        }
                        $usersToNotify = $usersToNotify->unique('id');

                        foreach ($usersToNotify as $userToNotify) {
                            SpiNotification::create([
                                'user_id'         => $userToNotify->id,
                                'title'           => 'Dokumen Resmi Disetujui',
                                'message'         => 'Dokumen Monitoring Tindak Lanjut "' . $model->title . '" telah disetujui oleh KSPI dan kini berstatus resmi.',
                                'type'            => 'success',
                                'link'            => route('other-activities.tl.show', $model),
                                'notifiable_type' => Document::class,
                                'notifiable_id'   => $model->id,
                            ]);
                        }
                    } else {
                        // KSPI approved, escalate to Direktur
                        $model->update(['status' => 'pending_direktur']);

                        Approval::create([
                            'approvable_type' => get_class($model),
                            'approvable_id'   => $model->id,
                            'approver_id'     => null,
                            'approval_level'  => 2,
                            'approval_role'   => 'direktur',
                            'action'          => null,
                        ]);

                        // Notify Direktur and Admin users
                        $direkturUsers = User::role('direktur')->get();
                        $adminUsers = User::role('admin')->get();
                        $usersToNotify = $direkturUsers->merge($adminUsers)->unique('id');

                        foreach ($usersToNotify as $userToNotify) {
                            SpiNotification::create([
                                'user_id'         => $userToNotify->id,
                                'title'           => 'Dokumen Menunggu Persetujuan',
                                'message'         => 'Dokumen "' . $model->title . '" telah disetujui KSPI dan menunggu tanda tangan Direktur.',
                                'type'            => 'approval',
                                'link'            => $this->resolveDocumentRoute($model),
                                'notifiable_type' => Document::class,
                                'notifiable_id'   => $model->id,
                            ]);
                        }

                        // Notify creator
                        if ($creator) {
                            SpiNotification::create([
                                'user_id'         => $creator->id,
                                'title'           => 'Dokumen Disetujui KSPI',
                                'message'         => 'Dokumen "' . $model->title . '" telah disetujui KSPI dan diteruskan ke Direktur.',
                                'type'            => 'info',
                                'link'            => $this->resolveDocumentRoute($model),
                                'notifiable_type' => Document::class,
                                'notifiable_id'   => $model->id,
                            ]);
                        }
                    }

                } elseif ($approval->approval_level === 2) {
                    // Direktur approved — generate QR code (SVG, no Imagick needed) and finalize
                    $qrUrl     = route('document.verify', $model->uuid);
                    $qrContent = QrCode::format('svg')->size(150)->generate($qrUrl);
                    $qrPath    = 'qrcodes/' . $model->uuid . '.svg';
                    Storage::disk('public')->put($qrPath, $qrContent);

                    $model->update([
                        'status'       => 'approved',
                        'approved_at'  => now(),
                        'qr_code_path' => $qrPath,
                    ]);

                    // Notify creator and Admin users
                    $adminUsers = User::role('admin')->get();
                    $usersToNotify = collect($adminUsers);
                    if ($creator) {
                        $usersToNotify->push($creator);
                    }
                    $usersToNotify = $usersToNotify->unique('id');

                    foreach ($usersToNotify as $userToNotify) {
                        SpiNotification::create([
                            'user_id'         => $userToNotify->id,
                            'title'           => 'Dokumen Resmi Disetujui',
                            'message'         => 'Dokumen "' . $model->title . '" telah disetujui oleh Direktur dan kini berstatus resmi.',
                            'type'            => 'success',
                            'link'            => $this->resolveDocumentRoute($model),
                            'notifiable_type' => Document::class,
                            'notifiable_id'   => $model->id,
                        ]);
                    }
                }
            }
        });

        $message = $request->action === 'approve' ? 'Dokumen berhasil disetujui.' : 'Dokumen dikembalikan untuk direvisi.';
        return redirect()->back()->with('success', $message);
    }

    /**
     * Direct approval for documents that don't need multi-level flow (e.g. Program Kerja)
     */
    public function approveDirect(Request $request, $id)
    {
        $document = Document::findOrFail($id);
        $user = Auth::user();

        // Only allow KSPI or Admin
        if (!$user->hasRole('kspi') && !$user->hasRole('admin')) {
            abort(403, 'Anda tidak memiliki hak untuk melakukan pengesahan langsung.');
        }

        // Verify if document is in valid state for approval
        if (!in_array($document->status, ['draft', 'revised', 'pending_kspi'])) {
            return redirect()->back()->withErrors('Dokumen tidak dapat disahkan pada status ini.');
        }

        DB::transaction(function () use ($document, $user) {
            // Generate QR Code just in case they need it for verification
            $qrUrl     = route('document.verify', $document->uuid);
            $qrContent = QrCode::format('svg')->size(150)->generate($qrUrl);
            $qrPath    = 'qrcodes/' . $document->uuid . '.svg';
            Storage::disk('public')->put($qrPath, $qrContent);

            $document->update([
                'status'       => 'approved',
                'approved_at'  => now(),
                'qr_code_path' => $qrPath,
            ]);

            Approval::create([
                'approvable_type' => Document::class,
                'approvable_id'   => $document->id,
                'approver_id'     => $user->id,
                'approval_level'  => 1,
                'approval_role'   => 'kspi',
                'action'          => 'approve',
                'comments'        => 'Disetujui secara langsung oleh ' . $user->name,
                'acted_at'        => now(),
            ]);

            // Notify Admin
            $adminUsers = User::role('admin')->get();
            foreach ($adminUsers as $admin) {
                SpiNotification::create([
                    'user_id'         => $admin->id,
                    'title'           => 'Dokumen Resmi Disetujui',
                    'message'         => 'Dokumen "' . $document->title . '" telah disahkan secara langsung oleh ' . $user->name,
                    'type'            => 'success',
                    'link'            => $this->resolveDocumentRoute($document),
                    'notifiable_type' => Document::class,
                    'notifiable_id'   => $document->id,
                ]);
            }
        });

        return redirect()->back()->with('success', 'Dokumen berhasil disahkan secara langsung.');
    }

    /**
     * Resolve the correct show route for a document based on its document type module.
     * Prevents hardcoded 'assurance.show' links for advisory/TL documents.
     */
    private function resolveDocumentRoute(Document $document): string
    {
        $module = $document->documentType?->module;

        return match ($module) {
            'advisory'      => route('advisory.show', $document),
            'tindak-lanjut' => route('other-activities.tl.show', $document),
            'qa'            => route('other-activities.qa.show', $document),
            'pkpt'          => route('pkpt.show', $document->pkpt_id ?? $document->id),
            'report'        => route('performance-reports.show', $document),
            default         => route('assurance.show', $document), // assurance and everything else
        };
    }
}
