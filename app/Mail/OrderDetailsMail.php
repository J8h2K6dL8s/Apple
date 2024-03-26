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
    public $nom;
    public $commande;
    public $produits;

    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     */

    // public function __construct($user, $commande, $produits)
    public function __construct($nom,$commande,$user)
    {
        $this->nom=$nom;
        $this->commande = $commande ;
        $this->user = $user;
         
        // $this->produits = $produits;
    }

    /**
     * Get the message envelope.
     */

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Details de votre commande | Mr Apple Store' ,
        );
    }

    /**
     * Get the message content definition.
     */
    
    public function content(): Content
    {
        $user = auth('sanctum')->user();

        return (new Content)
        ->view('emails/orderDetailsCommande',['nom'=>$this->nom, 'commande' =>$this->commande,'user' =>$this->user]);
        // ->view('emails/orderDetailsCommande',['user' =>$this->user, 'commande' =>$this->commande, 'produits' => $this->produits]);
     
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
