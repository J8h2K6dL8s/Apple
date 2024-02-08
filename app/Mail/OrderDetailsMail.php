<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class OrderDetailsMail extends Mailable
{
    public $user;
    public $data;
    public $commande;

    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     */

    public function __construct($user, $data, $commande)
    {
        $this->user = $user;
        $this->data = $data; 
        $this->commande = $commande ; 
    }

    /**
     * Get the message envelope.
     */

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Details de votre commande | Mr Apple' ,
        );
    }

    /**
     * Get the message content definition.
     */
    
    public function content(): Content
    {
        return (new Content)
        ->view('emails/orderDetailsCommande',['user' =>$this->user, 'data' => $this->data, 'commande' =>$this->commande]);
     
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
