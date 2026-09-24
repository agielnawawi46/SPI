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
