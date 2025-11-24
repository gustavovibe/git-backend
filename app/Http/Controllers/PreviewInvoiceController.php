<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Models\Order;
use App\Http\Controllers\StripeController;
use App\Filters\ToursFilters;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Log;

class PreviewInvoiceController extends Controller
{
    public function __invoke(Request $request)
    {
        Log::info('Invoice preview request received', ['query' => $request->all()]);
        // 1) Validate your inputs
        $data = $request->validate([
            'booking_id'  => 'required|integer|exists:orders,booking_id', 
        ]);
        Log::info('Request validated successfully', ['validated' => $data]);
        // 2) Rebuild exactly your invoice array
        //    (I’m pulling passengers directly from your 'attempts' JSON column)
        $order = \DB::table('orders')->where('booking_id', $data['booking_id'])->first();

        $passengers = Arr::wrap(json_decode($order->passengers, true));

        // Build `invoice` totals
        $invoice = [
            'adults'         => 0,
            'children'       => 0,
            'infants'        => 0,
            'travelers'      => 0,
            'total_adults'   => 0,
            'total_children' => 0,
            'total_infants'  => 0,
            'total_travelers'  => 0,
            'subtotal'       => 0,
            'tax'            => 0,
            'total'          => 0,
        ];
        foreach ($passengers as $p) {
            if (($p['passengerType'] ?? '') === 'adult') {
                $invoice['adults']++;
                $invoice['total_adults'] += $p['unitPrice'] * $p['passengers'];
            } elseif (($p['passengerType'] ?? '') === 'child') {
                $invoice['children']++;
                $invoice['total_children'] += $p['unitPrice'] * $p['passengers'];
            } else {
                $invoice['travelers']++;
                $invoice['total_travelers'] += $p['unitPrice'] * $p['passengers'];
            }
        }
        $invoice['unitPrice'] =  $p['unitPrice'];
        $invoice['subtotal'] = $order->paid;
        $invoice['tax']      = 0;
        $invoice['total']    = $invoice['subtotal'];

        // 3) Retrieve Stripe payment‐intent data
        $stripeResponse = StripeController::getPaymentIntent($order->payment_id);
        $stripeJson     = json_decode($stripeResponse->getContent(), true);

        // 4) Pull your `OrdersPrint` (so you can get $orders, if needed in the invoice view)
        $fakeRequest = Request::create('/', 'GET', [
            'booking_id' => $data['booking_id'],
            'orderId'    => $order->duffel_id,
            'q'          => $order->payment_id,
        ]);
        $ordersPrint = ToursFilters::OrdersPrint($fakeRequest);

        // 5) Compose the array your invoice Blade expects
        $invoice_content = [
            'data'   => $stripeJson['data'],
            'orders' => $ordersPrint,
            'values' => $invoice,
        ];

        // 6) Render & stream the PDF
        $pdf = Pdf::loadView('emails.invoice', $invoice_content);

        // Optionally force download:
        return $pdf->download('invoice.pdf');

        // Or just inline in the browser:
        return $pdf->stream('invoice.pdf');
    }
}
