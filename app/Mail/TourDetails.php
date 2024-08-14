<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class TourDetails extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct()
    {
        //
    }


    public function build()
    {
        $this->subject('Contact Form Submission')->view('emails.email_t');
    }
}
