<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use App\Services\Alarms\AlarmService;

class EmailVerifyEmail extends Mailable
{
    use Queueable, SerializesModels;
    protected $code;


    /**
     * Creatprotecte a new message instance.
     *
     * @return void
     */
    public function __construct($code)
    {
        $this->code =  $code;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()

    {
        // return $this->view('reset_password');


        return $this->markdown('emails.two_factor_auth.verifyemail', ['code' => $this->code]);
    }
}
