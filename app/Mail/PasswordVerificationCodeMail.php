<?php

namespace App\Mail;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class PasswordVerificationCodeMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public User $user,
        public string $code
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: "رمز التحقق لتغيير كلمة المرور: {$this->code}"
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.password-code',
            with: [
                'user' => $this->user,
                'code' => $this->code,
            ],
        );
    }
}
