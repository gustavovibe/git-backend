<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class MailCertificaciones extends Mailable
{
    use Queueable, SerializesModels;
    public $name;
    public $certification;
    public function __construct($name, $certification)
    {
        $this->name = $name;
        $this->certification = $certification;
    }
    public function build()
    {
        return $this->subject('Confirmación de Solicitud de Inscripción')
            ->view('emails.confirmaciondeinscripcion');
    }
}
