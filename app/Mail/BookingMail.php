<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class BookingMail extends Mailable
{
    use Queueable, SerializesModels;


    public function __construct($orders)
    {
        $this->orders=$orders;
    }


    public function build()
    {
        return $this->subject('Contact Form Submission')->view('emails.booking_confirmation_2')->with([
            'orders' => $this->orders,
        ]);

    }
}
