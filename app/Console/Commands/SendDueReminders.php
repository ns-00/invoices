<?php

namespace App\Console\Commands;

use App\Mail\DueAmountReminder;
use App\Models\Invoice;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

class SendDueReminders extends Command
{
    protected $signature = 'invoices:send-due-reminders {--dry-run : عرض ما سيُرسل دون إرسال فعلي}';

    protected $description = 'إرسال تذكير بالمبالغ المستحقة (5%) إلى إيميل صاحب المشروع';

    public function handle(): int
    {
        $today = today();
        $until = $today->copy()->addDays(config('invoices.remind_days_before'));

        // المستحقات غير المستلمة التي يقع استحقاقها اليوم أو خلال الأيام القادمة، ولم يُرسل لها تذكير اليوم
        $invoices = Invoice::with('month.project.user')
            ->where('has_due', true)
            ->where('due_paid', false)
            ->whereBetween('due_date', [$today->toDateString(), $until->toDateString()])
            ->where(fn ($q) => $q->whereNull('last_reminded_on')->orWhere('last_reminded_on', '<', $today->toDateString()))
            ->orderBy('due_date')
            ->get();

        // رسالة واحدة لكل مستخدم تجمع كل فواتيره المستحقة
        $byUser = $invoices->groupBy(fn ($i) => $i->month->project->user_id);

        $sent = 0;
        foreach ($byUser as $group) {
            $user = $group->first()->month->project->user;

            if (! $user || ! $user->is_active) {
                continue;
            }

            if ($this->option('dry-run')) {
                $this->line("[تجربة] {$user->email} ← {$group->count()} فاتورة");
                continue;
            }

            Mail::to($user->email)->send(new DueAmountReminder($user, $group));
            Invoice::whereIn('id', $group->pluck('id'))->update(['last_reminded_on' => $today->toDateString()]);
            $sent++;
        }

        $this->info("تم إرسال {$sent} رسالة تذكير.");

        return self::SUCCESS;
    }
}
