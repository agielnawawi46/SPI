<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class Approval extends Model
{
    protected $fillable = [
        'approvable_type', 'approvable_id',
        'approver_id', 'action', 'comments',
        'approval_level', 'approval_role', 'acted_at',
    ];

    protected function casts(): array
    {
        return [
            'acted_at' => 'datetime',
            'approval_level' => 'integer',
        ];
    }

    public function approvable(): MorphTo
    {
        return $this->morphTo();
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approver_id');
    }

    public function isPending(): bool
    {
        return is_null($this->action);
    }

    public function isApproved(): bool
    {
        return $this->action === 'approve';
    }

    public function isRejected(): bool
    {
        return $this->action === 'reject';
    }
}
