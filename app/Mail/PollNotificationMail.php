<?php

namespace App\Mail;

use App\Models\Poll;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class PollNotificationMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public Poll $poll,
        public string $type, // 'CREATED' or 'CLOSED'
        public string $creatorTitle,
        public ?string $resultMessage = null,
        public ?string $actionUrl = null
    ) {}

    public function envelope(): Envelope
    {
        $prefix = ($this->type === 'CLOSED') ? 'Hasil Musyawarah' : 'Musyawarah Warga Baru';
        return new Envelope(
            subject: "[SIWIL] {$prefix}: {$this->poll->title}",
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.poll-notification',
        );
    }
}
