<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class MiCorreo extends Mailable
{
    use Queueable, SerializesModels;
    public $name; // Propiedad para almacenar el nombre
    public $source; // Propiedad para almacenar el nombre
    public function __construct($name, $source)
    {
        $this->name = $name; // Asignar el nombre pasado como argumento al constructor
        $this->source = $source; // Asignar el nombre pasado como argumento al constructor
    }
    public function build()
    {
        return $this->subject('Invitacion kooltivo')
            ->view('emails.micorreo');
    }
}
