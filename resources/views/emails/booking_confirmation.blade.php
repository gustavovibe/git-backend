<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <title>Booking Confirmation</title>
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
            border-style: solid;
            padding: 3%;
            border-radius: 15px;
            background-color: rgba(0, 128, 0, 0.1);
        }

        .textG {
            font-weight: bold;
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
            /* Centra la imagen horizontalmente */
            width: 80%;
            /* Ajusta el tamaño del ancho */
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

        svg {
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
            display: flex;
            justify-content: center;
            align-items: center;
            text-align: center;
            margin: 20px;
        }

        .tt label {
            border: 2px solid #82CF45;
            padding: 3%;
            border-radius: 15px;
            background-color: rgba(0, 128, 0, 0.1);
            font-size: 1.5rem;
            /* Ajusta el tamaño según sea necesario */
            max-width: 100%;
            /* Asegura que no se desborde en pantallas pequeñas */
        }

        /* Ajustes para pantallas pequeñas */
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
            grid-template-columns: repeat(3, 1fr);
            gap: 10px;
        }

        .grid-container div label {
            color: #82CF45;
        }

        .grid-container div h4 {
            text-decoration: underline;
        }

        .Recomend {
            background-color: rgba(0, 128, 0, 0.1);
            padding: 2%;
        }

        .Recomend h1 {
            text-align: center;
        }

        .Recomend p {
            text-align: center;
            font-size: 13px;
        }

        .card-container {
            display: grid;
            padding: 1%;
            width: 100%;
            grid-template-columns: repeat(4, 1fr);
        }

        .card {
            background-color: white;
            border: 1px solid #ddd;
            padding: 20px;
            border-radius: 5px;
            text-align: center;
            margin-right: 2%;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
        }

        .card img {
            width: 100%;
            height: 100px;
        }

        .card a {
            background-color: orange;
            color: white;
            padding: 4%;
            border-radius: 5px;
            font-size: 11px;
            display: block;
            margin-top: auto;
        }

        .foot {
            padding: 3px;
            background-color: black;
            color: white;
            display: block;
            flex-wrap: wrap;
            padding: 20px;
        }

        .foot h5 {
            padding: 2%;
            color: #82CF45;
            border-bottom-style: solid;
        }

        /*    .foot svg{
                margin-right: -5px;
            } */
        .social-icons {
            display: flex;
            align-items: center;
            gap: 10px;
            /* Adjust the gap as needed */
            flex-wrap: wrap;
            /* Ensures that the icons wrap to the next line if there's no enough space */
        }

        .social-icons svg {
            width: 24px;
            /* Adjust the size of the icons */
            height: 24px;
            /* Adjust the size of the icons */
        }

        .foot #sp {
            width: 90%;
            height: 20%;
            border-radius: 5px;
        }

        .foot #pay {
            width: 98%;
            height: 50px;
            border-radius: 5px;
        }

        .foot #flag {
            padding-top: 1%;
            width: 10%;
            border-radius: 5px;
            margin-right: 10px;
        }

        .foot #pf {
            display: flex;
            align-items: center;
        }

        .foot p {
            font-size: 10px;
        }

        .foot #el {
            margin-right: 2%;
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
            left: 50%;
            transform: translateX(-50%);
        }

        .line::before {
            top: 0;
        }

        .line::after {
            bottom: 0;
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
                <img src="{{ rtrim(config('frontend.url'), '/') }}/public/images/logo.png" alt="">
            </div>
            <div>
                <label style="margin-top: 20px;">Manage my booking</label>
                <P style="text-align: right;">For more info, open <u>Help & support</u> </P>
            </div>
        </div>
    </div>
    <br>
    <div class="tt ">
        <label class="Tcolor">Your trip has been booked successfully! Confirmation <b>#:{{ $orders->booking_id }}</b>
        </label>
    </div>
    <br>
    <div class="textG mh">
        <h1>Bon voyage, <a class="Tcolor">{{ $orders->user->name }}!</a></h1>
        <p>Thank you for your booking!</p>
        <ul>
            <li>Below is a summary of your trip.</li>
            <li><a class="Tcolor">Flights tickets</a> and <a class="Tcolor">receipt</a> are attached</li>
            <li>You can also contact us, view and make changes to your booking inside the <u class="Tcolor">Travelers'
                    portal</u> </li>
        </ul>
    </div>
    <br>
    <div class="tGroup mh">
        <h1>Adventure summary</h1>
        <a  style="color: orange;text-decoration: underline;" href="https://vibeadventures.be/api/boooking-summary-pdf?tour_id={{ $orders->tour_id }}">
            Download itinerary
        </a>
    </div>

    <div class="tDesc Borderg mh">
        <div class="row">
            <div class="column" style="width: 40%;">
                <img src="{{ $orders->image }}">
            </div>
            <div class="column">
                <h3 class="Tcolor">{{ $orders->tour->tour_name }}</h3>
                <p><svg style="color: orange;" styeclass=" w-6 h-6 text-gray-800 dark:text-white" aria-hidden="true"
                        xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none"
                        viewBox="0 0 24 24">
                        <path stroke="currentColor" stroke-width="2"
                            d="M11.083 5.104c.35-.8 1.485-.8 1.834 0l1.752 4.022a1 1 0 0 0 .84.597l4.463.342c.9.069 1.255 1.2.556 1.771l-3.33 2.723a1 1 0 0 0-.337 1.016l1.03 4.119c.214.858-.71 1.552-1.474 1.106l-3.913-2.281a1 1 0 0 0-1.008 0L7.583 20.8c-.764.446-1.688-.248-1.474-1.106l1.03-4.119A1 1 0 0 0 6.8 14.56l-3.33-2.723c-.698-.571-.342-1.702.557-1.771l4.462-.342a1 1 0 0 0 .84-.597l1.753-4.022Z" />
                    </svg>
                    <b class="Tcolor">{{ $orders->ratings_overall }}</b> {{ $orders->reviews_count }} reviews
                </p>
                <div class="row">
                    <div class="col">
                        <p><svg class="Tcolor w-6 h-6 text-gray-800 dark:text-white" aria-hidden="true"
                                xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none"
                                viewBox="0 0 24 24">
                                <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"
                                    stroke-width="2" d="m17 16-4-4 4-4m-6 8-4-4 4-4" />
                            </svg>
                            Starts in: {{ $orders->start_city . ',' . $orders->origin }}</p>
                        <p><svg class="Tcolor w-6 h-6 text-gray-800 dark:text-white" aria-hidden="true"
                                xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none"
                                viewBox="0 0 24 24">
                                <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"
                                    stroke-width="2"
                                    d="M4 10h16M8 14h8m-4-7V4M7 7V4m10 3V4M5 20h14a1 1 0 0 0 1-1V7a1 1 0 0 0-1-1H5a1 1 0 0 0-1 1v12a1 1 0 0 0 1 1Z" />
                            </svg>
                            Starts on: {{ \Carbon\Carbon::parse($orders->start)->format('M d, Y') }}</p>
                        <p><svg class="Tcolor w-6 h-6 text-gray-800 dark:text-white" aria-hidden="true"
                                xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none"
                                viewBox="0 0 24 24">
                                <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"
                                    stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                            </svg>
                            Duration: {{ $orders->tour->tour_length_days }} days</p>
                    </div>

                    <div class="col">
                        <p><svg class="Tcolor w-6 h-6 text-gray-800 dark:text-white" aria-hidden="true"
                                xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none"
                                viewBox="0 0 24 24">
                                <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"
                                    stroke-width="2" d="m7 16 4-4-4-4m6 8 4-4-4-4" />
                            </svg>
                            Ends in: {{ $orders->end_city . ',' . $orders->f_destination }}</p>
                        <p><svg class="Tcolor w-6 h-6 text-gray-800 dark:text-white" aria-hidden="true"
                                xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none"
                                viewBox="0 0 24 24">
                                <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"
                                    stroke-width="2"
                                    d="M4 10h16M8 14h8m-4-7V4M7 7V4m10 3V4M5 20h14a1 1 0 0 0 1-1V7a1 1 0 0 0-1-1H5a1 1 0 0 0-1 1v12a1 1 0 0 0 1 1Z" />
                            </svg>
                            Ends on:{{ \Carbon\Carbon::parse($orders->end)->format('M d, Y') }}</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="tGroup mh">
        <h1>Flights summary</h1>
        <a href="{{ rtrim(config('frontend.url'), '/') }}/tour?search=true&tourId={{ $orders->tour_id }}">
            <u>Download tickets</u>
        </a>
    </div>
    <div class="fGroup Borderg mh">
        @foreach ($orders->flightTour->flight['data']['slices'] as $or)
            <div class="fffgroup">
                <div class="row" style="padding: 1%;">
                    <div class="column">
                        <label>{{ \Carbon\Carbon::parse($or['segments'][0]['departing_at'])->format('H:i') }}</label>
                        <p>{{ \Carbon\Carbon::parse($or['segments'][0]['departing_at'])->format('D, d/m') }}</p>
                        <p class="Tbox">{{ \Carbon\CarbonInterval::make($or['duration'])->format('%hh %im') }}</p>
                        <br>
                        <label>{{ \Carbon\Carbon::parse($or['segments'][0]['arriving_at'])->format('H:i') }}</label>
                        <p>{{ \Carbon\Carbon::parse($or['segments'][0]['arriving_at'])->format('D, d/m') }}</p>
                    </div>
                    <div class="column">
                        <div class="line-container">
                            <div class="line"></div>
                            <svg xmlns="http://www.w3.org/2000/svg" width="30" height="30"
                                fill="currentColor" class="icon bi bi-airplane-engines-fill" viewBox="0 0 16 16">
                                <path
                                    d="M8 0c-.787 0-1.292.592-1.572 1.151A4.35 4.35 0 0 0 6 3v3.691l-2 1V7.5a.5.5 0 0 0-.5-.5h-1a.5.5 0 0 0-.5.5v1.191l-1.17.585A1.5 1.5 0 0 0 0 10.618V12a.5.5 0 0 0 .582.493l1.631-.272.313.937a.5.5 0 0 0 .948 0l.405-1.214 2.21-.369.375 2.253-1.318 1.318A.5.5 0 0 0 5.5 16h5a.5.5 0 0 0 .354-.854l-1.318-1.318.375-2.253 2.21.369.405 1.214a.5.5 0 0 0 .948 0l.313-.937 1.63.272A.5.5 0 0 0 16 12v-1.382a1.5 1.5 0 0 0-.83-1.342L14 8.691V7.5a.5.5 0 0 0-.5-.5h-1a.5.5 0 0 0-.5.5v.191l-2-1V3c0-.568-.14-1.271-.428-1.849C9.292.591 8.787 0 8 0" />
                            </svg>
                        </div>
                    </div>
                    <div class="column">
                        <label class="Tcolor">{{ $or['origin']['city']['name'] }}</label>
                        <p>{{ $or['origin']['name'] }}</p>
                        <div class="flex" style="display: flex; align-items: center; gap: 10px;">
                            <img src="{{ $orders->flightTour->flight['data']['owner']['logo_symbol_url'] }}"
                                alt="{{ $orders->flightTour->flight['data']['owner']['name'] }}"
                                style="width: 10%; height: 10%;border-radius: 50%;">
                            <label>{{ $orders->flightTour->flight['data']['owner']['name'] }}</label>
                            {{--   <h4>{{ $or['segments'][0]['marketing_carrier_flight_number'] }}</h4> --}}
                        </div>
                        <label class="Tcolor">{{ $or['destination']['name'] }}</label>
                        <p>{{ $or['destination']['city_name'] }}</p>
                    </div>
                    <div class="column">
                        <a style="color: #82CF45;font-weight:bold;"><svg class="w-6 h-6 text-gray-800 dark:text-white"
                                aria-hidden="true" xmlns="http://www.w3.org/2000/svg" width="24" height="24"
                                fill="none" viewBox="0 0 24 24">
                                <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"
                                    stroke-width="2" d="m19 9-7 7-7-7" />
                            </svg>
                        </a>
                    </div>
                </div>
            </div>
        @endforeach
        <br>
        {{--   <div class="Tscolor">
            <label><svg class="w-6 h-6 text-gray-800 dark:text-white" aria-hidden="true"
                    xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none"
                    viewBox="0 0 24 24">
                    <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M12 8v4l3 3m6-3a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                </svg>
                5h 55m layover</label>
        </div> --}}
        <br>

        {{--  <div style="  text-align: center;">
            <label id="t2">13 nights in destination</label>
        </div> --}}

        <br>
        <br>
    </div>
    <br>
    <h1 class="mh">Payment</h1>
    <br>
    <div class="Borderg mh" style="padding: 1%;">
        <div>
            <h3>Price breakdown</h3>
        </div>
        <div>
            <table class="t1">
                <tbody>
                    @foreach ($orders->flightTour->tour['accommodations'] as $accommodation)
                        @if ($accommodation['type'] == 'basePrice')
                            <tr>
                                <td>
                                    <a>${{ number_format($accommodation['prices'][0]['price_per_pax'], 2) }}</a> USD x
                                    <a>{{ count($orders->flightTour->tour['passengers']) }}</a> adult(s)
                                </td>
                                <td>${{ number_format($accommodation['prices'][0]['price_per_pax'] * count($orders->flightTour->tour['passengers']), 2) }}
                                    USD</td>
                            </tr>
                        @elseif ($accommodation['type'] == 'accommodation')
                            <tr>
                                <td>
                                    <a>${{ number_format($accommodation['prices'][0]['price_per_pax'], 2) }}</a> USD x
                                    <a>{{ $accommodation['prices'][0]['pax_count'] }}</a> single
                                </td>
                                <td>${{ number_format($accommodation['prices'][0]['price_per_pax'] * $accommodation['prices'][0]['pax_count'], 2) }}
                                    USD</td>
                            </tr>
                        @endif
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="laterald">
            <p style="color:gray;">Total price of the trip including all taxes and fees</p>
            <h3>${{ number_format($orders->flightTour->tour['total_value'], 2) }} USD</h3>
        </div>
        <table class="t1">
            <tbody>
                <tr>
                    <td>Flights to/from destination</td>
                    <td> <a>included</a></td>
                </tr>
                <tr>
                    <td>Multi-day adventure</td>
                    <td><a>included</a></td>
                </tr>
            </tbody>
        </table>

        <div class="tGroup">
            <h3>Payment history</h3>
            <u>Download invoice</u>
        </div>

        <table class="t1">
            <tbody>
                <tr>
                    <td>{{ $orders->payment_method }}</td>
                    <td>{{ \Carbon\Carbon::parse($orders->departure)->format('M d, Y') }}</td>
                    <td>${{ number_format($orders->flightTour->tour['total_value'], 2) }} USD</td>
                </tr>
            </tbody>
            <tfoot>
                <td></td>
                <td>Total</td>
                <td>${{ number_format($orders->flightTour->tour['total_value'], 2) }} USD</td>
            </tfoot>
        </table>
    </div>
    <br>
    <div class="mh">
        <h1>Participants</h1>
        <div class="Borderg grid-container ">
            @foreach ($orders->flightTour->tour['passengers'] as $passenger)
                <div>
                    <h4>Participant 1</h4>
                    <label>First Name</label>
                    <p>{{ $passenger['fields']['first_name'] }}</p>
                    <label>Last Name</label>
                    <p>{{ $passenger['fields']['last_name'] }}</p>
                </div>
            @endforeach
        </div>
    </div>
    <br>
    <div class="Recomend mh">
        <h1>Recommended</h1>
        <p>Adding these services to your trip now can save you money to purchasing them later or in the destination</p>
        <div class="card-container">
            <div class="card">
                <img class="card-img-top" src="{{ rtrim(config('frontend.url'), '/') }}/public/images/transfer.png">
                <div class="card-body">
                    <h5 class="card-title">Airport transfer</h5>
                    <p class="card-text">Airport transfers not included adventure?</p>
                </div>
                <a>Go somewhere <i class="bi bi-arrow-up-right-square"></i></a>
            </div>
            <div class="card">
                <img class="card-img-top" src="{{ rtrim(config('frontend.url'), '/') }}/public/images/insurance.png">
                <div class="card-body">
                    <h5 class="card-title">Insurance</h5>
                    <p class="card-text">Available up to 24h before departure</p>
                </div>
                <a>Manager Insurance <i class="bi bi-arrow-up-right-square"></i></a>
            </div>
            <div class="card">
                <img class="card-img-top" src="{{ rtrim(config('frontend.url'), '/') }}/public/images/accommodation.png">
                <div class="card-body">
                    <h5 class="card-title">Accommodation</h5>
                    <p class="card-text">Need pre- or post-tour accommodation?</p>
                </div>
                <a>Book Accommodation <i class="bi bi-arrow-up-right-square"></i></a>
            </div>
            <div class="card">
                <img class="card-img-top" src="{{ rtrim(config('frontend.url'), '/') }}/public/images/activities.png">
                <div class="card-body">
                    <h5 class="card-title">Activities</h5>
                    <p class="card-text">Got extra days in the destination before or after the adventure?</p>
                </div>
                <a>Find Activities <i class="bi bi-arrow-up-right-square"></i></a>
            </div>
        </div>
    </div>
    <br>
    <div class="foot">
        <div class="card-container">
            <div id="el">
                <h5>Company</h5>
                <div class="row">
                    <div class="col">
                        <p>About us</p>
                        <p>Blog</p>
                        <p>Reviews</p>
                    </div>
                    <div class="col" style="width: 10%;"></div>
                    <div class="col">
                        <p>Afiliates</p>
                        <p>FAQ</p>
                        <p>Contact us</p>
                    </div>
                </div>
            </div>

            <div id="el">
                <h5>Customer Support</h5>
                <div class="row">
                    <div class="col">
                        <div style="margin-top: 50%;   margin-right: 10px;">
                            <svg class="phone" width="34" height="34" viewBox="0 0 34 34" fill="none"
                                xmlns="http://www.w3.org/2000/svg" data-v-a11a4c1f="">
                                <path
                                    d="M8.24306 16.4478C9.24881 18.2632 10.407 20.0066 11.9158 21.5974C13.4243 23.1971 15.3013 24.6531 17.7348 25.8936C17.9143 25.9833 18.085 25.9833 18.2375 25.9205C18.471 25.8308 18.7045 25.6419 18.9381 25.4082C19.1176 25.2285 19.342 24.9408 19.5758 24.6262C20.5096 23.3948 21.6684 21.8671 23.3025 22.6311C23.3385 22.6488 23.3656 22.6671 23.4013 22.6848L28.852 25.8213C28.87 25.8302 28.8882 25.8485 28.9059 25.8573C29.6245 26.3516 29.9205 27.1156 29.9294 27.9784C29.9294 28.8593 29.6062 29.8475 29.1303 30.6835C28.5017 31.7889 27.5767 32.5169 26.5079 33.0023C25.4933 33.4697 24.3617 33.7214 23.2748 33.8831C21.5688 34.1346 19.9704 33.9729 18.3357 33.4697C16.7373 32.9757 15.1298 32.1577 13.3698 31.0703L13.2441 30.9892C12.436 30.4861 11.5649 29.9467 10.7119 29.309C7.57763 26.945 4.38993 23.53 2.31534 19.7732C0.573055 16.6187 -0.378744 13.2125 0.142255 9.96801C0.429732 8.18862 1.19283 6.57094 2.52203 5.50154C3.68051 4.56698 5.24295 4.05443 7.26331 4.23442C7.49684 4.25214 7.70324 4.38727 7.81115 4.58471L11.3043 10.4983C11.8165 11.1634 11.879 11.8194 11.6006 12.4754C11.3671 13.0148 10.9003 13.5091 10.2626 13.9762C10.0742 14.1379 9.84977 14.2996 9.61625 14.4705C8.83489 15.0367 7.9459 15.6927 8.25108 16.4658L8.24306 16.4478ZM29.8597 4.98428C30.5774 4.98428 31.159 5.56661 31.159 6.28462C31.159 7.00264 30.5771 7.58497 29.8597 7.58497C29.1422 7.58497 28.5604 7.00264 28.5604 6.28462C28.5604 5.56661 29.142 4.98428 29.8597 4.98428ZM20.9451 4.97957C21.6645 4.97957 22.2475 5.56301 22.2475 6.28268C22.2475 7.00236 21.6645 7.58552 20.9451 7.58552C20.2258 7.58552 19.6436 7.00236 19.6436 6.28268C19.6436 5.56301 20.226 4.97957 20.9451 4.97957ZM25.5622 4.97957C26.2816 4.97957 26.8646 5.56301 26.8646 6.28268C26.8646 7.00236 26.2816 7.58552 25.5622 7.58552C24.8428 7.58552 24.2604 7.00236 24.2604 6.28268C24.2604 5.56301 24.8428 4.97957 25.5622 4.97957ZM20.2022 0C19.2289 0 18.3963 0.342808 17.7035 1.02842C17.0107 1.71404 16.6759 2.54752 16.6759 3.52942V9.72322C16.6759 10.7048 17.0181 11.5383 17.7035 12.2242C18.3886 12.9098 19.2214 13.2524 20.2022 13.2524H23.0997C22.8974 14.0313 22.6481 14.7715 22.3369 15.4648C22.0331 16.1662 21.5118 16.8361 20.7955 17.4749C22.1733 17.1166 23.3955 16.5791 24.4776 15.8699C25.5522 15.1688 26.4863 14.2885 27.2569 13.2521H30.4736C31.447 13.2521 32.2798 12.9015 32.9727 12.2239C33.6655 11.5381 34 10.7046 34 9.72295V3.52942C34 2.55527 33.6577 1.72179 32.9727 1.02842C32.287 0.335054 31.4545 0 30.4734 0C27.3435 0 23.3321 0 20.2022 0Z"
                                    fill="white" data-v-a11a4c1f=""></path>
                            </svg>
                        </div>
                    </div>
                    <div class="col">
                        <p id="pf"><img id="flag" src="{{ rtrim(config('frontend.url'), '/') }}/public/images/usa.png">
                            +1-201-500-1310</p>
                        <p id="pf"><img id="flag" src="{{ rtrim(config('frontend.url'), '/') }}/public/images/uk.png">
                            +44-7440-963840</p>
                        <p id="pf"><img id="flag" src="{{ rtrim(config('frontend.url'), '/') }}/public/images/mex.png">
                            +52-55-8526-6910</p>
                    </div>
                </div>
                <label>Mon-Sun: 9 am - 11 pm (EST)</label>
            </div>
            <div id="el">
                <h5>Supporting</h5>
                <img id="sp" src="{{ rtrim(config('frontend.url'), '/') }}/public/images/united.jpeg" alt="">
                <img id="sp" src="{{ rtrim(config('frontend.url'), '/') }}/public/images/future.jpeg" alt="">
                <img id="sp" src="{{ rtrim(config('frontend.url'), '/') }}/public/images/adventure.jpeg" alt="">
            </div>
            <div id="el">
                <div>
                    <h5>Follow Us</h5>
                    <div class="social-icons">

                        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="16" fill="currentColor"
                            class="bi bi-facebook" viewBox="0 0 16 16">
                            <path
                                d="M16 8.049c0-4.446-3.582-8.05-8-8.05C3.58 0-.002 3.603-.002 8.05c0 4.017 2.926 7.347 6.75 7.951v-5.625h-2.03V8.05H6.75V6.275c0-2.017 1.195-3.131 3.022-3.131.876 0 1.791.157 1.791.157v1.98h-1.009c-.993 0-1.303.621-1.303 1.258v1.51h2.218l-.354 2.326H9.25V16c3.824-.604 6.75-3.934 6.75-7.951" />
                        </svg>
                        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="16" fill="currentColor"
                            class="bi bi-twitter-x" viewBox="0 0 16 16">
                            <path
                                d="M12.6.75h2.454l-5.36 6.142L16 15.25h-4.937l-3.867-5.07-4.425 5.07H.316l5.733-6.57L0 .75h5.063l3.495 4.633L12.601.75Zm-.86 13.028h1.36L4.323 2.145H2.865z" />
                        </svg>
                        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="16" fill="currentColor"
                            class="bi bi-pinterest" viewBox="0 0 16 16">
                            <path
                                d="M8 0a8 8 0 0 0-2.915 15.452c-.07-.633-.134-1.606.027-2.297.146-.625.938-3.977.938-3.977s-.239-.479-.239-1.187c0-1.113.645-1.943 1.448-1.943.682 0 1.012.512 1.012 1.127 0 .686-.437 1.712-.663 2.663-.188.796.4 1.446 1.185 1.446 1.422 0 2.515-1.5 2.515-3.664 0-1.915-1.377-3.254-3.342-3.254-2.276 0-3.612 1.707-3.612 3.471 0 .688.265 1.425.595 1.826a.24.24 0 0 1 .056.23c-.061.252-.196.796-.222.907-.035.146-.116.177-.268.107-1-.465-1.624-1.926-1.624-3.1 0-2.523 1.834-4.84 5.286-4.84 2.775 0 4.932 1.977 4.932 4.62 0 2.757-1.739 4.976-4.151 4.976-.811 0-1.573-.421-1.834-.919l-.498 1.902c-.181.695-.669 1.566-.995 2.097A8 8 0 1 0 8 0" />
                        </svg>
                        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="16" fill="currentColor"
                            class="bi bi-instagram" viewBox="0 0 16 16">
                            <path
                                d="M8 0C5.829 0 5.556.01 4.703.048 3.85.088 3.269.222 2.76.42a3.9 3.9 0 0 0-1.417.923A3.9 3.9 0 0 0 .42 2.76C.222 3.268.087 3.85.048 4.7.01 5.555 0 5.827 0 8.001c0 2.172.01 2.444.048 3.297.04.852.174 1.433.372 1.942.205.526.478.972.923 1.417.444.445.89.719 1.416.923.51.198 1.09.333 1.942.372C5.555 15.99 5.827 16 8 16s2.444-.01 3.298-.048c.851-.04 1.434-.174 1.943-.372a3.9 3.9 0 0 0 1.416-.923c.445-.445.718-.891.923-1.417.197-.509.332-1.09.372-1.942C15.99 10.445 16 10.173 16 8s-.01-2.445-.048-3.299c-.04-.851-.175-1.433-.372-1.941a3.9 3.9 0 0 0-.923-1.417A3.9 3.9 0 0 0 13.24.42c-.51-.198-1.092-.333-1.943-.372C10.443.01 10.172 0 7.998 0zm-.717 1.442h.718c2.136 0 2.389.007 3.232.046.78.035 1.204.166 1.486.275.373.145.64.319.92.599s.453.546.598.92c.11.281.24.705.275 1.485.039.843.047 1.096.047 3.231s-.008 2.389-.047 3.232c-.035.78-.166 1.203-.275 1.485a2.5 2.5 0 0 1-.599.919c-.28.28-.546.453-.92.598-.28.11-.704.24-1.485.276-.843.038-1.096.047-3.232.047s-2.39-.009-3.233-.047c-.78-.036-1.203-.166-1.485-.276a2.5 2.5 0 0 1-.92-.598 2.5 2.5 0 0 1-.6-.92c-.109-.281-.24-.705-.275-1.485-.038-.843-.046-1.096-.046-3.233s.008-2.388.046-3.231c.036-.78.166-1.204.276-1.486.145-.373.319-.64.599-.92s.546-.453.92-.598c.282-.11.705-.24 1.485-.276.738-.034 1.024-.044 2.515-.045zm4.988 1.328a.96.96 0 1 0 0 1.92.96.96 0 0 0 0-1.92m-4.27 1.122a4.109 4.109 0 1 0 0 8.217 4.109 4.109 0 0 0 0-8.217m0 1.441a2.667 2.667 0 1 1 0 5.334 2.667 2.667 0 0 1 0-5.334" />
                        </svg>
                        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="16" fill="currentColor"
                            class="bi bi-youtube" viewBox="0 0 16 16">
                            <path
                                d="M8.051 1.999h.089c.822.003 4.987.033 6.11.335a2.01 2.01 0 0 1 1.415 1.42c.101.38.172.883.22 1.402l.01.104.022.26.008.104c.065.914.073 1.77.074 1.957v.075c-.001.194-.01 1.108-.082 2.06l-.008.105-.009.104c-.05.572-.124 1.14-.235 1.558a2.01 2.01 0 0 1-1.415 1.42c-1.16.312-5.569.334-6.18.335h-.142c-.309 0-1.587-.006-2.927-.052l-.17-.006-.087-.004-.171-.007-.171-.007c-1.11-.049-2.167-.128-2.654-.26a2.01 2.01 0 0 1-1.415-1.419c-.111-.417-.185-.986-.235-1.558L.09 9.82l-.008-.104A31 31 0 0 1 0 7.68v-.123c.002-.215.01-.958.064-1.778l.007-.103.003-.052.008-.104.022-.26.01-.104c.048-.519.119-1.023.22-1.402a2.01 2.01 0 0 1 1.415-1.42c.487-.13 1.544-.21 2.654-.26l.17-.007.172-.006.086-.003.171-.007A100 100 0 0 1 7.858 2zM6.4 5.209v4.818l4.157-2.408z" />
                        </svg>
                        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="16" fill="currentColor"
                            class="bi bi-tiktok" viewBox="0 0 16 16">
                            <path
                                d="M9 0h1.98c.144.715.54 1.617 1.235 2.512C12.895 3.389 13.797 4 15 4v2c-1.753 0-3.07-.814-4-1.829V11a5 5 0 1 1-5-5v2a3 3 0 1 0 3 3z" />
                        </svg>
                    </div>
                </div>
                <div>
                    <h5>Payment Methods</h5>
                    <img id="pay" src="{{ rtrim(config('frontend.url'), '/') }}/public/images/pay_methods.jpeg" alt="">
                </div>
            </div>

        </div>
        <div>
            <div style="float: left;">
                <p><svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor"
                        class="bi bi-c-circle" viewBox="0 0 16 16">
                        <path
                            d="M1 8a7 7 0 1 0 14 0A7 7 0 0 0 1 8m15 0A8 8 0 1 1 0 8a8 8 0 0 1 16 0M8.146 4.992c-1.212 0-1.927.92-1.927 2.502v1.06c0 1.571.703 2.462 1.927 2.462.979 0 1.641-.586 1.729-1.418h1.295v.093c-.1 1.448-1.354 2.467-3.03 2.467-2.091 0-3.269-1.336-3.269-3.603V7.482c0-2.261 1.201-3.638 3.27-3.638 1.681 0 2.935 1.054 3.029 2.572v.088H9.875c-.088-.879-.768-1.512-1.729-1.512" />
                    </svg>2023. Vibe Adventures</p>
            </div>
            <div style="float: right;">
                <u>Terms & conditions</u>
                <u>Privacy Policy</u>
            </div>
        </div>
    </div>
</body>

</html>
