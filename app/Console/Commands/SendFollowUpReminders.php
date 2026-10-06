<?php

namespace App\Console\Commands;

use App\Models\FollowUp;
use App\Models\SpiNotification;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class SendFollowUpReminders extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'followup:send-reminders';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send smart email & in-app reminders for open follow-ups based on days remaining';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Starting smart follow-up reminder check...');
        $today = Carbon::today();
        $isMonday = $today->isMonday();

        // Find follow-ups that are NOT closed/tdd, have a PIC email, and have a due date
        $followUps = FollowUp::whereNotIn('status', ['closed', 'tdd', 'completed'])
            ->where('approval_status', '!=', 'approved')
            ->where(function($query) {
                $query->whereNotNull('pic_email')
                      ->where('pic_email', '!=', '')
                      ->orWhereNotNull('assigned_to');
            })
            ->whereNotNull('due_date')
            ->with(['document', 'assignee'])
            ->get();

        $sentCount = 0;
        $skippedCount = 0;

        foreach ($followUps as $followUp) {
            $dueDate = Carbon::parse($followUp->due_date);
            $daysRemaining = $today->diffInDays($dueDate, false); // negative = overdue

            // Determine reminder type based on days remaining
            $reminderType = $this->determineReminderType($daysRemaining);

            // Determine if we should send today
            $shouldSend = $this->shouldSendToday($daysRemaining, $isMonday);

            if (!$shouldSend) {
                $skippedCount++;
                $this->line("  ⏭️  Skipped FollowUp #{$followUp->id} ({$daysRemaining} hari tersisa, bukan hari kirim)");
                continue;
            }

            // Collect recipients: PIC Staff + Manager Auditee (same department)
            $recipients = $this->collectRecipients($followUp);

            if ($recipients->isEmpty()) {
                $this->warn("  ⚠️  FollowUp #{$followUp->id}: Tidak ada penerima yang valid.");
                continue;
            }

            // Send in-app notification (which now automatically sends email)
            foreach ($recipients as $recipient) {
                if ($recipient['user_id']) {
                    $urgencyLabel = match($reminderType) {
                        'overdue' => 'Peringatan Terlambat',
                        'urgent'  => 'Perhatian',
                        default   => 'Pengingat',
                    };

                    SpiNotification::create([
                        'user_id'         => $recipient['user_id'],
                        'title'           => "{$urgencyLabel} - Tindak Lanjut",
                        'message'         => $this->buildNotificationMessage($followUp, $reminderType, $daysRemaining),
                        'type'            => $reminderType === 'overdue' ? 'error' : ($reminderType === 'urgent' ? 'warning' : 'info'),
                        'link'            => route('auditee.show', $followUp),
                        'notifiable_type' => FollowUp::class,
                        'notifiable_id'   => $followUp->id,
                    ]);
                } else if (!empty($recipient['email'])) {
                    $urgencyLabel = match($reminderType) {
                        'overdue' => 'Peringatan Terlambat',
                        'urgent'  => 'Perhatian',
                        default   => 'Pengingat',
                    };
                    
                    try {
                        \Illuminate\Support\Facades\Mail::to($recipient['email'])
                            ->send(new \App\Mail\DocumentNotificationMail(
                                "{$urgencyLabel} - Tindak Lanjut",
                                $this->buildNotificationMessage($followUp, $reminderType, $daysRemaining),
                                route('auditee.show', $followUp),
                                'Bapak/Ibu'
                            ));
                    } catch (\Exception $e) {
                        \Illuminate\Support\Facades\Log::error("Failed to send notification email to {$recipient['email']}: " . $e->getMessage());
                    }
                }
            }

            $sentCount++;
        }

        $this->newLine();
        $this->info("✅ Selesai. Reminder terkirim: {$sentCount}, Dilewati: {$skippedCount}");
    }

    /**
     * Determine reminder type based on days remaining.
     */
    private function determineReminderType(int $daysRemaining): string
    {
        if ($daysRemaining < 0) {
            return 'overdue';   // Sudah lewat jatuh tempo
        } elseif ($daysRemaining <= 7) {
            return 'urgent';    // ≤ 7 hari, kirim setiap hari
        } else {
            return 'reminder';  // > 7 hari, kirim mingguan
        }
    }

    /**
     * Determine whether we should send a reminder today.
     */
    private function shouldSendToday(int $daysRemaining, bool $isMonday): bool
    {
        // Overdue → kirim setiap hari
        if ($daysRemaining < 0) {
            return true;
        }

        // ≤ 7 hari → kirim setiap hari
        if ($daysRemaining <= 7) {
            return true;
        }

        // > 7 hari → kirim hanya pada hari Senin
        return $isMonday;
    }

    /**
     * Collect all recipients (PIC Staff + Manager Auditee of same department).
     */
    private function collectRecipients(FollowUp $followUp): \Illuminate\Support\Collection
    {
        $recipients = collect();

        // 1. PIC Staff (from assigned_to OR pic_email field)
        if ($followUp->assigned_to) {
            $assignee = $followUp->assignee;
            if ($assignee) {
                $recipients->push([
                    'email'   => $assignee->email,
                    'user_id' => $assignee->id,
                    'role'    => 'staff',
                ]);
            }
        } elseif ($followUp->pic_email) {
            $picUser = User::where('email', $followUp->pic_email)->first();
            $recipients->push([
                'email'   => $followUp->pic_email,
                'user_id' => $picUser?->id,
                'role'    => 'staff',
            ]);
        }

        // 2. Manager & Asman Auditee (same department)
        if ($followUp->auditee_department_id) {
            $managers = User::role(['manager', 'asman'])
                ->where('department_id', $followUp->auditee_department_id)
                ->get();

            foreach ($managers as $manager) {
                // Avoid duplicate if manager is also PIC
                if ($manager->email !== $followUp->pic_email) {
                    $recipients->push([
                        'email'   => $manager->email,
                        'user_id' => $manager->id,
                        'role'    => 'manager',
                    ]);
                }
            }
        }

        return $recipients;
    }

    /**
     * Build notification message based on type and days.
     */
    private function buildNotificationMessage(FollowUp $followUp, string $type, int $daysRemaining): string
    {
        $finding = Str::limit($followUp->finding, 50);

        return match($type) {
            'overdue' => "Mohon perhatian, tindak lanjut \"{$finding}\" telah melewati batas waktu penyelesaian selama " . abs($daysRemaining) . " hari. Kami memohon agar Anda segera menyelesaikan dan mengunggah bukti yang diperlukan.",
            'urgent'  => "Mohon perhatian, batas waktu penyelesaian untuk tindak lanjut \"{$finding}\" tersisa {$daysRemaining} hari lagi. Mohon untuk segera mengunggah bukti tindak lanjut Anda.",
            default   => "Sebagai pengingat, tindak lanjut \"{$finding}\" memiliki sisa waktu {$daysRemaining} hari sebelum batas waktu penyelesaian. Mohon untuk disiapkan dengan baik.",
        };
    }
}
