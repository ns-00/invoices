<?php

use Illuminate\Support\Facades\Schedule;

// تذكير المبالغ المستحقة يوميًا الساعة 8 صباحًا (حسب APP_TIMEZONE)
Schedule::command('invoices:send-due-reminders')->dailyAt('08:00')->withoutOverlapping();

// النسخ الاحتياطي التلقائي يومياً الساعة 2:00 صباحاً لقاعدة البيانات والملفات
Schedule::command('app:backup-run --source="Daily Scheduler"')
    ->dailyAt('02:00')
    ->withoutOverlapping()
    ->appendOutputTo(storage_path('logs/backup.log'));
