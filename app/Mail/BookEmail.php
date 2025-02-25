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
    protected $invoice;

    public function __construct($orders,$data,$summaryValues,$invoice)
    {
        $this->orders = $orders;
        $this->data = $data;
        $this->summaryValues = $summaryValues;
        $this->invoice = $invoice;
    }

    public function build()
    {
        $email = $this->subject('Booking confirmation')
                      ->view('emails.booking_confirmation_2')
                      ->with([
                          'orders' => $this->orders,
                      ]);

        // Adjuntar solo si 'data' no está vacío
        if (!empty($this->data['data'])) {
            $pdf1 = Pdf::loadView('emails.tickets_booking', [
                'data' => $this->data['data'],
            ]);
            $email->attachData($pdf1->output(), 'tickets_booking.pdf', [
                'mime' => 'application/pdf',
            ]);
        }

        if (!empty($this->invoice)) {
            $pdf3 = Pdf::loadView('emails.invoice', ['orders' => $this->orders,'data'=>$this->data,'values'=>$this->invoice]);
            $email->attachData($pdf3->output(), 'invoice.pdf', ['mime' => 'application/pdf']);
        }
        // Adjuntar siempre el segundo PDF (si es requerido en todos los casos)
        $pdf2 = Pdf::loadView('emails.send_summary', [
            'tour' => $this->summaryValues['tour'],
            'countries_d' => $this->summaryValues['countries_d'],
            'services' => $this->summaryValues['services'],
        ]);
        $email->attachData($pdf2->output(), 'booking_summary_tour.pdf', [
            'mime' => 'application/pdf',
        ]);

        return $email;
    }
}

