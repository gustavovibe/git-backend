<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>PDF Ticket</title>
    <style>
        /* Establece el encabezado como fijo */
        .header {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            height: 80px;
            text-align: center;
        /*  border-bottom: 2px solid black; */
            padding: 10px;
        }

        /* El contenido principal tiene margen en la parte superior para evitar que se sobreponga con el encabezado */
        body {
            margin-top:20px;
        }

        /* Asegurarse de que el encabezado se repita en cada página */
        @page {
            margin-top: 80px;
        }

        .content {
            margin-top: 20px;
        }
    </style>
</head>
<body style="font-family: Arial, sans-serif;">
    <div class="header">
        <table style="width: 100%">
            <tr>
                <td style="width: 40%">
                    <img style="width: 200px"
                         src="https://vibeadventures.be/images/logo.png"
                         alt="Logo">
                </td>
                <td style="width: 40%"></td>
                <td style="width: 60%" valign="right">
                    <table>
                        <tr>
                            <td>
                                <p style="font-family: 'Roboto', sans-serif;font-size: 13px;"><b>BOOKING NUMBER</b></p>
                            </td>
                            <td>
                                <p>{{ $data['booking_reference'] }}</p>
                            </td>
                        </tr>
                    </table>
                </td>
            </tr>
        </table>
    </div>

    <h2 style="font-family: 'Roboto', sans-serif;font-size:15px">Passengers</h2>
    <div>
        @foreach ($data['passengers'] as $passenger)
            <div style="border-style: groove; padding:2%; border-radius:8px; border-color:#82CF45;">
                <table>
                    <tr>
                        <td style="width: 10%">
                            <img style="width: 50%; height: auto;"
                                 src="https://vibeadventures.be/images/user.png"
                                 alt="User">
                        </td>
                        <td>
                            <b style="font-size: 12px; font-family: 'Roboto', sans-serif;">{{ ucfirst($passenger['title']) }} {{ $passenger['given_name'] }}
                                {{ $passenger['family_name'] }}
                                ({{ \Carbon\Carbon::parse($passenger['born_on'])->format('D M Y') }})</b>
                        </td>
                    </tr>
                </table>

                <p>
                            @foreach ($data['slices'][0]['segments'][0]['passengers'] as $seg_passenger)
                                @if ($seg_passenger['passenger_id'] == $passenger['id'])
                                <table style="width: 100%; margin-bottom: 5px;">
                                    <tr>
                                        <td style="width: 10%; vertical-align: middle; padding-right: 5px;">
                                            <img style="width: 20px; height: auto;"
                                                 src="https://vibeadventures.be/images/Bag.png"
                                                 alt="Bag">
                                        </td>
                                        <td style="vertical-align: middle;">
                                            <p style="margin: 0; padding: 0;">
                                                @foreach ($seg_passenger['baggages'] as $index => $baggage)
                                                    @if ($index > 0), @endif
                                                    {{ $baggage['quantity'] }} x
                                                    @if ($baggage['type'] == 'checked')Checked Bag (45 + 66 + 45cm, 10kg)
                                                    @elseif ($baggage['type'] == 'carry_on')carry-on luggage (45 + 66 + 45cm, 10kg)
                                                    @elseif ($baggage['type'] == 'personal')personal Item (20 + 35 + 45 cm, 5kg)
                                                    @else{{ ucfirst($baggage['type']) }} <!-- Default case if type is unknown -->
                                                    @endif
                                                @endforeach
                                            </p>
                                        </td>
                                    </tr>
                                </table>
                                @endif
                            @endforeach
                </p>
                <p>   
                                <table style="width: 100%; margin-bottom: 5px;">
                                    <tr>
                                        <td style="width: 10%; vertical-align: middle; padding-right: 5px;">
                                            <img style="width: 20px; height: auto;"
                                                 src="https://vibeadventures.be/images/Bag.png"
                                                 alt="Bag">
                                        </td>
                                        <td style="vertical-align: middle;">
                                            <p style="margin: 0; padding: 0;">
                                                <b>E-ticket-number: </b>
                                                <span style="color:#82CF45;">{{ $passenger['id'] }}</span>
                                            </p>
                                        </td>
                                    </tr>
                                </table>
                </p>
            </div>
            <br>
        @endforeach
    </div>

    <br>
    <h2>Itinerary</h2>
    @foreach ($data['slices'] as $slice)
    @foreach ($slice['segments'] as $segment)
    <div style="border-style: groove; padding:2%; border-radius:8px; border-color:#82CF45;">
        <table style="width: 100%">
            <tr>
                <td style="text-align: right">{{ $segment['formatted_departing_hour'] }}</td>
                <td style="padding-left: 5%">
                    <p>
                        <span style="color:#82CF45;">{{ $segment['origin']['iata_city_code'] }}</span> {{ $segment['origin']['city_name'] }}
                    </p>
                </td>
                <td>
                </td>
                <td rowspan="4" style="text-align: center; padding-left: 5%;">
                    <div style="text-align: right;">
                        <p><b>Carrier:</b>  <strong>{{ $segment['operating_carrier']['name'] }}</strong></p>
                        <p> <b>Flight no:</b>{{ $segment['operating_carrier_flight_number'] }} </p>
                        <p> <b>Duration:</b> {{ $segment['formatted_duration']  }}</p>
                    </div>
                </td>
            </tr>
            <tr>
                <td style="font-size: 12px;text-align: right">{{ $segment['formatted_arriving_at'] }}</td>
                <td style="padding-left: 5%">{{ $segment['origin']['name'] }}</td>
            </tr>
            <tr>
                <td style="text-align: right">{{ $segment['formatted_arriving_hour'] }}</td>
                <td style="padding-left: 5%">
                    <p>
                        <span style="color:#82CF45;">{{ $segment['destination']['iata_city_code'] }}</span> {{ $segment['destination']['city_name'] }}
                    </p>
                </td>
            </tr>
            <tr>
                <td style="font-size: 12px;text-align: right">{{ $segment['formatted_arriving_at'] }}</td>
                <td style="padding-left: 5%">{{ $segment['destination']['name'] }}</td>
            </tr>
        </table>
    </div>
        <br>
    @endforeach
@endforeach
<p> <b style="color: red;">*</b> All timer are local. Arrive at the airport at leaste 2 hours before domestic flights and 3 hours before international flights, especially with checked baggage. Check the airport's official guidelines for more details.</p>
<br>
<h2>Check In</h2>
<div style="margin-left:5%;border-style: groove; padding:2%; border-radius:8px; border-color:#82CF45;">
    <p>Check directly with the airline using the carrier reservation number (PNR): <span style="color:#82CF45;">{{ $data['booking_reference'] }}</span></p>
    @foreach ( $data["passengers"] as $passengers )
        <p>E-ticket number for {{ $passengers['given_name']." ".$passengers['family_name'].": "}}<span style="color:#82CF45;">{{ $passengers['id'] }}</span> </p>
    @endforeach
</div>

<div style="margin-top:20px">
    <span>
        <h2 style="display: inline; margin-right: 10px;">
            Fare Conditions <b style="color: red">*</b>
        </h2>
    </span>
    <div style="border-style: groove; padding:2%; border-radius:8px; border-color:#82CF45;">
    <p><b>Class:</b> Economy.</p>    
    <p><b>Refundabilty:</b> Non-refundable except under extraordinary circumstances.</p>
    <p><b>Changes:</b> Allowed up to 48h before departure, $100 USD fee applies.</p>
    <p><b>Baggage:</b> 1 carry-on (7kg) included, no checked baggage. </p>
    <p><b>Seat Selection:</b> Free at check-in, paid options available.</p>
    <p><b>Lounge Access:</b> Not included.</p>
    <p><b>Priority Boarding:</b> Not included.</p>
    <br>
    <label><b style="color: red">*</b> Please check the airline's website or contact the airline directly for further details.</label>
    </div>
</div>


<div style="margin-top:20px">
<h1>Help & Support</h1>
<h3>Please <a href="https://vibeadventures.com/contact" style="color: #82CF45">contact us</a> if any help is needed.</h3>
<br>
<h1>Additional Information</h1>
<br>
<p>"Vibeadventures.com does not take responsibility for any visa-related matters, including airport transit visas. Failure to provide the required documentation may result in denied boarding. It is your responsibility to ensure you have all necessary travel documents for your trip, such as a valid passport, appropriate visas, and any recommended vaccination records for your destination.}</p>
</div>
</div>
</body>
</html>
