<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class SendMeetauthorEmail extends Mailable
{
    use Queueable, SerializesModels, Dispatchable, InteractsWithQueue;
    
    public $toEmail;
    public $fromEmail;
    public $fromName;
    public $subject;
    public $content;  

    /**
     * Create a new message instance.
     */
    public function __construct($to, $from, $fromName, $subject, $body)
    {
        $this->toEmail = $to;
        $this->fromEmail = $from;
        $this->fromName = $fromName;
        $this->subject = $subject;
        $this->content = $body;
    }

    
    public function build()
    {
        return $this->from($this->fromEmail, $this->fromName)
        ->replyTo($this->fromEmail, $this->fromName)
        ->to($this->toEmail)
        ->subject($this->subject)
        ->html($this->content);
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: $this->subject,
        );
    }

    /**
     * Get the message content definition.
     */
    // public function content(): Content
    // {
    //     return new Content(
    //         view: 'view.name',
    //     );
    // }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        return [];
    }
}
