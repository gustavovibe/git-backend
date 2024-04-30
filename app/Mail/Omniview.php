<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class Omniview extends Mailable
{
    use Queueable, SerializesModels;

    public $subject;
    public $toEmail;
    public $fromEmail;
    public $fromName;

    /**
     * Create a new message instance.
     *
     * @param string $toEmail Email del destinatario
     * @param string $subject Asunto del correo
     * @param string $fromEmail Email del remitente
     * @param string $fromName Nombre del remitente (opcional)
     * @return void
     */
    public function __construct($toEmail, $subject, $fromEmail, $fromName = '')
    {
        $this->toEmail = $toEmail;
        $this->subject = $subject;
        $this->fromEmail = $fromEmail;
        $this->fromName = $fromName;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->to($this->toEmail)
            ->subject($this->subject)
            ->from($this->fromEmail, $this->fromName)
            ->view('cursos');
    }
}
