<?php
// app/Http/Controllers/PreviewMailController.php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Order;
use App\Mail\BookEmail;
use App\Mail\CancelMail;

class PreviewMailController extends Controller
{
    public function bookingConfirmation(Request $request)
    {
        // 1) validate+fetch the order
        $request->validate([
            'booking_id' => 'required|integer|exists:orders,booking_id',
        ]);
        $order = Order::findOrFail($request->query('booking_id'));

        // 2) instantiate with "just orders" and disable attachments
        //    build() will still call ->view('emails.booking_confirmation_2')->with(['orders'=>…])
        $mailable = new BookEmail(
            /* $orders */           $order,
            /* $data */             null,
            /* $summaryValues */    null,
            /* $invoice */          null,
            /* $invoice_content */  null,
            /* $flag */             false
        );

        // 3) return it — Laravel will render the HTML of your Blade template
        return $mailable;
    }

    public function cancelConfirmation(Request $request)
    {
        // 1) validate+fetch the order
        $request->validate([
            'booking_id' => 'required|integer|exists:orders,booking_id',
        ]);
        $order = Order::findOrFail($request->query('booking_id'));

        // 2) instantiate with "just orders" and disable attachments
        //    build() will still call ->view('emails.booking_confirmation_2')->with(['orders'=>…])
        $mailable = new CancelMail($order);
        

        // 3) return it — Laravel will render the HTML of your Blade template
        return $mailable;
    }
}
