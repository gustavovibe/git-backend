<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class EnquiryClient extends Mailable
{
    use Queueable, SerializesModels;

    protected $data;
    public function __construct($data)
    {
        $this->data=$data;
    }

    public function build()
    {
        return $this->subject('Vibe Adventures got your enquiry')->view('emails.enquery_client')->with('data', $this->data);
    }
}
