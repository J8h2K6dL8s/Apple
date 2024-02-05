<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class SendCodeResetPasswordMail extends Mailable
{
   public $code;
   public $type;
   public $url;

    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     */
    
    public function __construct($code,$type)
    {
        $this->code=$code;
        $this->type=$type;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Code de Reinitialisation | Mr Apple',
        );
    }

    /**
     * Get the message content definition.
     */
    public function content() {  
       
        if ($this->type =='user') { 
               $this->url="https://mrapple-store.com/reinitialisation";
           } else {
               $this->url="https://mrapple-store.com/admin/reinitialisation";
           }
       return (new Content)
          ->view('emails/authentification/passwordforget',['code' =>$this->code, 'url' => $this->url]);
       
    }
    
    // public function content(): Content
    // {
    //     return (new Content)
    //         ->view('emails/authentification/passwordforget',['code' =>$this->code, 'url' => $this->url]);

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
