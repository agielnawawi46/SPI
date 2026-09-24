<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Document extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'uuid',
        'document_number',
        'document_type_id',
        'pkpt_id',
        'audit_assignment_id',
        'created_by',
        'title',
        'header_text',
        'content',
        'audit_type',
        'status',
        'qr_code_path',
        'qr_code_path_auditee',
        'qr_code_path_auditor',
        'approved_at',
        'tgl_pka',
        'disusun_oleh',
        'disahkan_oleh',
        'tgl_entry',
        'peserta_entry',
        'tgl_kka',
        'direview_disahkan_oleh',
        'tgl_lha',
        'pengesahan_lha',
        'distribusi_lha',
        'tgl_distribusi_lha',
        'tgl_stg',
        'auditee',
        'auditee_id',
        'jadwal_audit',
        'lingkup_audit',
        'ketua_tim',
        'anggota_tim',
        'tujuan_audit',
        'periode_audit',
        'penandatangan_stg',
        'header_image',
        'judul_dokumen',
        'footer_image',
        'deskripsi_pembuka',
        'deskripsi_penutup',
        'tim_audit',
        'jabatan_direktur',
        'nama_direktur',
        'nama_kspi',
        'nama_pimpinan_klien',
        'nama_jabatan',
        'nip',
        'bidang_tugas',
        'deskripsi_permasalahan',
        'tujuan_konsultasi',
        'waktu_penyelesaian',
        'pengungkapan',
        'nomor_formulir',
        'tanggal_formulir',
        'review_permohonan',
        'kesimpulan',
        'alasan',
    ];

    protected function casts(): array
    {
        return [
            'approved_at' => 'datetime',
            'tgl_pka' => 'date',
            'tgl_entry' => 'date',
            'tgl_kka' => 'date',
            'tgl_lha' => 'date',
            'tgl_distribusi_lha' => 'date',
            'tgl_stg' => 'date',
            'tim_audit' => 'array',
        ];
    }

    protected static function boot(): void
    {
        parent::boot();
        static::creating(function (self $model) {
            if (empty($model->uuid)) {
                $model->uuid = (string) \Illuminate\Support\Str::uuid();
            }
        });
    }

    // ============================================
    // Relationships
    // ============================================

    public function documentType(): BelongsTo
    {
        return $this->belongsTo(DocumentType::class);
    }

    public function auditAssignment(): BelongsTo
    {
        return $this->belongsTo(AuditAssignment::class);
    }

    public function pkpt(): BelongsTo
    {
        return $this->belongsTo(Pkpt::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function assignments(): HasMany
    {
        return $this->hasMany(AuditAssignment::class);
    }

    public function auditeeUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'auditee_id');
    }

    public function attachments(): HasMany
    {
        return $this->hasMany(DocumentAttachment::class);
    }

    public function followUps(): HasMany
    {
        return $this->hasMany(FollowUp::class);
    }

    public function approvals(): MorphMany
    {
        return $this->morphMany(Approval::class, 'approvable');
    }

    // ============================================
    // Helpers
    // ============================================

    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            'draft'             => 'Draft',
            'pending_kspi'      => 'Menunggu KSPI',
            'pending_direktur'  => 'Menunggu Direktur',
            'approved'          => 'Closed',
            'rejected'          => 'Ditolak',
            'revised'           => 'Perlu Revisi',
            default             => $this->status,
        };
    }

    public function getStatusColorAttribute(): string
    {
        return match($this->status) {
            'draft'             => 'badge-draft',
            'pending_kspi',
            'pending_direktur'  => 'badge-pending',
            'approved'          => 'badge-approved',
            'rejected'          => 'badge-rejected',
            'revised'           => 'badge-warning',
            default             => 'badge-draft',
        };
    }

    public function isApproved(): bool
    {
        return $this->status === 'approved';
    }

    public function canBeEditedBy(User $user): bool
    {
        if ($user->hasRole('admin')) {
            return true;
        }

        $typeSlug = $this->documentType?->slug;

        // Surat Tugas Audit: allow auditor, kspi, direktur to edit before it is fully approved
        if ($typeSlug === 'surat-tugas' && $this->status !== 'approved') {
            if ($user->hasRole(['auditor', 'kspi', 'direktur'])) {
                return true;
            }
        }

        // For other documents created by auditor, they should be able to edit their drafts
        if ($user->hasRole('auditor') && in_array($this->status, ['draft', 'revised']) && $this->created_by === $user->id) {
            return true;
        }

        // If the user is an auditor but didn't match the above, they cannot edit
        if ($user->hasRole('auditor')) {
            return false;
        }

        // Creator can edit when draft or revised
        if (in_array($this->status, ['draft', 'revised']) && $this->created_by === $user->id) {
            return true;
        }

        // KSPI can edit when pending KSPI review
        if ($this->status === 'pending_kspi' && $user->hasRole('kspi')) {
            return true;
        }

        // Direktur can edit when pending Direktur review
        if ($this->status === 'pending_direktur' && $user->hasRole('direktur')) {
            return true;
        }

        return false;
    }

    public function canBeDeletedBy(User $user): bool
    {
        if ($user->hasRole('admin')) {
            return true;
        }

        // Auditor is view-only for assurance documents
        if ($user->hasRole('auditor')) {
            return false;
        }

        // Creator can delete only when still draft or revised (not yet submitted)
        if (in_array($this->status, ['draft', 'revised']) && $this->created_by === $user->id) {
            return true;
        }

        // KSPI and Direktur with delete permission can always delete
        if ($user->hasPermissionTo('assurance.delete')) {
            return true;
        }

        return false;
    }
}
