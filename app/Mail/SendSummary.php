<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class SendSummary extends Mailable
{
    use Queueable, SerializesModels;

    protected $data;

    public function __construct($data)
    {
        $this->data=$data;
    }

    public function build()
    {
        return $this->subject('Boooking Summary')->view('emails.summary_download')->with([
            'data' => $this->data,
        ]);
    }
}
