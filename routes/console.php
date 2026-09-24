<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Schedule the follow-up reminders to run every weekday (Mon-Fri) at 08:00
// The command itself decides whether to send based on days remaining
\Illuminate\Support\Facades\Schedule::command('followup:send-reminders')->weekdays()->at('08:00');
