<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class TradeMail extends Mailable
{
    use Queueable, SerializesModels;

    public $trade;
    public $user;
    public $type;

    

    /**
     * Create a new message instance.
     */
    public function __construct($trade,$type)
    {
        $this->trade = $trade;
        $this->type=$type;

    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Nouveau demande de trade ajouté',
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    { 
        if ($this->type =='admin') { 
            return  (new Content)
                ->view('emails/trade',['trade' =>$this->trade]);
        }

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
