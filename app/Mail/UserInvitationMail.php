<?php

namespace App\Mail;

use App\Models\Company;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class UserInvitationMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public User $user,
        public ?Company $company,
        public string $resetPasswordUrl,
    ) {}

    public function envelope(): Envelope
    {
        if ($this->user->hasCompletedRegistration()) {
            $subject = $this->company !== null
                ? 'Redefinição de senha — portal Talents ('.$this->company->name.')'
                : 'Redefinição de senha — equipe administrativa Talents';
        } else {
            $subject = $this->company !== null
                ? 'Convite — portal Talents ('.$this->company->name.')'
                : 'Convite — equipe administrativa Talents';
        }

        return new Envelope(
            subject: $subject,
        );
    }

    public function content(): Content
    {
        return new Content(
            html: 'mail.user-invitation',
            text: 'mail.user-invitation-text',
        );
    }

    /**
     * @return array<int, mixed>
     */
    public function attachments(): array
    {
        return [];
    }
}
