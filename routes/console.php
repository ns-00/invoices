<?php

use Illuminate\Support\Facades\Schedule;

// تذكير المبالغ المستحقة يوميًا الساعة 8:00 صباحًا (حسب APP_TIMEZONE)
Schedule::command('invoices:send-due-reminders')->dailyAt('08:00')->withoutOverlapping();

// تنظيف النسخ الاحتياطية القديمة يومياً الساعة 23:45 مساءً (نهاية اليوم)
Schedule::command('backup:clean')->dailyAt('23:45')->withoutOverlapping();

// النسخ الاحتياطي التلقائي الشامل لبيانات اليوم كاملة - مرة واحدة فقط في نهاية كل يوم الساعة 23:59 مساءً
Schedule::command('backup:run')
    ->dailyAt('23:59')
    ->withoutOverlapping()
    ->appendOutputTo(storage_path('logs/backup.log'));
