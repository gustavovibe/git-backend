<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class MailRegistro extends Mailable
{
    use Queueable, SerializesModels;
    public $email;
    public $name;
    public $password;
    public function __construct($email, $password, $name)
    {
        $this->email = $email;
        $this->password = $password;
        $this->name = $name;
    }
    public function build()
    {
        return $this->subject('Confirmación de registro')
            ->view('emails.registroapp');
    }
}
