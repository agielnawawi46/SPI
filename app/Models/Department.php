<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Department extends Model
{
    protected $fillable = [
        'name',
        'code',
        'description',
        'is_active',
        'parent_id',
        'level',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(Department::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(Department::class, 'parent_id');
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

    /**
     * Get all descendant IDs including self (recursively).
     *
     * @return array<int>
     */
    public function getAllChildIds(): array
    {
        $ids = [$this->id];
        $this->loadMissing('children');

        foreach ($this->children as $child) {
            $ids = array_merge($ids, $child->getAllChildIds());
        }

        return array_unique($ids);
    }

    /**
     * Get all ancestor IDs including self.
     *
     * @return array<int>
     */
    public function getAllParentIds(): array
    {
        $ids = [$this->id];
        $current = $this;

        while ($current->parent_id) {
            $ids[] = $current->parent_id;
            $current = $current->parent;
            if (!$current) break;
        }

        return array_unique($ids);
    }

    /**
     * Get structured active departments formatted for the Role -> Department selector modal.
     */
    public static function getStructuredForSelector()
    {
        return self::with(['parent.parent'])
            ->where('is_active', true)
            ->get()
            ->map(function ($dept) {
                $groupLabel = 'Lainnya';
                if ($dept->level === 'manager') {
                    $groupLabel = 'Tingkat Manager / Bidang Utama';
                } elseif ($dept->level === 'asman') {
                    $groupLabel = $dept->parent ? $dept->parent->name : 'Unit Khusus (Langsung Direktur)';
                } elseif ($dept->level === 'staff') {
                    if ($dept->parent && $dept->parent->parent) {
                        $groupLabel = $dept->parent->parent->name . ' ➔ ' . $dept->parent->name;
                    } elseif ($dept->parent) {
                        $groupLabel = $dept->parent->name;
                    } else {
                        $groupLabel = 'Unit Staff';
                    }
                } elseif ($dept->level === 'kspi' || $dept->level === 'auditor') {
                    $groupLabel = 'Satuan Pengawasan Intern (SPI)';
                }

                return [
                    'id'             => $dept->id,
                    'name'           => $dept->name,
                    'code'           => $dept->code,
                    'level'          => $dept->level,
                    'parent_id'      => $dept->parent_id,
                    'parent_name'    => $dept->parent?->name ?? '',
                    'hierarchy_path' => $dept->hierarchy_path,
                    'group_label'    => $groupLabel,
                ];
            });
    }

    /**
     * Get hierarchy path breadcrumb (e.g. "Keuangan > Revenue > Billing").
     */
    public function getHierarchyPathAttribute(): string
    {
        $parts = [$this->name];
        $current = $this;

        while ($current->parent) {
            $current = $current->parent;
            array_unshift($parts, $current->name);
        }

        return implode(' ➔ ', $parts);
    }
}
