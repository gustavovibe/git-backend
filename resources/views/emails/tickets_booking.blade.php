<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Flight ticket</title>
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

        body {
            margin-top: 100px;
        }

        /* Asegurarse de que el encabezado se repita en cada página */
        @page {
            margin-top: 20px;
        }

        .content {
            margin-top: 100px;
        }
    </style>
</head>
<body>
    <div class="header">
        <table style="width: 100%">
            <tr>
                <td style="width: 40%">
                    <img style="width: 200px"
                         src="https://vibeadventures.be/images/logo.png"
                         alt="Logo">
                </td>
                <td style="width: 40%"></td>
                <td style="width: 60%" valign="right" style="text-align:right">
                    <p style="font-family: 'Roboto', sans-serif;font-size: 13px;"><b>BOOKING NUMBER</b></p>
                    <p style="font-family: 'Roboto', sans-serif;font-size: 13px;">{{ $data['booking_reference'] }}</p>
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
                                 src="https://blog.vibeadventures.com/wp-content/uploads/2025/05/user.png"
                                 alt="User">
                        </td>
                        <td>
                            <p style="font-size: 14px; font-family: 'Roboto', sans-serif;margin:0">
                                <b>{{ ucfirst($passenger['title']) }} {{ $passenger['given_name'] }} {{ $passenger['family_name'] }}</b>
                                <span>({{ \Carbon\Carbon::parse($passenger['born_on'])->format('D M Y') }})</span>
                            </p>
                        </td>
                    </tr>
                </table>
                            @foreach ($data['slices'][0]['segments'][0]['passengers'] as $seg_passenger)
                                @if ($seg_passenger['passenger_id'] == $passenger['id'])
                                <table style="width: 100%; margin-bottom: 5px;">
                                    <tr>
                                        <td style="width: 10%; vertical-align: middle; padding-right: 5px;">
                                            <img style="width: 20px; height: auto;"
                                                 src="https://blog.vibeadventures.com/wp-content/uploads/2025/05/bag.png" />
                                        </td>
                                        <td style="vertical-align: middle;">
                                            <p style="font-size: 12px; font-family: 'Roboto', sans-serif;margin:0">
                                                @foreach ($seg_passenger['baggages'] as $index => $baggage)
                                                    @if ($index > 0), @endif
                                                    {{ $baggage['quantity'] }} x
                                                    @if ($baggage['type'] == 'checked')Checked Bag 
                                                    @elseif ($baggage['type'] == 'carry_on')carry-on luggage 
                                                    @elseif ($baggage['type'] == 'personal')personal Item
                                                    @else{{ ucfirst($baggage['type']) }} <!-- Default case if type is unknown -->
                                                    @endif
                                                @endforeach
                                            </p>
                                        </td>
                                    </tr>
                                </table>
                                @endif
                            @endforeach
                                <table style="width: 100%; margin-bottom: 5px;">
                                    <tr>
                                        <td style="width: 10%; vertical-align: middle; padding-right: 5px;">
                                            <img style="width: 20px; height: auto;"
                                                 src="https://blog.vibeadventures.com/wp-content/uploads/2025/05/ticket.png" />
                                        </td>
                                        <td style="vertical-align: middle;">
                                            <p style="margin: 0; padding: 0;font-size: 12px; font-family: 'Roboto', sans-serif;">
                                                <b>E-ticket-number: </b>
                                                <span style="color:#82CF45;">{{ $data['booking_reference'] }}</span>
                                            </p>
                                        </td>
                                    </tr>
                                </table>
            </div>
        @endforeach
    </div>

    <h2 style="font-family: 'Roboto', sans-serif;font-size: 15px;text-align: left">Itinerary</h2>
    @foreach ($data['slices'] as $slice)
    @foreach ($slice['segments'] as $segment)
    <div style="border-style: groove; padding:2%; border-radius:8px; border-color:#82CF45;margin-bottom:10px">
        <table style="width: 100%">
            <tr>
                <td style="text-align: right;font-family: 'Roboto', sans-serif;font-size: 12px;text-align: right"><b>{{ $segment['formatted_departing_hour'] }}</b></td>
                <td style="padding-left: 5%">
                    <b style="font-size: 14px; font-family: 'Roboto', sans-serif;">
                        <span style="color:#82CF45;">{{ $segment['origin']['iata_city_code'] }}</span> {{ $segment['origin']['city_name'] }}
                    </b>
                </td>
                <td>
                </td>
                <td rowspan="4" style="text-align: center; padding-left: 5%;">
                    <div style="text-align: right;">
                        <p style="font-size: 12px; font-family: 'Roboto', sans-serif;"><b>Carrier:</b> {{ $segment['operating_carrier']['iata_code'] }}</p>
                        <p style="font-size: 12px; font-family: 'Roboto', sans-serif;"><b>Flight #</b> {{ $segment['operating_carrier_flight_number'] }}</p>
                        <p style="font-size: 12px; font-family: 'Roboto', sans-serif;"><b>Duration:</b> {{ $segment['formatted_duration']  }}</p>
                    </div>
                </td>
            </tr>
            <tr>
                <td style="font-size: 12px;text-align: right;font-family: 'Roboto', sans-serif;font-size: 12px;text-align: right">{{ $segment['formatted_arriving_at'] }}</td>
                <td style="padding-left: 5%;font-family: 'Roboto', sans-serif;font-size: 12px;">{{ $segment['origin']['name'] }}</td>
            </tr>
            <tr>
                <td style="text-align: right;font-family: 'Roboto', sans-serif;font-size: 12px;text-align: right"><b>{{ $segment['formatted_arriving_hour'] }}</b></td>
                <td style="padding-left: 5%">
                    <b style="font-family: 'Roboto', sans-serif;font-size: 14px;">
                        <span style="color:#82CF45;">{{ $segment['destination']['iata_city_code'] }}</span> {{ $segment['destination']['city_name'] }}
                    </b>
                </td>
            </tr>
            <tr>
                <td style="font-size: 12px;text-align: right;font-family: 'Roboto', sans-serif;font-size: 12px;text-align: right">{{ $segment['formatted_arriving_at'] }}</td>
                <td style="padding-left: 5%;font-family: 'Roboto', sans-serif;font-size: 12px;">{{ $segment['destination']['name'] }}</td>
            </tr>
        </table>
    </div>
    @endforeach
@endforeach
<p style="font-family: 'Roboto', sans-serif;font-size: 12px;"> 
    <b style="color: red;">*</b> 
    All times are local. Arrive at the airport at least 2 hours before domestic flights and 3 hours before international flights, especially with checked baggage. Check the airport’s official guidelines for more details.
</p>
<h2 style="font-family: 'Roboto', sans-serif;font-size: 15px;">Check In</h2>
<div style="border-style: groove; padding:2%; border-radius:8px; border-color:#82CF45;">
    <p style="font-family: 'Roboto', sans-serif;font-size: 11px;">Check directly with the airline using the carrier reservation number (PNR): <span style="color:#82CF45;">{{ $data['booking_reference'] }}</span></p>
    @foreach ( $data["passengers"] as $passengers )
        <p style="font-family: 'Roboto', sans-serif;font-size: 11px;">E-ticket number for {{ $passengers['given_name']." ".$passengers['family_name'].": "}}<span style="color:#82CF45;">{{ $passengers['id'] }}</span> </p>
    @endforeach
</div>

<div style="margin-top:10px">
    <span>
        <h2 style="display: inline; margin-right: 10px;font-family: 'Roboto', sans-serif;font-size: 15px;">
            Fare Conditions <b style="color: red">*</b>
        </h2>
    </span>
    <div style="border-style: groove; padding:2%; border-radius:8px; border-color:#82CF45;">
    <p style="font-family: 'Roboto', sans-serif;font-size: 11px;"><b>Class:</b> Economy.</p>    
    <p style="font-family: 'Roboto', sans-serif;font-size: 11px;"><b>Refundabilty:</b> @if($data['conditions']['refund_before_departure']['allowed'] == false) <span>Not refundable</span> @else <span>Refundable</span> @endif</p>
    <p style="font-family: 'Roboto', sans-serif;font-size: 11px;"><b>Changes:</b>@if($data['conditions']['change_before_departure']['allowed'] == false) <span>Not allowed</span> @else <span>Allowed</span> @endif </p>
    </div>
    <label style="font-family: 'Roboto', sans-serif;font-size: 11px;">
        <b style="color: red">*</b> Please check the airline's full conditions on 
        <a href="{{ rtrim(config('frontend.url'), '/') }}/my-trips" style="color: #82CF45">My trips</a> portal.
    </label>
</div>


<div style="margin-top:10px">
<h2 style="font-family: 'Roboto', sans-serif;font-size: 15px;">Help & Support</h2>
<p style="font-family: 'Roboto', sans-serif;font-size: 11px;">Please 
    <a href="{{ rtrim(config('frontend.url'), '/') }}/contact" style="color: #82CF45">contact us</a> if any help is needed.
</p>
<h2 style="font-family: 'Roboto', sans-serif;font-size: 15px;">Additional Information</h2>
<p style="font-family: 'Roboto', sans-serif;font-size: 11px;">
It is your responsibility to ensure you have all necessary travel documents for your trip, such as a valid passport, appropriate visas, and any recommended vaccination records for your destination. Vibe Adventures  does not take responsibility for any visa-related matters, including airport transit visas. Failure to provide the required documentation may result in denied boarding. 
</p>
</div>
</div>
</body>
</html>
