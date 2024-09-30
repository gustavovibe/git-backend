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

        .svg {
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
                <img style="width: 20%" src="{{ public_path('images/logo.png') }}" alt="">
            </div>
            <div>
                <label style="margin-top: 20px;">Manage my booking</label>
                <P style="text-align: right;">For more info, open <u>Help & support</u> </P>
            </div>
        </div>
    </div>
    <br>
    <div style="text-align: center; margin: 20px;">
        <label style="display: block; width: 100%; max-width: 100%; border: 2px solid #82CF45; padding: 3%; border-radius: 15px; background-color: rgba(0, 128, 0, 0.1); font-size: 1.2rem; color: #82CF45;">
            Your trip has been booked successfully! Confirmation
            <b>#:{{ $orders->booking_id }}.</b>
        </label>
    </div>
    <br>
    <div class="textG mh" style="text-align: justify;">
        <h1>Bon voyage, <a class="Tcolor">{{ $orders->user->name }}!</a></h1>
        <p>Thank you for your booking!</p>
        <p>Below is a summary of the trip.</p>
        <p>Your flight <a id="color">tickets</a>, adventure <a id="color">itinerary</a><a style="color:red;">*</a>, and purchase <a id="color">invoice</a> are attached or can be downloaded from the
            links below.</p>
        <p>You can also <a id="under">contact us</a> if any help needed, or view and make changes to your booking inside the <a id="under" >Travelers' portal</a>.</p>
        <p style="font-style: italic;" >
            <a style="color:red;">*</a>The itinerary is approximate and may be subject to minor changes (without affecting the start/end date or
            locations) based on <a id="under">booking terms and conditions</a>. Final trip notes from the adventure organizer, including
            contact details for your tour leader, the exact schedule, and a list of accommodations, will be provided by
            our support team 2-4 weeks before departure (depending on the specific adventure organizer).</p>
    </div>
    <br>

    <div>
        <table width="100%">
            <tr>
                <td>
                    <h2>Adventure summary</h2>
                </td>
                <td style="text-align: right;">
                    <h3 style="color: orange;text-decoration: underline;">Download itinerary</h3>
                </td>
            </tr>
        </table>
        <div class="Borderg" style="padding: 1%;">
            <table width="100%">
                <tr>
                    <td style="width: 50%; vertical-align:middle;padding:2%;">
                        <img src="{{ $orders->image }}" style="width: 90%; height: 50%; border-radius:12px;" />
                    </td>
                    <td style="width: 80%; vertical-align: top;">
                        <h3 class="Tcolor">{{ $orders->tour->tour_name }}</h3>
                        <p>
                            <img style="width: 16px; height: 16px; vertical-align: middle;"
                                src="{{ public_path('images/star.svg') }}">
                            <b class="Tcolor">{{ $orders->ratings_overall }}</b> {{ $orders->reviews_count }} reviews
                        </p>
                        <table width="100%">
                            <tr>
                                <td style="padding: 1%;"><img style="width: 16px; height: 16px; vertical-align: middle;"
                                        src="{{ public_path('images/double-right.svg') }}"></td>
                                <td style="padding: 1%;">Starts in: {{ $orders->start_city . ',' . $orders->origin }}
                                </td>

                                <td style="padding: 1%;"><img style="width: 16px; height: 16px; vertical-align: middle;"
                                        src="{{ public_path('images/double-left.svg') }}"></td>
                                <td style="padding: 1%;">Ends in:
                                    {{ $orders->end_city . ',' . $orders->f_destination }}</td>
                            </tr>
                            <tr>
                                <td style="padding: 1%;"><img style="width: 16px; height: 16px; vertical-align: middle;"
                                        src="{{ public_path('images/calendar-event.svg') }}"></td>
                                <td style="padding: 1%;">Starts on:
                                    {{ \Carbon\Carbon::parse($orders->start)->format('M d, Y') }}</td>

                                <td style="padding: 1%;"><img style="width: 16px; height: 16px; vertical-align: middle;"
                                        src="{{ public_path('images/calendar-event.svg') }}"></td>
                                <td style="padding: 1%;">Ends on:
                                    {{ \Carbon\Carbon::parse($orders->end)->format('M d, Y') }}</td>
                            </tr>
                            <tr>
                                <td style="padding: 1%;"><img style="width: 16px; height: 16px; vertical-align: middle;"
                                        src="{{ public_path('images/clock.svg') }}"></td>
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
                        <h3 style="color: orange;text-decoration: underline;">Download tickets</h3>
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
                                    <img class="svg airplane-icon" src="{{ public_path('images/airplane.svg') }}"
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
                                                alt="{{ $orders->flightTour->flight['data']['owner']['name'] }}"
                                                style="width: 50%; height: 10%; border-radius: 12px;"></td>
                                        <td>{{ $orders->flightTour->flight['data']['owner']['name'] }}</td>
                                        <td style="margin-left:50%;"><img
                                                src="{{ public_path('images/chevron-down.svg') }}"></td>
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
                        <h4 style="color: orange;text-decoration: underline;">Download invoice</h4>
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
                                    <img id="img_" src="{{ public_path('images/transfer.svg') }}">
                                    <h5>Airport transfer</h5>
                                    <p style="width:100%">Airport transfers not included adventure?</p>
                                    <a>Go somewhere <img id="iconic"
                                            src="{{ public_path('images/box-arrow-up-right.svg') }}"> </a>

                                </div>
                            </td>
                            <td>
                                <div>
                                    <img id="img_" src="{{ public_path('images/insurance.svg') }}">
                                    <h5>Insurance</h5>
                                    <p style="width:100%">Available up to 24h before departure</p>
                                    <a>Manager Insurance <img id="iconic"
                                            src="{{ public_path('images/box-arrow-up-right.svg') }}"></a>
                                </div>
                            </td>
                            <td>
                                <div>
                                    <img id="img_" src="{{ public_path('images/accommodation.svg') }}">
                                    <h5>Accommodation</h5>
                                    <p style="width:100%">Need pre- or post-tour accommodation?</p>
                                    <a>Book Accommodation <img id="iconic"
                                            src="{{ public_path('images/box-arrow-up-right.svg') }}"></a>
                                </div>
                            </td>
                            <td>
                                <div>
                                    <img id="img_" src="{{ public_path('images/activities.svg') }}">
                                    <h5>Activities</h5>
                                    <p style="width:100%">Got extra days in the destination before or after
                                        the adventure?</p>
                                    <a>Find Activities <img id="iconic"
                                            src="{{ public_path('images/box-arrow-up-right.svg') }}"></a>
                                </div>
                            </td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>

        {{--   <div class="foot" style="page-break-inside: always; margin-top:20%">
            <div>
                <div id="el" style="float: left; width: 25%; margin-right: 5%;">
                    <h5>Company</h5>
                    <div class="row">
                        <table>
                            <tr>
                                <td>
                                    <p>About us</p>
                                </td>
                                <td style="width: 5px;"></td>
                                <td>
                                    <p>Afiliates</p>
                                </td>
                            </tr>
                            <tr>
                                <td>
                                    <p>Blog</p>
                                </td>
                                <td></td>
                                <td>
                                    <p>FAQ</p>
                                </td>
                            </tr>
                            <tr>
                                <td>
                                    <p>Reviews</p>
                                </td>
                                <td></td>
                                <td>
                                    <p>Contact us</p>
                                </td>
                            </tr>
                        </table>

                    </div>
                </div>
                <div id="el" style="float: left; width: 30%;">
                    <h5>Customer Support</h5>
                    <table>
                        <tr>
                            <td rowspan="3"> <img id="flag" src="{{ public_path('images/phone.svg') }}">
                            </td>
                            <td>
                                <p id="pf"><img id="flag" src="{{ public_path('images/usa.png') }}"
                                        style="width: 12%;margin-bottom:-5%;">
                                    +1-201-500-1310</p>
                            </td>
                            <td>
                        </tr>
                        <tr>
                            <p id="pf"><img id="flag" src="{{ public_path('images/uk.png') }}"
                                    style="width: 12%;;margin-bottom:-5%;">
                                +44-7440-963840</p>
                            </td>
                        </tr>
                        <tr>
                            <td>
                                <p id="pf"><img id="flag" src="{{ public_path('images/mex.png') }}"
                                        style="width: 12%;;margin-bottom:-5%;">
                                    +52-55-8526-6910</p>
                            </td>
                        </tr>
                    </table>
                    <label>
                        <p>Mon-Sun: 9 am - 11 pm (EST)</p>
                    </label>
                </div>
                <div id="el" style="float: left; width: 25%;">
                    <h5>Supporting</h5>
                    <table>
                        <tr>
                            <td>
                                <img id="sp" src="{{ public_path('images/united.jpeg') }}" alt=""
                                    style="width: 100%;">
                            </td>
                        </tr>
                        <tr>
                            <td>
                                <img id="sp" src="{{ public_path('images/future.jpeg') }}" alt=""
                                    style="width: 100%;">
                            </td>
                        </tr>
                        <tr>
                            <td>
                                <img id="sp" src="{{ public_path('images/adventure.jpeg') }}" alt=""
                                    style="width: 100%;">
                            </td>
                        </tr>
                    </table>


                </div>
                <div id="el" style="float: left; width: 25%;">
                    <h5>Follow Us</h5>
                    <div class="social-icons">
                        <img width="14" height="16" src="{{ public_path('images/facebook.svg') }}">
                        <img width="14" height="16" src="{{ public_path('images/twitter-x.svg') }}">
                        <img width="14" height="16" src="{{ public_path('images/pinterest.svg') }}">
                        <img width="14" height="16" src="{{ public_path('images/instagram.svg') }}">
                        <img width="14" height="16" src="{{ public_path('images/youtube.svg') }}">
                        <img width="14" height="16" src="{{ public_path('images/tiktok.svg') }}">
                    </div>
                    <h5>Payment Methods</h5>
                    <img id="pay" src="{{ public_path('images/pay_methods.jpeg') }}" alt="">
                </div>
            </div>
        </div>
 --}}
        <div class="footer">
            <hr>
            <div>
                <table width="100%">
                    <tr>
                        <label style="font-size: 18px;">
                            Excellent
                            <img src="{{ public_path('images/ranking.png') }}" alt=""
                                style="vertical-align: middle;">
                        </label>
                        <td style="text-align: right;">
                            <img src="{{ public_path('images/trust-index.png') }}" alt="">
                        </td>
                    </tr>
                </table>
            </div>
            <hr>
            <br>
            <div>
                <table width="100%">
                    <tr>
                        <img style="width:35%;" src="{{ public_path('images/logo.png') }}">
                        <td style="text-align: right;">
                            <img src="{{ public_path('images/face-icon.png') }}">
                            <img src="{{ public_path('images/insta-icon.png') }}">
                            <img src="{{ public_path('images/youtube-icon.png') }}">
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
