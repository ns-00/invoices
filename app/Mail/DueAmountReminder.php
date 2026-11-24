<?php

namespace App\Mail;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Collection;

class DueAmountReminder extends Mailable
{
    use Queueable, SerializesModels;

    /** @param Collection<int, \App\Models\Invoice> $invoices */
    public function __construct(public User $user, public Collection $invoices)
    {
    }

    public function envelope(): Envelope
    {
        $n = $this->invoices->count();

        return new Envelope(subject: "تذكير: مبلغ مستحق 5% في {$n} ".($n > 2 && $n < 11 ? 'فواتير' : 'فاتورة'));
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.due-reminder',
            with: [
                'total' => $this->invoices->sum(fn ($i) => $i->due_amount),
                'url'   => $this->user->isAdmin() ? url('/dashboard') : url('/projects'),
            ],
        );
    }
}
