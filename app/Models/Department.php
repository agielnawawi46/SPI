<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Department extends Model
{
    protected $fillable = ['name', 'code', 'description', 'is_active'];

    protected function casts(): array
    {
        return ['is_active' => 'boolean'];
    }

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    public function followUps(): HasMany
    {
        return $this->hasMany(FollowUp::class, 'auditee_department_id');
    }

    public function auditAssignments(): HasMany
    {
        return $this->hasMany(AuditAssignment::class, 'auditee_department_id');
    }

    public function pkptItems(): HasMany
    {
        return $this->hasMany(PkptItem::class, 'auditee_department_id');
    }
}
