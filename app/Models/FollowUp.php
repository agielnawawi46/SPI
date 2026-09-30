<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class FollowUp extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'document_id', 'auditee_department_id', 'finding', 'recommendation',
        'sebab', 'akibat', 'evaluasi_tl',
        'due_date', 'status', 'assigned_to', 'approval_status', 'notes', 'created_by',
        'pic_name', 'pic_email',
    ];

    protected function casts(): array
    {
        return [
            'due_date' => 'date',
        ];
    }

    public function document(): BelongsTo
    {
        return $this->belongsTo(Document::class);
    }

    public function auditeeDepartment(): BelongsTo
    {
        return $this->belongsTo(Department::class, 'auditee_department_id');
    }

    public function assignee(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function evidences(): HasMany
    {
        return $this->hasMany(FollowUpEvidence::class);
    }

    public function approvals(): MorphMany
    {
        return $this->morphMany(Approval::class, 'approvable');
    }

    public function isOverdue(): bool
    {
        return !in_array($this->status, ['completed', 'tdd'])
            && $this->due_date->isPast();
    }

    public function getDaysRemainingAttribute(): int
    {
        return now()->diffInDays($this->due_date, false);
    }

    public function getStatusLabelAttribute(): string
    {
        return match($this->status) {
            'open'        => 'Belum Ditindaklanjuti',
            'in_progress' => 'Dalam Proses',
            'completed'   => 'Selesai',
            'tdd'         => 'Tidak Dapat Ditindaklanjuti',
            'overdue'     => 'Melewati Jatuh Tempo',
            default       => $this->status,
        };
    }

    public function getStatusColorAttribute(): string
    {
        return match($this->status) {
            'open'        => 'badge-open',
            'in_progress' => 'badge-in-progress',
            'completed'   => 'badge-completed',
            'tdd'         => 'badge-draft',
            'overdue'     => 'badge-overdue',
            default       => 'badge-draft',
        };
    }
}
