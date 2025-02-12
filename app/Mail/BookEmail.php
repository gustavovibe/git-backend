<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Barryvdh\DomPDF\Facade\Pdf;

class BookEmail extends Mailable
{
    use Queueable, SerializesModels;

    protected $orders;
    protected $data;
    protected $summaryValues;

    public function __construct($orders,$data,$summaryValues)
    {
        $this->orders = $orders;
        $this->data = $data;
        $this->summaryValues = $summaryValues;
    }

    public function build()
    {
        $pdf1 = Pdf1::loadView('emails.tickets_booking', [
            'data' => $this->data,
        ]);

        $pdf2 = Pdf::loadView('emails.send_summary', [
            'tour' => $this->summaryValues['tour'],
            'countries_d' => $this->summaryValues['countries_d'],
            'services' => $this->summaryValues['services'],
        ]);

        return $this->subject('Booking confirmation')
                    ->view('emails.booking_confirmation_2')
                    ->with([
                        'orders' => $this->orders,
                    ])->attachData($pdf1->output(), 'tickets_booking.pdf', [
                        'mime' => 'application/pdf',
                    ])->attachData($pdf2->output(), 'booking_summary_tour.pdf', [
                        'mime' => 'application/pdf',
                    ]);
    }
}

