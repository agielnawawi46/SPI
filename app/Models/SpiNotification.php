<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SpiNotification extends Model
{
    protected $table = 'spi_notifications';

    protected $fillable = [
        'user_id', 'title', 'message', 'type',
        'link', 'notifiable_type', 'notifiable_id',
        'is_read', 'read_at',
    ];

    protected function casts(): array
    {
        return [
            'is_read'  => 'boolean',
            'read_at'  => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function markAsRead(): void
    {
        if (!$this->is_read) {
            $this->update(['is_read' => true, 'read_at' => now()]);
        }
    }

    /**
     * Get human-readable time ago.
     */
    public function getTimeAgoAttribute(): string
    {
        return $this->created_at->diffForHumans();
    }

    protected static function booted()
    {
        static::created(function ($notification) {
            $user = $notification->user;
            if ($user && $user->email) {
                try {
                    \Illuminate\Support\Facades\Mail::to($user->email)
                        ->send(new \App\Mail\DocumentNotificationMail(
                            $notification->title,
                            $notification->message,
                            $notification->link,
                            $user->name
                        ));
                } catch (\Exception $e) {
                    \Illuminate\Support\Facades\Log::error("Failed to send notification email to {$user->email}: " . $e->getMessage());
                }
            }
        });
    }
}
