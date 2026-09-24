<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PkptItem extends Model
{
    protected $fillable = [
        'pkpt_id', 'audit_object', 'audit_objective', 'audit_scope',
        'audit_type', 'planned_start', 'planned_end',
        'auditee_department_id', 'custom_auditee_name', 'attachment_path', 'planned_days', 'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'planned_start' => 'date',
            'planned_end'   => 'date',
        ];
    }

    public function pkpt(): BelongsTo
    {
        return $this->belongsTo(Pkpt::class);
    }

    public function auditeeDepartment(): BelongsTo
    {
        return $this->belongsTo(Department::class, 'auditee_department_id');
    }

    public function auditAssignments(): HasMany
    {
        return $this->hasMany(AuditAssignment::class);
    }
}
