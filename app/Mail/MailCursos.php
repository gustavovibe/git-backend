<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class MailCursos extends Mailable
{
    use Queueable, SerializesModels;
    // public $name;

    // public function __construct($name)
    // {
    //     $this->name = $name;
    // }
    public function build()
    {
        return $this->subject('¡No te pierdas la oportunidad de completar tu curso!')
            ->view('emails.seguimiento');
    }
}
