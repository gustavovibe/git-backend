<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class CancelMail extends Mailable
{
    use Queueable, SerializesModels;

    protected $orders;

    public function __construct($orders)
    {
        $this->orders = $orders;
    }


    public function build()
    {
        $email = $this->subject('Booking cancellation')
                      ->view('emails.cancellation')
                      ->with([
                          'orders' => $this->orders,
                      ]);
        return $email;
    }
}