<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <title>Document</title>
    <style>
        .column {
            float: left;
            width: 50%;
        }

        .row {
            display: flex;
        }

        .btnT {
            display: flex;
            justify-content: space-between;

            text-align: right;
            margin-top: 40px;
            align-items: center;
        }

        .btnT label {
            background-color: orange;
            color: white;
            border-color: orange;
            border-radius: 5px;
            font-weight: bold;
            padding: 10px;
        }

        .btnT u {
            font-weight: bold;
            text-underline-position: below;

        }

        .btnT img {
            width: 70%;
            float: left;
        }

        .t1 {
            margin: 0 20px;
            align-content: center;
            text-align: center;
            margin-top: 20px;
        }

        .t1 label {
          /*   border-style: solid; */
            padding: 3%;
            border-radius: 15px;
            background-color: rgba(0, 128, 0, 0.1);
        }

        .textG  #color {
            color:#82CF45;
        }
        .textG  #under {
            color:#82CF45;
            text-decoration: underline;
        }


        .tGroup {
            display: flex;
            justify-content: space-between;
            margin: 0 20px;
            text-align: right;
            align-items: center;
        }

        .tGroup u {
            color: orange;
            font-weight: bold;
        }

        .Borderg {
            border-color: #82CF45;
            border-style: solid;
            border-radius: 15px;

        }

        .tDesc img {
            padding: 8%;
            border-radius: 15px;
            display: block;
            margin: 0 auto;
            width: 80%;
            height: 80%;
        }

        .tDesc b {
            border-style: solid;
            border-radius: 5px;
            border-color: #82CF45;
            border-width: 2px;
            padding: 1%;
        }

        .fGroup {
            padding: 1%;
            display: flex;
            flex-direction: row;
            margin-bottom: 20px;
        }

        .fGroup #t2 {
            /* text-align: center; */
            align-content: center;
            color: orange;
            border-style: solid;
            padding: 1%;
            display: inline-block;
            margin-top: 1%;
            border-radius: 8px;
            font-weight: bold;
        }

        .ffGroup {
            display: flex;
            justify-content: space-between;
            text-align: right;
            align-items: center;
        }

        .ffGroup a {
            color: gray;
            font-weight: bold;
        }

        .ffGroup label {
            font-weight: bold;
            font-size: 20px;
            margin-bottom: 10px;
        }

        .fffgroup {
            border-width: 1px;
            border-style: solid;
            border-radius: 10px;
            border-color: gainsboro;
            border-width: 2px;

        }

        .fffgroup .column:first-child {
            text-align: right;
            width: 20%;
        }

        .fffgroup .column:last-child {
            text-align: right;
            align-content: center;
        }

        .fffgroup label {
            font-weight: bold;
        }

        .Tcolor {
            color: #82CF45;
        }

        .Tscolor {
            color: grey;
        }

        .png {
            vertical-align: middle;
            margin: 0 5px;

        }

        .Tbox {
            border-style: solid;
            border-radius: 5px;
            border-color: #82CF45;
            border-width: 2px;
            color: #82CF45;
            margin-bottom: 1em;
            display: inline-block;
        }

        .mh {
            margin: 0 20px;
        }

        @media (max-width: 768px) {
            .mh {
                margin: 0 10px;
            }
        }

        @media (max-width: 480px) {
            .mh {
                margin: 0 5px;
            }
        }

        .t1 {
            width: 100%;
            border-collapse: collapse;
            margin-left: 1px;
        }

        .t1 td:first-child {
            text-align: left;
        }

        .t1 td:last-child {
            text-align: right;
        }

        .t1 a {
            color: #82CF45;
        }

        .t1 tbody td {
            border-bottom: gainsboro;
            border-bottom-style: solid;
            padding: 1%;
        }

        .t1 tfoot td {
            color: #82CF45;
            font-weight: bold;
            padding-right: 1%;
        }


        .tt {

            justify-content: center;
            align-items: center;
            text-align: center;
            margin: 20px;

        }

        .tt label {
            width: 100%;
            border: 2px solid #82CF45;
            padding: 3%;
            border-radius: 15px;
            background-color: rgba(0, 128, 0, 0.1);
            font-size: 1.5rem;
            max-width: 100%;
        }


        @media (max-width: 768px) {
            .tt label {
                font-size: 1.2rem;
                padding: 5%;
            }

            .tt {
                margin: 10px;
            }
        }


        @media (max-width: 480px) {
            .tt label {
                font-size: 1rem;
                padding: 6%;
            }

            .tt {
                margin: 5px;
            }
        }


        .laterald {
            display: flex;
            justify-content: space-between;
        }

        .grid-container {
            display: grid;
            padding: 1%;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 10px;
        }

        .grid-container div label {
            color: #82CF45;
        }

        .grid-container div h4 {
            text-decoration: underline;
        }

        .Recomend #container {
            width: 100%;
            margin-left: -10px;
            border: 1px solid #ddd;
            border-radius: 12px;
            padding: 3%;
            background-color: rgba(0, 128, 0, 0.1);
        }

        .Recomend h1 {
            text-align: center;
        }

        .Recomend p {
            text-align: center;
        }



        .clearfix::after {
            content: "";
            display: table;
            clear: both;
        }

        .card {
            width: 100%;
        }


        .card #img_ {
            width: 80%;
            height: 100px;
            /* padding: 5%; */
            border-radius: 10px
        }

        .card #iconic {
            margin-top: 2%;
            width: 10%;
        }

        .card td {
            width: 20%;
            padding: 1%
        }



        .card a {
            background-color: orange;
            color: white;
          /*   padding: 1%; */
            padding-top: 2%;
            font-weight: bold;
            border-radius: 5px;
            font-size: 11px;
            display: block;
            text-align: center;
            align-content: center;
            vertical-align: middle;
            height: 3%;
            margin-bottom: 3%;
            margin-left: 3%;
            margin-right: 3%;
        }

        .card p {
            font-size: 12px;
            text-align: center;
            height: 7%;
            color: gray;
        }

        .card h5 {
            text-align: center;
        }

        .card div {
            background-color: white;
            padding: 2%;
            border-radius: 12px;
        }

        .footer hr{
            color: #ddd;
            margin-bottom: 3%;
            margin-top: 3%;
        }

        .footer label{
            color: gray;
        }

        .footer p{
            color: gray;
        }
        .footer a{
            text-decoration: underline;
        }

        .line-container {
            display: flex;
            align-items: center;
            justify-content: center;
            position: relative;
            width: 100%;
            height: 200px;
            /* Adjust height as needed */
            margin: 20px 0;
        }

        .line {
            width: 2px;
            height: 100%;
            margin-left: 50%;
            background-color: gainsboro;
            position: relative;
        }

        .line::before,
        .line::after {
            content: '';
            width: 10px;
            height: 10px;
            background-color: #ddd;
            border-radius: 50%;
            position: absolute;
            left: 80%;
            transform: translateX(-50%);
        }

        .line::before {
            top: 0;
        }

        .line::after {
            bottom: 0;
        }

        .airplane-icon {
            position: absolute;
            top: 50%;
            left: 48.5%;
            transform: translate(-50%, -50%);
            width: 20px;
            height: auto;
        }

        .icon {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-70%, -70%) rotate(180deg);
            /* Rotate the icon to point down */
            font-size: 24px;
        }
    </style>
</head>

<body style="font-family: 'Canaro', sans-serif;">
    <br>
    <div>
        <div class="lateralD btnT mh">
            <div>
                <img style="width: 20%" src="data:image/png;base64,{{ base64_encode(file_get_contents(public_path('images/logo.png')))}}" alt="">
            </div>
            <div>
                <label style="margin-top: 20px;">Manage my booking</label>
                <P style="text-align: right;">For more info, open <u>Help & support</u> </P>
            </div>
        </div>
    </div>
    <br>
 {{--    <div style="text-align: center; margin: 20px;">
        <label style="display: block; width: 100%; max-width: 100%; border: 2px solid #82CF45; padding: 3%; border-radius: 15px; background-color: rgba(0, 128, 0, 0.1); font-size: 1.2rem; color: #82CF45;">
            Your trip has been booked successfully! Confirmation
            <b>#:{{ $orders->booking_id }}.</b>
        </label>
    </div> --}}
    <div>
            @if ($orders->booking_status !='pending')
            <h1>We've booked everything for your trip!</h1>

            @else
            <h1>All good so far! We're now confirming your booking with the tour operator.</h1>
            @endif
        </div>
    <br>
    <div>
        <table style="width:100%">
            <tr>
                <td style="font-weight: bold;color:gray">BOOKING NUMBER</td>
                <td style="font-weight: bold;color:gray">BOOKING STATUS</td>
            </tr>
            <tr>
                <td><b>{{ $orders->booking_id }}</b> </td>
                <td>
                    @if ($orders->booking_status !='pending')
                    <img style="width: 25%; height: 3%;"
                    src="data:image/png;base64,{{ base64_encode(file_get_contents(public_path('images/confirmed.png')))}}">
                    @else
                    <img style="width: 25%; height: 3%;"
                    src="data:image/png;base64,{{ base64_encode(file_get_contents(public_path('images/processing.png')))}}">
                    @endif

                </td>
            </tr>
        </table>
    </div>
    <div class="textG mh" style="text-align: justify;">
       @if ($orders->booking_status!='pending')
       <p> <b>{{ $orders->user->name }}</b>, thank you for choosing Vibe Adventures! We're happy to confirm that your reservation is <p style="color: #82CF45">complete</p> </p>
       <img style="width: 100%; height: 40%;"
       src="data:image/png;base64,{{ base64_encode(file_get_contents(public_path('images/pay_done.png')))}}">
       <br>
       @else
       <p> <b>{{ $orders->user->name }}</b>, thank you for choosing Vibe Adventures! </p>
       <p>We've received you payment and are <a style="color: orange; font-weight:bold">confirming</a> yout booking with he tour operator(Your flights are currently reserved). This process can take up to 72 hours. We'll send your final
        booking confirmation and e-ticket as soon as posible.</p>
        <p>Her's what happens next:</p>
        <img style="width: 100%; height: 40%;"
                                src="data:image/png;base64,{{ base64_encode(file_get_contents(public_path('images/pay_pending.png')))}}">
        <br>


       @endif
        <br>
       <div style="text-align: center">
        <div>
            <a href="https://vibeadventures.be/api/boooking-summary-pdf?tour_id={{ $orders->booking_id }}" style="background-color: orange; padding:2%;color:white;border-radius:10px;font-weight:bold;text-decoration: none;">View booking</a>
        </div>
        <br>
        <div>
            <label>If you need help, <a style="color:#82CF45;text-decoration:none;" href="https://hopeful-nobel.74-208-189-166.plesk.page/contact-us" >contact us</a></label>
        </div>
        </div>
       <br>
       <div style="text-align: justify; border-style: dotted; padding:2%; border-radius:15px;border-color:#82CF45">

           @if ($orders->booking_status!='pending')
           <div style="page-break-inside: avoid">
            <p style="color: gray"> <b>FREQUENTLY ASKED QUESTIONS</b> </p>
               <p><b>How do i check in for my flight?</b></p>
               <p>You can check in online through the airline's website or at the airport check-in counter. Make sure to download your e-ticket and complete the process well in advance.</p>
           </div>
           <div style="page-break-inside: avoid">
               <p><b>Where can i find baggage and check-in policies?</b></p>
               <p>Review the conditions outlined by each airline carrier in your flight summary before your trip. </p>
           </div>
           <div style="page-break-inside: avoid">
               <p><b>Are the accommodations guarented as listed in the tour description or trip notes?</b></p>
               <p>Accommodations are approximate and subject to change based on availability, group size, and other factors. if changes occour, a similar category accommodation will be provided </p>
           </div>
           <div style="page-break-inside: avoid">
               <p><b>Will need to pay any additional fees for accommodations?</b></p>
               <p>In some locations, travelers may need to payy a municipal tax directly to hotels upon arrival. </p>
           </div>
           <div style="page-break-inside: avoid">
               <p><b>What happens if the weather impacts my scheduled activities?</b></p>
               <p>In case of unfavorable weather or other valid reasons, the sequence and duration of activities may be modifued or canceled without prior notice.</p>
           </div>
           @else
           <div style="page-break-inside: avoid;">
            <p style="color: gray"> <b>FREQUENTLY ASKED QUESTIONS</b> </p>
               <p><b>When will i get the final booking confirmation?</b></p>
               <p>You'll receive the final booking confirmation as soon as we get it from the tour operator, as we don't operate the adventures ourselves.</p>
               <p>Since we gather data from multiple tour operators to offer you the best selections and prices, our booking process is more complex. Most bookings are confirmed inmmediately, but occasionally, it may take uo to 72 hours. Rest assured, we prioritize bookings to ensure everyone can travel as planned.</p>
           </div>
           <div style="page-break-inside: avoid;">
               <p><b>What happens to my money?</b></p>
               <p>We've held the necessary funds for your booking to secure the flights and adventure, but the money remains with your bank and wont' be charged until the booking is confirmed. If we're unable to confirm your booking within 72 hours, it will be automatically canceled, and your request fully refunded.</p>
           </div>
           <div style="page-break-inside: avoid;">
               <p><b>Do i need a visa for my trip</b></p>
               <p>Check visa requirements for the country in your adventure itinerary and flight summary. Don't forget to check if you need a transit visa as well. </p>
           </div>
           @endif
       </div>
    </div>
    <br>
    <div>
        <table width="100%">
            <tr>
                <td>
                    <h2>Adventure summary</h2>
                </td>
                <td style="text-align: right;">
                    <h3>
                        <a style="color: orange;text-decoration: underline;" href="https://vibeadventures.be/api/boooking-summary-pdf?tour_id={{ $orders->tour_id }}">
                            Download itinerary
                        </a>
                    </h3>
                </td>
            </tr>
        </table>
        <div class="Borderg" style="padding: 1%;">
            <table width="100%">
                <tr>
                    @if ($orders->image)
                    <td style="width: 50%; vertical-align:middle;padding:2%;" >
                        <img src="{{ $orders->image }}" style="width: 90%; height: 150px; border-radius:12px;" />
                    </td>
                    @endif
                    <td style="width: 80%; vertical-align: top;">
                        <h3 class="Tcolor">{{ $orders->tour->tour_name }}</h3>
                        <p>
                            <img style="width: 16px; height: 16px; vertical-align: middle;"
                                src="data:image/png;base64,{{ base64_encode(file_get_contents(public_path('images/star.png')))}}">
                            <b class="Tcolor">{{ $orders->ratings_overall }}</b> {{ $orders->reviews_count }} reviews
                        </p>
                        <table width="100%">
                            <tr>
                                <td style="padding: 1%;"><img style="width: 16px; height: 16px; vertical-align: middle;"
                                        src="data:image/png;base64,{{ base64_encode(file_get_contents(public_path('images/double-right.png')))}}"></td>
                                <td style="padding: 1%;">Starts in: {{ $orders->start_city . ',' . $orders->origin }}
                                </td>

                                <td style="padding: 1%;"><img style="width: 16px; height: 16px; vertical-align: middle;"
                                        src="data:image/png;base64,{{ base64_encode(file_get_contents(public_path('images/double-left.png')))}}"></td>
                                <td style="padding: 1%;">Ends in:
                                    {{ $orders->end_city . ',' . $orders->f_destination }}</td>
                            </tr>
                            <tr>
                                <td style="padding: 1%;"><img style="width: 16px; height: 16px; vertical-align: middle;"
                                        src="data:image/png;base64,{{ base64_encode(file_get_contents(public_path('images/calendar-event.png')))}}"></td>
                                <td style="padding: 1%;">Starts on:
                                    {{ \Carbon\Carbon::parse($orders->start)->format('M d, Y') }}</td>

                                <td style="padding: 1%;"><img style="width: 16px; height: 16px; vertical-align: middle;"
                                        src="data:image/png;base64,{{ base64_encode(file_get_contents(public_path('images/calendar-event.png')))}}"></td>
                                <td style="padding: 1%;">Ends on:
                                    {{ \Carbon\Carbon::parse($orders->end)->format('M d, Y') }}</td>
                            </tr>
                            <tr>
                                <td style="padding: 1%;"><img style="width: 16px; height: 16px; vertical-align: middle;"
                                        src="data:image/png;base64,{{ base64_encode(file_get_contents(public_path('images/clock.png')))}}"></td>
                                <td style="padding: 1%;">Duration: {{ $orders->tour->tour_length_days }} days</td>
                                <td></td>
                                <td></td>
                            </tr>
                        </table>
                    </td>
                </tr>
            </table>
        </div>
        <div style="page-break-before: always;">
            <table width="100%">
                <tr>
                    <td>
                        <h2>Flights summary</h2>
                    </td>
                    <td style="text-align: right;">
                        <h3 >
                            <a style="color: orange;text-decoration: underline;" href="https://vibeadventures.be/api/get-tickets?orderId={{ $orders->duffel_id }}">
                                Download tickets
                            </a>
                        </h3>
                    </td>
                </tr>
            </table>
            <div class="Borderg" style="padding: 1%;">
                @foreach ($orders->flightTour->flight['data']['slices'] as $or)
                    <table style="width: 100%; border: 1px solid #ddd; border-radius: 12px; padding: 1%;">
                        <tr>
                            <td style="width: 25%; text-align: center; vertical-align: top;">
                                <label>{{ \Carbon\Carbon::parse($or['segments'][0]['departing_at'])->format('H:i') }}</label><br>
                                <p>{{ \Carbon\Carbon::parse($or['segments'][0]['departing_at'])->format('D, d/m') }}
                                </p>
                                <p class="Tbox">
                                    {{ \Carbon\CarbonInterval::make($or['duration'])->format('%hh %im') }}</p>
                                <br>
                                <label>{{ \Carbon\Carbon::parse($or['segments'][0]['arriving_at'])->format('H:i') }}</label><br>
                                <p>{{ \Carbon\Carbon::parse($or['segments'][0]['arriving_at'])->format('D, d/m') }}</p>
                            </td>
                            <td style="width: 10%; text-align: center; vertical-align: top; padding: 0;">
                                <div class="line-container">
                                    <div class="line"></div>
                                    <img class=" airplane-icon" src="data:image/png;base64,{{ base64_encode(file_get_contents(public_path('images/airplane.png')))}}"
                                        alt="Icon">
                                </div>
                            </td>
                            <td style="width: 40%; text-align: left; vertical-align: top;">
                                <label class="Tcolor">{{ $or['origin']['city']['name'] }}</label><br>
                                <p>{{ $or['origin']['name'] }}</p>
                                <table style="width: 100%">
                                    <tr>
                                        <td style=" width:20%"><img
                                            src="{{ $orders->flightTour->flight['data']['owner']['logo_symbol_url'] }}"
                                            {{-- src="{{ public_path('storage/images/logo_flight.png')}}" --}}
                                                style="width: 50%; height: 10%; border-radius: 12px;"></td>
                                        <td>{{ $orders->flightTour->flight['data']['owner']['name'] }}</td>
                                        <td style="margin-left:50%;"><img
                                                src="data:image/png;base64,{{ base64_encode(file_get_contents(public_path('images/chevron-down.png')))}}"></td>
                                    </tr>
                                </table>
                                <label class="Tcolor">{{ $or['destination']['name'] }}</label><br>
                                <p>{{ $or['destination']['city_name'] }}</p>
                            </td>
                        </tr>
                        <tr>
                            <td colspan="4" style="height: 20px;"></td> <!-- Espacio entre filas -->
                        </tr>
                    </table>
                    <br>
                @endforeach
            </div>
        </div>

        <div style="page-break-inside: avoid;">
            <h2 class="mh">Payment</h2>
            <br>
            <div class="Borderg " style="padding: 2%;">
                <div>
                    <h3>Price breakdown</h3>
                </div>
                <div>
                    <table style="width: 100%;">
                            @foreach ($orders->flightTour->tour['accommodations'] as $accommodation)
                                @if ($accommodation['type'] == 'basePrice')
                                    <tr>
                                        <td>
                                            <a style="color:#82CF45;">${{ number_format($accommodation['prices'][0]['price_per_pax'], 2) }}</a>
                                            USD x
                                            <a style="color:#82CF45;">{{ count($orders->flightTour->tour['passengers']) }}</a> adult(s)
                                        </td>
                                        <td style="text-align: right;">${{ number_format($accommodation['prices'][0]['price_per_pax'] * count($orders->flightTour->tour['passengers']), 2) }}
                                            USD</td>
                                    </tr>
                                    <br>
                                @elseif ($accommodation['type'] == 'accommodation')
                                    <tr>
                                        <td>
                                            <a style="color:#82CF45;">${{ number_format($accommodation['prices'][0]['price_per_pax'], 2) }}</a>
                                            USD x
                                            <a style="color:#82CF45;">{{ $accommodation['prices'][0]['pax_count'] }}</a> single
                                        </td>
                                        <td style="text-align: right;">${{ number_format($accommodation['prices'][0]['price_per_pax'] * $accommodation['prices'][0]['pax_count'], 2) }}
                                            USD</td>
                                    </tr>
                                @endif
                            @endforeach
                    </table>
                </div>
                <div style="padding: 1%;">
                    <div class="laterald">
                        <table style="width: 100%;margin-left:2%;">
                            <tr>
                                <td>
                                    <p style="color:gray;font-size:10px;">Total price of the trip including all taxes and fees</p>
                                </td>
                                <td style="text-align: right;">
                                    <h3>${{ number_format($orders->flightTour->tour['total_value'], 2) }} USD</h3>
                                </td>
                            </tr>
                        </table>
                    </div>

                    <table style="width: 100%;margin-left:2%;" class="textG" >
                            <tr>
                                <td>
                                    <a style="font-style: italic;">Flights to/from destination</a>
                                </td>
                                <td style="text-align: right;"> <a id="color">included</a></td>
                            </tr>
                    </table>
                    <br>
                    <table style="width: 100%;margin-left:2%;" class="textG" >
                        <tr>
                            <td>
                                <a style="font-style: italic;">Multi-day adventure</a>
                            </td>
                            <td style="text-align: right;"><a id="color">included</a></td>
                        </tr>
                    </table>
                </div>


  <table width="100%">
                <tr>
                    <td>
                        <h3>Payment history</h3>
                    </td>
                    <td style="text-align: right;">
                        <a href="{{ $url_payment }}" style="text-decoration: none">

                            <h4 style="color: orange;text-decoration: underline;">Download invoice</h4>
                        </a>
                    </td>
                </tr>
            </table>
                <table style="width: 100%;" >
                    <tbody>
                        <tr>
                            <td>{{ $orders->payment_method }}</td>
                            <td>{{ \Carbon\Carbon::parse($orders->departure)->format('M d, Y') }}</td>
                            <td style="text-align:right;">${{ number_format($orders->flightTour->tour['total_value'], 2) }} USD</td>
                        </tr>
                    </tbody>
                    <tfoot>
                        <td></td>
                        <td style="color:#82CF45;">Total</td>
                        <td style="color:#82CF45;text-align:right;">${{ number_format($orders->flightTour->tour['total_value'], 2) }} USD</td>
                    </tfoot>
                </table>
            </div>
        </div>


        <div style="page-break-before: always;">
            <h2>Participants</h2>
            <table style="width: 100%; " class="Borderg">
                <tr>
                    @php $counter = 1; @endphp
                    @foreach ($orders->flightTour->tour['passengers'] as $passenger)
                        <td style="width: 33%; padding: 5px; vertical-align: top;">
                            <h4 style="text-decoration: underline;">Participant {{ $counter }}</h4>
                            <label style="color: #82CF45;font-weight:bold;">First Name:</label>
                            <p>{{ $passenger['fields']['first_name'] }}</p>
                            <label style="color: #82CF45;font-weight:bold;">Last Name:</label>
                            <p>{{ $passenger['fields']['last_name'] }}</p>
                        </td>
                        @if ($counter % 3 == 0 && !$loop->last)
                </tr>
                <tr>
                    @endif
                    @php $counter++; @endphp
                    @endforeach
                </tr>
            </table>
        </div>
        <br>
        <div style="page-break-before: always;" class="Recomend">
            <div id="container">
                <h2 style="text-align: center;">Recommended</h2>
                <p style="color: grey; font-size:12px;">Adding these services to your trip now can save you money to
                    purchasing them later or in the
                    destination</p>
                <div>
                    <table class="card">
                        <tr>
                            <td>
                                <div>
                                    <img id="img_" src="data:image/png;base64,{{ base64_encode(file_get_contents(public_path('images/transfer.png')))}}">
                                    <h5>Airport transfer</h5>
                                    <p style="width:100%">Airport transfers not included adventure?</p>
                                    <a>Go somewhere <img id="iconic"
                                            src="data:image/png;base64,{{ base64_encode(file_get_contents(public_path('images/box-arrow-up-right.png')))}}"> </a>

                                </div>
                            </td>
                            <td>
                                <div>
                                    <img id="img_" src="data:image/png;base64,{{ base64_encode(file_get_contents(public_path('images/insurance.png')))}}">
                                    <h5>Insurance</h5>
                                    <p style="width:100%">Available up to 24h before departure</p>
                                    <a>Manager Insurance <img id="iconic"
                                            src="data:image/png;base64,{{ base64_encode(file_get_contents(public_path('images/box-arrow-up-right.png')))}}"></a>
                                </div>
                            </td>
                            <td>
                                <div>
                                    <img id="img_" src="data:image/png;base64,{{ base64_encode(file_get_contents(public_path('images/accommodation.png')))}}">
                                    <h5>Accommodation</h5>
                                    <p style="width:100%">Need pre- or post-tour accommodation?</p>
                                    <a>Book Accommodation <img id="iconic"
                                            src="data:image/png;base64,{{ base64_encode(file_get_contents(public_path('images/box-arrow-up-right.png')))}}"></a>
                                </div>
                            </td>
                            <td>
                                <div>
                                    <img id="img_" src="data:image/png;base64,{{ base64_encode(file_get_contents(public_path('images/activities.png')))}}">
                                    <h5>Activities</h5>
                                    <p style="width:100%">Got extra days in the destination before or after
                                        the adventure?</p>
                                    <a>Find Activities <img id="iconic"
                                            src="data:image/png;base64,{{ base64_encode(file_get_contents(public_path('images/box-arrow-up-right.png')))}}"></a>
                                </div>
                            </td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>
        <div class="footer">
            <hr>
            <div>
                <table width="100%">
                    <tr>
                        <label style="font-size: 18px;">
                            Excellent
                            <img src="data:image/png;base64,{{ base64_encode(file_get_contents(public_path('images/ranking.png')))}}" alt=""
                                style="vertical-align: middle;">
                        </label>
                        <td style="text-align: right;">
                            <img src="data:image/png;base64,{{ base64_encode(file_get_contents(public_path('images/trust-index.png')))}}" alt="">
                        </td>
                    </tr>
                </table>
            </div>
            <hr>
            <br>
            <div>
                <table width="100%">
                    <tr>
                        <img style="width:35%;" src="data:image/png;base64,{{ base64_encode(file_get_contents(public_path('images/logo.png')))}}">
                        <td style="text-align: right;">
                            <img src="data:image/png;base64,{{ base64_encode(file_get_contents(public_path('images/face-icon.png')))}}">
                            <img src="data:image/png;base64,{{ base64_encode(file_get_contents(public_path('images/insta-icon.png')))}}">
                            <img src="data:image/png;base64,{{ base64_encode(file_get_contents(public_path('images/youtube-icon.png')))}}">
                        </td>
                    </tr>
                </table>
                <br>
                <div>
                    <p>300 Delaware Ave, Ste 210 #549</p>
                    <p>Wilmington, DE 19801</p>
                    <br>
                    <p>You can <a>change your email preferences</a> or view our <a>Terms & Conditions</a> and <a>Privacy
                            Policy</a></p>
                </div>
            </div>
        </div>
</body>


</html>
