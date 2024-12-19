
<div>
    <div>
      <table style="width: 100%">
        <tr>
            <td style="width: 40%">
                <img style="width: 100%" src="data:image/png;base64,{{ base64_encode(file_get_contents(public_path('images/logo.png')))}}" alt="">
            </td>
            <td style="width: 60%">
            </td>
            <td style="width: 40%">
                <h2>Booking Reference</h2>

            </td>
        </tr>
      </table>
    </div>
    <br>
    <div>
        <h2>Flight details</h2>
        @foreach ( $data['slices'] as $slices )
            @foreach (  $slices['segments'] as $segments)
            <div style="border-style: groove; padding:2%">
                <table style="width: 100%">
                    <tr>
                        <td>
                            <p>Iberia</p>
                        </td>
                        <td>
                            <p><b>{{$segments['formatted_departing_hour'].' - '.$segments['formatted_arriving_hour']   }}</b></p>
                            <p>{{ $segments['operating_carrier']['name'] }}</p>
                        </td>
                        <td>
                            <p> <b>
                                {{ $segments['formatted_duration'] }}
                                </b>
                            </p>
                            <p>{{ $segments['origin']['iata_code'] }} - {{ $segments['destination']['iata_code'] }}</p>
                        </td>
                        <td>
                            @if(count($segments['stops'])>0)
                            <h3> {{ count($segments['stops']) }} stop(s) </h3>
                            @else
                            <h3>Non-stops</h3>
                            @endif
                        </td>
                    </tr>
                </table>
                <table style="width: 100%">
                    <tr>
                        <td style="width: 30%">
                            <b>  {{ $segments['formatted_departing_at'] }}</b>
                        </td>
                        <td style="width: 20%"></td>
                        <td style="width: 50%">
                            <p>{{ $segments['origin']['name'] }}</p>
                            <p> {{ $segments['origin']['iata_code'] }}, Terminal {{ $segments['origin_terminal']  }}</p>
                        </td>
                    </tr>
                </table>
                <table style="width: 100%">
                    <tr>
                        <td style="width: 30%">
                            <p>Flight duration: {{ $segments['formatted_duration'] }}</p>
                            <p> <b>{{ $segments['formatted_arriving_at'] }}</b></p>
                        </td>
                        <td style="width: 20%"></td>
                        <td style="width: 50%">
                            <p>{{ $segments['destination']['name'] }}</p>
                            <p>{{ $segments['destination']['iata_code'] }}, Terminal {{ $segments['destination_terminal']  }}</p>
                        </td>
                    </tr>
                </table>
                <table style="width: 100%">
                    <tr>
                        <td><p>{{ $segments['class'] }}</p></td>
                        <td><p>{{ $segments['operating_carrier']['name'] }}</p></td>
                        <td><p>{{ $segments['aircraft']['name'] }}</p></td>
                        <td><p>{{ $segments['operating_carrier_flight_number'] }}</p></td>
                    </tr>
                </table>
            </div>
            <br>
            @endforeach
        @endforeach
    </div>
    <br>
    <div style="page-break-before: always;">
        <h2>Passengers</h2>
        <div style="border-style: groove; padding:2%">
            @foreach ( $data['passengers'] as $passengers )
            <p><b>Type:</b>{{ ' '.$passengers['type'] }} </p>
            <table style="width: 100%">
                <tr>
                   <td> Name </td>
                   <td>Date of birth</td>
                   <td>Gender</td>
                </tr>
                <tr>
                   <td><p><b>{{ $passengers['given_name'].' '.$passengers['family_name'] }}</b></p></td>
                   <td>{{ $passengers['born_on'] }}</td>
                   <td><b>{{ $passengers['gender'] =='m'? 'Male':'Female'  }}</b> </td>
                </tr>
            </table>
            <p> <b> Electronic Ticket: </b>{{ '  '.$passengers['id'] }} </p>
            <br>
            @endforeach
            <p>Flight information</p>

            @foreach ( $data['slices'] as $slices )
            @foreach (  $slices['segments'] as $segments)
            <div style="border-style: groove; padding:2%; ">
                <table>
                    <tr >
                       <td  colspan="2" ><p>{{ $segments['origin']['iata_code'] }} to {{ $segments['destination']['iata_code'] }}  on <b> {{ $segments['formatted_departing_at'] }}</b></p>
                    </td>
                    </tr>
                    <tr>
                        @foreach ( $segments['passengers'] as $passengers )
                        @foreach ($passengers['baggages'] as $baggage)
                        <td>
                            {{ $baggage['quantity'] }} {{ $baggage['type'] }} bag{{ $baggage['quantity'] > 1 ? 's' : '' }}
                        </td>
                    @endforeach
                        @endforeach
                    </tr>
                </table>
            </div>
                <br>
            @endforeach
            <br>
            @endforeach
            <br>
          {{--   <div style="border-style: groove; padding:2%">
                <table>
                    <tr >
                       <td  colspan="2" >JFK to ATH on Fri, 16 May 2025 at 11:36
                    </td>
                    </tr>
                    <tr>
                        <td>1 checked bag</td>
                        <td> 1 carry on bag</td>
                    </tr>
                </table>
            </div> --}}
        </div>


    </div>
    <br>
    <h3>Ticket numbers</h3>
    <div style="border-style: groove; padding:2%">
        <p> Gustavo Menchaca: 1</p>
    </div>
</div>
