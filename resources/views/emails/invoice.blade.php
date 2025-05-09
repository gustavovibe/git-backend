<body>
    <div style="padding: 4%">

        <table style="width: 100%">
            <tr>
                <td>
                    <div>
                        <h2 style="color: black">INVOICE</h2>
                        <h2 style="color: #7F91A8">{{ $data['charge_details']['receipt_number'] }}</h2>
                    </div>
                </td>
                <td style="text-align: right">
                    <img style="width: 30%" src="https://vibeadventures.be/images/logo.png" alt="">
                </td>
            </tr>



        </table>
        <h2 style="text-align: center; color: #650808; padding:1%;" >This invoice was already paid.</h2>

        <table style="width: 100%;">
            <tr>
                <td style="width: 48%; padding:3%; border: 1px solid #FAFAFA;">
                    <div>
                        <p><b>Supplier</b></p>
                        <p>Vibe Adventures, Inc.</p>
                        <p>300 Delaware Ave,Ste 210 #549</p>
                        <p>Wilmington, 1981</p>
                        <p>US</p>
                        <br>
                        <p><b>Customer</b></p>
                        <p>{{ $data['charge_details']['billing_details']['name'] }}</p>
                        <p>{{  $data['charge_details']['billing_details']['address']['line1']  }}</p>
                        <p>{{  $data['charge_details']['billing_details']['address']['line2'].' '.$data['charge_details']['billing_details']['address']['postal_code'] }}</p>
                        <p>{{ $data['charge_details']['billing_details']['address']['state'] }}</p>
                    </div>
                </td>
                <td style="width: 4%;"></td>
                <td style="padding:3%">
                    <div style="margin-bottom:10%; text-align:center;">
                        <table>
                            <tr>
                                <td> <p><b>Booking ID</b></p> </td>
                                <td style="width: 3%;"></td>
                                <td> <p>{{ $orders->booking_id }}</p></td>
                            </tr>
                            <tr>
                                <td> <p><b>Type</b></p> </td>
                                <td style="width: 3%;"></td>
                                <td> <p>Booking</p></td>
                            </tr>
                            <tr>
                                <td> <p><b>Issue Date</b></p> </td>
                                <td style="width: 3%;"></td>
                                <td> <p>{{ date('Y-m-d',$data['balance_transaction']['created']) }}</p></td>
                            </tr>
                            <tr>
                                <td> <p><b>Due Date</b></p> </td>
                                <td style="width: 3%;"></td>
                                <td> <p>{{ date('Y-m-d',$data['balance_transaction']['created']) }}</p></td>
                            </tr>
                            <tr>
                                <td> <p><b>Taxable Date</b></p> </td>
                                <td style="width: 3%;"></td>
                                <td> <p>{{ date('Y-m-d',$data['balance_transaction']['created']) }}</p></td>
                            </tr>
                            <tr>
                                <td> <p><b>Currency</b></p> </td>
                                <td style="width: 3%;"></td>
                                <td> <p>{{ strtoupper($data['payment_intent']['currency']) }}</p></td>
                            </tr>
                        </table>
                    </div>
                </td>
            </tr>
        </table>
        <br>
        <table style="width: 100%;border-collapse: collapse; text-align:center">
            <tr>
                <th style="width: 10%;padding:1%"> <p>Item</p></th>
                <th style="width: 50%">Description</th>
                <th style="width: 15%">Unit Price</th>
                <th style="width: 10%"> Qty</th>
                <th style="width: 15%">Price inc. VAT</th>
            </tr>
            <tr>
                <td><p>Trip</p></td>
                <td style="border-right: 1px dotted black">    
                    <p>
                        {{ $orders->tour_name }} organized adventure
                        ({{ \Carbon\Carbon::parse($orders->arrival)->format('M d, Y') }} - {{ \Carbon\Carbon::parse($orders->end)->format('M d, Y') }}) for {{$orders->travelers_number}} travelers:
                    </p><br>
                    <ul>
                    <li style="color: #9ca3af;font-family: 'Interstate Light Cond', sans-serif; font-size: 12px;">
						@foreach ($orders['passengers'] as $acc)
                            <span style="color: #82CF45;">
                                {{ $acc['passengers'] }}
                            </span>
                                × {{ $acc['name'] }}
                        @endforeach
                    </li>
                    @if ($orders->attempt->duffel_res['data']['slices'])
                    <li>
                    flights ( {{$orders->attempt->duffel_res['data']['slices'][0]['segments'][0]['origin']['iata_code']}} 
                    - {{$orders->attempt->duffel_res['data']['slices'][0]['segments'][0]['destination']['iata_code']}} 
                    on {{ \Carbon\Carbon::parse($orders->attempt->duffel_res['data']['slices'][0]['segments'][0]['departing_at'])->format('D, d/m') }}
                    ; {{$orders->attempt->duffel_res['data']['slices'][1]['segments'][0]['origin']['iata_code']}} 
                    - {{$orders->attempt->duffel_res['data']['slices'][1]['segments'][0]['destination']['iata_code']}} 
                    on {{ \Carbon\Carbon::parse($orders->attempt->duffel_res['data']['slices'][1]['segments'][0]['departing_at'])->format('D, d/m') }}) 
                    </li>
                    @endif
                </td>
                <td><p>{{ 'US $'.$orders->paid }}</p></td>
                <td><p>1</p></td>
                <td><p>{{ 'US $'.$orders->paid }}</p></td>
            </tr>
        </table>
        <br>
        <hr  style="border-bottom: 1px solid #82CF45;">
        <br>
        <div>

            <table style="text-align: center; width:100%" >
                <tr>
                    <td style="width: 70%"></td>
                    <th>Subtotal</th>
                    <td>{{ 'US $'.$orders->paid }}</td>
                </tr>
                <tr>
                    <td style="width: 70%"></td>
                    <th>Tax</th>
                    <td><p>{{ 'US $'.$values['tax'] }}</p></td>
                </tr>
                <tr>
                    <td style="width: 70%"></td>
                    <th>Total</th>
                    <td><b>{{ 'US $'.$orders->paid }}</b></td>
                </tr>
            </table>
        </div>
    </div>
</body>
