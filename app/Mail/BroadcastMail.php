<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class BroadcastMail extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     */
    public function __construct(
        public array $messageContent,
        public ?string $bannerImage = null,
        public ?string $attachmentPath = null,
        public ?string $attachmentName = null
    ) {
        // Process dynamic content in the message
        $this->messageContent['message'] = $this->processDynamicContent(
            $this->messageContent['message'],
            $this->messageContent
        );
    }

    /**
     * Process dynamic content in the message.
     */
    protected function processDynamicContent(string $content, array $data): string
    {
        if (!isset($data['user']) || !is_object($data['user'])) {
            return $content;
        }

        $user = $data['user'];
        
        return preg_replace_callback('/{{([^}]+)}}/', function ($matches) use ($user) {
            $key = trim($matches[1]);
            
            // Only process if it's a user attribute
            if (isset($user->{$key})) {
                return $user->{$key};
            }            
            
            return $matches[0];
        }, $content);
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            from: new Address('info@cellministry.tv', $this->messageContent['title']),
            subject: $this->messageContent['subject']
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.broadcast',
            with: [
                'message' => $this->messageContent['message'],
                'name' => $this->messageContent['name'],
                'bannerImage' => $this->bannerImage,
            ]
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        if ($this->attachmentPath && $this->attachmentName) {
            // Convert public URL to storage path
            $path = str_replace(asset('storage/'), '', $this->attachmentPath);
            $fullPath = storage_path('app/public/' . $path);

            if (!file_exists($fullPath)) {
                Log::error('Attachment file not found', [
                    'path' => $fullPath,
                    'url' => $this->attachmentPath
                ]);
                return [];
            }

            return [
                Attachment::fromPath($fullPath)
                    ->as($this->attachmentName)
                    ->withMime(mime_content_type($fullPath))
            ];
        }

        return [];
    }
}
