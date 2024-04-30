<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use App\Services\Alarms\AlarmService;

class EmailUsersApp extends Mailable
{
    use Queueable, SerializesModels;
    protected $url;
    protected $token;
    protected $id;

    /**
     * Creatprotecte a new message instance.
     *
     * @return void
     */
    public function __construct($url, $token, $iduser)
    {
        $this->token =  $token;
        $this->id = $iduser;
        $this->url = $url;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()

    {
        // return $this->view('reset_password');
        return $this->view('reset_password', ['url' => $this->url, 'token' => $this->token, 'id' => $this->id,]);
    }
}
