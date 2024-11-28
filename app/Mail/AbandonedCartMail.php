<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class AbandonedCartMail extends Mailable
{
    use Queueable, SerializesModels;

    protected $tour;
    public function __construct($tour)
    {
        $this->tour=$tour;
    }


    public function build()
    {
        return $this->subject('Continue with your Booking')->view('emails.abandoned_cart')->with([
            'tour' => $this->tour,
        ]);

    }
}
