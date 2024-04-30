<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class RoutersStatusReportMail extends Mailable
{
    use Queueable, SerializesModels;

    protected $filename;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($filename)
    {
        $this->filename = $filename;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->view('routers_status_report')
                    ->attachFromStorageDisk('public_router_export', $this->filename);
    }
}
