<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Barryvdh\DomPDF\Facade\Pdf;

class BookingMail extends Mailable
{
    use Queueable, SerializesModels;

    protected $orders;
    protected $data;

    public function __construct($orders,$data)
    {
        $this->orders=$orders;
        $this->data= $data;
    }


    public function build()
    {
        $pdf = Pdf::loadView('emails.booking_confirmation_2', [
            'orders' => $this->orders,
            'data' => $this->data,
        ]);

        return $this->subject('Booking confirmation')->view('emails.booking_confirmation_2')->with([
            'orders' => $this->orders,
        ])->attachData($pdf->output(), 'tickets_booking.pdf', [
                        'mime' => 'application/pdf',
                    ]);;

    }
}
