<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class AuditAssignment extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'pkpt_item_id', 'assignment_number', 'title', 'audit_type',
        'auditor_ids', 'auditee_department_id', 'start_date', 'end_date',
        'status', 'created_by',
    ];

    protected function casts(): array
    {
        return [
            'auditor_ids' => 'array',
            'start_date'  => 'date',
            'end_date'    => 'date',
        ];
    }

    public function pkptItem(): BelongsTo
    {
        return $this->belongsTo(PkptItem::class);
    }

    public function auditeeDepartment(): BelongsTo
    {
        return $this->belongsTo(Department::class, 'auditee_department_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function documents(): HasMany
    {
        return $this->hasMany(Document::class);
    }

    /**
     * Get auditors as a collection of User models.
     */
    public function getAuditorsAttribute()
    {
        return User::whereIn('id', $this->auditor_ids ?? [])->get();
    }
}
