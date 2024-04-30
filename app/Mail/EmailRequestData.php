<?php

namespace App\Mail;

use App\Models\Email;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class EmailRequestData extends Mailable
{
    use Queueable, SerializesModels;

    public $info;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct(Email $email)
    {
        $this->info =  json_decode($email->data);
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this
                ->subject('Nuevo cliente')
                ->view('email_request_data', [ 'data' => $this->info ]);
    }
}
