<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class EnquiryTestLisboa extends Mailable
{
    use Queueable, SerializesModels;

    protected $data;
    public function __construct($data)
    {
        $this->data=$data;
    }

    public function build()
    {
        return $this->subject('Thanks for your test')->view('emails.enquery_test_lisboa')->with('data', $this->data);
    }
}

