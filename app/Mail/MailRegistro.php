<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class MailRegistro extends Mailable
{
    use Queueable, SerializesModels;
    public $email;
    public $code;
    public function __construct($email, $code)
    {
        $this->email = $email;
        $this->code = $code;

    }
    public function build()
    {
        return $this->subject('Confirmación de verificacion')
            ->view('emails.registroapp');
    }
}
