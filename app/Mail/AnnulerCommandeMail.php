<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class AnnulerCommandeMail extends Mailable
{
    public $user;
    public $nom;
    public $commande;

    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     */

    public function __construct($nom,$commande,$user)
    {
        $this->nom=$nom;
        $this->commande = $commande ;
        $this->user = $user;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Annuler Commande | Mr Apple Store',
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return (new Content)
        ->view('emails/annulerCommande',['nom'=>$this->nom, 'commande' =>$this->commande,'user' =>$this->user]);
        // ->view('emails/orderDetailsCommande',['user' =>$this->user,'produit' => $this->listeProduit, 'commande' =>$this->commande]);
     
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
