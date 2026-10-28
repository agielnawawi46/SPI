<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Pkpt extends Model
{
    use SoftDeletes;
    protected $fillable = [
        'title',
        'period_year',
        'description',
        'status',
        'created_by',
        'qr_code_path',
        'file_name',
        'file_path',
        'file_size',
        'file_type',
    ];

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function items(): HasMany
    {
        return $this->hasMany(PkptItem::class);
    }

    public function documents(): HasMany
    {
        return $this->hasMany(Document::class);
    }

    public function pkptDocument()
    {
        $docType = DocumentType::where('slug', 'pkpt')->first();
        return $this->hasOne(Document::class, 'pkpt_id')->where('document_type_id', $docType?->id);
    }

    public function getAttachmentsAttribute()
    {
        $doc = $this->pkptDocument;
        if (!$doc) {
            return collect();
        }
        return $doc->attachments()->latest()->get();
    }

    public function getFileCountAttribute(): int
    {
        $pkptDocType = DocumentType::where('slug', 'pkpt')->first();
        if ($this->relationLoaded('documents')) {
            $doc = $this->documents->firstWhere('document_type_id', $pkptDocType?->id);
            if ($doc && $doc->relationLoaded('attachments')) {
                $count = $doc->attachments->count();
                if ($count > 0) {
                    return $count;
                }
            }
        }

        $count = $this->attachments->count();
        if ($count > 0) {
            return $count;
        }

        return !empty($this->file_path) ? 1 : 0;
    }

    public function approvals(): MorphMany
    {
        return $this->morphMany(Approval::class, 'approvable');
    }

    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            'draft'            => 'Draft',
            'pending_kspi'     => 'Menunggu KSPI',
            'pending_direktur' => 'Menunggu Direktur',
            'approved'         => 'Closed',
            'rejected'         => 'Ditolak',
            'revised'          => 'Perlu Revisi',
            default            => $this->status,
        };
    }
}
