<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class OrderAchatMail extends Mailable
{
    
    public $user;

    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     */

    public function __construct($user)
    {
        $this->user = $user;
       
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Votre achat | Mr Apple',
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        // $this->user =app('currentUser');
        $user = auth('sanctum')->user();
    
        return (new Content)
        ->view('emails/orderAchatMail',['user'=>$user]);
     
    }

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
