<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use App\Services\Alarms\AlarmService;

class EmailAuthTwoFactor extends Mailable
{
    use Queueable, SerializesModels;
    protected $code;
    protected $expTime;


    /**
     * Creatprotecte a new message instance.
     *
     * @return void
     */
    public function __construct($code, $expTime)
    {
        $this->code =  $code;
        $this->expTime =  $expTime;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()

    {
        // return $this->view('reset_password');
        return $this->markdown('emails.two_factor_auth.auth_code', ['code' => $this->code, 'exp_time' => $this->expTime]);
    }
}
