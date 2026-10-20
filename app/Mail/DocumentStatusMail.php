<?php

namespace App\Mail;

use App\Models\DocumentRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class DocumentStatusMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public DocumentRequest $docRequest,
        public string $status,
        public string $statusTitle,
        public string $statusDescription,
        public string $docTypeName,
        public string $userName,
        public ?string $actorTitle = null,
        public ?string $actorName = null,
        public ?string $notes = null,
        public ?string $actionUrl = null
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: "[SIWIL] {$this->statusTitle}: {$this->docTypeName}",
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.document-status',
        );
    }
}
